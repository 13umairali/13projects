<?php

namespace App\Services\Api;

use App\Support\ApiCache;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Throwable;

/**
 * Thin, resilient HTTP client for the Mighty School backend.
 *
 * Every concrete service (content, auth, institutes, packages, ...) extends
 * this so token handling, headers, retries, exception handling and response
 * normalisation live in exactly one place. Controllers must never call Http
 * directly — they go through a service.
 */
abstract class BaseApiService
{
    protected string $baseUrl;

    protected string $prefix;

    protected ApiCache $cache;

    public function __construct()
    {
        $this->baseUrl = config('saas.api.base_url');
        $this->prefix = trim(config('saas.api.prefix', 'api'), '/');
        $this->cache = app(ApiCache::class);
    }

    /* -----------------------------------------------------------------
     |  Public verbs
     | ----------------------------------------------------------------- */

    public function get(string $endpoint, array $query = []): ApiResponse
    {
        return $this->send('get', $endpoint, ['query' => $query]);
    }

    /**
     * A GET whose response is cached (and memoised for the rest of the request).
     * Use it for reads; never for the backend's GET-shaped writes — see action().
     *
     * Only successful responses are cached. The first failure is returned as-is
     * so the caller still sees the real message; repeat failures are served from
     * a short-lived negative cache instead of hammering a backend that is down.
     */
    public function cachedGet(string $endpoint, array $query = []): ApiResponse
    {
        $live = null;

        $cached = $this->cache->remember(
            $this->readKey($endpoint, $query),
            function () use (&$live, $endpoint, $query) {
                $live = $this->get($endpoint, $query);

                return $live->successful() ? $live->toArray() : null;
            }
        );

        if (is_array($cached)) {
            return ApiResponse::fromArray($cached);
        }

        return $live ?? ApiResponse::failure('The service is temporarily unavailable. Please try again shortly.', 503);
    }

    /**
     * A GET that mutates state on the backend (status toggles, approvals — the
     * backend models several writes as GET). Never cached, and invalidates the
     * cache on success so lists reflect the change immediately.
     */
    public function action(string $endpoint, array $query = []): ApiResponse
    {
        $response = $this->get($endpoint, $query);

        if ($response->successful()) {
            $this->cache->flush();
        }

        return $response;
    }

    public function post(string $endpoint, array $data = []): ApiResponse
    {
        return $this->send('post', $endpoint, ['json' => $data]);
    }

    public function put(string $endpoint, array $data = []): ApiResponse
    {
        return $this->send('put', $endpoint, ['json' => $data]);
    }

    public function patch(string $endpoint, array $data = []): ApiResponse
    {
        return $this->send('patch', $endpoint, ['json' => $data]);
    }

    public function delete(string $endpoint, array $data = []): ApiResponse
    {
        return $this->send('delete', $endpoint, ['json' => $data]);
    }

    /**
     * Multipart request that transparently attaches any UploadedFile values.
     */
    public function upload(string $endpoint, array $data = [], string $method = 'post'): ApiResponse
    {
        $request = $this->client();

        foreach ($data as $key => $value) {
            if ($value instanceof UploadedFile) {
                $request = $request->attach(
                    $key,
                    fopen($value->getRealPath(), 'r'),
                    $value->getClientOriginalName()
                );
                unset($data[$key]);
            }
        }

        // Laravel maps PUT/PATCH multipart to POST + _method for PHP compat.
        if (in_array(strtolower($method), ['put', 'patch'], true)) {
            $data['_method'] = strtoupper($method);
            $method = 'post';
        }

        return $this->execute(fn () => $request->{$method}($this->url($endpoint), $data), $method, $endpoint);
    }

    /* -----------------------------------------------------------------
     |  Internals
     | ----------------------------------------------------------------- */

    protected function send(string $method, string $endpoint, array $options): ApiResponse
    {
        $request = $this->client();

        return $this->execute(function () use ($request, $method, $endpoint, $options) {
            return match ($method) {
                'get' => $request->get($this->url($endpoint), $options['query'] ?? []),
                default => $request->{$method}($this->url($endpoint), $options['json'] ?? []),
            };
        }, $method, $endpoint);
    }

    protected function execute(callable $callback, string $method, string $endpoint): ApiResponse
    {
        try {
            /** @var Response $response */
            $response = $callback();

            $body = $response->json() ?? [];

            if (! is_array($body)) {
                $body = ['status' => $response->successful(), 'message' => (string) $response->body()];
            }

            // A 401 on a call we authenticated means the session's bearer token
            // is stale (expired or revoked backend-side). Drop it and bounce the
            // operator to sign in again rather than showing a panel full of
            // "service unavailable" panels. Unauthenticated calls (login,
            // public content) carry no token and are unaffected.
            if ($response->status() === 401 && $this->token()) {
                Session::forget(config('saas.auth.token_key'));
                Session::forget(config('saas.auth.user_key'));

                throw new AuthenticationException(
                    'Your session has expired. Please sign in again.',
                    ['web'],
                    route('login')
                );
            }

            $api = ApiResponse::fromEnvelope($response->status(), $body);

            // Any successful write invalidates every cached read, so a list can
            // never show a record that was just created, edited or deleted.
            if ($api->successful() && strtolower($method) !== 'get') {
                $this->cache->flush();
            }

            if ($api->failed() && $response->serverError()) {
                $this->log('warning', $method, $endpoint, $response->status(), $api->message);
            }

            return $api;
        } catch (AuthenticationException $e) {
            throw $e; // handled by the framework -> redirect to login
        } catch (Throwable $e) {
            $this->log('error', $method, $endpoint, 0, $e->getMessage());

            return ApiResponse::failure(
                'The service is temporarily unavailable. Please try again shortly.',
                503
            );
        }
    }

    protected function client(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout(config('saas.api.timeout', 12))
            ->connectTimeout(config('saas.api.connect_timeout', 5))
            ->retry(
                config('saas.api.retry_times', 2),
                config('saas.api.retry_sleep', 150),
                throw: false
            )
            ->withHeaders($this->headers());
    }

    protected function headers(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'X-Domain' => request()?->getHost() ?? parse_url(config('app.url'), PHP_URL_HOST),
        ];

        if ($token = $this->token()) {
            $headers['Authorization'] = 'Bearer '.$token;
        }

        return $headers;
    }

    /**
     * Bearer token for authenticated (admin) calls; null for public content.
     */
    protected function token(): ?string
    {
        return Session::get(config('saas.auth.token_key'));
    }

    protected function url(string $endpoint): string
    {
        $endpoint = ltrim($endpoint, '/');

        return "{$this->baseUrl}/{$this->prefix}/{$endpoint}";
    }

    /** Cache key for a read: the endpoint plus whatever query varies it. */
    protected function readKey(string $endpoint, array $query): string
    {
        $key = 'get.'.str_replace('/', '.', trim($endpoint, '/'));

        if ($query !== []) {
            ksort($query);
            $key .= '.'.md5((string) json_encode($query));
        }

        return $key;
    }

    protected function log(string $level, string $method, string $endpoint, int $status, string $message): void
    {
        Log::channel(config('logging.default'))->{$level}('[SaaS API] request failed', [
            'method' => strtoupper($method),
            'endpoint' => $endpoint,
            'status' => $status,
            'message' => $message,
        ]);
    }
}
