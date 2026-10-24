<?php

namespace App\Http\Middleware;

use App\Services\TenantLimitsService;
use Closure;
use Illuminate\Http\Request;

class CheckTenantFeature
{
    protected TenantLimitsService $limits;

    public function __construct(TenantLimitsService $limits)
    {
        $this->limits = $limits;
    }

    /**
     * Usage: middleware('tenant.feature:pos')
     *        middleware('tenant.feature:hrm,accounting')  ← requires ANY of the listed features
     */
    public function handle(Request $request, Closure $next, string ...$features)
    {
        if (empty($features)) {
            return $next($request);
        }

        $plan = $this->limits->getActivePlan();

        // No active plan → block
        if (! $plan) {
            return $this->deny($request, $features);
        }

        // Allow if the plan has at least one of the listed features
        foreach ($features as $feature) {
            if ($plan->hasFeature($feature)) {
                return $next($request);
            }
        }

        return $this->deny($request, $features);
    }

    protected function deny(Request $request, array $features): mixed
    {
        $label = $this->featureLabel($features[0] ?? '');

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'feature_unavailable',
                'message' => "The \"{$label}\" feature is not available in your current plan. Please upgrade to access it.",
            ], 403);
        }

        $message = "The \"{$label}\" feature is not available in your current plan. Please upgrade to access it.";

        // back() falls back to the site root when there is no referer, which
        // loops forever on a gated page that IS the root (root-domain
        // storefront) or on any directly-opened gated URL. Send those to the
        // dashboard instead.
        $previous = url()->previous();
        if ($previous === $request->fullUrl() || $previous === url('/')) {
            return redirect('/app/dashboard')->with('error', $message);
        }

        return redirect()->back()->with('error', $message);
    }

    protected function featureLabel(string $key): string
    {
        return \App\Models\Central\Plan::AVAILABLE_FEATURES[$key]['label'] ?? ucfirst(str_replace('_', ' ', $key));
    }
}
