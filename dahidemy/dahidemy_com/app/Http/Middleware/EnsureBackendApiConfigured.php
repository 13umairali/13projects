<?php

namespace App\Http\Middleware;

use App\Services\Setup\SetupManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks the entire panel until the backend API URL is configured.
 *
 * The panel is a pure API client: with no backend URL every page is an empty
 * shell and every login fails against a host that is not there. Rather than
 * degrade, we send the operator to the installer.
 *
 * Runs on the web group only. API-shaped and asset requests are answered
 * directly (see below) instead of being redirected into an HTML page.
 */
class EnsureBackendApiConfigured
{
    /**
     * Paths that must keep working while the panel is unconfigured.
     *
     * The setup wizard itself, the assets it is built from, and the health
     * check — a load balancer must not conclude the container is dead just
     * because it is waiting to be installed.
     *
     * @var list<string>
     */
    protected array $except = [
        'setup',
        'setup/*',
        'up',
        'build/*',
        'storage/*',
        'vendor/*',
        'favicon.ico',
        'robots.txt',
    ];

    public function __construct(
        protected SetupManager $setup
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->setup->isConfigured() || $this->shouldSkip($request)) {
            return $next($request);
        }

        // A fetch/XHR caller cannot follow a redirect to an HTML installer in
        // any useful way — it would parse the page as its payload. Tell it what
        // is actually wrong and where to go.
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => 'This installation is not configured yet.',
                'setup_url' => route('setup.show'),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return redirect()->route('setup.show');
    }

    protected function shouldSkip(Request $request): bool
    {
        // Files served straight off the public root (built assets, uploads,
        // favicons). Under most SAPIs these never reach PHP, but they do with
        // `artisan serve` and some shared hosts — and a redirected stylesheet
        // renders the installer unstyled.
        if ($request->is(...$this->except)) {
            return true;
        }

        return $request->isMethod('GET')
            && (bool) pathinfo((string) $request->path(), PATHINFO_EXTENSION);
    }
}
