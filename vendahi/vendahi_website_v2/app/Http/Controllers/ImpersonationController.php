<?php

namespace App\Http\Controllers;

use App\Models\Central\ImpersonationLog;
use App\Models\User;
use App\Models\UserLoginSession;
use App\Support\LoginSessionKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Tenant side of super-admin impersonation ("login as user").
 *
 * start(): exchanges a single-use, short-lived token (issued on the central
 * domain) for a normal tenant web session — the existing auth guard, session
 * and middleware stack are reused, so the impersonated session behaves exactly
 * like a regular login without ever touching the user's password.
 *
 * leave(): ends the impersonated session and returns the admin to the
 * central super panel (their central session cookie is still valid there).
 */
class ImpersonationController extends Controller
{
    /** Session key holding the impersonation state. */
    public const SESSION_KEY = 'impersonation';

    /** Tenant-cache flag so the action-logging middleware stays free when idle. */
    public const CACHE_FLAG = 'impersonation_active';

    /**
     * GET /impersonate/{token}
     */
    public function start(Request $request, string $token)
    {
        abort_unless(strlen($token) === 64 && ctype_alnum($token), 404);

        $log = ImpersonationLog::where('token_hash', hash('sha256', $token))->first();

        if (! $log
            || $log->status !== ImpersonationLog::STATUS_PENDING
            || (string) $log->tenant_id !== (string) tenant('id')) {
            abort(403, 'Invalid impersonation link.');
        }

        // Single hop from the central redirect — expire quickly.
        if ($log->created_at->lt(now()->subSeconds(ImpersonationLog::TOKEN_TTL))) {
            $log->forceFill(['status' => ImpersonationLog::STATUS_EXPIRED, 'token_hash' => null])->save();
            abort(403, 'This impersonation link has expired.');
        }

        $user = User::find($log->user_id);
        if (! $user || (int) $user->statut !== 1) {
            $log->forceFill(['status' => ImpersonationLog::STATUS_FAILED, 'token_hash' => null])->save();
            abort(403, 'This user can no longer be impersonated.');
        }

        // If this browser is already impersonating someone on this tenant, close that trail first.
        $previous = $request->session()->get(self::SESSION_KEY);
        if (! empty($previous['log_id'])) {
            $old = ImpersonationLog::find($previous['log_id']);
            if ($old && $old->isActive()) {
                $old->end('exit');
            }
        }

        // Never "remember" an impersonated session.
        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        $request->session()->put(self::SESSION_KEY, [
            'log_id'     => $log->id,
            'admin_name' => $log->admin_name,
        ]);

        // Mirror LoginController: track the web session so revocation keeps working.
        try {
            $sessionKey = LoginSessionKey::fromSession($request);
            if ($sessionKey) {
                UserLoginSession::query()->updateOrCreate(
                    ['access_token_id' => $sessionKey],
                    [
                        'user_id'          => (int) $user->id,
                        'session_id'       => $request->session()->getId(),
                        'ip_address'       => $request->ip(),
                        'user_agent'       => (string) ($request->userAgent() ?? ''),
                        'logged_in_at'     => now(),
                        'last_activity_at' => now(),
                        'revoked_at'       => null,
                    ]
                );
            }
        } catch (\Throwable $e) {
            // Never break impersonation if tracking fails
        }

        $log->forceFill([
            'token_hash'       => null,
            'status'           => ImpersonationLog::STATUS_ACTIVE,
            'started_at'       => now(),
            'last_activity_at' => now(),
        ])->save();

        // Cheap signal for the action-logging middleware (tenant-prefixed cache).
        Cache::put(self::CACHE_FLAG, true, now()->addHours(ImpersonationLog::STALE_HOURS));

        return redirect('/app/dashboard');
    }

    /**
     * POST /impersonate/leave
     */
    public function leave(Request $request)
    {
        $state = $request->session()->get(self::SESSION_KEY);

        if (! empty($state['log_id'])) {
            $log = ImpersonationLog::find($state['log_id']);
            if ($log && $log->isActive()) {
                $log->end('exit');
            }
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        static::clearCacheFlagIfIdle();

        // Back to the central super panel; the admin's central session cookie
        // lives on the central domain and is unaffected by this logout.
        return redirect()->away(
            rtrim(config('app.url'), '/') . '/super/tenants/' . tenant('id') . '/users'
        );
    }

    /**
     * Drop the tenant-cache flag once no impersonation session is active
     * for this tenant anymore.
     */
    public static function clearCacheFlagIfIdle(): void
    {
        try {
            $stillActive = ImpersonationLog::where('tenant_id', (string) tenant('id'))
                ->where('status', ImpersonationLog::STATUS_ACTIVE)
                ->exists();

            if (! $stillActive) {
                Cache::forget(self::CACHE_FLAG);
            }
        } catch (\Throwable $e) {
            // best effort only
        }
    }
}
