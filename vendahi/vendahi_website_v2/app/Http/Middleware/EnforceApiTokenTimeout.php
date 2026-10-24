<?php

namespace App\Http\Middleware;

use App\Models\UserLoginSession;
use App\Support\LoginSessionKey;
use Closure;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Http\Request;

/**
 * Keeps user_login_sessions in step with what the API is actually serving, so
 * Settings > Login Devices shows live data:
 *
 *  - refreshes last_activity_at for the session behind this request
 *  - blocks a session an admin revoked from that screen (the api guard reads a
 *    JWT cookie, which stays valid on its own, so nothing else would stop it)
 *
 * The api route group has no session, so the current session is identified from
 * the Passport cookie -- see LoginSessionKey.
 */
class EnforceApiTokenTimeout
{
    /** Don't write on every request; last_activity_at only needs minute resolution. */
    private const TOUCH_AFTER_SECONDS = 60;

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user('api');
        if (! $user) {
            return $next($request);
        }

        $sessionKey = $this->resolveSessionKey($request, $user);
        if ($sessionKey === null) {
            return $next($request);
        }

        try {
            $session = UserLoginSession::query()
                ->where('access_token_id', $sessionKey)
                ->first();
        } catch (\Throwable $e) {
            // Table missing (setup) or DB hiccup: never block the request over tracking.
            return $next($request);
        }

        if ($session && $session->revoked_at !== null) {
            return response()->json([
                'message' => 'This device was signed out.',
            ], 409)->header('X-Session-Revoked', '1');
        }

        try {
            if (! $session) {
                // First API call of a session created before this tracking existed.
                UserLoginSession::query()->create([
                    'user_id' => (int) $user->id,
                    'access_token_id' => $sessionKey,
                    'ip_address' => $request->ip(),
                    'user_agent' => (string) ($request->userAgent() ?? ''),
                    'logged_in_at' => now(),
                    'last_activity_at' => now(),
                ]);
            } elseif (
                $session->last_activity_at === null
                || $session->last_activity_at->diffInSeconds(now()) >= self::TOUCH_AFTER_SECONDS
            ) {
                $session->forceFill(['last_activity_at' => now()])->save();
            }
        } catch (\Throwable $e) {
            // Best-effort only.
        }

        return $next($request);
    }

    private function resolveSessionKey(Request $request, $user): ?string
    {
        // Personal / password-grant token: keyed by its oauth_access_tokens id.
        $token = method_exists($user, 'token') ? $user->token() : null;
        if ($token instanceof EloquentModel) {
            $id = (string) $token->getKey();

            return $id !== '' ? $id : null;
        }

        // Cookie guard (TransientToken): keyed off the JWT's csrf claim.
        return LoginSessionKey::fromPassportCookie($request, (int) $user->id);
    }
}
