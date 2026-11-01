<?php

namespace App\PaymentChannels\Drivers\MercadoPago;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class Channel extends BasePaymentChannel implements IChannel
{
    protected $currency;
    protected $order_session_key;
    protected $test_mode;
    protected $access_token;

    protected array $credentialItems = [
        'access_token',
    ];

    /**
     * Channel constructor.
     * @param PaymentChannel $paymentChannel
     */
    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->currency = currency(); // BRL
        $this->order_session_key = 'mercado.payments.order_id';
        $this->setCredentialItems($paymentChannel);
    }

    public function paymentRequest(Order $order)
    {
        //$generalSettings = getGeneralSettings();
        $user = $order->user;

        try {
            $names = explode(' ', $user->full_name, 2);
            $payer = [
                'name' => $names[0] ?? '',
                'surname' => $names[1] ?? '',
                'email' => $user->email,
            ];

            $items = [];
            foreach ($order->orderItems as $orderItem) {
                $items[] = [
                    'id' => (string) $orderItem->id,
                    'title' => "item " . $orderItem->id,
                    'quantity' => 1,
                    'unit_price' => (float) $this->makeAmountByCurrency($orderItem->total_amount, $this->currency),
                    'currency_id' => $this->currency,
                ];
            }

            $preferenceData = [
                'external_reference' => (string) $order->id,
                'auto_return' => 'approved',
                'payer' => $payer,
                'items' => $items,
                'back_urls' => [
                    'success' => $this->makeCallbackUrl("success"),
                    'pending' => $this->makeCallbackUrl("pending"),
                    'failure' => $this->makeCallbackUrl("failure"),
                ],
            ];

            // Convert data to JSON
            $createPaymentUrl = 'https://api.mercadopago.com/checkout/preferences';

            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->access_token,
            ];

            $response = Http::withHeaders($headers)
                ->post($createPaymentUrl, $preferenceData);

            $preference = $response->json();

            if ($response->failed()) {
                \Log::error('MercadoPago preference creation failed', ['response' => $preference]);
                throw new \Exception('MercadoPago preference creation failed: ' . ($preference['message'] ?? 'Unknown error'));
            }

            session()->put($this->order_session_key, $order->id);


            return $this->test_mode ? $preference['sandbox_init_point'] : $preference['init_point'];
        } catch (\Exception $exception) {
            \Log::error('MercadoPago paymentRequest error: ' . $exception->getMessage());
        }

        $toastData = [
            'title' => trans('cart.fail_purchase'),
            'msg' => '',
            'status' => 'error'
        ];

        return redirect()->back()->with(['toast' => $toastData])->withInput();
    }

    private function makeCallbackUrl($status)
    {
        return url("/payments/verify/MercadoPago");
    }

    public function verify(Request $request)
    {
        $data = $request->all();

        $paymentId = $data['payment_id'] ?? $data['collection_id'] ?? null;
        $externalReference = $data['external_reference'] ?? null;

        $user = auth()->user();

        // 1. Try to get order ID from external_reference (most reliable)
        $orderId = $externalReference;

        // 2. Fallback to session if external_reference is missing
        if (empty($orderId)) {
            $orderId = session()->get($this->order_session_key, null);
        }
        session()->forget($this->order_session_key);

        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!empty($order)) {
            $orderStatus = Order::$fail;

            try {
                if ($paymentId && $paymentId !== 'null') {
                    $response = Http::withToken($this->access_token)
                        ->get("https://api.mercadopago.com/v1/payments/$paymentId");

                    if ($response->successful()) {
                        $paymentData = $response->json();

                        // Check payment status
                        if (isset($paymentData['status'])) {
                            $status = $paymentData['status'];

                            // Process payment status
                            if ($status === 'approved') {
                                $orderStatus = Order::$paying;
                            }
                        }
                    } else {
                        \Log::error('MercadoPago verify API error: ' . $response->body());
                    }
                } else {
                    // Payment failed or cancelled by user, or valid ID missing
                    $status = $data['status'] ?? $data['collection_status'] ?? 'unknown';
                    if ($status !== 'null' && $status === 'approved') {
                        \Log::info('MercadoPago verify: Payment ID missing but status is ' . $status, ['data' => $data]);
                    }
                }

            } catch (\Exception $exception) {
                \Log::error('MercadoPago verify exception: ' . $exception->getMessage());
            }

            $order->update([
                'status' => $orderStatus,
                'payment_data' => json_encode($data),
            ]);
        } else {
            \Log::error('MercadoPago verify error: Order not found', ['order_id' => $orderId, 'user_id' => $user->id]);
        }

        return $order;
    }
}
