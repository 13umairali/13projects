<?php

namespace App\PaymentChannels\Drivers\Flutterwave;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Channel extends BasePaymentChannel implements IChannel
{
    protected $currency;
    protected $test_mode;
    protected $publicKey;
    protected $secretKey;
    protected $secretHash;

    protected array $credentialItems = [
        'publicKey',
        'secretKey',
        'secretHash',
    ];

    /**
     * Channel constructor.
     * @param PaymentChannel $paymentChannel
     */
    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->currency = currency(); // This Gateway just Support => NGN, GBP or EUR
        $this->setCredentialItems($paymentChannel);
    }

    public function paymentRequest(Order $order)
    {
        $user = $order->user;
        $price = $this->makeAmountByCurrency($order->total_amount, $this->currency);
        $generalSettings = getGeneralSettings();

        $paymentId = sha1($order->id);

        $data = [
            'payment_options' => 'card,banktransfer',
            'amount' => $price,
            'email' => $user->email ?? $generalSettings['site_email'],
            'tx_ref' => $paymentId,
            'currency' => $this->currency,
            'redirect_url' => $this->makeCallbackUrl($order),
            'customer' => [
                'email' => $user->email ?? $generalSettings['site_email'],
                "phonenumber" => $user->mobile,
                "name" => $user->full_name
            ],
            "customizations" => [
                "title" => $generalSettings['site_name'] . ' payment',
                "description" => $generalSettings['site_name'] . ' payment'
            ]
        ];

        try {
            $payment = Http::withToken($this->secretKey)->post(
                'https://api.flutterwave.com/v3/payments',
                $data
            )->json();

            if ($payment['status'] == 'success') {
                return $payment['data']['link'];
            }
        } catch (\Exception $e) {
            //dd($e->getMessage());
        }

        $toastData = [
            'title' => trans('cart.fail_purchase'),
            'msg' => '',
            'status' => 'error'
        ];

        return redirect()->back()->with(['toast' => $toastData])->withInput();
    }

    private function makeCallbackUrl($order)
    {
        $callbackUrl = route('payment_verify', [
            'gateway' => 'Flutterwave',
            'order_id' => $order->id
        ]);

        return $callbackUrl;
    }

    public function verify(Request $request)
    {
        $transactionId = $request->get('transaction_id');
        $order_id = $request->get('order_id');
        $tx_ref = $request->get('tx_ref');
        $status = $request->get('status'); // Flutterwave sends status param on redirect

        $user = auth()->user();

        // Fallback: find order first so we can re-login if session was lost
        $order = null;
        if (!empty($order_id)) {
            if (!empty($user)) {
                $order = Order::where('id', $order_id)
                    ->where('user_id', $user->id)
                    ->first();
            } else {
                // Session lost during redirect - find order and re-login user
                $order = Order::where('id', $order_id)->first();
                if (!empty($order)) {
                    auth()->loginUsingId($order->user_id, true);
                    $user = auth()->user();
                }
            }
        }

        if (!empty($order) && !empty($transactionId) && !empty($tx_ref) && $tx_ref == sha1($order->id)) {

            // Prevent duplicate processing
            if ($order->status === Order::$paid) {
                return $order;
            }

            try {
                // Correct Flutterwave v3 verify endpoint:
                // GET /v3/transactions/{id}/verify
                // Docs: https://developer.flutterwave.com/reference/endpoints/transactions#verify-a-transaction
                $payment = Http::withToken($this->secretKey)
                    ->get("https://api.flutterwave.com/v3/transactions/{$transactionId}/verify")
                    ->json();

                if (
                    !empty($payment)
                    && isset($payment['status'])
                    && $payment['status'] === 'success'
                    && isset($payment['data']['status'])
                    && $payment['data']['status'] === 'successful'
                    && isset($payment['data']['tx_ref'])
                    && $payment['data']['tx_ref'] === sha1($order->id)
                ) {
                    // Validate amount matches (Flutterwave best practice)
                    $expectedAmount = $this->makeAmountByCurrency($order->total_amount, $this->currency);
                    $paidAmount = $payment['data']['amount'] ?? 0;

                    if ($paidAmount >= $expectedAmount) {
                        $order->update([
                            'status' => Order::$paying,
                            'payment_data' => json_encode($payment),
                        ]);

                        return $order;
                    } else {
                        \Log::warning('Flutterwave amount mismatch', [
                            'order_id' => $order->id,
                            'expected' => $expectedAmount,
                            'paid' => $paidAmount,
                        ]);
                    }
                } else {
                    \Log::warning('Flutterwave verify failed', [
                        'order_id' => $order->id,
                        'transaction_id' => $transactionId,
                        'response' => $payment,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Flutterwave verify exception: ' . $e->getMessage(), [
                    'order_id' => $order->id,
                    'transaction_id' => $transactionId,
                ]);
            }
        }

        if (!empty($order) && $order->status !== Order::$paid) {
            $order->update(['status' => Order::$fail]);
        }

        return $order;
    }
}
