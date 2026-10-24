<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ImpersonationController;
use App\Models\Central\ImpersonationLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Appends state-changing requests to the active impersonation audit log.
 *
 * Runs on every tenant request (web + api) but is effectively free unless an
 * impersonation session is active for the tenant: GET/HEAD requests return
 * immediately, and the tenant-cache flag gates the central-DB lookup.
 *
 * Web requests are correlated via the session marker; SPA/API requests
 * (Passport cookie auth, no session) fall back to the authenticated user.
 */
class LogImpersonationActions
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
                return $response;
            }

            if (! function_exists('tenancy') || ! tenancy()->initialized) {
                return $response;
            }

            if (! Cache::get(ImpersonationController::CACHE_FLAG)) {
                return $response;
            }

            $path = '/' . ltrim($request->path(), '/');

            // The impersonation endpoints themselves are lifecycle events, not actions.
            if (str_starts_with($path, '/impersonate')) {
                return $response;
            }

            $log = $this->resolveLog($request);

            if ($log && $log->isActive()) {
                $log->recordAction($request->method(), $path, $response->getStatusCode());
            }
        } catch (\Throwable $e) {
            // Auditing must never break the request.
        }

        return $response;
    }

    private function resolveLog(Request $request): ?ImpersonationLog
    {
        // Web request: exact correlation via the session marker.
        if ($request->hasSession() && $request->session()->has(ImpersonationController::SESSION_KEY)) {
            $logId = $request->session()->get(ImpersonationController::SESSION_KEY)['log_id'] ?? null;

            return $logId ? ImpersonationLog::find($logId) : null;
        }

        // API request (Passport cookie, no session): correlate by tenant + user.
        $user = Auth::guard('web')->user() ?? Auth::guard('api')->user();

        if (! $user) {
            return null;
        }

        return ImpersonationLog::where('tenant_id', (string) tenant('id'))
            ->where('user_id', $user->id)
            ->where('status', ImpersonationLog::STATUS_ACTIVE)
            ->latest('id')
            ->first();
    }
}
