<?php

namespace App\Services\Setup;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Proves that a candidate URL is a reachable, working Mighty School backend.
 *
 * Single responsibility: probe and report. It never writes config and never
 * decides what happens next — {@see SetupManager} owns that.
 *
 * Backend contract (mighty-school-backend-api/routes/api.php):
 *   GET {url}/api/check-status -> {"status": true, "message": "..."}
 *
 * That endpoint is public, and it opens a PDO connection before answering, so a
 * 200 with status:true proves three things at once: the host is reachable, it is
 * running this API (not merely *some* web server), and its database is up.
 * A generic /health probe would only prove the first.
 *
 * Every failure mode below is deliberately distinguished, because "could not
 * connect" is useless to someone installing the product — they need to know
 * whether to fix a typo, a firewall, a certificate or the backend itself.
 */
class BackendConnectionTester
{
    /**
     * Probe the URL. Returns a result rather than throwing: an unreachable
     * backend is the expected answer half the time during installation.
     */
    public function test(string $url): ConnectionResult
    {
        $url = $this->normalize($url);

        if (! $this->isValidUrl($url)) {
            return ConnectionResult::failure(
                'That is not a valid URL.',
                'Use the full address including the scheme, for example http://127.0.0.1:8000'
            );
        }

        $endpoint = $this->endpoint($url);
        $startedAt = microtime(true);

        try {
            $response = $this->request()->get($endpoint);
        } catch (ConnectionException $e) {
            return $this->explainConnectionFailure($e, $url);
        } catch (Throwable $e) {
            return ConnectionResult::failure(
                'The connection attempt failed unexpectedly.',
                $this->firstLine($e->getMessage())
            );
        }

        $latency = (int) round((microtime(true) - $startedAt) * 1000);

        return $this->interpret($response, $url, $latency);
    }

    /**
     * Trim whitespace and drop trailing slashes so the value we probe is byte
     * for byte the value we later persist — testing one URL and saving a
     * different one is how an installer passes and the app still breaks.
     */
    public function normalize(string $url): string
    {
        return rtrim(trim($url), '/');
    }

    /**
     * A syntactically valid absolute http(s) URL with a host.
     *
     * FILTER_VALIDATE_URL alone accepts schemes we cannot speak (ftp://, and on
     * some builds javascript:), so the scheme is checked explicitly.
     */
    public function isValidUrl(string $url): bool
    {
        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true)
            && filled(parse_url($url, PHP_URL_HOST));
    }

    /* -----------------------------------------------------------------
     |  Internals
     | ----------------------------------------------------------------- */

    protected function request(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout(config('saas.setup.timeout', 8))
            ->connectTimeout(config('saas.setup.connect_timeout', 5))
            // No retries: the operator is watching a spinner. Fail fast and
            // tell them what is wrong instead of tripling the wait.
            ->withHeaders(['X-Domain' => request()?->getHost()]);
    }

    protected function endpoint(string $url): string
    {
        $prefix = trim((string) config('saas.api.prefix', 'api'), '/');
        $health = trim((string) config('saas.setup.health_endpoint', 'check-status'), '/');

        return $url.'/'.$prefix.'/'.$health;
    }

    /**
     * Turn a live response into a verdict. Order matters: a 200 that is not
     * JSON is a different problem from a 404, and both are different from a 500.
     */
    protected function interpret(Response $response, string $url, int $latency): ConnectionResult
    {
        $status = $response->status();

        if ($status === 404) {
            return ConnectionResult::failure(
                'Reached the server, but the Mighty School API is not at this address.',
                "{$this->endpoint($url)} returned 404. Check that the URL points at the backend's root (no /api suffix) and that its routes are cached correctly.",
                $status
            );
        }

        if (in_array($status, [401, 403], true)) {
            return ConnectionResult::failure(
                'The server refused the request.',
                'The health endpoint must be publicly reachable. Remove any HTTP auth, IP allow-list or firewall rule shielding it.',
                $status
            );
        }

        if ($response->serverError()) {
            return ConnectionResult::failure(
                "The backend answered with an error (HTTP {$status}).",
                'The URL is correct but the backend itself is failing. Check its logs — most often a database connection or a missing APP_KEY.',
                $status
            );
        }

        if (! $response->successful()) {
            return ConnectionResult::failure(
                "Unexpected response from the server (HTTP {$status}).",
                'Confirm the URL points directly at the backend and not at a proxy, cache or redirect in front of it.',
                $status
            );
        }

        $body = $response->json();

        if (! is_array($body)) {
            return ConnectionResult::failure(
                'The server replied, but not with JSON.',
                'This address is probably a website or a proxy rather than the Mighty School API. Double-check the host and port.',
                $status
            );
        }

        // The endpoint returns 200 with status:false when the backend is up but
        // its own database is not — a real failure the operator must fix now,
        // not after the panel is live and every page is empty.
        if (array_key_exists('status', $body) && $body['status'] === false) {
            return ConnectionResult::failure(
                'The backend is reachable but reports a problem.',
                $this->firstLine((string) ($body['message'] ?? 'Its database connection failed. Check the backend .env credentials.')),
                $status
            );
        }

        if (! array_key_exists('status', $body)) {
            return ConnectionResult::failure(
                'The server replied with JSON that this API would not produce.',
                'Make sure the URL is the Mighty School backend root and nothing else is answering on that port.',
                $status
            );
        }

        return ConnectionResult::success(
            $this->firstLine((string) ($body['message'] ?? 'Connected to the backend API successfully.')),
            $status,
            $latency
        );
    }

    /**
     * Map a transport failure to something actionable.
     *
     * Laravel wraps Guzzle's ConnectException, whose message carries the cURL
     * error number. Matching on those numbers is what lets us tell DNS from a
     * refused port from an expired certificate — all of which surface here as
     * the same exception class.
     */
    protected function explainConnectionFailure(ConnectionException $e, string $url): ConnectionResult
    {
        $message = $e->getMessage();
        $host = (string) parse_url($url, PHP_URL_HOST);

        return match (true) {
            // 6/7 resolve, 28 timeout, 35/58/60/77/83 TLS, 7 refused.
            $this->matches($message, ['cURL error 6', 'Could not resolve host', 'name or service not known']) => ConnectionResult::failure(
                "The hostname '{$host}' could not be resolved.",
                'Check the spelling, or use an IP address if this host has no DNS record.'
            ),

            $this->matches($message, ['cURL error 28', 'Operation timed out', 'timed out']) => ConnectionResult::failure(
                'The server did not respond in time.',
                "It may be starting up, overloaded, or a firewall may be silently dropping traffic to {$host}."
            ),

            $this->matches($message, ['cURL error 60', 'certificate verify failed', 'SSL certificate problem', 'unable to get local issuer']) => ConnectionResult::failure(
                "The HTTPS certificate for '{$host}' could not be verified.",
                'Install a valid certificate, or use http:// if this backend is on a trusted private network.'
            ),

            $this->matches($message, ['cURL error 35', 'SSL connect error', 'wrong version number', 'handshake']) => ConnectionResult::failure(
                'The secure connection could not be negotiated.',
                'This address may be serving plain HTTP. Try http:// instead of https://.'
            ),

            $this->matches($message, ['cURL error 7', 'Connection refused', 'Failed to connect']) => ConnectionResult::failure(
                "Nothing is accepting connections at {$host}.",
                'Confirm the backend is running and that the port is correct and open.'
            ),

            $this->matches($message, ['Network is unreachable', 'cURL error 45']) => ConnectionResult::failure(
                'The network is unreachable from this server.',
                'This machine has no route to the backend. Check its network configuration.'
            ),

            default => ConnectionResult::failure(
                'Could not connect to the backend.',
                $this->firstLine($message)
            ),
        };
    }

    /** @param  list<string>  $needles */
    protected function matches(string $haystack, array $needles): bool
    {
        return Str::contains($haystack, $needles, ignoreCase: true);
    }

    /**
     * Guzzle messages run to several lines and repeat the full URL; the card
     * shows one line, so keep the part that identifies the fault.
     */
    protected function firstLine(string $message): string
    {
        $line = trim(strtok($message, "\n") ?: $message);

        return Str::limit($line, 180);
    }
}
