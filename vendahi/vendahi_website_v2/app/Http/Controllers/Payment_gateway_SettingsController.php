<?php

namespace App\Http\Controllers;

use App\Models\PaymentSetting;
use App\Models\Setting;
use Illuminate\Http\Request;

class Payment_gateway_SettingsController extends Controller
{
    // -------------- Get Payment Gateway ---------------\\

    public function Get_payment_gateway(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'payment_gateway', Setting::class);

        $settings = PaymentSetting::current();

        $item['stripe_key'] = $settings->stripe_key;
        $item['stripe_secret'] = '';
        $item['deleted'] = false;
        $item['razorpay_key_id'] = $settings->razorpay_key_id;
        $item['razorpay_key_secret'] = '';
        $item['razorpay_deleted'] = false;
        $item['payfast_merchant_id'] = $settings->payfast_merchant_id;
        $item['payfast_merchant_key'] = '';
        $item['payfast_passphrase'] = '';
        $item['payfast_sandbox'] = (bool) $settings->payfast_sandbox;
        $item['payfast_deleted'] = false;
        $item['ligdicash_api_key'] = $settings->ligdicash_api_key;
        $item['ligdicash_auth_token'] = '';
        $item['ligdicash_deleted'] = false;
        $item['dodo_api_key'] = '';
        $item['dodo_product_id'] = $settings->dodo_product_id;
        $item['dodo_webhook_secret'] = '';
        $item['dodo_test_mode'] = (bool) $settings->dodo_test_mode;
        $item['dodo_deleted'] = false;
        $item['waafipay_merchant_uid'] = $settings->waafipay_merchant_uid;
        $item['waafipay_store_id'] = $settings->waafipay_store_id;
        $item['waafipay_hpp_key'] = '';
        $item['waafipay_api_user_id'] = $settings->waafipay_api_user_id;
        $item['waafipay_api_key'] = '';
        $item['waafipay_webhook_secret'] = '';
        $item['waafipay_test_mode'] = (bool) $settings->waafipay_test_mode;
        $item['waafipay_deleted'] = false;
        $item['bkash_app_key'] = $settings->bkash_app_key;
        $item['bkash_app_secret'] = '';
        $item['bkash_username'] = $settings->bkash_username;
        $item['bkash_password'] = '';
        $item['bkash_test_mode'] = (bool) ($settings->bkash_test_mode ?? true);
        $item['bkash_deleted'] = false;
        $item['sslcommerz_store_id'] = $settings->sslcommerz_store_id;
        $item['sslcommerz_store_password'] = '';
        $item['sslcommerz_test_mode'] = (bool) ($settings->sslcommerz_test_mode ?? true);
        $item['sslcommerz_deleted'] = false;
        $item['paymob_region'] = $settings->paymob_region;
        $item['paymob_public_key'] = $settings->paymob_public_key;
        $item['paymob_secret_key'] = '';
        $item['paymob_api_key'] = '';
        $item['paymob_integration_id'] = $settings->paymob_integration_id;
        $item['paymob_hmac_secret'] = '';
        $item['paymob_currency'] = $settings->paymob_currency ?: 'EGP';
        $item['paymob_deleted'] = false;
        $item['myfatoorah_api_token'] = '';
        $item['myfatoorah_country'] = $settings->myfatoorah_country;
        $item['myfatoorah_currency'] = $settings->myfatoorah_currency ?: 'KWD';
        $item['myfatoorah_test_mode'] = (bool) ($settings->myfatoorah_test_mode ?? true);
        $item['myfatoorah_configured'] = trim((string) $settings->myfatoorah_api_token) !== '';
        $item['myfatoorah_deleted'] = false;
        $item['moyasar_secret_key'] = '';
        $item['moyasar_currency'] = $settings->moyasar_currency ?: 'SAR';
        $item['moyasar_configured'] = trim((string) $settings->moyasar_secret_key) !== '';
        $item['moyasar_deleted'] = false;
        $item['xpay_account_id'] = $settings->xpay_account_id;
        $item['xpay_publishable_key'] = $settings->xpay_publishable_key;
        $item['xpay_secret_key'] = '';
        $item['xpay_hmac_secret'] = '';
        $item['xpay_webhook_hmac_secret'] = '';
        $item['xpay_currency'] = $settings->xpay_currency ?: 'PKR';
        $item['xpay_test_mode'] = (bool) ($settings->xpay_test_mode ?? true);
        $item['xpay_configured'] = trim((string) $settings->xpay_account_id) !== ''
            && trim((string) $settings->xpay_publishable_key) !== ''
            && trim((string) $settings->xpay_secret_key) !== ''
            && trim((string) $settings->xpay_hmac_secret) !== '';
        $item['xpay_webhook_secret_set'] = trim((string) $settings->xpay_webhook_hmac_secret) !== '';
        $item['xpay_deleted'] = false;
        $item['paypal_client_id'] = $settings->paypal_client_id;
        $item['paypal_client_secret'] = '';
        $item['paypal_webhook_id'] = $settings->paypal_webhook_id;
        $item['paypal_test_mode'] = (bool) ($settings->paypal_test_mode ?? true);
        $item['paypal_configured'] = trim((string) $settings->paypal_client_id) !== ''
            && trim((string) $settings->paypal_client_secret) !== '';
        $item['paypal_deleted'] = false;
        $item['paystack_public_key'] = $settings->paystack_public_key;
        $item['paystack_secret_key'] = '';
        $item['paystack_configured'] = trim((string) $settings->paystack_public_key) !== ''
            && trim((string) $settings->paystack_secret_key) !== '';
        $item['paystack_deleted'] = false;
        $item['flutterwave_public_key'] = $settings->flutterwave_public_key;
        $item['flutterwave_secret_key'] = '';
        $item['flutterwave_secret_hash'] = '';
        $item['flutterwave_configured'] = trim((string) $settings->flutterwave_public_key) !== ''
            && trim((string) $settings->flutterwave_secret_key) !== '';
        $item['flutterwave_hash_set'] = trim((string) $settings->flutterwave_secret_hash) !== '';
        $item['flutterwave_deleted'] = false;

        return response()->json(['gateway' => $item], 200);
    }

    public function get_payment_gateway_ws(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Setting::class);

        $settings = PaymentSetting::current();

        $item['stripe_key'] = $settings->stripe_key;
        $item['stripe_secret'] = '';
        $item['deleted'] = false;
        $item['razorpay_key_id'] = $settings->razorpay_key_id;
        $item['razorpay_key_secret'] = '';
        $item['razorpay_deleted'] = false;
        $item['payfast_merchant_id'] = $settings->payfast_merchant_id;
        $item['payfast_merchant_key'] = '';
        $item['payfast_passphrase'] = '';
        $item['payfast_sandbox'] = (bool) $settings->payfast_sandbox;
        $item['payfast_deleted'] = false;
        $item['ligdicash_api_key'] = $settings->ligdicash_api_key;
        $item['ligdicash_auth_token'] = '';
        $item['ligdicash_deleted'] = false;
        $item['dodo_api_key'] = '';
        $item['dodo_product_id'] = $settings->dodo_product_id;
        $item['dodo_webhook_secret'] = '';
        $item['dodo_test_mode'] = (bool) $settings->dodo_test_mode;
        $item['dodo_deleted'] = false;
        $item['waafipay_merchant_uid'] = $settings->waafipay_merchant_uid;
        $item['waafipay_store_id'] = $settings->waafipay_store_id;
        $item['waafipay_hpp_key'] = '';
        $item['waafipay_api_user_id'] = $settings->waafipay_api_user_id;
        $item['waafipay_api_key'] = '';
        $item['waafipay_webhook_secret'] = '';
        $item['waafipay_test_mode'] = (bool) $settings->waafipay_test_mode;
        $item['waafipay_deleted'] = false;
        $item['bkash_app_key'] = $settings->bkash_app_key;
        $item['bkash_app_secret'] = '';
        $item['bkash_username'] = $settings->bkash_username;
        $item['bkash_password'] = '';
        $item['bkash_test_mode'] = (bool) ($settings->bkash_test_mode ?? true);
        $item['bkash_deleted'] = false;
        $item['sslcommerz_store_id'] = $settings->sslcommerz_store_id;
        $item['sslcommerz_store_password'] = '';
        $item['sslcommerz_test_mode'] = (bool) ($settings->sslcommerz_test_mode ?? true);
        $item['sslcommerz_deleted'] = false;
        $item['paymob_region'] = $settings->paymob_region;
        $item['paymob_public_key'] = $settings->paymob_public_key;
        $item['paymob_secret_key'] = '';
        $item['paymob_api_key'] = '';
        $item['paymob_integration_id'] = $settings->paymob_integration_id;
        $item['paymob_hmac_secret'] = '';
        $item['paymob_currency'] = $settings->paymob_currency ?: 'EGP';
        $item['paymob_deleted'] = false;
        $item['myfatoorah_api_token'] = '';
        $item['myfatoorah_country'] = $settings->myfatoorah_country;
        $item['myfatoorah_currency'] = $settings->myfatoorah_currency ?: 'KWD';
        $item['myfatoorah_test_mode'] = (bool) ($settings->myfatoorah_test_mode ?? true);
        $item['myfatoorah_configured'] = trim((string) $settings->myfatoorah_api_token) !== '';
        $item['myfatoorah_deleted'] = false;
        $item['moyasar_secret_key'] = '';
        $item['moyasar_currency'] = $settings->moyasar_currency ?: 'SAR';
        $item['moyasar_configured'] = trim((string) $settings->moyasar_secret_key) !== '';
        $item['moyasar_deleted'] = false;
        $item['xpay_account_id'] = $settings->xpay_account_id;
        $item['xpay_publishable_key'] = $settings->xpay_publishable_key;
        $item['xpay_secret_key'] = '';
        $item['xpay_hmac_secret'] = '';
        $item['xpay_webhook_hmac_secret'] = '';
        $item['xpay_currency'] = $settings->xpay_currency ?: 'PKR';
        $item['xpay_test_mode'] = (bool) ($settings->xpay_test_mode ?? true);
        $item['xpay_configured'] = trim((string) $settings->xpay_account_id) !== ''
            && trim((string) $settings->xpay_publishable_key) !== ''
            && trim((string) $settings->xpay_secret_key) !== ''
            && trim((string) $settings->xpay_hmac_secret) !== '';
        $item['xpay_webhook_secret_set'] = trim((string) $settings->xpay_webhook_hmac_secret) !== '';
        $item['xpay_deleted'] = false;
        $item['paypal_client_id'] = $settings->paypal_client_id;
        $item['paypal_client_secret'] = '';
        $item['paypal_webhook_id'] = $settings->paypal_webhook_id;
        $item['paypal_test_mode'] = (bool) ($settings->paypal_test_mode ?? true);
        $item['paypal_configured'] = trim((string) $settings->paypal_client_id) !== ''
            && trim((string) $settings->paypal_client_secret) !== '';
        $item['paypal_deleted'] = false;
        $item['paystack_public_key'] = $settings->paystack_public_key;
        $item['paystack_secret_key'] = '';
        $item['paystack_configured'] = trim((string) $settings->paystack_public_key) !== ''
            && trim((string) $settings->paystack_secret_key) !== '';
        $item['paystack_deleted'] = false;
        $item['flutterwave_public_key'] = $settings->flutterwave_public_key;
        $item['flutterwave_secret_key'] = '';
        $item['flutterwave_secret_hash'] = '';
        $item['flutterwave_configured'] = trim((string) $settings->flutterwave_public_key) !== ''
            && trim((string) $settings->flutterwave_secret_key) !== '';
        $item['flutterwave_hash_set'] = trim((string) $settings->flutterwave_secret_hash) !== '';
        $item['flutterwave_deleted'] = false;

        return response()->json(['gateway' => $item], 200);
    }

    // -------------- Update  Payment Gateway ---------------\\

    public function Update_payment_gateway(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'payment_gateway', Setting::class);

        $payment_settings = PaymentSetting::current();

        // Every gateway block below is guarded by has(): a request only
        // touches the gateways it actually mentions, so a per-tab save (the
        // Vue page posts one gateway at a time) can never blank another
        // gateway's stored credentials.
        if ($request->has('deleted') || $request->has('stripe_key')) {
            if ($request['deleted'] == 'true') {
                $payment_settings->update([
                    'stripe_key' => null,
                    'stripe_secret' => null,
                ]);
            } else {
                $payment_settings->update([
                    'stripe_key' => $request['stripe_key'] !== null ? $request['stripe_key'] : null,
                    'stripe_secret' => $request['stripe_secret'] !== null ? $request['stripe_secret'] : $payment_settings->stripe_secret,
                ]);
            }
        }

        if ($request->has('razorpay_deleted') || $request->has('razorpay_key_id')) {
        if ($request['razorpay_deleted'] == 'true' || $request['razorpay_deleted'] === true) {
            $payment_settings->update([
                'razorpay_key_id' => null,
                'razorpay_key_secret' => null,
            ]);
        } else {
            $payment_settings->update([
                'razorpay_key_id' => $request['razorpay_key_id'] !== null ? $request['razorpay_key_id'] : null,
                // Blank secret means "keep the existing value" — it is never echoed back to the form.
                'razorpay_key_secret' => $request->filled('razorpay_key_secret') ? $request['razorpay_key_secret'] : $payment_settings->razorpay_key_secret,
            ]);
        }
        }

        if ($request->has('payfast_deleted') || $request->has('payfast_merchant_id')) {
        if ($request['payfast_deleted'] == 'true' || $request['payfast_deleted'] === true) {
            $payment_settings->update([
                'payfast_merchant_id' => null,
                'payfast_merchant_key' => null,
                'payfast_passphrase' => null,
                'payfast_sandbox' => false,
            ]);
        } else {
            $payment_settings->update([
                'payfast_merchant_id' => $request['payfast_merchant_id'] !== null ? $request['payfast_merchant_id'] : null,
                // Blank secrets mean "keep the existing value" — they are never echoed back to the form.
                'payfast_merchant_key' => $request->filled('payfast_merchant_key') ? $request['payfast_merchant_key'] : $payment_settings->payfast_merchant_key,
                'payfast_passphrase' => $request->filled('payfast_passphrase') ? $request['payfast_passphrase'] : $payment_settings->payfast_passphrase,
                'payfast_sandbox' => $request['payfast_sandbox'] == 'true' || $request['payfast_sandbox'] === true,
            ]);
        }
        }

        if ($request->has('ligdicash_deleted') || $request->has('ligdicash_api_key')) {
        if ($request['ligdicash_deleted'] == 'true' || $request['ligdicash_deleted'] === true) {
            $payment_settings->update([
                'ligdicash_api_key' => null,
                'ligdicash_auth_token' => null,
            ]);
        } else {
            $payment_settings->update([
                'ligdicash_api_key' => $request['ligdicash_api_key'] !== null ? $request['ligdicash_api_key'] : null,
                // Blank token means "keep the existing value" — it is never echoed back to the form.
                'ligdicash_auth_token' => $request->filled('ligdicash_auth_token') ? $request['ligdicash_auth_token'] : $payment_settings->ligdicash_auth_token,
            ]);
        }
        }

        if ($request->has('dodo_deleted') || $request->has('dodo_product_id')) {
        if ($request['dodo_deleted'] == 'true' || $request['dodo_deleted'] === true) {
            $payment_settings->update([
                'dodo_api_key' => null,
                'dodo_product_id' => null,
                'dodo_webhook_secret' => null,
                'dodo_test_mode' => false,
            ]);
        } else {
            $payment_settings->update([
                'dodo_product_id' => $request['dodo_product_id'] !== null ? $request['dodo_product_id'] : null,
                // Blank secrets mean "keep the existing value" — they are never echoed back to the form.
                'dodo_api_key' => $request->filled('dodo_api_key') ? $request['dodo_api_key'] : $payment_settings->dodo_api_key,
                'dodo_webhook_secret' => $request->filled('dodo_webhook_secret') ? $request['dodo_webhook_secret'] : $payment_settings->dodo_webhook_secret,
                'dodo_test_mode' => $request['dodo_test_mode'] == 'true' || $request['dodo_test_mode'] === true,
            ]);
        }
        }

        if ($request->has('waafipay_deleted') || $request->has('waafipay_merchant_uid')) {
        if ($request['waafipay_deleted'] == 'true' || $request['waafipay_deleted'] === true) {
            $payment_settings->update([
                'waafipay_merchant_uid' => null,
                'waafipay_store_id' => null,
                'waafipay_hpp_key' => null,
                'waafipay_api_user_id' => null,
                'waafipay_api_key' => null,
                'waafipay_webhook_secret' => null,
                'waafipay_test_mode' => false,
            ]);
        } else {
            $payment_settings->update([
                'waafipay_merchant_uid' => $request['waafipay_merchant_uid'] !== null ? $request['waafipay_merchant_uid'] : null,
                'waafipay_store_id' => $request['waafipay_store_id'] !== null ? $request['waafipay_store_id'] : null,
                'waafipay_api_user_id' => $request['waafipay_api_user_id'] !== null ? $request['waafipay_api_user_id'] : null,
                // Blank secrets mean "keep the existing value" — they are never echoed back to the form.
                'waafipay_hpp_key' => $request->filled('waafipay_hpp_key') ? $request['waafipay_hpp_key'] : $payment_settings->waafipay_hpp_key,
                'waafipay_api_key' => $request->filled('waafipay_api_key') ? $request['waafipay_api_key'] : $payment_settings->waafipay_api_key,
                'waafipay_webhook_secret' => $request->filled('waafipay_webhook_secret') ? $request['waafipay_webhook_secret'] : $payment_settings->waafipay_webhook_secret,
                'waafipay_test_mode' => $request['waafipay_test_mode'] == 'true' || $request['waafipay_test_mode'] === true,
            ]);
        }
        }

        if ($request->has('bkash_deleted') || $request->has('bkash_app_key')) {
            if ($request['bkash_deleted'] == 'true' || $request['bkash_deleted'] === true) {
                $payment_settings->update([
                    'bkash_app_key' => null,
                    'bkash_app_secret' => null,
                    'bkash_username' => null,
                    'bkash_password' => null,
                    'bkash_test_mode' => true,
                ]);
            } else {
                $payment_settings->update([
                    'bkash_app_key' => $request['bkash_app_key'] !== null ? $request['bkash_app_key'] : null,
                    'bkash_username' => $request['bkash_username'] !== null ? $request['bkash_username'] : null,
                    // Blank secrets mean "keep the existing value" — they are never echoed back to the form.
                    'bkash_app_secret' => $request->filled('bkash_app_secret') ? $request['bkash_app_secret'] : $payment_settings->bkash_app_secret,
                    'bkash_password' => $request->filled('bkash_password') ? $request['bkash_password'] : $payment_settings->bkash_password,
                    'bkash_test_mode' => $request['bkash_test_mode'] == 'true' || $request['bkash_test_mode'] === true,
                ]);
            }
        }

        if ($request->has('sslcommerz_deleted') || $request->has('sslcommerz_store_id')) {
            if ($request['sslcommerz_deleted'] == 'true' || $request['sslcommerz_deleted'] === true) {
                $payment_settings->update([
                    'sslcommerz_store_id' => null,
                    'sslcommerz_store_password' => null,
                    'sslcommerz_test_mode' => true,
                ]);
            } else {
                $payment_settings->update([
                    'sslcommerz_store_id' => $request['sslcommerz_store_id'] !== null ? $request['sslcommerz_store_id'] : null,
                    // Blank password means "keep the existing value" — it is never echoed back to the form.
                    'sslcommerz_store_password' => $request->filled('sslcommerz_store_password') ? $request['sslcommerz_store_password'] : $payment_settings->sslcommerz_store_password,
                    'sslcommerz_test_mode' => $request['sslcommerz_test_mode'] == 'true' || $request['sslcommerz_test_mode'] === true,
                ]);
            }
        }

        if ($request->has('paymob_deleted') || $request->has('paymob_public_key')) {
            if ($request['paymob_deleted'] == 'true' || $request['paymob_deleted'] === true) {
                $payment_settings->update([
                    'paymob_region' => null,
                    'paymob_public_key' => null,
                    'paymob_secret_key' => null,
                    'paymob_api_key' => null,
                    'paymob_integration_id' => null,
                    'paymob_hmac_secret' => null,
                    'paymob_currency' => 'EGP',
                ]);
            } else {
                $payment_settings->update([
                    'paymob_region' => $request['paymob_region'] !== null ? $request['paymob_region'] : null,
                    'paymob_public_key' => $request['paymob_public_key'] !== null ? $request['paymob_public_key'] : null,
                    'paymob_integration_id' => $request['paymob_integration_id'] !== null ? $request['paymob_integration_id'] : null,
                    'paymob_currency' => $request->filled('paymob_currency') ? strtoupper((string) $request['paymob_currency']) : 'EGP',
                    // Blank secrets mean "keep the existing value" — they are never echoed back to the form.
                    'paymob_secret_key' => $request->filled('paymob_secret_key') ? $request['paymob_secret_key'] : $payment_settings->paymob_secret_key,
                    'paymob_api_key' => $request->filled('paymob_api_key') ? $request['paymob_api_key'] : $payment_settings->paymob_api_key,
                    'paymob_hmac_secret' => $request->filled('paymob_hmac_secret') ? $request['paymob_hmac_secret'] : $payment_settings->paymob_hmac_secret,
                ]);
            }
        }

        if ($request->has('myfatoorah_deleted') || $request->has('myfatoorah_currency')) {
            if ($request['myfatoorah_deleted'] == 'true' || $request['myfatoorah_deleted'] === true) {
                $payment_settings->update([
                    'myfatoorah_api_token' => null,
                    'myfatoorah_country' => null,
                    'myfatoorah_currency' => 'KWD',
                    'myfatoorah_test_mode' => true,
                ]);
            } else {
                $payment_settings->update([
                    'myfatoorah_country' => $request['myfatoorah_country'] !== null ? strtoupper((string) $request['myfatoorah_country']) : null,
                    'myfatoorah_currency' => $request->filled('myfatoorah_currency') ? strtoupper((string) $request['myfatoorah_currency']) : 'KWD',
                    // Blank token means "keep the existing value" — it is never echoed back to the form.
                    'myfatoorah_api_token' => $request->filled('myfatoorah_api_token') ? $request['myfatoorah_api_token'] : $payment_settings->myfatoorah_api_token,
                    'myfatoorah_test_mode' => $request['myfatoorah_test_mode'] == 'true' || $request['myfatoorah_test_mode'] === true,
                ]);
            }
        }

        if ($request->has('moyasar_deleted') || $request->has('moyasar_currency')) {
            if ($request['moyasar_deleted'] == 'true' || $request['moyasar_deleted'] === true) {
                $payment_settings->update([
                    'moyasar_secret_key' => null,
                    'moyasar_currency' => 'SAR',
                ]);
            } else {
                $payment_settings->update([
                    'moyasar_currency' => $request->filled('moyasar_currency') ? strtoupper((string) $request['moyasar_currency']) : 'SAR',
                    // Blank key means "keep the existing value" — it is never echoed back to the form.
                    'moyasar_secret_key' => $request->filled('moyasar_secret_key') ? $request['moyasar_secret_key'] : $payment_settings->moyasar_secret_key,
                ]);
            }
        }

        if ($request->has('xpay_deleted') || $request->has('xpay_account_id')) {
            if ($request['xpay_deleted'] == 'true' || $request['xpay_deleted'] === true) {
                $payment_settings->update([
                    'xpay_account_id' => null,
                    'xpay_publishable_key' => null,
                    'xpay_secret_key' => null,
                    'xpay_hmac_secret' => null,
                    'xpay_webhook_hmac_secret' => null,
                    'xpay_currency' => 'PKR',
                    'xpay_test_mode' => true,
                ]);
            } else {
                $payment_settings->update([
                    'xpay_account_id' => $request['xpay_account_id'] !== null ? trim((string) $request['xpay_account_id']) : null,
                    'xpay_publishable_key' => $request['xpay_publishable_key'] !== null ? trim((string) $request['xpay_publishable_key']) : null,
                    'xpay_currency' => $request->filled('xpay_currency') ? strtoupper((string) $request['xpay_currency']) : 'PKR',
                    'xpay_test_mode' => $request['xpay_test_mode'] == 'true' || $request['xpay_test_mode'] === true,
                    // Blank secrets mean "keep the existing value" — they are never echoed back to the form.
                    'xpay_secret_key' => $request->filled('xpay_secret_key') ? $request['xpay_secret_key'] : $payment_settings->xpay_secret_key,
                    'xpay_hmac_secret' => $request->filled('xpay_hmac_secret') ? $request['xpay_hmac_secret'] : $payment_settings->xpay_hmac_secret,
                    'xpay_webhook_hmac_secret' => $request->filled('xpay_webhook_hmac_secret') ? $request['xpay_webhook_hmac_secret'] : $payment_settings->xpay_webhook_hmac_secret,
                ]);
            }
        }

        if ($request->has('paypal_deleted') || $request->has('paypal_client_id')) {
            if ($request['paypal_deleted'] == 'true' || $request['paypal_deleted'] === true) {
                $payment_settings->update([
                    'paypal_client_id' => null,
                    'paypal_client_secret' => null,
                    'paypal_webhook_id' => null,
                    'paypal_test_mode' => true,
                ]);
            } else {
                $payment_settings->update([
                    'paypal_client_id' => $request['paypal_client_id'] !== null ? trim((string) $request['paypal_client_id']) : null,
                    'paypal_webhook_id' => $request['paypal_webhook_id'] !== null ? trim((string) $request['paypal_webhook_id']) : null,
                    'paypal_test_mode' => $request['paypal_test_mode'] == 'true' || $request['paypal_test_mode'] === true,
                    // Blank secret means "keep the existing value" — it is never echoed back to the form.
                    'paypal_client_secret' => $request->filled('paypal_client_secret') ? $request['paypal_client_secret'] : $payment_settings->paypal_client_secret,
                ]);
            }
        }

        if ($request->has('paystack_deleted') || $request->has('paystack_public_key')) {
            if ($request['paystack_deleted'] == 'true' || $request['paystack_deleted'] === true) {
                $payment_settings->update([
                    'paystack_public_key' => null,
                    'paystack_secret_key' => null,
                ]);
            } else {
                $payment_settings->update([
                    'paystack_public_key' => $request['paystack_public_key'] !== null ? trim((string) $request['paystack_public_key']) : null,
                    // Blank secret means "keep the existing value" — it is never echoed back to the form.
                    'paystack_secret_key' => $request->filled('paystack_secret_key') ? $request['paystack_secret_key'] : $payment_settings->paystack_secret_key,
                ]);
            }
        }

        if ($request->has('flutterwave_deleted') || $request->has('flutterwave_public_key')) {
            if ($request['flutterwave_deleted'] == 'true' || $request['flutterwave_deleted'] === true) {
                $payment_settings->update([
                    'flutterwave_public_key' => null,
                    'flutterwave_secret_key' => null,
                    'flutterwave_secret_hash' => null,
                ]);
            } else {
                $payment_settings->update([
                    'flutterwave_public_key' => $request['flutterwave_public_key'] !== null ? trim((string) $request['flutterwave_public_key']) : null,
                    // Blank secrets mean "keep the existing value" — they are never echoed back to the form.
                    'flutterwave_secret_key' => $request->filled('flutterwave_secret_key') ? $request['flutterwave_secret_key'] : $payment_settings->flutterwave_secret_key,
                    'flutterwave_secret_hash' => $request->filled('flutterwave_secret_hash') ? $request['flutterwave_secret_hash'] : $payment_settings->flutterwave_secret_hash,
                ]);
            }
        }

        return response()->json(['success' => true]);

    }
}
