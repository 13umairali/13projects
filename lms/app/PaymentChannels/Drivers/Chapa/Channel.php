<?php

namespace App\PaymentChannels\Drivers\Chapa;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Channel extends BasePaymentChannel implements IChannel
{
    protected $order_session_key;
    protected $currency;
    protected $test_mode;
    protected $reference;
    protected $baseUrl = 'https://api.chapa.co/v1';

    protected $public_key;
    protected $secret_key;
    protected $encryption_key;
    protected $merchant_id;

    protected array $credentialItems = [
        'public_key',
        'secret_key',
        'encryption_key',
        'merchant_id',
    ];

    /**
     * Channel constructor.
     * @param PaymentChannel $paymentChannel
     * https://developer.chapa.co/laravel-sdk
     */
    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->order_session_key = 'chapa.payments.order_id';
        $this->currency = "ETB";//currency(); // "ETB"
        $this->setCredentialItems($paymentChannel);
    }

    /**
     * Generate a unique reference for the transaction
     * @param int $orderId
     */
    private function generateReference($orderId): string
    {
        return 'order_' . $orderId . '_chapa_' . uniqid(time());
    }

    public function paymentRequest(Order $order)
    {
        $this->reference = $this->generateReference($order->id);

        $generalSettings = getGeneralSettings();
        $user = $order->user;
        $price = $this->makeAmountByCurrency($order->total_amount, $this->currency);

        $data = [
            'amount' => $price,
            'email' => $user->email,
            'tx_ref' => $this->reference,
            'currency' => $this->currency,
            'return_url' => $this->makeCallbackUrl($order->id),
            'first_name' => $user->full_name,
            'last_name' => "",
            "customization" => [
                "title" => 'Order ' . $order->id,
                "description" => "{$generalSettings['site_name']} payment",
            ]
        ];

        try {
            $payment = Http::withToken($this->secret_key)->post(
                $this->baseUrl . '/transaction/initialize',
                $data
            )->json();

            if (isset($payment['status']) && $payment['status'] == 'success') {
                session()->put($this->order_session_key, $order->id);

                return $payment['data']['checkout_url'];
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        // Throw exception if payment initialization failed - PaymentController will catch it
        throw new \Exception('Chapa payment initialization failed');
    }

    public function verify(Request $request)
    {
        $reference = $request->get('reference');
        $user = auth()->user();

        // Try to get order_id from request first (URL parameter), then fallback to session
        $orderId = $request->get('order_id');
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
                $verifyTransaction = Http::withToken($this->secret_key)
                    ->get($this->baseUrl . '/transaction/verify/' . $reference)
                    ->json();

                if (!empty($verifyTransaction) && $verifyTransaction['status'] == 'success') {
                    $orderStatus = Order::$paying;
                }
            } catch (\Exception $e) {
                // Log verification error
                // \Log::error('Chapa verify error: ' . $e->getMessage());
            }

            $order->update([
                'status' => $orderStatus,
            ]);
        }

        return $order;
    }

    private function makeCallbackUrl($orderId)
    {
        return route("chapa.callback", ['reference' => $this->reference, 'order_id' => $orderId]);
    }
}
