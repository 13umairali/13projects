<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\Plan;
use Modules\Authentication\Models\Subscription;
use Modules\Authentication\Models\SubscriptionItem;
use Modules\Gateways\Models\PaymentRequest;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Throwable;

/**
 * Self-service plan changes/renewals for an institute that already exists
 * and is logged in - distinct from InstituteProvisioningCheckoutService,
 * which handles brand-new sign-ups. A free plan (or downgrading to one)
 * applies immediately; a paid plan is gated behind a real, server-verified
 * Stripe payment before the subscription actually changes - never trusts a
 * client-submitted amount, unlike the existing admin-approval queue which
 * is fine for that because a human reviews it first.
 */
class SubscriptionUpgradeCheckoutService
{
    /**
     * Free plan: applies immediately, no payment involved.
     */
    public function applyFreePlan(Institute $institute, Plan $plan): void
    {
        $this->applyPlan($institute->id, $plan);
    }

    /**
     * Paid plan: creates a Stripe Checkout Session. The subscription is NOT
     * changed yet - only confirmPayment() below does that, after Stripe
     * confirms the charge actually succeeded.
     */
    public function createCheckoutSession(Institute $institute, Plan $plan, string $successUrl, string $cancelUrl): array
    {
        $payment = PaymentRequest::create([
            'institute_id' => $institute->id,
            'payment_amount' => $plan->price,
            'currency_code' => 'usd',
            'payment_type' => 'subscription_payment',
            'plan_id' => $plan->id,
            'is_paid' => 0,
            'success_hook' => 'complete_subscription_upgrade',
            'additional_data' => ['institute_id' => $institute->id, 'plan_id' => $plan->id],
            'notes' => 'Subscription upgrade - '.$plan->name,
        ]);

        $stripeConfig = $this->stripeConfig();
        Stripe::setApiKey($stripeConfig['api_key'] ?? '');

        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => (int) round($plan->price * 100),
                    'product_data' => [
                        'name' => $plan->name.' Plan - '.$institute->name,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl.(str_contains($successUrl, '?') ? '&' : '?').'session_id={CHECKOUT_SESSION_ID}&payment_id='.$payment->id,
            'cancel_url' => $cancelUrl,
        ]);

        return [
            'checkout_url' => $checkoutSession->url,
            'payment_id' => $payment->id,
        ];
    }

    /**
     * Called when the admin's browser returns from Stripe. Verifies the
     * session server-side before applying anything - safe to call more
     * than once for the same payment_id.
     */
    public function confirmPayment(string $paymentId, string $sessionId): array
    {
        $payment = PaymentRequest::find($paymentId);

        if (! $payment) {
            return ['success' => false, 'message' => 'Payment record not found.'];
        }

        if ($payment->is_paid) {
            return ['success' => true, 'message' => 'Payment already confirmed.'];
        }

        $stripeConfig = $this->stripeConfig();
        Stripe::setApiKey($stripeConfig['api_key'] ?? '');

        try {
            $session = Session::retrieve($sessionId);
        } catch (Throwable $e) {
            Log::error('Stripe session retrieval failed during subscription upgrade confirm', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'We could not verify your payment. Please contact support.'];
        }

        if ($session->payment_status !== 'paid' || $session->status !== 'complete') {
            return ['success' => false, 'message' => 'Payment was not completed.'];
        }

        DB::transaction(function () use ($payment, $session) {
            $payment->update([
                'is_paid' => 1,
                'transaction_id' => $session->payment_intent,
                'payment_method' => 'stripe',
            ]);

            $payment->runSuccessHook();
        });

        return ['success' => true, 'message' => 'Your plan has been upgraded successfully.'];
    }

    /**
     * The success_hook target, invoked by PaymentRequest's own allowlisted
     * hook system after a confirmed paid upgrade. Static for the same
     * reason as InstituteProvisioningCheckoutService::completeAfterPayment.
     */
    public static function completeAfterPayment(PaymentRequest $payment): void
    {
        app(static::class)->handleCompleteAfterPayment($payment);
    }

    protected function handleCompleteAfterPayment(PaymentRequest $payment): void
    {
        $instituteId = $payment->additional_data['institute_id'] ?? $payment->institute_id;
        $planId = $payment->additional_data['plan_id'] ?? $payment->plan_id;

        if (! $instituteId || ! $planId) {
            Log::error('Subscription upgrade hook missing institute/plan reference', ['payment_id' => $payment->id]);

            return;
        }

        $plan = Plan::find($planId);
        if (! $plan) {
            return;
        }

        $this->applyPlan((int) $instituteId, $plan);

        // The institute may have signed up on the free plan earlier, with
        // a percentage-based referral recorded then but left unpaid at
        // the time. Now that they've actually paid for a real plan, that
        // reward becomes calculable and is applied automatically.
        app(\App\Services\ReferralService::class)
            ->autoRewardPercentageForInstitute((int) $instituteId);
    }

    /**
     * The actual effect of a plan change: update-or-create the
     * institute's Subscription row with the new plan and a fresh
     * start/end date, and log a SubscriptionItem the same way every other
     * plan-assignment path in this system already does. Every plan-limit
     * check elsewhere reads this same Subscription row dynamically, so
     * nothing else needs to be touched for the new limits to take effect.
     */
    protected function applyPlan(int $instituteId, Plan $plan): void
    {
        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addDays((int) ($plan->duration_days ?: 30));

        $subscription = Subscription::where('institute_id', $instituteId)->first();
        $invoiceData = [
            'invoice_no' => 'INV-'.strtoupper(Str::random(6)),
            'issued_at' => $startDate->toDateString(),
            'notes' => 'Self-service plan change to '.$plan->name,
            'amount' => (float) $plan->price,
        ];

        if ($subscription) {
            $subscription->update([
                'plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'invoice_details' => $invoiceData,
            ]);
        } else {
            $subscription = Subscription::create([
                'institute_id' => $instituteId,
                'plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'invoice_details' => $invoiceData,
            ]);
        }

        SubscriptionItem::create([
            'subscription_id' => $subscription->id,
            'reference_no' => 'PAY-'.strtoupper(Str::random(8)),
            'amount_paid' => (float) $plan->price,
            'payment_method' => $plan->is_free ? 'Free' : 'Stripe',
        ]);
    }

    protected function stripeConfig(): array
    {
        $setting = \Modules\Authentication\Models\SAASSetting::where('name', 'stripe')
            ->where('type', 'payment_config')
            ->first();

        return $setting?->payment_info ?? [];
    }
}