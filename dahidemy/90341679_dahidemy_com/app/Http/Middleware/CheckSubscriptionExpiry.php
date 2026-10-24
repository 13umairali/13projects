<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * The rule for "what happens after a trial expires": nothing previously
 * enforced this at all - CheckSubscription existed with the right logic
 * but was never actually attached to any route, so an institute could keep
 * using the whole platform indefinitely past its subscription's end_date.
 *
 * This runs globally. For an institute user whose subscription has
 * expired (or was never created), every route is blocked EXCEPT a small
 * safe-list needed to actually renew or log out - everything else
 * redirects straight to the self-service renewal page built earlier.
 *
 * Super Admin accounts (no institute_id) and guests are completely
 * unaffected.
 */
class CheckSubscriptionExpiry
{
    /**
     * Route names always reachable even with an expired subscription -
     * without these, a person could never actually pay to fix the
     * problem, or even log out.
     */
    private const ALLOWED_ROUTES = [
        'my-subscription.index',
        'my-subscription.checkout',
        'my-subscription.checkout-success',
        'my-referrals.index',
        'logout',
        'profile.edit',
        'profile.update',
        'profile.destroy',
        'language.switch',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Guests, and Super Admin accounts (no institute_id at all), are
        // not subject to this check.
        if (! $user || ! $user->institute_id) {
            return $next($request);
        }

        $institute = $user->institute;
        if (! $institute) {
            return $next($request);
        }

        // A currently-active subscription (real row, active status, not
        // past end_date) - nothing to do.
        if ($institute->activeSubscription()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if ($routeName && in_array($routeName, self::ALLOWED_ROUTES, true)) {
            return $next($request);
        }

        // Static assets and similar never carry a route name - never
        // block those.
        if (! $routeName) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => false,
                'message' => _lang('Your trial or subscription has expired. Please renew or change your plan to continue.'),
            ], 402);
        }

        return redirect()->route('my-subscription.index')
            ->with('error', _lang('Your trial or subscription has expired. Please choose a plan below to continue using the platform.'));
    }
}