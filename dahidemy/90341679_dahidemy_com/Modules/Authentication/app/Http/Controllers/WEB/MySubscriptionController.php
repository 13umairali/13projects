<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionUpgradeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Authentication\Models\Plan;
use Modules\Authentication\Models\Subscription;

/**
 * Lets an institute admin see their own plan/subscription details and
 * change or renew it themselves - fully automatic. A free plan (or
 * downgrading to one) applies immediately; a paid plan goes through a
 * real, server-verified Stripe payment first (see
 * SubscriptionUpgradeCheckoutService) - nothing here trusts a
 * client-submitted amount the way the separate admin-approval queue does.
 */
class MySubscriptionController extends Controller
{
    public function index(): View
    {
        $instituteId = get_institute_id();

        $subscription = Subscription::where('institute_id', $instituteId)->first();
        $currentPlan = $subscription ? Plan::find($subscription->plan_id) : null;

        $availablePlans = Plan::where('status', true)->orderBy('price')->get();

        $daysRemaining = null;
        if ($subscription && $subscription->end_date) {
            $daysRemaining = now()->startOfDay()->diffInDays($subscription->end_date->startOfDay(), false);
        }

        return view('authentication::subscription.my-subscription', [
            'subscription' => $subscription,
            'currentPlan' => $currentPlan,
            'availablePlans' => $availablePlans,
            'daysRemaining' => $daysRemaining,
        ]);
    }

    /**
     * Free plan (or renewing/switching to one) applies immediately. A
     * paid plan is redirected to a real Stripe Checkout - the plan does
     * NOT change until checkoutSuccess() below verifies the payment.
     */
    public function checkout(Request $request, SubscriptionUpgradeCheckoutService $checkout): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
        ]);

        $institute = auth()->user()->institute;
        $plan = Plan::findOrFail($validated['plan_id']);

        if ($plan->is_free) {
            $checkout->applyFreePlan($institute, $plan);

            return redirect()->route('my-subscription.index')
                ->with('success', 'You are now on the '.$plan->name.' plan.');
        }

        $session = $checkout->createCheckoutSession(
            $institute,
            $plan,
            route('my-subscription.checkout-success'),
            route('my-subscription.index')
        );

        return redirect()->away($session['checkout_url']);
    }

    /**
     * Where Stripe sends the admin's browser back to after payment.
     * Verifies the payment server-side (never trusts the redirect alone)
     * before the plan actually changes.
     */
    public function checkoutSuccess(Request $request, SubscriptionUpgradeCheckoutService $checkout): RedirectResponse
    {
        $paymentId = $request->query('payment_id');
        $sessionId = $request->query('session_id');

        if (! $paymentId || ! $sessionId) {
            return redirect()->route('my-subscription.index')
                ->with('error', 'Missing payment confirmation details.');
        }

        $result = $checkout->confirmPayment($paymentId, $sessionId);

        return redirect()->route('my-subscription.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}