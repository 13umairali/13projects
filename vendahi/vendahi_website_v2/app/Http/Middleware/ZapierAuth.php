<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\ZapierSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Authenticates Zapier's server-to-server requests with the tenant's Zapier
 * API key (X-Api-Key header, or Authorization: Bearer). On success the
 * configured acting user is bound to the `api` guard so downstream policies
 * and warehouse/record restrictions apply exactly as they do in the app.
 */
class ZapierAuth
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('X-Api-Key') ?: $request->bearerToken();

        if (! $key) {
            return response()->json(['message' => 'Missing API key.'], 401);
        }

        $settings = ZapierSetting::query()->first();

        if (! $settings || ! $settings->enabled || empty($settings->api_key_hash)) {
            return response()->json(['message' => 'Zapier integration is not enabled.'], 403);
        }

        if (! hash_equals($settings->api_key_hash, hash('sha256', $key))) {
            return response()->json(['message' => 'Invalid API key.'], 401);
        }

        $user = $settings->user_id ? User::find($settings->user_id) : null;
        $user = $user ?: User::orderBy('id')->first();
        if (! $user) {
            return response()->json(['message' => 'No acting user configured.'], 403);
        }

        Auth::guard('api')->setUser($user);
        Auth::shouldUse('api');

        // Touch at most once a minute — Zapier polls aggressively.
        if (! $settings->last_used_at || $settings->last_used_at->lt(now()->subMinute())) {
            $settings->forceFill(['last_used_at' => now()])->saveQuietly();
        }

        return $next($request);
    }
}
