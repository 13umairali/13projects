<?php

namespace App\Services;

use App\Traits\PaymentProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Authentication\Models\Plan;
use Modules\Authentication\Services\InstituteProvisioningService;
use Modules\Frontend\Models\Onboarding;
use Modules\Gateways\Models\PaymentRequest;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Throwable;

/**
 * Handles paid-plan sign-up: unlike the free-trial path (which provisions
 * the institute immediately in FrontendController::onboarding()), a paid
 * plan must not create the institute until a real payment has actually
 * been confirmed. This mirrors the existing gateway pattern used for
 * course/event fees (see StripePaymentController) rather than inventing a
 * separate one, and plugs into PaymentRequest's existing hook system.
 */
class InstituteProvisioningCheckoutService
{
    public function __construct(
        protected InstituteProvisioningService $provisioner,
    ) {}

    /**
     * Stores the registration as a pending Onboarding (same as the free
     * path), creates a PaymentRequest for the plan's price, and returns a
     * Stripe Checkout URL. The institute is NOT created at this point.
     */
    public function createCheckoutSession(array $validated, Plan $plan, string $successUrl, string $cancelUrl): array
    {
        $onboarding = Onboarding::create([
            'collected_data' => Onboarding::collectedDataFrom($validated),
            'institute_logo' => $validated['institute_logo'] ?? null,
            'user_avatar' => $validated['user_avatar'] ?? null,
            'status' => Onboarding::STATUS_PENDING,
        ]);

        $payment = PaymentRequest::create([
            'payment_amount' => $plan->price,
            'currency_code' => 'usd',
            'payment_type' => 'subscription_payment',
            'plan_id' => $plan->id,
            'is_paid' => 0,
            'success_hook' => 'complete_onboarding',
            'failure_hook' => null,
            'additional_data' => ['onboarding_id' => $onboarding->id],
            'notes' => 'Institute onboarding - '.$plan->name,
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
                        'name' => $plan->name.' Plan - '.$validated['institute_name'],
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
     * Called when the person's browser returns from Stripe. Verifies the
     * session server-side (never trusts the redirect alone), marks the
     * payment paid, and runs the hook that actually provisions the
     * institute. Safe to call more than once for the same payment_id -
     * already-paid/already-provisioned requests are simply confirmed
     * again without double-creating anything.
     */
    public function confirmPayment(string $paymentId, string $sessionId): array
    {
        $payment = PaymentRequest::find($paymentId);

        if (! $payment) {
            return ['success' => false, 'message' => 'Payment record not found.'];
        }

        if ($payment->is_paid) {
            return $this->resultFromPayment($payment);
        }

        $stripeConfig = $this->stripeConfig();
        Stripe::setApiKey($stripeConfig['api_key'] ?? '');

        try {
            $session = Session::retrieve($sessionId);
        } catch (Throwable $e) {
            Log::error('Stripe session retrieval failed during onboarding confirm', ['error' => $e->getMessage()]);

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

        return $this->resultFromPayment($payment->fresh());
    }

    /**
     * The actual success_hook target, invoked by PaymentRequest's own
     * allowlisted hook system - not called directly by anything else.
     * Static because call_user_func(['ClassName', 'method']) requires a
     * static method; resolves the real instance via the container so
     * constructor-injected dependencies (the provisioner) still work.
     */
    public static function completeAfterPayment(PaymentRequest $payment): void
    {
        app(static::class)->handleCompleteAfterPayment($payment);
    }

    protected function handleCompleteAfterPayment(PaymentRequest $payment): void
    {
        $onboardingId = $payment->additional_data['onboarding_id'] ?? null;
        $onboarding = $onboardingId ? Onboarding::find($onboardingId) : null;

        if (! $onboarding || $onboarding->status !== Onboarding::STATUS_PENDING) {
            // Already provisioned (e.g. confirm called twice) or the
            // onboarding record is missing - do nothing further.
            return;
        }

        try {
            $result = DB::transaction(fn () => $this->provisioner->provisionFromOnboarding($onboarding));
        } catch (Throwable $e) {
            Log::error('Paid institute onboarding failed after successful payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        // Deliberately does NOT call sendWelcomeMail() here: by the time
        // payment confirms, only the hashed password exists (collected_data
        // stores it via Hash::make, never plaintext) - sendWelcomeMail()
        // requires the real plaintext, which genuinely no longer exists on
        // this request. The marketing site's own Thank You page already
        // has the plaintext from the original form submission (before
        // redirecting to Stripe) and displays it there instead - see
        // registerConfirm() on the frontend.

        $payment->update([
            'additional_data' => array_merge($payment->additional_data ?? [], [
                'institute_id' => $result['institute']->id,
                'institute_name' => $result['institute']->name,
                'institute_domain' => $result['institute']->domain,
            ]),
        ]);
    }

    protected function resultFromPayment(PaymentRequest $payment): array
    {
        $data = $payment->additional_data ?? [];

        if (empty($data['institute_id'])) {
            // Paid, but the hook hasn't finished yet (should be
            // near-instant since it runs synchronously in the same
            // transaction as marking the payment paid - this is a
            // defensive fallback, not the expected path).
            return ['success' => false, 'message' => 'Your payment is confirmed and your institution is being set up. Please refresh in a moment.'];
        }

        return [
            'success' => true,
            'institute_name' => $data['institute_name'] ?? null,
            'institute_domain' => $data['institute_domain'] ?? null,
        ];
    }

    protected function stripeConfig(): array
    {
        $setting = \Modules\Authentication\Models\SAASSetting::where('name', 'stripe')
            ->where('type', 'payment_config')
            ->first();

        return $setting?->payment_info ?? [];
    }
}