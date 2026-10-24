<?php

namespace App\Services\Shopify;

use App\Models\ShopifyLog;
use App\Models\ShopifyStore;
use App\Services\Shopify\Client as Api;

/**
 * Subscribing a shop to the webhooks this ERP knows how to handle.
 *
 * Until now the module could receive webhooks but never asked for any: the
 * operator had to create nine subscriptions by hand in the Shopify admin and
 * get every topic name and the callback URL exactly right. This does it in one
 * call, and — more usefully — can say what a shop is currently subscribed to,
 * which is the question you actually have when orders stop arriving.
 *
 * One thing it cannot do: hand you the signing secret. Webhooks created
 * through the Admin API are signed with the *app's* API secret key, which
 * Shopify only shows on the custom app's page. So registration reminds the
 * operator to paste that into the store when it is missing, rather than
 * pretending the shop is ready.
 */
class WebhookManager
{
    private ShopifyStore $store;

    private Api $client;

    public function __construct(ShopifyStore $store)
    {
        $this->store = $store;
        $this->client = Api::forStore($store);
    }

    public static function for(ShopifyStore $store): self
    {
        return new self($store);
    }

    /**
     * Where Shopify should post: this TENANT's own public URL.
     *
     * Which tenant handles an inbound webhook is decided by the host it
     * arrives on, so the host in the URL Shopify is handed is the whole of the
     * routing. In a web request `url()` already builds it from the host the
     * admin is looking at, which is the tenant's — registration happens from
     * the tenant's own admin, so that is the normal path. On the CLI there is
     * no request and `url()` falls back to APP_URL, the central domain, which
     * belongs to no shop at all; there the tenant's domain is read directly.
     */
    public static function callbackUrl(): string
    {
        if (app()->runningInConsole() && tenancy()->initialized) {
            $base = rtrim((string) tenant()->getTenantUrl(), '/');

            if ($base !== '') {
                return $base.'/api/shopify/webhook';
            }
        }

        return url('/api/shopify/webhook');
    }

    /**
     * The topics this store should be subscribed to: the ones the handler
     * understands, minus those whose entity it has switched off. No point
     * receiving product updates for a shop that does not sync products — every
     * delivery would be stored and then ignored.
     *
     * @return array<int, string>
     */
    public function wantedTopics(): array
    {
        $wanted = [];

        foreach (ShopifyStore::WEBHOOK_TOPICS as $topic => $entity) {
            if ($this->store->syncs($entity)) {
                $wanted[] = $topic;
            }
        }

        return $wanted;
    }

    /**
     * What the shop has now, annotated with what it means to us.
     *
     * `mine` marks a subscription pointing at this installation; a topic
     * pointing somewhere else is reported rather than touched, because it may
     * well belong to another app the merchant uses.
     *
     * @return array{ok: bool, error: ?string, webhooks: array, missing: array, stale: array}
     */
    public function inspect(): array
    {
        $result = $this->client->page('webhooks', 'webhooks', [], null, 250);

        if (! $result['ok']) {
            return ['ok' => false, 'error' => $result['error'], 'webhooks' => [], 'missing' => [], 'stale' => []];
        }

        $callback = self::callbackUrl();
        $handled = array_keys(ShopifyStore::WEBHOOK_TOPICS);
        $webhooks = [];
        $mineByTopic = [];

        foreach ($result['items'] as $hook) {
            $topic = (string) ($hook['topic'] ?? '');
            $address = (string) ($hook['address'] ?? '');
            $mine = $this->sameEndpoint($address, $callback);

            if ($mine) {
                $mineByTopic[$topic] = true;
            }

            $webhooks[] = [
                'id' => (string) ($hook['id'] ?? ''),
                'topic' => $topic,
                'address' => $address,
                'format' => $hook['format'] ?? 'json',
                'api_version' => $hook['api_version'] ?? null,
                'created_at' => $hook['created_at'] ?? null,
                'updated_at' => $hook['updated_at'] ?? null,
                'mine' => $mine,
                'handled' => in_array($topic, $handled, true),
            ];
        }

        $missing = array_values(array_diff($this->wantedTopics(), array_keys($mineByTopic)));

        // Ours, but for a topic this store no longer wants — usually left over
        // from before an entity was switched off.
        $stale = [];
        foreach ($webhooks as $hook) {
            if ($hook['mine'] && ! in_array($hook['topic'], $this->wantedTopics(), true)) {
                $stale[] = $hook;
            }
        }

        return ['ok' => true, 'error' => null, 'webhooks' => $webhooks, 'missing' => $missing, 'stale' => $stale];
    }

    /**
     * Create whatever is missing.
     *
     * Idempotent on purpose: pressing the button twice is a no-op, not a
     * second set of subscriptions delivering every order twice. (The ERP would
     * deduplicate those anyway, but a shop with eighteen webhooks is a mess
     * nobody wants to untangle later.)
     *
     * @param  bool  $prune  also delete our subscriptions for topics no longer wanted
     * @return array{ok: bool, created: array, existing: array, failed: array, deleted: array, error: ?string, warning: ?string}
     */
    public function register(bool $prune = false): array
    {
        $state = $this->inspect();

        if (! $state['ok']) {
            return [
                'ok' => false, 'created' => [], 'existing' => [], 'failed' => [], 'deleted' => [],
                'error' => $state['error'], 'warning' => null,
            ];
        }

        $callback = self::callbackUrl();
        $created = [];
        $failed = [];
        $deleted = [];

        foreach ($state['missing'] as $topic) {
            $res = $this->client->post('webhooks', [
                'webhook' => ['topic' => $topic, 'address' => $callback, 'format' => 'json'],
            ]);

            if ($res->successful()) {
                $created[] = $topic;

                continue;
            }

            // Shopify answers 422 "for this topic and address" when the
            // subscription already exists — which is success, not failure.
            $body = $res->body();
            if ($res->status() === 422 && stripos($body, 'already been taken') !== false) {
                $created[] = $topic;

                continue;
            }

            $failed[] = ['topic' => $topic, 'error' => $this->client->explain($res)];
        }

        if ($prune) {
            foreach ($state['stale'] as $hook) {
                $res = $this->client->delete('webhooks/'.$hook['id']);
                if ($res->successful() || $res->status() === 404) {
                    $deleted[] = $hook['topic'];
                }
            }
        }

        $existing = array_values(array_diff($this->wantedTopics(), $state['missing']));

        ShopifyLog::write($this->store->id, 'webhook.register',
            'Webhooks registered: '.(count($created) ?: 'none').' created, '.count($existing).' already present'
                .($deleted ? ', '.count($deleted).' removed' : '')
                .($failed ? ', '.count($failed).' failed' : '').'.',
            $failed ? 'warning' : 'info',
            ['created' => $created, 'failed' => $failed, 'deleted' => $deleted, 'address' => $callback],
            null, 'webhooks');

        return [
            'ok' => empty($failed),
            'created' => $created,
            'existing' => $existing,
            'failed' => $failed,
            'deleted' => $deleted,
            'error' => null,
            // The subscriptions exist but every delivery will be refused until
            // the secret is stored, so this is worth saying out loud.
            'warning' => ! $this->store->hasWebhookSecret()
                ? 'Webhooks are subscribed, but this store has no signing secret yet — deliveries will be rejected until you paste the app\'s API secret key into the store settings.'
                : null,
        ];
    }

    /** Remove one subscription by its Shopify id. */
    public function delete(string $webhookId): array
    {
        $res = $this->client->delete('webhooks/'.$webhookId);

        if (! $res->successful() && $res->status() !== 404) {
            return ['ok' => false, 'error' => $this->client->explain($res)];
        }

        ShopifyLog::write($this->store->id, 'webhook.delete',
            'Webhook '.$webhookId.' removed from '.$this->store->shop_domain.'.', 'info', [], null, 'webhooks');

        return ['ok' => true, 'error' => null];
    }

    /**
     * Whether two callback URLs are the same endpoint.
     *
     * Compared without the scheme and without a trailing slash: a shop
     * registered over http before the site got a certificate is still the same
     * subscription, and re-creating it would mean every order arriving twice.
     */
    private function sameEndpoint(string $a, string $b): bool
    {
        $normalise = static function (string $url): string {
            $url = preg_replace('#^https?://#i', '', trim($url));

            return rtrim(strtolower((string) $url), '/');
        };

        return $normalise($a) === $normalise($b);
    }
}
