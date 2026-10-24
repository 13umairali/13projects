<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manual (offline) storefront payment methods: GCash, Bank Transfer and
 * Cash on Pickup. Each carries the account details the shopper needs plus
 * free-text instructions rendered on checkout / thank-you / order pages.
 *
 * Default OFF: unlike COD and mobile money, these cannot be offered until the
 * admin has filled in the account details to pay into.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('store_settings')) {
            return;
        }

        // A tenant that somehow lacks the older offline flags must not blow up
        // on `AFTER <missing column>` — column order is cosmetic, so drop it.
        $anchor = Schema::hasColumn('store_settings', 'payment_mobile_money_enabled')
            ? 'payment_mobile_money_enabled'
            : null;

        Schema::table('store_settings', function (Blueprint $table) use ($anchor) {
            // GCash
            if (! Schema::hasColumn('store_settings', 'payment_gcash_enabled')) {
                $col = $table->boolean('payment_gcash_enabled')->default(false);
                if ($anchor) {
                    $col->after($anchor);
                }
            }
            if (! Schema::hasColumn('store_settings', 'gcash_account_name')) {
                $table->string('gcash_account_name', 150)->nullable()->after('payment_gcash_enabled');
            }
            if (! Schema::hasColumn('store_settings', 'gcash_account_number')) {
                $table->string('gcash_account_number', 60)->nullable()->after('gcash_account_name');
            }
            if (! Schema::hasColumn('store_settings', 'gcash_qr_path')) {
                $table->string('gcash_qr_path', 255)->nullable()->after('gcash_account_number');
            }
            if (! Schema::hasColumn('store_settings', 'gcash_instructions')) {
                $table->text('gcash_instructions')->nullable()->after('gcash_qr_path');
            }

            // Bank transfer
            if (! Schema::hasColumn('store_settings', 'payment_bank_transfer_enabled')) {
                $table->boolean('payment_bank_transfer_enabled')->default(false)->after('gcash_instructions');
            }
            if (! Schema::hasColumn('store_settings', 'bank_name')) {
                $table->string('bank_name', 150)->nullable()->after('payment_bank_transfer_enabled');
            }
            if (! Schema::hasColumn('store_settings', 'bank_account_name')) {
                $table->string('bank_account_name', 150)->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('store_settings', 'bank_account_number')) {
                $table->string('bank_account_number', 60)->nullable()->after('bank_account_name');
            }
            if (! Schema::hasColumn('store_settings', 'bank_branch')) {
                $table->string('bank_branch', 150)->nullable()->after('bank_account_number');
            }
            if (! Schema::hasColumn('store_settings', 'bank_instructions')) {
                $table->text('bank_instructions')->nullable()->after('bank_branch');
            }

            // Cash on pickup (branch collection)
            if (! Schema::hasColumn('store_settings', 'payment_cash_on_pickup_enabled')) {
                $table->boolean('payment_cash_on_pickup_enabled')->default(false)->after('bank_instructions');
            }
            if (! Schema::hasColumn('store_settings', 'pickup_instructions')) {
                $table->text('pickup_instructions')->nullable()->after('payment_cash_on_pickup_enabled');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('store_settings')) {
            return;
        }

        Schema::table('store_settings', function (Blueprint $table) {
            foreach ([
                'payment_gcash_enabled', 'gcash_account_name', 'gcash_account_number', 'gcash_qr_path', 'gcash_instructions',
                'payment_bank_transfer_enabled', 'bank_name', 'bank_account_name', 'bank_account_number', 'bank_branch', 'bank_instructions',
                'payment_cash_on_pickup_enabled', 'pickup_instructions',
            ] as $col) {
                if (Schema::hasColumn('store_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
