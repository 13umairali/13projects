<?php

namespace App\Http\Controllers;

use App\Models\OauthAccessToken;
use App\Models\OauthRefreshToken;
use App\Models\User;
use App\Models\UserLoginSession;
use App\Support\LoginSessionKey;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Http\Request;
use App\Models\Setting;

class SecuritySettingsController extends Controller
{
    

    public function sessions(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'login_device_management', Setting::class);

        $user = $request->user('api');
        $currentSessionKey = $this->resolveCurrentSessionKey($request, (int) $user->id, $user->token());

        $tokens = OauthAccessToken::query()
            ->where('revoked', '=', 0)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('created_at')
            ->get(['id', 'user_id', 'created_at', 'expires_at']);

        $tokenSessionMap = UserLoginSession::query()
            ->whereIn('access_token_id', $tokens->pluck('id')->all())
            ->get()
            ->keyBy('access_token_id');

        $userIds = $tokens->pluck('user_id')
            ->merge(UserLoginSession::query()
                ->whereNull('revoked_at')
                ->where('access_token_id', 'like', 'cookie:%')
                ->pluck('user_id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $userMap = User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'firstname', 'lastname', 'username'])
            ->keyBy('id');

        $resolveUserName = function ($userId) use ($userMap) {
            $u = $userMap->get((int) $userId);
            if (! $u) {
                return null;
            }
            $full = trim(((string) $u->firstname).' '.((string) $u->lastname));
            return $full !== '' ? $full : (string) $u->username;
        };

        $tokenSessions = $tokens->map(function ($token) use ($tokenSessionMap, $currentSessionKey, $resolveUserName) {
            $s = $tokenSessionMap->get($token->id);
            $ua = $s ? (string) ($s->user_agent ?? '') : '';

            return [
                'token_id' => (string) $token->id,
                'user_id' => (int) $token->user_id,
                'user_name' => $resolveUserName($token->user_id),
                'device' => $this->formatDeviceFromUserAgent($ua),
                'ip_address' => $s ? ($s->ip_address ?? null) : null,
                'login_at' => $token->created_at,
                'last_activity_at' => $s ? ($s->last_activity_at ?? null) : null,
                'is_current' => $currentSessionKey !== null && (string) $token->id === $currentSessionKey,
                'expires_at' => $token->expires_at,
            ];
        })->values();

        // Cookie-auth sessions (Passport TransientToken): stored with key
        // "cookie:<sha256(csrf)>". This is what a normal admin login produces --
        // the cookie guard issues no oauth_access_tokens row -- so in practice
        // these ARE the rows this screen lists.
        //
        // A browser that is simply closed never tells us so; drop rows that have
        // been idle longer than the web session they stand for could survive, or
        // the list would keep growing with sessions that cannot be used again.
        $idleCutoff = now()->subMinutes(max((int) config('session.lifetime', 120), 1));

        $cookieSessions = UserLoginSession::query()
            ->whereNull('revoked_at')
            ->where('access_token_id', 'like', 'cookie:%')
            ->where(function ($q) use ($idleCutoff) {
                $q->where('last_activity_at', '>=', $idleCutoff)
                    ->orWhere(function ($q2) use ($idleCutoff) {
                        $q2->whereNull('last_activity_at')
                            ->where('logged_in_at', '>=', $idleCutoff);
                    });
            })
            ->orderByDesc('last_activity_at')
            ->get(['user_id', 'access_token_id', 'ip_address', 'user_agent', 'logged_in_at', 'last_activity_at'])
            ->map(function ($s) use ($currentSessionKey, $resolveUserName) {
                $ua = (string) ($s->user_agent ?? '');
                return [
                    'token_id' => (string) $s->access_token_id,
                    'user_id' => (int) $s->user_id,
                    'user_name' => $resolveUserName($s->user_id),
                    'device' => $this->formatDeviceFromUserAgent($ua),
                    'ip_address' => $s->ip_address ?? null,
                    'login_at' => $s->logged_in_at,
                    'last_activity_at' => $s->last_activity_at,
                    'is_current' => $currentSessionKey !== null && (string) $s->access_token_id === $currentSessionKey,
                    'expires_at' => null,
                ];
            })
            ->values();

        $sessions = $cookieSessions->concat($tokenSessions)->values();

        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    public function logoutSession(Request $request, string $tokenId)
    {
        $this->authorizeForUser($request->user('api'), 'login_device_management', Setting::class);

        $user = $request->user('api');
        $currentSessionKey = $this->resolveCurrentSessionKey($request, (int) $user->id, $user->token());

        if ($currentSessionKey !== null && $tokenId === $currentSessionKey) {
            return response()->json([
                'message' => 'You cannot logout the current session from this list.',
            ], 422);
        }

        // Cookie-auth session: just mark it revoked; EnforceApiTokenTimeout will block it.
        if (str_starts_with($tokenId, 'cookie:')) {
            $session = UserLoginSession::query()
                ->where('access_token_id', $tokenId)
                ->first();

            if (! $session) {
                return response()->json(['message' => 'Session not found.'], 404);
            }

            $session->revoked_at = now();
            $session->save();

            return response()->json(['success' => true]);
        }

        $token = OauthAccessToken::query()
            ->where('id', $tokenId)
            ->first();

        if (! $token) {
            return response()->json(['message' => 'Session not found.'], 404);
        }

        OauthAccessToken::query()->where('id', $tokenId)->update([
            'revoked' => 1,
            'updated_at' => now(),
        ]);
        OauthRefreshToken::query()->where('access_token_id', $tokenId)->update([
            'revoked' => 1,
        ]);

        UserLoginSession::query()->updateOrCreate(
            ['access_token_id' => $tokenId],
            [
                'user_id' => (int) $token->user_id,
                'revoked_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function logoutAllOtherDevices(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'login_device_management', Setting::class);

        $user = $request->user('api');
        $currentSessionKey = $this->resolveCurrentSessionKey($request, (int) $user->id, $user->token());

        if ($currentSessionKey === null) {
            return response()->json(['message' => 'Unable to identify the current session.'], 422);
        }

        $otherTokenIds = OauthAccessToken::query()
            ->where('user_id', $user->id)
            ->where('revoked', '=', 0)
            ->where('id', '!=', $currentSessionKey)
            ->pluck('id')
            ->map(fn ($v) => (string) $v)
            ->all();

        // Revoke other cookie-auth sessions too
        $revokedCookieCount = UserLoginSession::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->where('access_token_id', 'like', 'cookie:%')
            ->where('access_token_id', '!=', $currentSessionKey)
            ->update([
                'revoked_at' => now(),
                'updated_at' => now(),
            ]);

        if (empty($otherTokenIds) && ! $revokedCookieCount) {
            return response()->json(['success' => true, 'revoked' => 0]);
        }

        if (! empty($otherTokenIds)) {
            OauthAccessToken::query()->whereIn('id', $otherTokenIds)->update([
                'revoked' => 1,
                'updated_at' => now(),
            ]);
            OauthRefreshToken::query()->whereIn('access_token_id', $otherTokenIds)->update([
                'revoked' => 1,
            ]);
            UserLoginSession::query()->whereIn('access_token_id', $otherTokenIds)->update([
                'revoked_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['success' => true, 'revoked' => (count($otherTokenIds) + (int) $revokedCookieCount)]);
    }

    private function resolveCurrentSessionKey(Request $request, int $userId, $passportToken): ?string
    {
        // Personal / password-grant token: the oauth_access_tokens id is the key.
        if ($passportToken instanceof EloquentModel) {
            $k = (string) $passportToken->getKey();

            return $k !== '' ? $k : null;
        }

        // Cookie guard (TransientToken): derive the key from the Passport cookie.
        return LoginSessionKey::fromPassportCookie($request, $userId);
    }

    /**
     * Get login activity report - shows all login sessions including historical ones
     */
    public function loginActivityReport(Request $request)
    {
        // Use login_device_management permission for now, or you can create a separate permission
        $this->authorizeForUser($request->user('api'), 'report_device_management', Setting::class);

        $user = $request->user('api');
        $currentSessionKey = $this->resolveCurrentSessionKey($request, (int) $user->id, $user->token());

        // Pagination parameters
        $perPage = $request->get('limit', 50);
        $pageStart = (int) $request->get('page', 1);

        // Search + sort sent by the report page (columns map to DB fields).
        $sortMap = [
            'login_at' => 'logged_in_at',
            'last_activity_at' => 'last_activity_at',
            'ip_address' => 'ip_address',
            'device' => 'user_agent',
        ];
        $sortField = $sortMap[(string) $request->input('SortField', 'login_at')] ?? 'logged_in_at';
        $sortType = strtolower((string) $request->input('SortType', 'desc')) === 'asc' ? 'asc' : 'desc';

        $base = UserLoginSession::query()
            ->where('user_id', $user->id)
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($qq) use ($s) {
                    $qq->where('ip_address', 'LIKE', "%{$s}%")
                        ->orWhere('user_agent', 'LIKE', "%{$s}%");
                });
            });

        // Get total count first
        $totalRows = (clone $base)->count();

        // Build query
        $query = (clone $base)
            ->orderBy($sortField, $sortType)
            ->orderByDesc('id');

        // Handle "ALL" option (-1) - show all records without pagination
        if ($perPage == '-1' || $perPage == -1) {
            // Don't apply offset/limit when showing all records
            $allSessions = $query->get();
        } else {
            // Apply pagination
            $perPage = (int) $perPage;
            $offSet = ($pageStart * $perPage) - $perPage;
            $allSessions = $query->offset($offSet)->limit($perPage)->get();
        }

        $sessions = $allSessions->map(function ($s) use ($currentSessionKey) {
            $ua = (string) ($s->user_agent ?? '');
            $isActive = false;
            
            // Check if session is active (not revoked)
            if ($s->revoked_at === null) {
                // For cookie sessions
                if (str_starts_with($s->access_token_id, 'cookie:')) {
                    $isActive = true;
                } else {
                    // For token sessions, check if token exists and is not revoked
                    $token = OauthAccessToken::query()
                        ->where('id', $s->access_token_id)
                        ->where('revoked', '=', 0)
                        ->where(function ($q) {
                            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                        })
                        ->first();
                    $isActive = $token !== null;
                }
            }

            return [
                'token_id' => (string) $s->access_token_id,
                'device' => $this->formatDeviceFromUserAgent($ua),
                'ip_address' => $s->ip_address ?? null,
                'login_at' => $s->logged_in_at,
                'last_activity_at' => $s->last_activity_at,
                'revoked_at' => $s->revoked_at,
                'is_active' => $isActive,
                'is_current' => $currentSessionKey !== null && (string) $s->access_token_id === $currentSessionKey,
            ];
        })->values();

        return response()->json([
            'sessions' => $sessions,
            'totalRows' => $totalRows,
        ]);
    }

    private function formatDeviceFromUserAgent(string $ua): string
    {
        $uaLower = strtolower($ua);
        if ($uaLower === '') {
            return 'Unknown device';
        }

        $platform = 'Unknown OS';
        if (str_contains($uaLower, 'windows')) {
            $platform = 'Windows';
        } elseif (str_contains($uaLower, 'mac os') || str_contains($uaLower, 'macintosh')) {
            $platform = 'macOS';
        } elseif (str_contains($uaLower, 'android')) {
            $platform = 'Android';
        } elseif (str_contains($uaLower, 'iphone') || str_contains($uaLower, 'ipad') || str_contains($uaLower, 'ios')) {
            $platform = 'iOS';
        } elseif (str_contains($uaLower, 'linux')) {
            $platform = 'Linux';
        }

        $browser = 'Unknown Browser';
        if (str_contains($uaLower, 'edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($uaLower, 'opr/') || str_contains($uaLower, 'opera')) {
            $browser = 'Opera';
        } elseif (str_contains($uaLower, 'chrome/') && ! str_contains($uaLower, 'edg/')) {
            $browser = 'Chrome';
        } elseif (str_contains($uaLower, 'safari/') && ! str_contains($uaLower, 'chrome/')) {
            $browser = 'Safari';
        } elseif (str_contains($uaLower, 'firefox/')) {
            $browser = 'Firefox';
        } elseif (str_contains($uaLower, 'msie') || str_contains($uaLower, 'trident/')) {
            $browser = 'Internet Explorer';
        }

        return $browser.' on '.$platform;
    }
}




