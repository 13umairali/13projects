<?php

namespace App\Services\Shopify;

use App\Models\ShopifyStore;
use Carbon\Carbon;

/**
 * The Admin API token a store should be called with right now.
 *
 * Two kinds of store exist:
 *
 *  - Legacy custom apps (created in the Shopify admin before 2026) have a
 *    permanent `shpat_` token pasted in by hand. It is returned as-is.
 *  - Dev Dashboard apps hand out a client id + client secret instead. Shopify
 *    swaps the pair for a token via the client credentials grant, and that
 *    token expires after 24 hours. The last one fetched is cached on the store
 *    row (`access_token` + `token_expires_at`) and swapped for a fresh one
 *    shortly before it runs out, so a long sync started at 23:58 does not die
 *    at midnight.
 *
 * Nothing else in the module needs to know which kind it is talking to:
 * Client::forStore() calls this and gets a token either way.
 */
class AccessToken
{
    /** Fetch a new token this many seconds before the current one expires. */
    public const REFRESH_MARGIN_SECONDS = 300;

    /** Shopify's grant returns 86399; used only when the response omits it. */
    private const DEFAULT_LIFETIME_SECONDS = 86399;

    /**
     * The token to use, fetching a fresh one first when the cached one is
     * missing or about to expire.
     *
     * @throws \RuntimeException when Shopify refuses the client credentials
     */
    public static function for(ShopifyStore $store): string
    {
        if (! $store->usesClientCredentials()) {
            return trim((string) $store->access_token);
        }

        if (! self::needsRefresh($store)) {
            return trim((string) $store->access_token);
        }

        return self::refresh($store);
    }

    /** Whether the cached token is absent, expired, or inside the refresh margin. */
    public static function needsRefresh(ShopifyStore $store): bool
    {
        if (trim((string) $store->access_token) === '') {
            return true;
        }
        if (! $store->token_expires_at) {
            return true;
        }

        return Carbon::now()->addSeconds(self::REFRESH_MARGIN_SECONDS)->gte($store->token_expires_at);
    }

    /**
     * Exchange the store's client credentials for a new token and cache it.
     *
     * @throws \RuntimeException
     */
    public static function refresh(ShopifyStore $store): string
    {
        $result = self::exchange($store->shop_domain, (string) $store->client_id, (string) $store->client_secret);

        if (empty($result['ok'])) {
            throw new \RuntimeException($result['error'] ?? 'Shopify did not issue an access token.');
        }

        $store->forceFill([
            'access_token' => $result['access_token'],
            'token_expires_at' => Carbon::now()->addSeconds($result['expires_in']),
        ])->save();

        return $result['access_token'];
    }

    /**
     * The raw client credentials grant, with nothing cached.
     *
     * Returns ['ok' => true, 'access_token' => ..., 'expires_in' => int, 'scope' => string]
     * or ['ok' => false, 'error' => string].
     */
    public static function exchange(string $shopDomain, string $clientId, string $clientSecret): array
    {
        $domain = ShopifyStore::normaliseDomain($shopDomain);
        $clientId = trim($clientId);
        $clientSecret = trim($clientSecret);

        if ($domain === '' || $clientId === '' || $clientSecret === '') {
            return ['ok' => false, 'error' => 'A shop domain, client ID and client secret are all required.'];
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://'.$domain.'/admin/oauth/access_token',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]),
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: Stocky-ERP-Shopify-Integration',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => 20000,
            CURLOPT_CONNECTTIMEOUT_MS => 10000,
            CURLOPT_NOSIGNAL => 1,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ]);

        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = (int) curl_errno($ch);
        $error = (string) curl_error($ch);
        curl_close($ch);

        if ($errno !== 0 || $body === false) {
            return ['ok' => false, 'error' => 'Could not reach Shopify to request a token: '.($error ?: 'curl error '.$errno)];
        }

        $json = json_decode((string) $body, true);
        $json = is_array($json) ? $json : [];

        if ($status < 200 || $status >= 300 || empty($json['access_token'])) {
            return ['ok' => false, 'error' => self::explain($status, $json, (string) $body)];
        }

        return [
            'ok' => true,
            'access_token' => (string) $json['access_token'],
            'expires_in' => max(60, (int) ($json['expires_in'] ?? self::DEFAULT_LIFETIME_SECONDS)),
            'scope' => (string) ($json['scope'] ?? ''),
        ];
    }

    /**
     * Name the failures people actually hit. The grant only works when the app
     * and the shop sit in the same Dev Dashboard organisation, and that rule is
     * the one nobody expects.
     */
    private static function explain(int $status, array $json, string $body): string
    {
        $detail = '';
        foreach (['error_description', 'error', 'errors'] as $key) {
            if (! empty($json[$key])) {
                $detail = is_string($json[$key]) ? $json[$key] : json_encode($json[$key], JSON_UNESCAPED_SLASHES);
                break;
            }
        }

        if (in_array($status, [400, 401], true)) {
            return 'Shopify rejected the client credentials ('.$status.'). Check the client ID and client secret, '
                .'that the app is installed on this shop, and that the app and the shop belong to the same '
                .'Dev Dashboard organisation.'.($detail !== '' ? ' Shopify said: '.$detail : '');
        }
        if ($status === 404) {
            return 'Shopify returned 404 for the token request. Check the shop domain.';
        }
        if ($status === 423) {
            return 'The shop is locked or frozen (423).';
        }

        if ($detail !== '') {
            return $detail;
        }

        $body = trim($body);

        return $body !== '' ? mb_substr($body, 0, 500) : ('Token request failed with HTTP '.$status);
    }
}
