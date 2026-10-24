<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to platform (SAAS) administrators.
 *
 * Platform-level endpoints (institutes, plans, subscriptions, mail config, cache/storage
 * utilities, ...) used to sit behind `auth:api` only, so ANY valid API token - including one
 * belonging to a student or to an account created through public registration - could call them.
 *
 * SAAS Admin accounts are, by design, the only accounts that have no institute_id.
 */
class EnsureSaasAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('api') ?? $request->user();

        if (! $user || ($user->user_type ?? null) !== 'SAAS Admin' || ! empty($user->institute_id)) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'This action is restricted to platform administrators.',
            ], 403);
        }

        return $next($request);
    }
}
