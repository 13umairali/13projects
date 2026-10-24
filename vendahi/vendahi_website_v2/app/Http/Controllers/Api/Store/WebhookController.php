<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\OnlineOrder;
use App\Services\TenantFlutterwaveService;
use App\Services\TenantPaypalService;
use App\Services\TenantPaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Storefront payment-gateway webhooks for PayPal / Paystack / Flutterwave.
 *
 * These are the backstop for the browser return legs: when the buyer pays but
 * the redirect never lands (tab closed, network drop), the gateway's
 * server-to-server event still marks the order paid. They also record refunds
 * and chargebacks issued from the gateway dashboard.
 *
 * Signature verification lives in the tenant services (PayPal's
 * verify-webhook-signature API, Paystack's HMAC-SHA512, Flutterwave's
 * `verif-hash` Secret Hash); an unverified event is rejected with 400 and
 * nothing is touched. Processing is idempotent — the return handlers and
 * these webhooks can arrive in any order.
 *
 * Routes are public (no auth, no CSRF: they live under /api), and tenancy is
 * resolved from the request host like every other tenant API route.
 */
class WebhookController extends Controller
{
    public function paypal(Request $request, TenantPaypalService $paypal)
    {
        $result = $paypal->verifyWebhook(
            $request->getContent(),
            (string) $request->header('PAYPAL-TRANSMISSION-SIG', ''),
            [
                'auth_algo' => $request->header('PAYPAL-AUTH-ALGO', ''),
                'cert_url' => $request->header('PAYPAL-CERT-URL', ''),
                'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID', ''),
                'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME', ''),
            ]
        );

        return $this->process('paypal', $result);
    }

    public function paystack(Request $request, TenantPaystackService $paystack)
    {
        $result = $paystack->verifyWebhook(
            $request->getContent(),
            (string) $request->header('x-paystack-signature', '')
        );

        return $this->process('paystack', $result);
    }

    public function flutterwave(Request $request, TenantFlutterwaveService $flutterwave)
    {
        $result = $flutterwave->verifyWebhook(
            $request->getContent(),
            (string) $request->header('verif-hash', '')
        );

        return $this->process('flutterwave', $result);
    }

    /**
     * Apply a verified, normalized event. Always answers 200 once the
     * signature checks out so the gateway stops retrying — a failure to find
     * the order is logged, not bounced back.
     */
    protected function process(string $gateway, array $result)
    {
        if (empty($result['valid'])) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        match ($result['status']) {
            'paid' => $this->handlePaid($gateway, $result),
            'refunded' => $this->handleRefunded($gateway, $result),
            'failed' => $this->handleFailed($gateway, $result),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    protected function handlePaid(string $gateway, array $result): void
    {
        $order = $this->findOrder($gateway, $result);
        if (! $order) {
            Log::warning("Store webhook ({$gateway}): order not found for paid event.", $result);

            return;
        }

        if ($order->payment_status === 'paid') {
            return; // Already settled by the return leg.
        }

        $order->update([
            'payment_status' => 'paid',
            // If the order was auto-cancelled (e.g. a transient verify failure)
            // but the money actually moved, reopen it for fulfilment.
            'status' => $order->status === 'cancelled' ? 'pending' : $order->status,
        ] + match ($gateway) {
            'paypal' => ['paypal_capture_id' => $result['transaction_id'] ?: $order->paypal_capture_id],
            'paystack' => ['paystack_transaction_id' => $result['transaction_id'] ?: $order->paystack_transaction_id],
            'flutterwave' => ['flutterwave_transaction_id' => $result['transaction_id'] ?: $order->flutterwave_transaction_id],
            default => [],
        });

        Log::info("Store webhook ({$gateway}): order {$order->id} marked paid.");
    }

    protected function handleRefunded(string $gateway, array $result): void
    {
        $order = $this->findOrder($gateway, $result);
        if (! $order) {
            Log::warning("Store webhook ({$gateway}): order not found for refund event.", $result);

            return;
        }

        if ($order->payment_status !== 'refunded') {
            $order->update(['payment_status' => 'refunded']);
            Log::info("Store webhook ({$gateway}): order {$order->id} marked refunded.");
        }
    }

    protected function handleFailed(string $gateway, array $result): void
    {
        $order = $this->findOrder($gateway, $result);

        // Never downgrade a paid order on a late or retried failure event.
        if ($order && $order->payment_status === 'pending') {
            $order->update(['payment_status' => 'failed']);
        }
    }

    /**
     * Resolve the order: by the online_order_id carried in the gateway
     * metadata first, falling back to the stored gateway ids (capture id /
     * reference / transaction id) for events that carry no metadata.
     */
    protected function findOrder(string $gateway, array $result): ?OnlineOrder
    {
        if (! empty($result['online_order_id'])) {
            $order = OnlineOrder::find($result['online_order_id']);
            if ($order) {
                return $order;
            }
        }

        $txn = (string) ($result['transaction_id'] ?? '');
        $ref = (string) ($result['gateway_payment_id'] ?? '');

        // No identifiers at all: an empty where() would match ANY order.
        if ($txn === '' && $ref === '') {
            return null;
        }

        [$refColumn, $txnColumn] = match ($gateway) {
            'paypal' => ['paypal_order_id', 'paypal_capture_id'],
            'flutterwave' => ['flutterwave_tx_ref', 'flutterwave_transaction_id'],
            default => ['paystack_reference', 'paystack_transaction_id'],
        };

        return OnlineOrder::where('payment_method', $gateway)
            ->where(function ($q) use ($txn, $ref, $refColumn, $txnColumn) {
                foreach (array_filter([$txn, $ref]) as $value) {
                    $q->orWhere($refColumn, $value)->orWhere($txnColumn, $value);
                }
            })
            ->first();
    }
}
