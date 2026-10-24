<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PayPal / Paystack / Flutterwave for the storefront checkout.
 *
 * Credentials live on the tenant's own `payment_settings` row so every tenant
 * charges into its own merchant account. All three are redirect gateways that
 * charge in the store's base currency, so they need no per-gateway currency
 * column (unlike Paymob/MyFatoorah/Moyasar, which settle in a fixed one).
 *
 * PayPal is the only one with a sandbox switch — Paystack and Flutterwave
 * decide test vs live from the key pair itself (sk_test_ / FLWSECK_TEST-).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_settings')) {
            Schema::table('payment_settings', function (Blueprint $table) {
                // PayPal (Orders API v2)
                if (! Schema::hasColumn('payment_settings', 'paypal_client_id')) {
                    $table->string('paypal_client_id', 191)->nullable();
                }
                if (! Schema::hasColumn('payment_settings', 'paypal_client_secret')) {
                    $table->text('paypal_client_secret')->nullable();
                }
                if (! Schema::hasColumn('payment_settings', 'paypal_webhook_id')) {
                    $table->string('paypal_webhook_id', 191)->nullable();
                }
                if (! Schema::hasColumn('payment_settings', 'paypal_test_mode')) {
                    $table->boolean('paypal_test_mode')->default(true);
                }

                // Paystack (Transactions API)
                if (! Schema::hasColumn('payment_settings', 'paystack_public_key')) {
                    $table->string('paystack_public_key', 191)->nullable();
                }
                if (! Schema::hasColumn('payment_settings', 'paystack_secret_key')) {
                    $table->text('paystack_secret_key')->nullable();
                }

                // Flutterwave v3 (Standard hosted payments)
                if (! Schema::hasColumn('payment_settings', 'flutterwave_public_key')) {
                    $table->string('flutterwave_public_key', 191)->nullable();
                }
                if (! Schema::hasColumn('payment_settings', 'flutterwave_secret_key')) {
                    $table->text('flutterwave_secret_key')->nullable();
                }
                // Secret Hash for the `verif-hash` webhook header.
                if (! Schema::hasColumn('payment_settings', 'flutterwave_secret_hash')) {
                    $table->text('flutterwave_secret_hash')->nullable();
                }
            });
        }

        // Gateway ids on storefront orders. The reference resolves the pending
        // order on the callback; the transaction/capture id records the
        // settled charge. Unique so one payment can never settle two orders.
        if (Schema::hasTable('online_orders')) {
            Schema::table('online_orders', function (Blueprint $table) {
                foreach ([
                    'paypal_order_id', 'paypal_capture_id',
                    'paystack_reference', 'paystack_transaction_id',
                    'flutterwave_tx_ref', 'flutterwave_transaction_id',
                ] as $col) {
                    if (! Schema::hasColumn('online_orders', $col)) {
                        $table->string($col, 64)->nullable()->unique();
                    }
                }
            });
        }

        // Per-method on/off switches, matching the other online gateways:
        // default ON so a gateway is offered as soon as its keys are stored.
        if (Schema::hasTable('store_settings')) {
            Schema::table('store_settings', function (Blueprint $table) {
                foreach ([
                    'payment_paypal_enabled',
                    'payment_paystack_enabled',
                    'payment_flutterwave_enabled',
                ] as $col) {
                    if (! Schema::hasColumn('store_settings', $col)) {
                        $table->boolean($col)->default(true);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_settings')) {
            Schema::table('payment_settings', function (Blueprint $table) {
                foreach ([
                    'paypal_client_id', 'paypal_client_secret', 'paypal_webhook_id', 'paypal_test_mode',
                    'paystack_public_key', 'paystack_secret_key',
                    'flutterwave_public_key', 'flutterwave_secret_key', 'flutterwave_secret_hash',
                ] as $col) {
                    if (Schema::hasColumn('payment_settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('online_orders')) {
            Schema::table('online_orders', function (Blueprint $table) {
                foreach ([
                    'paypal_order_id', 'paypal_capture_id',
                    'paystack_reference', 'paystack_transaction_id',
                    'flutterwave_tx_ref', 'flutterwave_transaction_id',
                ] as $col) {
                    if (Schema::hasColumn('online_orders', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('store_settings')) {
            Schema::table('store_settings', function (Blueprint $table) {
                foreach ([
                    'payment_paypal_enabled', 'payment_paystack_enabled', 'payment_flutterwave_enabled',
                ] as $col) {
                    if (Schema::hasColumn('store_settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
