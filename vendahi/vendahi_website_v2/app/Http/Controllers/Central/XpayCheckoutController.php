<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\GeneralSetting;
use App\Models\Central\PendingRegistration;
use App\Models\Central\Plan;
use App\Models\Central\TenantBillingPayment;
use App\Services\PaymentGateways\PaymentGatewayFactory;
use App\Services\PaymentGateways\XpayGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Internal XPay payment page.
 *
 * XPay has no hosted redirect checkout: the card element must live on our
 * own page. XpayGateway::createCheckoutUrl()/createCheckoutSession() create
 * the Payment Intent, keep its client secret on the registration/payment
 * metadata and send the payer to /xpay/pay/{ref} (signed, time-limited).
 * The page mounts the element, confirms in the browser, then calls
 * confirm(), which re-fetches the intent server-side and settles the
 * registration or billing payment exactly like a verified webhook would.
 */
class XpayCheckoutController extends Controller
{
    public function show(Request $request, string $ref)
    {
        if (! XpayGateway::payPageSignatureValid($ref, $request->query('expires'), $request->query('signature'))) {
            abort(403, 'This payment link is invalid or has expired.');
        }

        $context = $this->resolveContext($ref);
        if (! $context) {
            abort(404);
        }

        /** @var XpayGateway|null $gateway */
        $gateway = PaymentGatewayFactory::resolve('xpay') ?? PaymentGatewayFactory::resolveForWebhook('xpay');
        if (! $gateway) {
            abort(503, 'XPay is not available.');
        }

        // Already settled (webhook or a previous confirm) — go straight on.
        if ($context['paid']) {
            return redirect()->away($context['success_url']);
        }

        $settings = GeneralSetting::instance();

        return view('central.xpay_checkout', [
            'ref'            => $ref,
            'appName'        => $settings->app_name ?: config('app.name'),
            'planName'       => $context['plan_name'],
            'cycle'          => $context['cycle'],
            'amount'         => $context['amount'],
            'chargeCurrency' => $context['currency'],
            'element'        => $gateway->elementConfig(),
            'clientSecret'   => $context['xpay']['client_secret'] ?? '',
            'encryptionKey'  => $context['xpay']['encryption_key'] ?? '',
            'customerName'   => $context['customer_name'],
            'confirmUrl'     => route('central.xpay.confirm', ['ref' => $ref, 'expires' => $request->query('expires'), 'signature' => $request->query('signature')]),
            'cancelUrl'      => $context['cancel_url'],
            'successUrl'     => $context['success_url'],
        ]);
    }

    /**
     * Called by the page after the browser-side confirmPayment() resolves.
     * The intent is re-fetched server-side; the browser's word is never
     * enough to mark anything paid.
     */
    public function confirm(Request $request, string $ref)
    {
        if (! XpayGateway::payPageSignatureValid($ref, $request->query('expires'), $request->query('signature'))) {
            return response()->json(['confirmed' => false, 'message' => 'This payment link is invalid or has expired.'], 403);
        }

        $context = $this->resolveContext($ref);
        if (! $context) {
            return response()->json(['confirmed' => false, 'message' => 'Payment not found.'], 404);
        }

        if ($context['paid']) {
            return response()->json(['confirmed' => true, 'redirect' => $context['success_url']]);
        }

        /** @var XpayGateway|null $gateway */
        $gateway = PaymentGatewayFactory::resolve('xpay') ?? PaymentGatewayFactory::resolveForWebhook('xpay');
        if (! $gateway) {
            return response()->json(['confirmed' => false, 'message' => 'XPay is not available.'], 503);
        }

        $intentId = (string) ($context['xpay']['intent_id'] ?? '');
        if ($intentId === '') {
            return response()->json(['confirmed' => false, 'message' => 'No payment intent on record.'], 422);
        }

        try {
            $verification = $gateway->verifyTransaction($intentId);
        } catch (\Throwable $e) {
            Log::error('XPay confirm: verification threw', ['ref' => $ref, 'error' => $e->getMessage()]);

            return response()->json(['confirmed' => false, 'message' => 'Verification failed. Please try again.'], 500);
        }

        if (! $verification['success']) {
            $failed = in_array($verification['status'], ['FAILED', 'DECLINED', 'CANCELED', 'CANCELLED', 'EXPIRED', 'VOIDED', 'ERROR'], true);

            return response()->json([
                'confirmed' => false,
                'final'     => $failed,
                'message'   => $failed ? 'The payment was not completed.' : 'Payment not confirmed yet.',
            ]);
        }

        // The charge must cover what we asked for, in the currency we asked for.
        $paidAmount   = $verification['amount'];
        $paidCurrency = strtoupper((string) ($verification['currency'] ?? ''));
        if ($paidAmount !== null && $paidAmount + 0.01 < (float) $context['amount']) {
            Log::error("XPay confirm: paid amount {$paidAmount} below expected {$context['amount']} for {$ref}");

            return response()->json(['confirmed' => false, 'message' => 'Paid amount does not match the order.'], 422);
        }
        if ($paidCurrency !== '' && $paidCurrency !== strtoupper((string) $context['currency'])) {
            Log::error("XPay confirm: paid currency {$paidCurrency} differs from expected {$context['currency']} for {$ref}");

            return response()->json(['confirmed' => false, 'message' => 'Paid currency does not match the order.'], 422);
        }

        $result = [
            'valid'              => true,
            'event_type'         => 'xpay.confirm',
            'gateway_payment_id' => $intentId,
            'transaction_id'     => (string) ($verification['transaction_id'] ?? ''),
            'payment_id'         => $context['type'] === 'pay' ? $context['model']->id : null,
            'tenant_id'          => $context['type'] === 'pay' ? $context['model']->tenant_id : null,
            'registration_id'    => $context['type'] === 'reg' ? $context['model']->id : null,
            'status'             => 'paid',
            'amount'             => $paidAmount,
            'currency'           => $paidCurrency ?: null,
        ];

        try {
            app(WebhookController::class)->applyVerifiedResult($result, 'xpay');
        } catch (\Throwable $e) {
            Log::error('XPay confirm: settling the payment failed', ['ref' => $ref, 'error' => $e->getMessage()]);

            return response()->json(['confirmed' => false, 'message' => 'Payment received but could not be recorded. Please contact support.'], 500);
        }

        return response()->json(['confirmed' => true, 'redirect' => $context['success_url']]);
    }

    /**
     * Resolve "reg-{id}" / "pay-{id}" to the model plus everything the page needs.
     */
    protected function resolveContext(string $ref): ?array
    {
        [$type, $id] = array_pad(explode('-', $ref, 2), 2, null);

        if ($type === 'reg') {
            $registration = PendingRegistration::where('id', (int) $id)->where('gateway', 'xpay')->first();
            if (! $registration) {
                return null;
            }

            $meta = $registration->metadata ?? [];
            $plan = Plan::find($registration->plan_id);
            $paid = in_array($registration->status, [
                PendingRegistration::STATUS_PAID,
                PendingRegistration::STATUS_PROVISIONING,
                PendingRegistration::STATUS_COMPLETED,
            ], true) || ! empty($registration->tenant_id);

            return [
                'type'          => 'reg',
                'model'         => $registration,
                'paid'          => $paid,
                'xpay'          => $meta['xpay'] ?? [],
                'plan_name'     => $plan?->name ?? 'Subscription',
                'cycle'         => $registration->billing_cycle,
                'amount'        => (float) ($registration->gateway_amount ?? $registration->amount),
                'currency'      => strtoupper((string) ($registration->gateway_currency ?: $registration->currency)),
                'customer_name' => $registration->company_name,
                'success_url'   => $meta['xpay']['success_url'] ?? route('central.registration.preparing', ['token' => $registration->token]),
                'cancel_url'    => $meta['xpay']['cancel_url'] ?? route('central.checkout', ['token' => $registration->token, 'cancelled' => 1]),
            ];
        }

        if ($type === 'pay') {
            $payment = TenantBillingPayment::with(['plan', 'tenant'])->where('id', (int) $id)->where('gateway', 'xpay')->first();
            if (! $payment) {
                return null;
            }

            $meta = $payment->metadata ?? [];

            return [
                'type'          => 'pay',
                'model'         => $payment,
                'paid'          => $payment->isPaid(),
                'xpay'          => $meta['xpay'] ?? [],
                'plan_name'     => $payment->plan?->name ?? 'Subscription',
                'cycle'         => $payment->billing_cycle,
                'amount'        => (float) ($payment->gateway_amount ?? $payment->amount),
                'currency'      => strtoupper((string) ($payment->gateway_currency ?: $payment->currency)),
                'customer_name' => $payment->tenant?->company_name ?? 'Customer',
                'success_url'   => $meta['xpay']['success_url'] ?? rtrim((string) config('app.url'), '/'),
                'cancel_url'    => $meta['xpay']['cancel_url'] ?? rtrim((string) config('app.url'), '/'),
            ];
        }

        return null;
    }
}
