<?php

namespace App\PaymentChannels\Drivers\Cintepay;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Channel extends BasePaymentChannel implements IChannel
{

    protected $order_session_key;
    protected $currency;
    protected $test_mode;
    protected $api_key;
    protected $site_id;
    protected $secret_key;

    protected array $credentialItems = [
        'api_key',
        'site_id',
        'secret_key',
    ];


    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->order_session_key = 'cintepay.payments.order_id';
        $this->currency = currency(); // XOF
        $this->setCredentialItems($paymentChannel);
    }

    public function paymentRequest(Order $order)
    {
        $generalSettings = getGeneralSettings();
        $user = $order->user;
        $amount = $this->makeAmountByCurrency($order->total_amount, $this->currency);

        // V2 transaction ID
        $transactionId = "order_" . $order->id . "_" . time();

        $names = explode(' ', $user->full_name);
        $firstName = $names[0] ?? 'Customer';
        $lastName = $names[1] ?? 'Name';

        $data = [
            'apikey' => $this->api_key,
            'site_id' => $this->site_id,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'currency' => $this->currency,
            'description' => "{$generalSettings['site_name']} payment",
            'customer_name' => $firstName,
            'customer_surname' => $lastName,
            'customer_email' => $user->email,
            'customer_phone_number' => $user->mobile ?? '0000000000',
            'customer_address' => 'Abidjan',
            'customer_city' => 'Abidjan',
            'customer_country' => 'CI',
            'customer_state' => 'Abidjan',
            'customer_zip_code' => '225',
            'notify_url' => $this->makeCallbackUrl("notify"),
            'return_url' => $this->makeCallbackUrl("return"),
            'channels' => 'ALL',
            'metadata' => (string) $order->id
        ];

        try {
            $response = Http::post('https://api-checkout.cinetpay.com/v2/payment', $data);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['code']) && $result['code'] == '201' && isset($result['data']['payment_url'])) {
                    return redirect()->away(trim($result['data']['payment_url']));
                } else {
                    Log::error('Cinetpay API Failed Response: ', $result);

                    $message = $result['description'] ?? 'Unknown error';
                    $toastData = [
                        'title' => trans('cart.fail_purchase'),
                        'msg' => $message,
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                }
            } else {
                Log::error('Cinetpay HTTP Error: ' . $response->status(), [
                    'body' => $response->body()
                ]);

                $toastData = [
                    'title' => trans('cart.fail_purchase'),
                    'msg' => trans('cart.gateway_error'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }
        } catch (\Exception $e) {
            Log::error('Cinetpay Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            $toastData = [
                'title' => trans('cart.fail_purchase'),
                'msg' => trans('cart.gateway_error'),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }
    }

    private function makeCallbackUrl($status)
    {
        return url("/payments/verify/Cintepay?status={$status}");
    }

    public function verify(Request $request)
    {
        $data = $request->all();
        $transactionId = $request->get('transaction_id') ?? $request->get('cpm_trans_id');

        if (!empty($transactionId)) {
            try {
                $response = Http::post('https://api-checkout.cinetpay.com/v2/payment/check', [
                    'apikey' => $this->api_key,
                    'site_id' => $this->site_id,
                    'transaction_id' => $transactionId
                ]);

                if ($response->successful()) {
                    $result = $response->json();

                    if (isset($result['code']) && $result['code'] == '00') {
                        $paymentData = $result['data'];
                        $orderId = $paymentData['metadata'] ?? null;

                        if (empty($orderId)) {
                            $parts = explode('_', $transactionId);
                            if (count($parts) >= 2 && $parts[0] === 'order') {
                                $orderId = $parts[1];
                            }
                        }

                        if (!empty($orderId)) {
                            $order = Order::query()->where('id', $orderId)->first();

                            if (!empty($order)) {
                                Auth::loginUsingId($order->user_id);

                                $orderStatus = Order::$fail;

                                if (isset($paymentData['status']) && $paymentData['status'] === 'ACCEPTED') {
                                    $orderStatus = Order::$paying;
                                }

                                $order->update([
                                    'status' => $orderStatus,
                                    'payment_data' => json_encode($data)
                                ]);
                            }

                            return $order;
                        }
                    }
                }
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        }

        return null;
    }

}
