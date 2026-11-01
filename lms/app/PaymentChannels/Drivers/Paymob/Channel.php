<?php

namespace App\PaymentChannels\Drivers\Paymob;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class Channel extends BasePaymentChannel implements IChannel
{
    use PaymobTrait;

    protected $currency;
    protected $test_mode;
    protected $order_session_key;

    protected $api_key;
    protected $username;
    protected $password;
    protected $integration_id;
    protected $iframe_id;
    protected $HMAC;


    protected array $credentialItems = [
        'api_key',
        'username',
        'password',
        'integration_id',
        'iframe_id',
        'HMAC',
    ];

    // https://github.com/samir-hussein/paymob
    // https://github.com/ctf0/laravel-paymob
    // https://acceptdocs.paymobsolutions.com/docs/card-payments

    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->currency = currency(); // EGP
        $this->setCredentialItems($paymentChannel);
        $this->order_session_key = 'paymob.payments.order_id';
    }

    public function paymentRequest(Order $order)
    {
        //$generalSettings = getGeneralSettings();
        $user = $order->user;
        $amount = $this->makeAmountByCurrency($order->total_amount, $this->currency);

        $errorMsg = null;
        try {

            $paymentAuth = $this->AuthenticationRequest();

            // Check if authentication was successful
            if (empty($paymentAuth) || empty($paymentAuth->token)) {
                $errorMsg = $paymentAuth->message ?? 'Paymob authentication failed';
                throw new \Exception($errorMsg);
            }

            $orderItems = [];

            foreach ($order->orderItems as $orderItem) {
                $orderItems[] = [
                    'name' => "order_item_{$orderItem->id}",
                    'amount_cents' => $orderItem->total_amount * 100,
                    'quantity' => 1,
                    "description" => "order_item_{$orderItem->id}",
                ];
            }

            $paymentOrder = $this->OrderRegistrationAPI([
                'auth_token' => $paymentAuth->token,
                'amount_cents' => $amount * 100,
                'currency' => $this->currency,
                'delivery_needed' => false,
                'merchant_order_id' => $order->id . '_' . time(),
                'items' => $orderItems
            ]);

            // Check if order registration was successful
            if (empty($paymentOrder) || empty($paymentOrder->id)) {
                $errorMsg = $paymentOrder->message ?? 'Paymob order registration failed';
                \Log::error('Paymob OrderRegistrationAPI failed', ['response' => $paymentOrder]);
                throw new \Exception($errorMsg);
            }

            $paymentKey = $this->PaymentKeyRequest([
                'auth_token' => $paymentAuth->token,
                'amount_cents' => $amount * 100,
                'currency' => $this->currency,
                'order_id' => $paymentOrder->id,
                "billing_data" => [ // put your client information
                    "apartment" => "NA",
                    "email" => $user->email ?? 'NA@example.com',
                    "floor" => "NA",
                    "first_name" => $user->full_name ?? 'Customer',
                    "street" => "NA",
                    "building" => "NA",
                    "phone_number" => $user->mobile ?? 'NA',
                    "shipping_method" => "NA",
                    "postal_code" => "NA",
                    "city" => $user->getRegionByTypeId($user->city_id) ?? 'NA',
                    "country" => $user->getRegionByTypeId($user->country_id) ?? 'EG',
                    "last_name" => "NA",
                    "state" => $user->getRegionByTypeId($user->province_id) ?? 'NA',
                ]
            ]);

            if (!empty($paymentKey) and !empty($paymentKey->token)) {
                
                $checkoutUrl = "https://accept.paymob.com/api/acceptance/iframes/{$this->iframe_id}?payment_token={$paymentKey->token}";
                return $checkoutUrl;
            } else if (!empty($paymentKey) and !empty($paymentAuth->message)) {
                $errorMsg = $paymentAuth->message;
            } else if (!empty($paymentKey->billing_data)) {
                
                $errorMsg = 'Billing data error: ' . json_encode($paymentKey->billing_data);
                \Log::error('Paymob PaymentKeyRequest billing error', ['response' => $paymentKey]);
            }

        } catch (\Exception $e) {
            \Log::error('Paymob payment error: ' . $e->getMessage());
            $errorMsg = $e->getMessage();
        }

        $toastData = [
            'title' => trans('cart.fail_purchase'),
            'msg' => $errorMsg ?? trans('update.gateway_error_please_contact_support'),
            'status' => 'error'
        ];
        return redirect()->back()->with(['toast' => $toastData])->withInput();
    }

    private function makeCallbackUrl()
    {
        return url("/payments/verify/Paymob");
    }

    public function verify(Request $request)
    {
        $data = $request->all();


        if (!empty($data)) {
            $requestHmac = !empty($data['hmac']) ? $data['hmac'] : '';

            try {
                $calcHmac = $this->calcHMAC($request);


                if ($requestHmac == $calcHmac) {
                    $merchantOrderId = $data['merchant_order_id'];
                    // Extract real order ID from merchant_order_id (format: orderId_timestamp)
                    $orderId = explode('_', $merchantOrderId)[0];
                    $amount_cents = $data['amount_cents'];
                    $data['transaction_id'] = $data['id'];



                    // Get order by order ID only (user session may not exist on callback)
                    $order = Order::where('id', $orderId)->first();

                    if (!empty($order)) {


                        // Re-login user if session was lost during cross-site redirect
                        if (!auth()->check() && !empty($order->user_id)) {
                            auth()->loginUsingId($order->user_id, true); // true = remember
                            session()->save(); // Ensure session is saved before redirect

                        }

                        $orderStatus = Order::$fail;

                        if (!empty($data['success']) and ($order->total_amount * 100) == $amount_cents) {
                            $orderStatus = Order::$paying;
                        }



                        $order->update([
                            'status' => $orderStatus,
                            'payment_data' => json_encode($data)
                        ]);
                    }

                    return $order;
                }
            } catch (\Exception $e) {
                
                \Log::error('Paymob verify error: ' . $e->getMessage());
            }
        }

        return null;
    }

}
