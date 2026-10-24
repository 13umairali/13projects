<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Product;
use App\Models\product_warehouse;
use App\Models\ProductVariant;
use App\Models\StoreCoupon;
use App\Models\StoreCouponRedemption;
use App\Models\StorePickupBranch;
use App\Models\Warehouse;
use App\Services\CheckoutException;
use App\Services\CheckoutService;
use App\Services\StorePaymentMethodService;
use App\Services\TenantBkashService;
use App\Services\TenantFlutterwaveService;
use App\Services\TenantDodoService;
use App\Services\TenantLigdicashService;
use App\Services\TenantMoyasarService;
use App\Services\TenantMyfatoorahService;
use App\Services\TenantPayfastService;
use App\Services\TenantPaypalService;
use App\Services\TenantPaystackService;
use App\Services\TenantPaymobService;
use App\Services\TenantRazorpayService;
use App\Services\TenantSslcommerzService;
use App\Services\TenantWaafipayService;
use App\Services\TenantXpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkout)
    {
    }

    /**
     * Live quote for the cart: available shipping methods for the customer's
     * region + server-side subtotal/discount/tax/shipping/total. Drives the
     * checkout UI (coupon box, shipping picker, summary rows).
     */
    public function quote(Request $req)
    {
        $user = Auth::guard('store')->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $data = $req->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.product_variant_id' => ['nullable', 'integer'],
            'items.*.qty' => ['required', 'numeric', 'min:1'],
            'shipping_method_id' => ['nullable', 'integer'],
            'warehouse_id' => ['nullable', 'integer'],
            'coupon_code' => ['nullable', 'string', 'max:60'],
            'payment_method' => ['nullable', 'string', 'max:40'],
            'pickup_branch_id' => ['nullable', 'integer'],
        ]);

        [$country, $state] = $this->locationFor($req, $user);

        // Cash on pickup hides the address form, so tax falls back to the
        // chosen branch's country here exactly as it does when the order is
        // created, and no shipping method is offered.
        $isPickup = StorePaymentMethodService::isPickup($data['payment_method'] ?? null);
        if ($isPickup && ! $country) {
            $country = Warehouse::where('id', (int) ($data['pickup_branch_id'] ?? 0))->value('country');
        }

        try {
            $warehouseIds = [$this->resolveWarehouseId((int) ($data['warehouse_id'] ?? 0))];
            $lines = $this->checkout->buildLineItems($data['items'], $warehouseIds);
            $subtotal = $lines['subtotal'];

            // Coupon is lenient here: an invalid code returns a message but the
            // quote still works (so the UI can show the totals + the error).
            $discount = 0.0;
            $couponCode = null;
            $couponError = null;
            if ($req->filled('coupon_code')) {
                try {
                    $c = $this->checkout->resolveCoupon($req->input('coupon_code'), $subtotal, $user->client_id);
                    $discount = $c['discount'];
                    $couponCode = $c['coupon']->code;
                } catch (CheckoutException $e) {
                    $couponError = $e->getMessage();
                }
            }

            $netSubtotal = round(max(0, $subtotal - $discount), 2);
            $tax = $this->checkout->resolveTax($netSubtotal, $country, $state);

            $cartProductIds = collect($data['items'])->pluck('product_id')->filter()->unique()->all();
            $methods = $isPickup ? collect() : $this->checkout->availableShippingMethods(
                $country,
                $cartProductIds
            )->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'price' => (float) $m->price,
            ])->values();

            // Shipping cost only if a valid method is chosen.
            $shippingCost = 0.0;
            if (! $isPickup && ($chosen = $req->input('shipping_method_id'))) {
                $match = $methods->firstWhere('id', (int) $chosen);
                if ($match) {
                    $shippingCost = (float) $match['price'];
                }
            }

            return response()->json([
                'subtotal' => $subtotal,
                'discount' => $discount,
                'coupon_code' => $couponCode,
                'coupon_error' => $couponError,
                'tax' => $tax['amount'],
                'tax_rate' => $tax['rate'],
                'shipping_cost' => $shippingCost,
                'total' => round($netSubtotal + $tax['amount'] + $shippingCost, 2),
                'shipping_methods' => $methods,
                'shipping_required' => $methods->isNotEmpty(),
                'shipping_configured' => ! $isPickup && $this->checkout->shippingConfigured(),
                'is_pickup' => $isPickup,
                'country' => $country,
            ]);
        } catch (CheckoutException $e) {
            return $e->toResponse();
        }
    }

    /** Country/state for shipping + tax: the posted address, else the customer profile. */
    private function locationFor(Request $req, $user): array
    {
        $client = $user && $user->client_id ? Client::find($user->client_id) : null;
        $country = trim((string) ($req->input('country') ?: $req->input('shipping_country') ?: ($client->country ?? '')));
        $state = trim((string) ($req->input('state') ?: $req->input('shipping_state') ?: ($client->state ?? '')));

        return [$country !== '' ? $country : null, $state !== '' ? $state : null];
    }

    /** Warehouse the order is booked under: requested → store default → first. */
    private function resolveWarehouseId(int $requested = 0): int
    {
        $warehouseId = $requested;
        if (! $warehouseId) {
            $warehouseId = (int) DB::table('store_settings')->value('default_warehouse_id');
        }
        if ($warehouseId && ! Warehouse::whereKey($warehouseId)->exists()) {
            $warehouseId = 0;
        }
        if (! $warehouseId) {
            $warehouseId = (int) Warehouse::value('id');
        }
        if (! $warehouseId) {
            throw new CheckoutException('No warehouse configured.', 422);
        }

        return $warehouseId;
    }

    /**
     * Amount a gateway session is opened for: recomputed server-side from the
     * cart when the client sends its items (flash prices, coupon, tax and
     * shipping included), else the posted amount. store() re-verifies the
     * charge against the final total either way.
     */
    private function serverTotal(Request $req, $user, float $fallback): float
    {
        $items = $req->input('items');
        if (! is_array($items) || ! $items) {
            return $fallback;
        }

        [$country, $state] = $this->locationFor($req, $user);
        $warehouseId = $this->resolveWarehouseId((int) $req->input('warehouse_id', 0));
        $breakdown = $this->checkout->calculate(
            $items, [$warehouseId], $country, $state,
            $req->input('shipping_method_id'), $req->input('coupon_code'), $user->client_id ?? null
        );

        return (float) $breakdown['total'];
    }

    /**
     * Create a Stripe PaymentIntent for the given cart total.
     * Called via AJAX before placing a credit-card order.
     */
    public function createPaymentIntent(Request $req)
    {
        $user = Auth::guard('store')->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $data = $req->validate([
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['required_with:items', 'integer'],
            'items.*.product_variant_id' => ['nullable', 'integer'],
            'items.*.qty' => ['required_with:items', 'numeric', 'min:1'],
            'shipping_method_id' => ['nullable', 'integer'],
            'coupon_code' => ['nullable', 'string', 'max:60'],
        ]);

        try {
            $chargeTotal = $this->serverTotal($req, $user, (float) ($data['amount'] ?? 0));
        } catch (CheckoutException $e) {
            return $e->toResponse();
        }
        if ($chargeTotal < 0.5) {
            return response()->json(['error' => 'Amount too small.'], 422);
        }

        $stripeSecret = \App\Models\PaymentSetting::current()->stripe_secret;
        if (! $stripeSecret) {
            return response()->json(['error' => 'Stripe is not configured.'], 500);
        }

        \Stripe\Stripe::setApiKey($stripeSecret);

        // Multi-Currency: charge in the store's ACTIVE currency (base with
        // rate 1 otherwise) — the client-sent currency is ignored (it used to
        // carry the display symbol, which regex-stripped to "usd"). The rate
        // is snapshotted into the intent metadata so the verify step in
        // store() compares against the SAME rate even if the admin edits
        // rates mid-checkout. The amount here is the client's BASE-currency
        // cart total; the real anti-tamper check happens in store() against
        // the server-computed total.
        $storeCurrency = \App\Services\StoreCurrencyService::active();
        $currency = strtolower($this->storeCurrencyCode());
        $amountInCents = $this->toGatewayMinorUnits($chargeTotal, (float) $storeCurrency['rate'], $currency);

        $intent = \Stripe\PaymentIntent::create([
            'amount' => $amountInCents,
            'currency' => $currency,
            'metadata' => [
                'client_id' => $user->client_id ?? $user->id,
                'currency_rate' => (string) $storeCurrency['rate'],
                'currency_id' => (string) ($storeCurrency['id'] ?? ''),
            ],
        ]);

        return response()->json([
            'clientSecret' => $intent->client_secret,
        ]);
    }

    /**
     * Create a Razorpay order for the given cart total.
     * Called via AJAX before opening the Razorpay checkout modal. The amount
     * is re-verified against the server-side cart total when the order is
     * placed in store().
     */
    public function createRazorpayOrder(Request $req, TenantRazorpayService $razorpay)
    {
        $user = Auth::guard('store')->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $data = $req->validate([
            'amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['required_with:items', 'integer'],
            'items.*.product_variant_id' => ['nullable', 'integer'],
            'items.*.qty' => ['required_with:items', 'numeric', 'min:1'],
            'shipping_method_id' => ['nullable', 'integer'],
            'coupon_code' => ['nullable', 'string', 'max:60'],
        ]);

        try {
            $chargeTotal = $this->serverTotal($req, $user, (float) ($data['amount'] ?? 0));
        } catch (CheckoutException $e) {
            return $e->toResponse();
        }
        if ($chargeTotal < 1) {
            return response()->json(['error' => 'Amount too small.'], 422);
        }

        if (! \App\Models\StoreSetting::paymentMethodEnabled('razorpay')) {
            return response()->json(['error' => 'This payment method is not available.'], 422);
        }

        if (! $razorpay->isConfigured()) {
            return response()->json(['error' => 'Razorpay is not configured.'], 500);
        }

        // Multi-Currency: the client sends the BASE-currency cart total;
        // charge it converted into the active store currency. store() re-runs
        // the same conversion against the server-computed total when the
        // payment is cross-checked.
        $storeCurrency = \App\Services\StoreCurrencyService::active();
        $currencyCode = $this->storeCurrencyCode();

        try {
            $order = $razorpay->createOrder(
                $this->toGatewayAmount($chargeTotal, (float) $storeCurrency['rate'], $currencyCode),
                'store_' . uniqid(),
                [
                    'source'    => 'storefront',
                    'client_id' => (string) ($user->client_id ?? $user->id),
                ],
                $currencyCode
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Storefront Razorpay order creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Could not initiate Razorpay payment.'], 500);
        }

        return response()->json([
            'key_id'   => $razorpay->keyId(),
            'order_id' => $order['id'],
            'amount'   => $order['amount'],   // paise
            'currency' => $order['currency'],
        ]);
    }

    /**
     * Create an XPay (PostEx) Payment Intent for the server-computed cart
     * total. Called via AJAX before the XPay card element confirms the card;
     * store() re-fetches the intent and cross-checks its amount when the
     * order is placed. Charges in the configured xpay_currency (PKR).
     */
    public function createXpayIntent(Request $req, TenantXpayService $xpay)
    {
        $user = Auth::guard('store')->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $data = $req->validate([
            'amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['required_with:items', 'integer'],
            'items.*.product_variant_id' => ['nullable', 'integer'],
            'items.*.qty' => ['required_with:items', 'numeric', 'min:1'],
            'shipping_method_id' => ['nullable', 'integer'],
            'coupon_code' => ['nullable', 'string', 'max:60'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'shipping_address' => ['nullable', 'string', 'max:250'],
            'shipping_city' => ['nullable', 'string', 'max:100'],
            'shipping_state' => ['nullable', 'string', 'max:100'],
            'shipping_zip' => ['nullable', 'string', 'max:30'],
            'shipping_country' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $chargeTotal = $this->serverTotal($req, $user, (float) ($data['amount'] ?? 0));
        } catch (CheckoutException $e) {
            return $e->toResponse();
        }
        if ($chargeTotal < 1) {
            return response()->json(['error' => 'Amount too small.'], 422);
        }

        if (! \App\Models\StoreSetting::paymentMethodEnabled('xpay')) {
            return response()->json(['error' => 'This payment method is not available.'], 422);
        }

        if (! $xpay->isConfigured()) {
            return response()->json(['error' => 'XPay is not configured.'], 500);
        }

        $client = $user->client_id ? Client::find($user->client_id) : null;

        $pick = fn (string $key, $fallback) => trim((string) (($data[$key] ?? '') !== '' ? $data[$key] : $fallback));

        try {
            $intent = $xpay->createIntent(
                $chargeTotal,
                [
                    'name'  => $pick('customer_name', $client->name ?? $user->name ?? ''),
                    'email' => trim((string) ($client->email ?? $user->email ?? '')),
                    'phone' => $pick('customer_phone', $client->phone ?? ''),
                ],
                [
                    'address1' => $pick('shipping_address', $client->adresse ?? ''),
                    'city'     => $pick('shipping_city', $client->city ?? ''),
                    'province' => $pick('shipping_state', $client->state ?? ''),
                    'zip'      => $pick('shipping_zip', $client->zip ?? ''),
                    'country'  => $pick('shipping_country', $client->country ?? ''),
                ],
                [
                    'source'          => 'storefront',
                    'client_id'       => (string) ($user->client_id ?? $user->id),
                    'order_reference' => 'store-' . ($user->client_id ?? $user->id) . '-' . time(),
                ]
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Storefront XPay intent creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Could not initiate XPay payment.'], 500);
        }

        return response()->json([
            'intentId'      => $intent['intent_id'],
            'clientSecret'  => $intent['client_secret'],
            'encryptionKey' => $intent['encryption_key'],
            'currency'      => $xpay->currency(),
        ]);
    }

    public function store(Request $req)
    {
        $user = Auth::guard('store')->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $data = $req->validate([
            'items'                      => ['required', 'array', 'min:1'],
            'items.*.product_id'         => ['required', 'integer'],
            'items.*.product_variant_id' => ['nullable', 'integer'],
            'items.*.qty'                => ['required', 'numeric', 'min:1'],
            'warehouse_id'               => ['nullable', 'integer'],
            'shipping_method_id'         => ['nullable', 'integer'],
            'coupon_code'                => ['nullable', 'string', 'max:60'],
            // Customer / shipping address (falls back to the customer profile)
            'customer_name'              => ['nullable', 'string', 'max:150'],
            'customer_phone'             => ['nullable', 'string', 'max:40'],
            'shipping_address'           => ['nullable', 'string', 'max:250'],
            'shipping_city'              => ['nullable', 'string', 'max:100'],
            'shipping_state'             => ['nullable', 'string', 'max:100'],
            'shipping_zip'               => ['nullable', 'string', 'max:30'],
            'shipping_country'           => ['nullable', 'string', 'max:100'],
            'payment_method'             => ['required', 'string', 'in:credit_card,mobile_money,cod,razorpay,payfast,ligdicash,dodo,waafipay,bkash,sslcommerz,paymob,myfatoorah,moyasar,xpay,paypal,paystack,flutterwave,gcash,bank_transfer,cash_on_pickup'],
            'pickup_branch_id'           => ['nullable', 'integer'],
            'stripe_payment_intent_id'   => ['nullable', 'string', 'max:128'],
            'xpay_intent_id'             => ['nullable', 'string', 'max:128'],
            'razorpay_order_id'          => ['nullable', 'string', 'max:64'],
            'razorpay_payment_id'        => ['nullable', 'string', 'max:64'],
            'razorpay_signature'         => ['nullable', 'string', 'max:256'],
            'waafipay_phone'             => ['nullable', 'string', 'regex:/^252\d{9}$/'],
        ]);

        $paymentMethod = $data['payment_method'];

        // Methods switched off in Online Store → Payment Gateway are refused
        // even if a stale checkout page still offers them.
        $storeSetting = \App\Models\StoreSetting::first();
        if (! \App\Models\StoreSetting::paymentMethodEnabled($paymentMethod, $storeSetting)) {
            return response()->json(['error' => 'This payment method is not available.'], 422);
        }

        // ---- Cash on pickup: paying at the counter means collecting at a
        // branch, so the shopper must have chosen one that is still open.
        $isPickup = StorePaymentMethodService::isPickup($paymentMethod);
        $pickupBranch = null;
        if ($isPickup) {
            $branches = StorePickupBranch::selectable();
            if ($branches->isEmpty()) {
                return response()->json(['error' => __('messages.NoPickupBranchAvailable')], 422);
            }
            $pickupBranch = $branches->firstWhere('warehouse_id', (int) ($data['pickup_branch_id'] ?? 0));
            if (! $pickupBranch) {
                return response()->json(['error' => __('messages.PickupBranchRequired')], 422);
            }
        }

        // For credit-card orders, verify the Stripe PaymentIntent succeeded.
        // The charged amount is cross-checked against the server-side total
        // once it has been computed below.
        $stripeIntent = null;
        if ($paymentMethod === 'credit_card') {
            $piId = $data['stripe_payment_intent_id'] ?? null;
            if (! $piId) {
                return response()->json(['error' => 'Stripe payment confirmation is required.'], 422);
            }

            $stripeSecret = \App\Models\PaymentSetting::current()->stripe_secret;
            if (! $stripeSecret) {
                return response()->json(['error' => 'Stripe is not configured.'], 500);
            }

            \Stripe\Stripe::setApiKey($stripeSecret);
            try {
                $stripeIntent = \Stripe\PaymentIntent::retrieve($piId);
                if ($stripeIntent->status !== 'succeeded') {
                    return response()->json(['error' => 'Payment has not been completed.'], 422);
                }
            } catch (\Exception $e) {
                return response()->json(['error' => 'Could not verify payment.'], 422);
            }
        }

        // For Razorpay orders, verify the handler signature before any work;
        // the captured amount is cross-checked against the server-side total below.
        $razorpay = null;
        if ($paymentMethod === 'razorpay') {
            if (empty($data['razorpay_order_id']) || empty($data['razorpay_payment_id']) || empty($data['razorpay_signature'])) {
                return response()->json(['error' => 'Razorpay payment confirmation is required.'], 422);
            }

            $razorpay = app(TenantRazorpayService::class);
            if (! $razorpay->isConfigured()) {
                return response()->json(['error' => 'Razorpay is not configured.'], 500);
            }

            if (! $razorpay->verifyPaymentSignature($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature'])) {
                return response()->json(['error' => 'Payment could not be verified.'], 422);
            }
        }

        // PayFast is redirect-based: the order is created as pending here and
        // the ITN callback marks it paid. Just confirm it is configured.
        $payfast = null;
        if ($paymentMethod === 'payfast') {
            $payfast = app(TenantPayfastService::class);
            if (! $payfast->isConfigured()) {
                return response()->json(['error' => 'PayFast is not configured.'], 500);
            }
        }

        // LigdiCash is redirect-based too: pending order now, callback
        // (re-verified via the confirm endpoint) marks it paid.
        $ligdicash = null;
        if ($paymentMethod === 'ligdicash') {
            $ligdicash = app(TenantLigdicashService::class);
            if (! $ligdicash->isConfigured()) {
                return response()->json(['error' => 'LigdiCash is not configured.'], 500);
            }
        }

        // Dodo Payments is redirect-based too: pending order now, signed
        // webhook marks it paid.
        $dodo = null;
        if ($paymentMethod === 'dodo') {
            $dodo = app(TenantDodoService::class);
            if (! $dodo->isConfigured()) {
                return response()->json(['error' => 'Dodo Payments is not configured.'], 500);
            }
        }

        // WaafiPay has two flows: the direct wallet API (synchronous USSD/PIN
        // debit, preferred — needs the payer's phone) and the Hosted Payment
        // Page redirect (pending order + signed webhook) as fallback.
        $waafipay = null;
        $waafipayDirect = false;
        if ($paymentMethod === 'waafipay') {
            $waafipay = app(TenantWaafipayService::class);
            $waafipayDirect = $waafipay->isDirectConfigured() && ! empty($data['waafipay_phone']);

            if ($waafipay->isDirectConfigured() && empty($data['waafipay_phone'])) {
                return response()->json(['error' => 'Please enter your mobile wallet number (2526XXXXXXXX).'], 422);
            }

            if (! $waafipayDirect && ! $waafipay->isConfigured()) {
                return response()->json(['error' => 'WaafiPay is not configured.'], 500);
            }
        }

        // bKash is redirect-based: pending order now, then the callback
        // executes the payment server-side and marks it paid (no webhook
        // exists for this flow). Just confirm it is configured.
        $bkash = null;
        if ($paymentMethod === 'bkash') {
            $bkash = app(TenantBkashService::class);
            if (! $bkash->isConfigured()) {
                return response()->json(['error' => 'bKash is not configured.'], 500);
            }
        }

        // SSLCommerz is redirect-based too: pending order now, then the
        // success callback / IPN (re-verified via the validation API) marks
        // it paid.
        $sslcommerz = null;
        if ($paymentMethod === 'sslcommerz') {
            $sslcommerz = app(TenantSslcommerzService::class);
            if (! $sslcommerz->isConfigured()) {
                return response()->json(['error' => 'SSLCommerz is not configured.'], 500);
            }
        }

        // Paymob is redirect-based too: pending order now, then the return
        // leg / HMAC-authenticated webhook (both re-verified server-side)
        // mark it paid.
        $paymob = null;
        if ($paymentMethod === 'paymob') {
            $paymob = app(TenantPaymobService::class);
            if (! $paymob->isConfigured()) {
                return response()->json(['error' => 'Paymob is not configured.'], 500);
            }
        }

        // MyFatoorah is redirect-based too: pending order now, then the
        // callback (re-verified via GetPaymentStatus) marks it paid.
        $myfatoorah = null;
        if ($paymentMethod === 'myfatoorah') {
            $myfatoorah = app(TenantMyfatoorahService::class);
            if (! $myfatoorah->isConfigured()) {
                return response()->json(['error' => 'MyFatoorah is not configured.'], 500);
            }
        }

        // Moyasar is redirect-based too: pending order now, then the callback
        // (re-verified by re-fetching the invoice) marks it paid.
        $moyasar = null;
        if ($paymentMethod === 'moyasar') {
            $moyasar = app(TenantMoyasarService::class);
            if (! $moyasar->isConfigured()) {
                return response()->json(['error' => 'Moyasar is not configured.'], 500);
            }
        }

        // XPay (embedded card element, like Stripe): the browser already
        // confirmed the Payment Intent; re-fetch it now (it must have
        // succeeded) and cross-check the amount against the server-computed
        // total below. The intent id is unique on online_orders so one
        // payment can never settle two orders.
        $xpay = null;
        $xpayVerify = null;
        if ($paymentMethod === 'xpay') {
            $xpayIntentId = trim((string) ($data['xpay_intent_id'] ?? ''));
            if ($xpayIntentId === '') {
                return response()->json(['error' => 'XPay payment confirmation is required.'], 422);
            }

            $xpay = app(TenantXpayService::class);
            if (! $xpay->isConfigured()) {
                return response()->json(['error' => 'XPay is not configured.'], 500);
            }

            if (\App\Models\OnlineOrder::where('xpay_intent_id', $xpayIntentId)->exists()) {
                return response()->json(['error' => 'This payment was already used for another order.'], 422);
            }

            try {
                $xpayVerify = $xpay->fetchIntent($xpayIntentId);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Storefront XPay intent fetch failed: ' . $e->getMessage());
                return response()->json(['error' => 'Could not verify payment.'], 422);
            }

            if (empty($xpayVerify['success'])) {
                return response()->json(['error' => 'Payment has not been completed.'], 422);
            }
        }

        // Resolve warehouse
        $warehouseId = (int) ($data['warehouse_id'] ?? 0);
        if (! $warehouseId) {
            $warehouseId = (int) DB::table('store_settings')->value('default_warehouse_id');
        }
        if ($warehouseId && ! Warehouse::whereKey($warehouseId)->exists()) {
            $warehouseId = 0;
        }
        if (! $warehouseId) {
            $warehouseId = (int) Warehouse::value('id');
        }
        if (! $warehouseId) {
            return response()->json(['error' => 'No warehouse configured.'], 422);
        }
        // A collected order is booked under the branch the shopper chose.
        if ($isPickup) {
            $warehouseId = (int) $pickupBranch->warehouse_id;
        }

        // ---- Customer information: address snapshot (shipping methods and tax
        // rates resolve from the country/state; the profile fills any blank) --
        $client = $user->client_id ? Client::find($user->client_id) : null;
        if (! $client) {
            return response()->json(['error' => __('messages.CustomerProfileMissing')], 422);
        }
        $customer = [
            'name' => trim((string) (($data['customer_name'] ?? '') !== '' ? $data['customer_name'] : $client->name)),
            'email' => trim((string) ($client->email ?: $user->email)),
            'phone' => trim((string) (($data['customer_phone'] ?? '') !== '' ? $data['customer_phone'] : $client->phone)),
            'address' => trim((string) (($data['shipping_address'] ?? '') !== '' ? $data['shipping_address'] : $client->adresse)),
            'city' => trim((string) (($data['shipping_city'] ?? '') !== '' ? $data['shipping_city'] : $client->city)),
            'state' => trim((string) (($data['shipping_state'] ?? '') !== '' ? $data['shipping_state'] : $client->state)),
            'zip' => trim((string) (($data['shipping_zip'] ?? '') !== '' ? $data['shipping_zip'] : $client->zip)),
            'country' => trim((string) (($data['shipping_country'] ?? '') !== '' ? $data['shipping_country'] : $client->country)),
        ];
        // A collected order needs no shipping address; the branch country
        // stands in for the customer's when tax has to be resolved.
        if ($isPickup) {
            $customer['country'] = $customer['country'] ?: (string) ($pickupBranch->warehouse->country ?? '');
        }
        // A delivery address is only mandatory once the store actually ships
        // (an active shipping method is configured); stores with no shipping
        // set-up keep the lighter checkout they had before.
        $requiredFields = (! $isPickup && $this->checkout->shippingConfigured()) ? ['name', 'address', 'country'] : ['name'];
        $missing = [];
        foreach ($requiredFields as $field) {
            if ($customer[$field] === '') {
                $missing[] = $field;
            }
        }
        if ($missing) {
            return response()->json(['error' => __('messages.CustomerInfoIncomplete'), 'missing' => $missing], 422);
        }

        // ---- Server-side recomputation: line prices (flash sales applied),
        // coupon, location tax and the chosen shipping method ----------------
        try {
            $breakdown = $this->checkout->calculate(
                $data['items'], [$warehouseId], $customer['country'] ?: null, $customer['state'] ?: null,
                $isPickup ? null : ($data['shipping_method_id'] ?? null),
                $data['coupon_code'] ?? null, $user->client_id, $isPickup
            );
        } catch (CheckoutException $e) {
            return $e->toResponse();
        }

        $normalizedItems = array_map(fn ($i) => [
            'product_id'         => $i['product_id'],
            'product_variant_id' => $i['product_variant_id'],
            'qty'                => $i['qty'],
            'price'              => $i['price'],
            'line_total'         => $i['line_total'],
            'TaxNet'             => $i['TaxNet'],
            'discount'           => $i['discount'],
            'discount_method'    => $i['discount_method'],
            'tax_method'         => $i['tax_method'],
            'is_preorder'        => $i['is_preorder'],
            'created_at'         => now(),
            'updated_at'         => now(),
        ], $breakdown['items']);
        $hasPreorderItems = (bool) $breakdown['has_preorder_items'];

        $grand    = (float) $breakdown['total'];
        $clientId = $user->client_id ?? null;

        // Persist the address back to the customer profile for next time.
        $client->fill([
            'name' => $customer['name'] ?: $client->name,
            'phone' => $customer['phone'] ?: $client->phone,
            'adresse' => $customer['address'] ?: $client->adresse,
            'city' => $customer['city'] ?: $client->city,
            'state' => $customer['state'] ?: $client->state,
            'zip' => $customer['zip'] ?: $client->zip,
            'country' => $customer['country'] ?: $client->country,
        ])->save();

        // Multi-Currency: the shopper's checkout currency (base with rate 1
        // when the module is off / nothing selected). Snapshotted on the
        // order below; totals stay in the BASE currency.
        $activeCurrency = \App\Services\StoreCurrencyService::active();

        // Stripe: the charged amount must equal the server-computed total
        // (anti-tamper). Multi-Currency: compare using the rate snapshotted
        // on the intent at creation (rate 1 for base-currency checkouts).
        if ($paymentMethod === 'credit_card' && $stripeIntent !== null) {
            $verifyRate = (float) ($stripeIntent->metadata['currency_rate'] ?? 0);
            if ($verifyRate <= 0) {
                $verifyRate = (float) $activeCurrency['rate'];
            }
            $expectedCents = $this->toGatewayMinorUnits($grand, $verifyRate, (string) $stripeIntent->currency);
            if ((int) $stripeIntent->amount !== $expectedCents) {
                \Illuminate\Support\Facades\Log::warning("Storefront checkout amount mismatch: intent {$stripeIntent->amount} vs expected {$expectedCents}");

                return response()->json(['error' => 'Payment amount mismatch.'], 422);
            }
        }

        // Razorpay: cross-check the captured payment against the server-side
        // total, and guard against a payment being reused for another order.
        // Multi-Currency: the charge was created for total × active rate in
        // the active currency, so the expectation is converted the same way.
        if ($paymentMethod === 'razorpay') {
            try {
                $payment = $razorpay->fetchPayment($data['razorpay_payment_id']);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Could not verify payment.'], 422);
            }

            $rzExpectedMinor = (int) round($this->toGatewayAmount($grand, (float) $activeCurrency['rate'], $this->storeCurrencyCode()) * 100);
            if (($payment['status'] ?? '') !== 'captured'
                || ($payment['order_id'] ?? '') !== $data['razorpay_order_id']
                || (int) ($payment['amount'] ?? -1) !== $rzExpectedMinor) {
                return response()->json(['error' => 'Payment has not been completed.'], 422);
            }

            $alreadyUsed = \App\Models\OnlineOrder::where('razorpay_payment_id', $data['razorpay_payment_id'])->exists();
            if ($alreadyUsed) {
                return response()->json(['error' => 'This payment was already used for another order.'], 422);
            }
        }

        // XPay: the intent's charge must cover the server-computed total in
        // the configured currency (fails closed inside amountMatches()).
        if ($paymentMethod === 'xpay' && $xpayVerify !== null) {
            if (! $xpay->amountMatches($xpayVerify, $grand)) {
                return response()->json(['error' => 'Payment amount mismatch.'], 422);
            }
        }

        $todayDate = now()->toDateString();
        $nowTime   = now()->format('H:i:s');
        $ref = method_exists(\App\Models\OnlineOrder::class, 'generateRef')
            ? \App\Models\OnlineOrder::generateRef()
            : ('SO-' . now()->format('Ymd') . '-' . str_pad((string) ((\App\Models\OnlineOrder::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT));

        // WaafiPay direct: perform the synchronous wallet debit BEFORE the
        // order exists. The payer approves the PIN prompt on their handset
        // and the API response itself is the confirmation — no webhook.
        $waafipayTransactionId = null;
        if ($paymentMethod === 'waafipay' && $waafipayDirect) {
            try {
                $purchase = $waafipay->directPurchase(
                    (string) $data['waafipay_phone'],
                    $grand,
                    'USD',
                    (string) ((int) (microtime(true) * 1000)),
                    $ref,
                    'Online store order ' . $ref
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Storefront WaafiPay direct purchase failed: ' . $e->getMessage());
                return response()->json(['error' => 'Could not process WaafiPay payment.'], 500);
            }

            if (! $purchase['approved']) {
                return response()->json(['error' => $purchase['message']], 422);
            }

            $waafipayTransactionId = $purchase['transaction_id'] ?: null;
        }

        $paymentStatus = in_array($paymentMethod, ['credit_card', 'razorpay', 'xpay'], true) || $waafipayTransactionId !== null
            ? 'paid'
            : 'pending';

        $order = DB::transaction(function () use ($clientId, $warehouseId, $grand, $normalizedItems, $todayDate, $nowTime, $ref, $paymentMethod, $paymentStatus, $data, $hasPreorderItems, $waafipayTransactionId, $activeCurrency, $breakdown, $customer, $xpayVerify, $isPickup, $pickupBranch) {
            $order = \App\Models\OnlineOrder::create([
                'date'                      => $todayDate,
                'time'                      => $nowTime,
                'ref'                       => $ref,
                'status'                    => 'pending',
                'has_preorder_items'        => $hasPreorderItems,
                'client_id'                 => $clientId,
                'warehouse_id'              => $warehouseId,
                'subtotal'                  => $breakdown['subtotal'],
                'discount'                  => $breakdown['discount'] ?? 0,
                'coupon_code'               => $breakdown['coupon']->code ?? null,
                'tax'                       => $breakdown['tax'],
                'tax_rate'                  => $breakdown['tax_rate'],
                'shipping_cost'             => $breakdown['shipping_cost'],
                'shipping_method_id'        => $breakdown['shipping_method']->id ?? null,
                'shipping_method_name'      => $breakdown['shipping_method']->name ?? null,
                'delivery_method'           => $isPickup ? 'pickup' : 'ship',
                'pickup_branch_id'          => $isPickup ? (int) $pickupBranch->warehouse_id : null,
                'total'                     => $grand,
                'customer_name'             => $customer['name'],
                'customer_email'            => $customer['email'],
                'customer_phone'            => $customer['phone'],
                'shipping_address'          => $customer['address'],
                'shipping_city'             => $customer['city'],
                'shipping_state'            => $customer['state'],
                'shipping_zip'              => $customer['zip'],
                'shipping_country'          => $customer['country'],
                'payment_method'            => $paymentMethod,
                'payment_status'            => $paymentStatus,
                'stripe_payment_intent_id'  => $data['stripe_payment_intent_id'] ?? null,
                'razorpay_order_id'         => $paymentMethod === 'razorpay' ? ($data['razorpay_order_id'] ?? null) : null,
                'razorpay_payment_id'       => $paymentMethod === 'razorpay' ? ($data['razorpay_payment_id'] ?? null) : null,
                'waafipay_transaction_id'   => $waafipayTransactionId,
                'xpay_intent_id'            => $paymentMethod === 'xpay' ? ($data['xpay_intent_id'] ?? null) : null,
                'xpay_transaction_id'       => $paymentMethod === 'xpay' ? ($xpayVerify['transaction_id'] ?? null) : null,
                // Multi-Currency: snapshot the shopper's checkout currency
                // (NULL/NULL when base). Totals stay in the base currency.
                'currency_id'               => $activeCurrency['is_base'] ? null : $activeCurrency['id'],
                'exchange_rate'             => $activeCurrency['is_base'] ? null : $activeCurrency['rate'],
            ]);

            $order->items()->createMany($normalizedItems);

            // Record the coupon redemption + bump usage (inside the tx).
            if (! empty($breakdown['coupon']) && ($breakdown['discount'] ?? 0) > 0) {
                StoreCouponRedemption::create([
                    'coupon_id' => $breakdown['coupon']->id,
                    'client_id' => $clientId,
                    'online_order_id' => $order->id,
                    'discount' => $breakdown['discount'],
                ]);
                StoreCoupon::whereKey($breakdown['coupon']->id)->increment('used_count');
            }

            return $order;
        });

        // Multi-Currency note: the redirect gateways below each charge a
        // FIXED currency (PayFast → ZAR, LigdiCash → XOF, Dodo → the product's
        // dashboard currency, WaafiPay → USD, bKash/SSLCommerz → BDT,
        // Paymob/MyFatoorah/Moyasar → their configured settings currency), so
        // their charges stay at the BASE-currency order total exactly as
        // before — their webhooks/callbacks verify against order.total
        // unchanged. The order still records the shopper's currency pair
        // above, so invoices/history display in their currency.

        $paymentRedirect = null;

        // Shared by the three hosted gateways below: the buyer-facing name and
        // the SERVER-computed total converted into the charge currency.
        $storeName = (string) (DB::table('store_settings')->value('store_name') ?: config('app.name'));
        $chargeCurrency = $this->storeCurrencyCode();
        $chargeAmount = $this->toGatewayAmount((float) $order->total, (float) ($order->exchange_rate ?: 1), $chargeCurrency);

        // PayPal: create an order with intent CAPTURE for the server total and
        // hand back the approve link. Capture happens on the return leg.
        if ($paymentMethod === 'paypal') {
            try {
                $pp = app(TenantPaypalService::class)->createOrder(
                    $chargeAmount,
                    $chargeCurrency,
                    'Order ' . $order->ref,
                    ['online_order_id' => $order->id, 'client_id' => $clientId],
                    // PayPal appends ?token={paypal_order_id} to both URLs.
                    url('/api/store/paypal/return'),
                    url('/api/store/paypal/cancel'),
                    $storeName
                );

                $order->update(['paypal_order_id' => $pp['id']]);
                $paymentRedirect = $pp['url'];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Storefront PayPal order failed: ' . $e->getMessage());
                return response()->json(['error' => 'Could not initiate PayPal payment.'], 500);
            }
        }

        // Paystack: initialize a hosted transaction with a server-generated
        // reference; the return leg verifies it before anything is marked paid.
        if ($paymentMethod === 'paystack') {
            try {
                $ps = app(TenantPaystackService::class)->initializeTransaction(
                    $chargeAmount,
                    $chargeCurrency,
                    (string) ($customer['email'] ?? ''),
                    ['online_order_id' => $order->id, 'client_id' => $clientId],
                    url('/api/store/paystack/return')
                );

                $order->update(['paystack_reference' => $ps['reference']]);
                $paymentRedirect = $ps['url'];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Storefront Paystack init failed: ' . $e->getMessage());
                return response()->json(['error' => 'Could not initiate Paystack payment.'], 500);
            }
        }

        // Flutterwave v3 Standard: hosted payment with a server-generated
        // tx_ref; the return leg verifies by reference.
        if ($paymentMethod === 'flutterwave') {
            try {
                $fw = app(TenantFlutterwaveService::class)->initializePayment(
                    $chargeAmount,
                    $chargeCurrency,
                    (string) ($customer['email'] ?? ''),
                    (string) ($customer['name'] ?? ''),
                    'Order ' . $order->ref,
                    ['online_order_id' => $order->id, 'client_id' => $clientId],
                    url('/api/store/flutterwave/return'),
                    $storeName
                );

                $order->update(['flutterwave_tx_ref' => $fw['tx_ref']]);
                $paymentRedirect = $fw['url'];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Storefront Flutterwave init failed: ' . $e->getMessage());
                return response()->json(['error' => 'Could not initiate Flutterwave payment.'], 500);
            }
        }

        // PayFast: hand back the signed redirect URL; the ITN callback will
        // flip the order to paid once the buyer completes payment.
        if ($paymentMethod === 'payfast') {
            try {
                $paymentRedirect = $payfast->buildRedirectUrl(
                    $order,
                    store_base_url('thank-you'),
                    store_base_url('checkout'),
                    store_base_url('payfast/itn')
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Storefront PayFast redirect build failed: ' . $e->getMessage());
                return response()->json(['error' => 'Could not initiate PayFast payment.'], 500);
            }
        }

        // LigdiCash: create the hosted invoice, store its token on the order
        // (the callback re-verifies with it), and hand back the payment URL.
        if ($paymentMethod === 'ligdicash') {
            try {
                $storeName = (string) (DB::table('store_settings')->value('store_name') ?: config('app.name'));

                $invoice = $ligdicash->createInvoice(
                    (float) $order->total,
                    $order->ref,
                    $storeName,
                    store_base_url('thank-you'),
                    store_base_url('checkout'),
                    store_base_url('ligdicash/callback')
                );

                $order->update(['ligdicash_token' => $invoice['token']]);
                $paymentRedirect = $invoice['url'];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Storefront LigdiCash invoice failed: ' . $e->getMessage());
                return response()->json(['error' => 'Could not initiate LigdiCash payment.'], 500);
            }
        }

        // Dodo Payments: create the hosted checkout session, store its id on
        // the order (the webhook cross-checks it), and hand back the URL.
        if ($paymentMethod === 'dodo') {
            try {
                $session = $dodo->createCheckoutSession(
                    (float) $order->total,
                    $order->ref,
                    $user->email ?? null,
                    store_base_url('thank-you')
                );

                $order->update(['dodo_session_id' => $session['session_id']]);
                $paymentRedirect = $session['url'];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Storefront Dodo checkout failed: ' . $e->getMessage());
                return response()->json(['error' => 'Could not initiate Dodo payment.'], 500);
            }
        }

        // WaafiPay HPP fallback: create the hosted session (referenceId =
        // order ref, echoed back by the webhook) and hand back the URL.
        // Skipped for the direct flow — that order is already paid.
        if ($paymentMethod === 'waafipay' && ! $waafipayDirect) {
            try {
                $paymentRedirect = $waafipay->createHppSession(
                    (float) $order->total,
                    'USD',
                    $order->ref,
                    store_base_url('thank-you'),
                    store_base_url('checkout')
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Storefront WaafiPay HPP failed: ' . $e->getMessage());
                return response()->json(['error' => 'Could not initiate WaafiPay payment.'], 500);
            }
        }

        // bKash: create the tokenized-checkout payment, store its paymentID on
        // the order (the callback resolves and executes by it), and hand back
        // the hosted bkashURL. The callback lives on the sessionless /api
        // stack — see routes/tenant_api.php for why.
        if ($paymentMethod === 'bkash') {
            try {
                $client = $user->client_id ? \App\Models\Client::find($user->client_id) : null;

                $bk = $bkash->createPayment(
                    (float) $order->total,
                    (string) ($client->phone ?? $user->email ?? $order->ref),
                    $order->ref,
                    url('/api/store/bkash/return')
                );

                $order->update(['bkash_payment_id' => $bk['payment_id']]);
                $paymentRedirect = $bk['url'];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Storefront bKash create failed: ' . $e->getMessage());
                return response()->json(['error' => 'Could not initiate bKash payment.'], 500);
            }
        }

        // SSLCommerz: create the hosted session with a self-generated tran_id,
        // store it on the order (the callbacks/IPN resolve by it and the
        // validation API cross-checks it), and hand back the GatewayPageURL.
        if ($paymentMethod === 'sslcommerz') {
            try {
                $client = $user->client_id ? \App\Models\Client::find($user->client_id) : null;

                $ssl = $sslcommerz->initializeSession(
                    (float) $order->total,
                    $order->ref,
                    [
                        'name'    => (string) ($client->name ?? ''),
                        'email'   => (string) ($user->email ?? ''),
                        'phone'   => (string) ($client->phone ?? ''),
                        'address' => (string) ($client->adresse ?? ''),
                    ],
                    url('/api/store/sslcommerz/return'),
                    url('/api/store/sslcommerz/fail'),
                    url('/api/store/sslcommerz/cancel'),
                    url('/api/store/sslcommerz/ipn')
                );

                $order->update(['sslcommerz_tran_id' => $ssl['tran_id']]);
                $paymentRedirect = $ssl['url'];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Storefront SSLCommerz session failed: ' . $e->getMessage());
                return response()->json(['error' => 'Could not initiate SSLCommerz payment.'], 500);
            }
        }

        // Paymob: create the unified-checkout intention with a self-generated
        // special_reference, store it on the order (the return leg / webhook
        // resolve by it and the inquiry API cross-checks it), and hand back
        // the hosted checkout URL. Charges in the configured paymob_currency.
        if ($paymentMethod === 'paymob') {
            try {
                $client = $user->client_id ? \App\Models\Client::find($user->client_id) : null;

                $intention = $paymob->createIntention(
                    (float) $order->total,
                    $order->ref,
                    [
                        'name'  => (string) ($client->name ?? ''),
                        'email' => (string) ($user->email ?? ''),
                        'phone' => (string) ($client->phone ?? ''),
                    ],
                    url('/api/store/paymob/return'),
                    url('/api/store/paymob/webhook')
                );

                $order->update(['paymob_reference' => $intention['reference']]);
                $paymentRedirect = $intention['url'];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Storefront Paymob intention failed: ' . $e->getMessage());
                return response()->json(['error' => 'Could not initiate Paymob payment.'], 500);
            }
        }

        // MyFatoorah: create the hosted invoice link, store its InvoiceId on
        // the order (the callback verification resolves by it), and hand back
        // the InvoiceURL. Charges in the configured myfatoorah_currency.
        if ($paymentMethod === 'myfatoorah') {
            try {
                $client = $user->client_id ? \App\Models\Client::find($user->client_id) : null;

                $invoice = $myfatoorah->createInvoice(
                    (float) $order->total,
                    $order->ref,
                    [
                        'name'  => (string) ($client->name ?? ''),
                        'email' => (string) ($user->email ?? ''),
                        'phone' => (string) ($client->phone ?? ''),
                    ],
                    url('/api/store/myfatoorah/return'),
                    store_base_url('checkout')
                );

                $order->update(['myfatoorah_invoice_id' => $invoice['invoice_id']]);
                $paymentRedirect = $invoice['url'];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Storefront MyFatoorah invoice failed: ' . $e->getMessage());
                return response()->json(['error' => 'Could not initiate MyFatoorah payment.'], 500);
            }
        }

        // Moyasar: create the hosted invoice, store its id on the order (the
        // callback verification re-fetches by it), and hand back the payment
        // page URL. Charges in the configured moyasar_currency.
        if ($paymentMethod === 'moyasar') {
            try {
                $invoice = $moyasar->createInvoice(
                    (float) $order->total,
                    $order->ref,
                    url('/api/store/moyasar/return'),
                    store_base_url('checkout')
                );

                $order->update(['moyasar_invoice_id' => $invoice['invoice_id']]);
                $paymentRedirect = $invoice['url'];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Storefront Moyasar invoice failed: ' . $e->getMessage());
                return response()->json(['error' => 'Could not initiate Moyasar payment.'], 500);
            }
        }

        return response()->json([
            'id'               => $order->id,
            'ref'              => $order->ref,
            'status'           => $order->status,
            'date'             => (string) $order->date,
            'time'             => (string) $order->time,
            'subtotal'         => (float) $order->subtotal,
            'discount'         => (float) $order->discount,
            'coupon_code'      => $order->coupon_code,
            'tax'              => (float) $order->tax,
            'shipping_cost'    => (float) $order->shipping_cost,
            'total'            => (float) $order->total,
            'payment_method'   => $order->payment_method,
            'payment_status'   => $order->payment_status,
            'payment_redirect' => $paymentRedirect,
        ], 201);
    }

    /**
     * PayFast ITN (Instant Transaction Notification) callback.
     * Public endpoint — authenticated by the ITN signature plus a server-side
     * confirmation against PayFast, both inside TenantPayfastService.
     */
    public function payfastItn(Request $req, TenantPayfastService $payfast)
    {
        $data = [];
        parse_str($req->getContent(), $data);

        if (! $payfast->verifyItn($data)) {
            return response('Invalid ITN', 400);
        }

        $paymentStatus = strtoupper((string) ($data['payment_status'] ?? ''));
        if ($paymentStatus !== 'COMPLETE') {
            // Acknowledged but not acted on (PENDING / CANCELLED / FAILED).
            return response('OK', 200);
        }

        $ref         = (string) ($data['m_payment_id'] ?? '');
        $pfPaymentId = (string) ($data['pf_payment_id'] ?? '');

        $order = \App\Models\OnlineOrder::where('ref', $ref)
            ->where('payment_method', 'payfast')
            ->first();

        if (! $order) {
            \Illuminate\Support\Facades\Log::warning('PayFast ITN: order not found', ['m_payment_id' => $ref]);
            return response('OK', 200);
        }

        if ($order->payment_status === 'paid') {
            return response('OK', 200);
        }

        $amountGross = (float) ($data['amount_gross'] ?? -1);
        if (abs($amountGross - (float) $order->total) > 0.01) {
            \Illuminate\Support\Facades\Log::error('PayFast ITN: amount mismatch — order left unpaid', [
                'order_ref' => $ref,
                'expected'  => (float) $order->total,
                'received'  => $amountGross,
            ]);
            return response('OK', 200);
        }

        if ($pfPaymentId !== '' && \App\Models\OnlineOrder::where('payfast_payment_id', $pfPaymentId)->exists()) {
            \Illuminate\Support\Facades\Log::error('PayFast ITN: payment already used for another order', [
                'pf_payment_id' => $pfPaymentId,
            ]);
            return response('OK', 200);
        }

        // Guarded update: only one concurrent ITN delivery wins.
        \App\Models\OnlineOrder::whereKey($order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status'     => 'paid',
                'payfast_payment_id' => $pfPaymentId ?: null,
                'updated_at'         => now(),
            ]);

        return response('OK', 200);
    }

    /**
     * LigdiCash callback. The payload is unsigned and its token field is
     * always empty, so it is only used to locate our order; the actual status
     * comes from re-verifying the token stored at checkout with LigdiCash's
     * confirm endpoint ("never act on the payload — always re-verify").
     */
    public function ligdicashCallback(Request $req, TenantLigdicashService $ligdicash)
    {
        if (! $ligdicash->isConfigured()) {
            return response('Not configured', 400);
        }

        // Delivered both as JSON and form-encoded — accept either shape.
        $data = json_decode($req->getContent(), true);
        if (! is_array($data)) {
            parse_str($req->getContent(), $data);
        }

        $customData = $ligdicash->normalizeCustomData($data['custom_data'] ?? []);
        $ref        = (string) ($customData['order_ref'] ?? '');

        if ($ref === '') {
            \Illuminate\Support\Facades\Log::warning('LigdiCash callback discarded: no order_ref in custom_data');
            return response('OK', 200);
        }

        $order = \App\Models\OnlineOrder::where('ref', $ref)
            ->where('payment_method', 'ligdicash')
            ->first();

        if (! $order || ! $order->ligdicash_token) {
            \Illuminate\Support\Facades\Log::warning('LigdiCash callback discarded: no matching order/token', ['ref' => $ref]);
            return response('OK', 200);
        }

        if ($order->payment_status === 'paid') {
            return response('OK', 200);
        }

        try {
            $verification = $ligdicash->confirmByToken($order->ligdicash_token);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('LigdiCash callback confirm failed: ' . $e->getMessage(), ['ref' => $ref]);
            // Non-200 so LigdiCash retries while the confirm endpoint is unreachable.
            return response('Verification failed', 500);
        }

        if ($verification['status'] !== 'completed') {
            return response('OK', 200);
        }

        // XOF is integer-valued — the invoice was created for round(total).
        if ($verification['amount'] !== null
            && (int) round($verification['amount']) !== (int) round((float) $order->total)) {
            \Illuminate\Support\Facades\Log::error('LigdiCash callback: amount mismatch — order left unpaid', [
                'order_ref' => $ref,
                'expected'  => (float) $order->total,
                'received'  => $verification['amount'],
            ]);
            return response('OK', 200);
        }

        // Guarded update: only one concurrent callback delivery wins.
        \App\Models\OnlineOrder::whereKey($order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status' => 'paid',
                'updated_at'     => now(),
            ]);

        return response('OK', 200);
    }

    /**
     * Dodo Payments webhook (Standard Webhooks spec). Public endpoint —
     * authenticated by the webhook-signature HMAC over
     * "{webhook-id}.{webhook-timestamp}.{raw body}".
     */
    public function dodoWebhook(Request $req, TenantDodoService $dodo)
    {
        $payload = $req->getContent();

        $verified = $dodo->verifyWebhookSignature(
            $payload,
            (string) $req->header('webhook-signature', ''),
            (string) $req->header('webhook-id', ''),
            (string) $req->header('webhook-timestamp', '')
        );

        if (! $verified) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $body = json_decode($payload, true) ?: [];

        if (($body['type'] ?? '') !== 'payment.succeeded') {
            // Other events are acknowledged and ignored.
            return response()->json(['status' => 'ok']);
        }

        $data = (array) ($body['data'] ?? []);
        $meta = (array) ($data['metadata'] ?? []);

        $ref           = (string) ($meta['order_ref'] ?? '');
        $dodoPaymentId = (string) ($data['payment_id'] ?? '');
        $sessionId     = (string) ($data['checkout_session_id'] ?? '');

        if ($ref === '') {
            \Illuminate\Support\Facades\Log::warning('Dodo webhook: no order_ref in metadata');
            return response()->json(['status' => 'ok']);
        }

        $order = \App\Models\OnlineOrder::where('ref', $ref)
            ->where('payment_method', 'dodo')
            ->first();

        if (! $order) {
            \Illuminate\Support\Facades\Log::warning('Dodo webhook: order not found', ['ref' => $ref]);
            return response()->json(['status' => 'ok']);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['status' => 'ok']);
        }

        // Cross-check the session when both sides know it.
        if ($sessionId !== '' && $order->dodo_session_id && $sessionId !== $order->dodo_session_id) {
            \Illuminate\Support\Facades\Log::error('Dodo webhook: session mismatch — order left unpaid', [
                'order_ref' => $ref,
                'expected'  => $order->dodo_session_id,
                'received'  => $sessionId,
            ]);
            return response()->json(['status' => 'ok']);
        }

        // total_amount is tax-inclusive (merchant of record), so it may
        // legitimately exceed the order total — but never be less.
        if (isset($data['total_amount'])
            && (int) $data['total_amount'] < (int) round((float) $order->total * 100)) {
            \Illuminate\Support\Facades\Log::error('Dodo webhook: short payment — order left unpaid', [
                'order_ref' => $ref,
                'expected'  => (int) round((float) $order->total * 100),
                'received'  => (int) $data['total_amount'],
            ]);
            return response()->json(['status' => 'ok']);
        }

        if ($dodoPaymentId !== '' && \App\Models\OnlineOrder::where('dodo_payment_id', $dodoPaymentId)->exists()) {
            \Illuminate\Support\Facades\Log::error('Dodo webhook: payment already used for another order', [
                'dodo_payment_id' => $dodoPaymentId,
            ]);
            return response()->json(['status' => 'ok']);
        }

        // Guarded update: only one concurrent delivery wins.
        \App\Models\OnlineOrder::whereKey($order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status'  => 'paid',
                'dodo_payment_id' => $dodoPaymentId ?: null,
                'updated_at'      => now(),
            ]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * WaafiPay webhook. Public endpoint — authenticated by the HMAC-SHA256
     * signature over the raw body (webhook endpoint secret). Unsigned
     * "webhook.test" pings sent at registration are acknowledged only.
     */
    public function waafipayWebhook(Request $req, TenantWaafipayService $waafipay)
    {
        $payload = $req->getContent();
        $body    = json_decode($payload, true) ?: [];

        // Registration ping: unsigned by design; acknowledge and do nothing.
        if (($body['event'] ?? $body['type'] ?? '') === 'webhook.test') {
            return response()->json(['status' => 'ok']);
        }

        $signature = (string) ($req->header('X-Signature')
            ?? $req->header('X-Webhook-Signature')
            ?? $req->header('webhook-signature')
            ?? $req->header('Signature', ''));

        if (! $waafipay->verifyWebhookSignature($payload, $signature)) {
            \Illuminate\Support\Facades\Log::error('WaafiPay webhook rejected: signature verification failed');
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        // Payload fields vary slightly between deliveries — read defensively.
        $data = (array) ($body['data'] ?? $body['params'] ?? $body);

        $ref           = (string) ($data['referenceId'] ?? $data['reference_id'] ?? '');
        $transactionId = (string) ($data['transactionId'] ?? $data['transaction_id'] ?? '');
        $state         = strtoupper((string) ($data['state'] ?? $data['status'] ?? ''));

        if (! in_array($state, ['APPROVED', 'COMPLETED'], true)) {
            // Failures/cancellations are acknowledged but not acted on.
            return response()->json(['status' => 'ok']);
        }

        if ($ref === '') {
            \Illuminate\Support\Facades\Log::warning('WaafiPay webhook: no referenceId in payload');
            return response()->json(['status' => 'ok']);
        }

        $order = \App\Models\OnlineOrder::where('ref', $ref)
            ->where('payment_method', 'waafipay')
            ->first();

        if (! $order) {
            \Illuminate\Support\Facades\Log::warning('WaafiPay webhook: order not found', ['ref' => $ref]);
            return response()->json(['status' => 'ok']);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['status' => 'ok']);
        }

        if (isset($data['amount']) && abs((float) $data['amount'] - (float) $order->total) > 0.01) {
            \Illuminate\Support\Facades\Log::error('WaafiPay webhook: amount mismatch — order left unpaid', [
                'order_ref' => $ref,
                'expected'  => (float) $order->total,
                'received'  => (float) $data['amount'],
            ]);
            return response()->json(['status' => 'ok']);
        }

        if ($transactionId !== '' && \App\Models\OnlineOrder::where('waafipay_transaction_id', $transactionId)->exists()) {
            \Illuminate\Support\Facades\Log::error('WaafiPay webhook: transaction already used for another order', [
                'waafipay_transaction_id' => $transactionId,
            ]);
            return response()->json(['status' => 'ok']);
        }

        // Guarded update: only one concurrent delivery wins.
        \App\Models\OnlineOrder::whereKey($order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status'          => 'paid',
                'waafipay_transaction_id' => $transactionId ?: null,
                'updated_at'              => now(),
            ]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * bKash browser callback: GET ?paymentID&status=success|failure|cancel.
     * The status param is only trusted for cancel/failure; success is
     * EXECUTED server-side (query endpoint as fallback for a retried or
     * timed-out execute — bKash can debit and still time out) before the
     * order is marked paid. bKash has no webhook, so this is the only
     * confirmation channel. Registered on the sessionless /api stack.
     */
    public function bkashReturn(Request $req, TenantBkashService $bkash)
    {
        $thankYou = store_base_url('thank-you');
        $checkout = store_base_url('checkout');

        $paymentId = (string) $req->query('paymentID', '');
        $order = $paymentId !== ''
            ? \App\Models\OnlineOrder::where('bkash_payment_id', $paymentId)->where('payment_method', 'bkash')->first()
            : null;

        if (! $order) {
            return redirect($checkout);
        }

        // Idempotent: a refresh of the callback page must not double-process.
        if ($order->payment_status === 'paid') {
            return redirect($thankYou);
        }

        $status = strtolower((string) $req->query('status', ''));
        if ($status !== 'success') {
            // cancel / failure: the order stays pending-unpaid (same as an
            // abandoned PayFast redirect); the buyer can retry from checkout.
            return redirect($checkout);
        }

        try {
            $verify = $bkash->executePayment($paymentId);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('bKash execute threw: ' . $e->getMessage(), ['payment_id' => $paymentId]);
            $verify = ['success' => false, 'trx_id' => null];
        }

        if (empty($verify['success'])) {
            try {
                $verify = $bkash->queryPayment($paymentId);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('bKash query threw: ' . $e->getMessage(), ['payment_id' => $paymentId]);
                $verify = ['success' => false, 'trx_id' => null];
            }
        }

        if (empty($verify['success'])) {
            return redirect($checkout);
        }

        $trxId = (string) ($verify['trx_id'] ?? '');
        if ($trxId !== '' && \App\Models\OnlineOrder::where('bkash_trx_id', $trxId)->exists()) {
            \Illuminate\Support\Facades\Log::error('bKash callback: transaction already used for another order', [
                'bkash_trx_id' => $trxId,
            ]);
            return redirect($checkout);
        }

        // Guarded update: only one concurrent callback hit wins.
        \App\Models\OnlineOrder::whereKey($order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status' => 'paid',
                'bkash_trx_id'   => $trxId ?: null,
                'updated_at'     => now(),
            ]);

        return redirect($thankYou);
    }

    /**
     * SSLCommerz success callback: the shopper's browser is POSTed here
     * cross-site with tran_id + val_id. The POST is NEVER trusted — the
     * transaction is validated via the validation API (tran_id + BDT amount
     * cross-checked) before the order is marked paid. Registered on the
     * sessionless /api stack so the response cannot clobber the shopper's
     * SameSite=Lax session cookie.
     */
    public function sslcommerzReturn(Request $req, TenantSslcommerzService $sslcommerz)
    {
        $thankYou = store_base_url('thank-you');
        $checkout = store_base_url('checkout');

        $tranId = (string) $req->input('tran_id', '');
        $order = $tranId !== ''
            ? \App\Models\OnlineOrder::where('sslcommerz_tran_id', $tranId)->where('payment_method', 'sslcommerz')->first()
            : null;

        if (! $order) {
            return redirect($checkout);
        }

        // Idempotent: a refresh (or the IPN winning first) lands on thank-you.
        if ($order->payment_status === 'paid') {
            return redirect($thankYou);
        }

        try {
            $valId = (string) $req->input('val_id', '');
            $verify = $valId !== ''
                ? $sslcommerz->validateTransaction($valId, $tranId, (float) $order->total)
                : $sslcommerz->validateByTranId($tranId, (float) $order->total);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SSLCommerz validate threw: ' . $e->getMessage(), ['tran_id' => $tranId]);
            $verify = ['success' => false, 'val_id' => null];
        }

        if (empty($verify['success'])) {
            return redirect($checkout);
        }

        // Guarded update: only one of the callback/IPN deliveries wins.
        \App\Models\OnlineOrder::whereKey($order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status'     => 'paid',
                'sslcommerz_val_id'  => $verify['val_id'] ?: null,
                'updated_at'         => now(),
            ]);

        return redirect($thankYou);
    }

    /**
     * SSLCommerz fail/cancel callbacks: the payment did not complete on the
     * hosted page. The order stays pending-unpaid (same as an abandoned
     * PayFast redirect); the buyer is sent back to checkout to retry.
     */
    public function sslcommerzFailed(Request $req)
    {
        return redirect(store_base_url('checkout'));
    }

    /**
     * SSLCommerz IPN — the server-to-server backstop when the browser
     * callback never lands. Authenticated by the verify_sign MD5 inside the
     * body; a VALID status is then still confirmed through the validation
     * API before the order is marked paid.
     */
    public function sslcommerzIpn(Request $req, TenantSslcommerzService $sslcommerz)
    {
        if (! $sslcommerz->isConfigured()) {
            return response('Not configured', 400);
        }

        $data = [];
        parse_str($req->getContent(), $data);

        if (empty($data['tran_id'])) {
            return response('OK', 200);
        }

        if (! $sslcommerz->checkIpnSignature($data)) {
            \Illuminate\Support\Facades\Log::error('SSLCommerz IPN rejected: verify_sign mismatch', ['tran_id' => $data['tran_id']]);
            return response('Invalid signature', 400);
        }

        $status = strtoupper((string) ($data['status'] ?? ''));
        if (! in_array($status, ['VALID', 'VALIDATED'], true)) {
            // FAILED / CANCELLED / EXPIRED: acknowledged but not acted on.
            return response('OK', 200);
        }

        $tranId = (string) $data['tran_id'];
        $order = \App\Models\OnlineOrder::where('sslcommerz_tran_id', $tranId)
            ->where('payment_method', 'sslcommerz')
            ->first();

        if (! $order) {
            \Illuminate\Support\Facades\Log::warning('SSLCommerz IPN: order not found', ['tran_id' => $tranId]);
            return response('OK', 200);
        }

        if ($order->payment_status === 'paid') {
            return response('OK', 200);
        }

        try {
            $valId = (string) ($data['val_id'] ?? '');
            $verify = $valId !== ''
                ? $sslcommerz->validateTransaction($valId, $tranId, (float) $order->total)
                : $sslcommerz->validateByTranId($tranId, (float) $order->total);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SSLCommerz IPN validate failed: ' . $e->getMessage(), ['tran_id' => $tranId]);
            // Non-200 so SSLCommerz retries while the validation API is unreachable.
            return response('Verification failed', 500);
        }

        if (empty($verify['success'])) {
            return response('OK', 200);
        }

        // Guarded update: only one of the callback/IPN deliveries wins.
        \App\Models\OnlineOrder::whereKey($order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status'    => 'paid',
                'sslcommerz_val_id' => $verify['val_id'] ?: null,
                'updated_at'        => now(),
            ]);

        return response('OK', 200);
    }

    /**
     * Paymob return leg — the browser lands here after the unified checkout.
     * The query carries the transaction fields, but nothing from it is
     * trusted: the order is resolved by merchant_order_id and the charge is
     * re-verified through the transaction inquiry API (amount/currency
     * cross-checked) before it is marked paid.
     */
    public function paymobReturn(Request $req, TenantPaymobService $paymob)
    {
        $thankYou = store_base_url('thank-you');
        $checkout = store_base_url('checkout');

        $reference = (string) $req->input('merchant_order_id', '');
        $order = $reference !== ''
            ? \App\Models\OnlineOrder::where('paymob_reference', $reference)->where('payment_method', 'paymob')->first()
            : null;

        if (! $order || ! $paymob->isConfigured()) {
            return redirect($checkout);
        }

        // Idempotent: a refresh (or the webhook winning first) lands on thank-you.
        if ($order->payment_status === 'paid') {
            return redirect($thankYou);
        }

        try {
            $verify = $paymob->verifyByReference($reference, (float) $order->total);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Paymob verify threw: ' . $e->getMessage(), ['reference' => $reference]);
            $verify = ['success' => false, 'transaction_id' => null];
        }

        if (empty($verify['success'])) {
            return redirect($checkout);
        }

        // Guarded update: only one of the return/webhook deliveries wins.
        \App\Models\OnlineOrder::whereKey($order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status'        => 'paid',
                'paymob_transaction_id' => $verify['transaction_id'] ?: null,
                'updated_at'            => now(),
            ]);

        return redirect($thankYou);
    }

    /**
     * Paymob transaction processed callback — the server-to-server backstop
     * when the browser return never lands. Authenticated by the HMAC query
     * parameter over the transaction object; the (authenticated) object is
     * then still cross-checked for success/amount/currency before the order
     * is marked paid.
     */
    public function paymobWebhook(Request $req, TenantPaymobService $paymob)
    {
        if (! $paymob->isConfigured()) {
            return response('Not configured', 400);
        }

        $body = json_decode($req->getContent(), true);
        $obj  = is_array($body) ? ($body['obj'] ?? null) : null;

        if (! is_array($obj) || strtoupper((string) ($body['type'] ?? '')) !== 'TRANSACTION' || empty($obj['id'])) {
            return response('OK', 200);
        }

        if (! $paymob->checkWebhookHmac($obj, (string) $req->query('hmac', ''))) {
            \Illuminate\Support\Facades\Log::error('Paymob webhook rejected: HMAC mismatch', ['transaction_id' => $obj['id']]);
            return response('Invalid signature', 400);
        }

        $reference = (string) ($obj['order']['merchant_order_id'] ?? '');
        $order = $reference !== ''
            ? \App\Models\OnlineOrder::where('paymob_reference', $reference)->where('payment_method', 'paymob')->first()
            : null;

        if (! $order) {
            \Illuminate\Support\Facades\Log::warning('Paymob webhook: order not found', ['reference' => $reference]);
            return response('OK', 200);
        }

        if ($order->payment_status === 'paid') {
            return response('OK', 200);
        }

        $verify = $paymob->checkTransaction($obj, (float) $order->total);
        if (empty($verify['success'])) {
            // Declined / pending / mismatch: acknowledged but not acted on.
            return response('OK', 200);
        }

        // Guarded update: only one of the return/webhook deliveries wins.
        \App\Models\OnlineOrder::whereKey($order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status'        => 'paid',
                'paymob_transaction_id' => $verify['transaction_id'] ?: null,
                'updated_at'            => now(),
            ]);

        return response('OK', 200);
    }

    /**
     * MyFatoorah callback — the browser lands here with ?paymentId= after the
     * hosted invoice page. Nothing from the query is trusted: the paymentId
     * is resolved to its InvoiceId server-side, the order is looked up by our
     * STORED invoice id, and the invoice is verified through GetPaymentStatus
     * (status + amount cross-checked) before it is marked paid.
     */
    public function myfatoorahReturn(Request $req, TenantMyfatoorahService $myfatoorah)
    {
        $thankYou = store_base_url('thank-you');
        $checkout = store_base_url('checkout');

        if (! $myfatoorah->isConfigured()) {
            return redirect($checkout);
        }

        $paymentId = (string) $req->query('paymentId', '');
        if ($paymentId === '') {
            return redirect($checkout);
        }

        try {
            $invoiceId = $myfatoorah->invoiceIdForPaymentId($paymentId);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('MyFatoorah paymentId resolve threw: ' . $e->getMessage());
            $invoiceId = null;
        }

        $order = $invoiceId
            ? \App\Models\OnlineOrder::where('myfatoorah_invoice_id', $invoiceId)->where('payment_method', 'myfatoorah')->first()
            : null;

        if (! $order) {
            return redirect($checkout);
        }

        if ($order->payment_status === 'paid') {
            return redirect($thankYou);
        }

        try {
            $verify = $myfatoorah->verifyInvoice((string) $order->myfatoorah_invoice_id, (float) $order->total);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('MyFatoorah verify threw: ' . $e->getMessage(), ['invoice_id' => $invoiceId]);
            $verify = ['success' => false, 'transaction_id' => null];
        }

        if (empty($verify['success'])) {
            return redirect($checkout);
        }

        \App\Models\OnlineOrder::whereKey($order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status'            => 'paid',
                'myfatoorah_transaction_id' => $verify['transaction_id'] ?: null,
                'updated_at'                => now(),
            ]);

        return redirect($thankYou);
    }

    /**
     * Moyasar callback — the browser lands here after the hosted invoice
     * page. Nothing from the query is trusted: the order is resolved by our
     * stored invoice id (cross-checked against the ?id= parameter when
     * present) and the invoice is re-fetched server-side (status + amount +
     * currency cross-checked) before it is marked paid.
     */
    public function moyasarReturn(Request $req, TenantMoyasarService $moyasar)
    {
        $thankYou = store_base_url('thank-you');
        $checkout = store_base_url('checkout');

        if (! $moyasar->isConfigured()) {
            return redirect($checkout);
        }

        $invoiceId = (string) ($req->query('invoice_id') ?: $req->query('id', ''));
        $order = $invoiceId !== ''
            ? \App\Models\OnlineOrder::where('moyasar_invoice_id', $invoiceId)->where('payment_method', 'moyasar')->first()
            : null;

        if (! $order) {
            return redirect($checkout);
        }

        if ($order->payment_status === 'paid') {
            return redirect($thankYou);
        }

        try {
            $verify = $moyasar->verifyInvoice((string) $order->moyasar_invoice_id, (float) $order->total);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Moyasar verify threw: ' . $e->getMessage(), ['invoice_id' => $invoiceId]);
            $verify = ['success' => false, 'payment_id' => null];
        }

        if (empty($verify['success'])) {
            return redirect($checkout);
        }

        \App\Models\OnlineOrder::whereKey($order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status'    => 'paid',
                'moyasar_payment_id' => $verify['payment_id'] ?: null,
                'updated_at'        => now(),
            ]);

        return redirect($thankYou);
    }

    /**
     * XPay webhook (server-to-server backstop).
     * Public endpoint — authenticated by the X-Signature HMAC (hex SHA-256 of
     * the body keyed with the tenant's webhook HMAC secret). The payload is
     * never trusted: the order is resolved by our stored intent id and the
     * intent is re-fetched (status + amount + currency) before it is marked
     * paid. Orders only exist once store() ran after the browser
     * confirmation, so an event for an unknown intent is acknowledged.
     */
    public function xpayWebhook(Request $req, TenantXpayService $xpay)
    {
        if (! $xpay->isConfigured() || ! $xpay->hasWebhookSecret()) {
            return response('XPay webhook not configured', 400);
        }

        $payload = $req->getContent();
        if (! $xpay->webhookSignatureValid($payload, (string) $req->header('X-Signature', ''))) {
            \Illuminate\Support\Facades\Log::warning('XPay webhook rejected: signature mismatch');
            return response('Invalid signature', 400);
        }

        $body     = json_decode($payload, true);
        $intentId = is_array($body) ? (string) ($body['payment_intent_id'] ?? $body['payment_intent'] ?? '') : '';
        if ($intentId === '') {
            return response('Ignored', 200);
        }

        $order = \App\Models\OnlineOrder::where('xpay_intent_id', $intentId)->where('payment_method', 'xpay')->first();
        if (! $order) {
            \Illuminate\Support\Facades\Log::info('XPay webhook: no order for intent ' . $intentId);
            return response('No order', 200);
        }

        if ($order->payment_status === 'paid') {
            return response('OK', 200);
        }

        try {
            $intent = $xpay->fetchIntent($intentId);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('XPay webhook verify threw: ' . $e->getMessage(), ['intent_id' => $intentId]);
            return response('Verification failed', 500);
        }

        if (! $xpay->amountMatches($intent, (float) $order->total)) {
            return response('Not paid', 200);
        }

        \App\Models\OnlineOrder::whereKey($order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status'      => 'paid',
                'xpay_transaction_id' => $intent['transaction_id'] ?: null,
                'updated_at'          => now(),
            ]);

        return response('OK', 200);
    }

    /**
     * Currencies whose minor unit IS the major unit (no cents) — Stripe et al.
     * take these amounts un-multiplied.
     */
    private const ZERO_DECIMAL_CURRENCIES = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA',
        'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    /**
     * ISO currency code for gateway charges — the real ISO code from the
     * active store currency (the shopper's Multi-Currency selection, or the
     * base currency), never the display symbol.
     */
    /**
     * GET /api/store/paypal/return?token={paypal_order_id}
     * PayPal sends the buyer back here after approval. The approval itself is
     * not payment: the order is CAPTURED server-side and only a COMPLETED
     * capture for the expected amount marks it paid.
     */
    public function paypalReturn(Request $req, TenantPaypalService $paypal)
    {
        $thankYou = store_base_url('thank-you');
        $checkout = store_base_url('checkout');

        $token = (string) $req->query('token', '');
        $order = $token !== ''
            ? \App\Models\OnlineOrder::where('paypal_order_id', $token)->where('payment_method', 'paypal')->first()
            : null;

        if (! $order || ! $paypal->isConfigured()) {
            return redirect($checkout);
        }
        if ($order->payment_status === 'paid') {
            return redirect($thankYou);
        }

        try {
            $capture = $paypal->captureOrder(
                $token,
                $this->toGatewayAmount((float) $order->total, (float) ($order->exchange_rate ?: 1), $this->storeCurrencyCode()),
                $this->storeCurrencyCode()
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PayPal capture threw: ' . $e->getMessage(), ['order_id' => $order->id]);
            $capture = ['success' => false, 'capture_id' => null];
        }

        if (empty($capture['success'])) {
            return redirect($checkout);
        }

        \App\Models\OnlineOrder::whereKey($order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status' => 'paid',
                'paypal_capture_id' => $capture['capture_id'] ?: null,
                'updated_at' => now(),
            ]);

        return redirect($thankYou);
    }

    /**
     * GET /api/store/paypal/cancel?token={paypal_order_id}
     * The buyer backed out on PayPal — leave the order pending so they can
     * retry, and send them back to the checkout.
     */
    public function paypalCancel(Request $req)
    {
        return redirect(store_base_url('checkout'));
    }

    /**
     * GET /api/store/paystack/return?reference={paystack_reference}
     * Paystack redirects here after the hosted payment. The reference is
     * re-verified server-side; the redirect alone is never trusted.
     */
    public function paystackReturn(Request $req, TenantPaystackService $paystack)
    {
        $thankYou = store_base_url('thank-you');
        $checkout = store_base_url('checkout');

        $reference = (string) ($req->query('reference') ?: $req->query('trxref', ''));
        $order = $reference !== ''
            ? \App\Models\OnlineOrder::where('paystack_reference', $reference)->where('payment_method', 'paystack')->first()
            : null;

        if (! $order || ! $paystack->isConfigured()) {
            return redirect($checkout);
        }
        if ($order->payment_status === 'paid') {
            return redirect($thankYou);
        }

        try {
            $verify = $paystack->verifyTransaction(
                (string) $order->paystack_reference,
                $this->toGatewayAmount((float) $order->total, (float) ($order->exchange_rate ?: 1), $this->storeCurrencyCode()),
                $this->storeCurrencyCode()
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Paystack verify threw: ' . $e->getMessage(), ['reference' => $reference]);
            $verify = ['success' => false, 'transaction_id' => null];
        }

        if (empty($verify['success'])) {
            return redirect($checkout);
        }

        \App\Models\OnlineOrder::whereKey($order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status' => 'paid',
                'paystack_transaction_id' => $verify['transaction_id'] ?: null,
                'updated_at' => now(),
            ]);

        return redirect($thankYou);
    }

    /**
     * GET /api/store/flutterwave/return?status=…&tx_ref=…
     * Flutterwave redirects here after the hosted payment. The status in the
     * query string is advisory only — the charge is re-verified by tx_ref.
     */
    public function flutterwaveReturn(Request $req, TenantFlutterwaveService $flutterwave)
    {
        $thankYou = store_base_url('thank-you');
        $checkout = store_base_url('checkout');

        $txRef = (string) $req->query('tx_ref', '');
        $order = $txRef !== ''
            ? \App\Models\OnlineOrder::where('flutterwave_tx_ref', $txRef)->where('payment_method', 'flutterwave')->first()
            : null;

        if (! $order || ! $flutterwave->isConfigured()) {
            return redirect($checkout);
        }
        if ($order->payment_status === 'paid') {
            return redirect($thankYou);
        }

        try {
            $verify = $flutterwave->verifyTransaction(
                (string) $order->flutterwave_tx_ref,
                $this->toGatewayAmount((float) $order->total, (float) ($order->exchange_rate ?: 1), $this->storeCurrencyCode()),
                $this->storeCurrencyCode()
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Flutterwave verify threw: ' . $e->getMessage(), ['tx_ref' => $txRef]);
            $verify = ['success' => false, 'transaction_id' => ''];
        }

        if (empty($verify['success'])) {
            return redirect($checkout);
        }

        \App\Models\OnlineOrder::whereKey($order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status' => 'paid',
                'flutterwave_transaction_id' => $verify['transaction_id'] ?: null,
                'updated_at' => now(),
            ]);

        return redirect($thankYou);
    }

    private function storeCurrencyCode(): string
    {
        $code = strtolower((string) (\App\Services\StoreCurrencyService::active()['code'] ?? ''));

        return strtoupper(substr(preg_replace('/[^a-z]/', '', $code) ?: 'usd', 0, 3));
    }

    /**
     * A BASE-currency total converted into the charge currency, rounded to
     * that currency's precision (rate 1 = unchanged legacy behavior).
     */
    private function toGatewayAmount(float $baseTotal, float $rate, string $code): float
    {
        $converted = $baseTotal * ($rate ?: 1);
        $decimals = in_array(strtoupper($code), self::ZERO_DECIMAL_CURRENCIES, true) ? 0 : 2;

        return round($converted, $decimals);
    }

    /**
     * The same conversion expressed in the gateway's minor units (Stripe).
     */
    private function toGatewayMinorUnits(float $baseTotal, float $rate, string $code): int
    {
        $converted = $baseTotal * ($rate ?: 1);

        return in_array(strtoupper($code), self::ZERO_DECIMAL_CURRENCIES, true)
            ? (int) round($converted)
            : (int) round($converted * 100);
    }
}
