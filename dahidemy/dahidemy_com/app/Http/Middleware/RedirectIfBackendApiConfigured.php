<?php

namespace App\Http\Middleware;

use App\Services\Setup\SetupManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Seals the setup wizard once the panel is installed.
 *
 * Without this the installer stays a permanent, unauthenticated endpoint that
 * rewrites .env and repoints the panel at an attacker's backend — every
 * credential typed into the login form would then be posted to them. So the
 * wizard is reachable only while the panel is unconfigured.
 *
 * Re-running it afterwards is an explicit, server-side decision: set
 * SAAS_SETUP_ENABLED=true in .env. That deliberately cannot be done from the
 * browser, which is the point — someone with .env access is already trusted.
 */
class RedirectIfBackendApiConfigured
{
    public function __construct(
        protected SetupManager $setup
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->setup->isUnlocked()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => 'This installation is already configured.',
            ], Response::HTTP_FORBIDDEN);
        }

        return redirect()->route('index');
    }
}
