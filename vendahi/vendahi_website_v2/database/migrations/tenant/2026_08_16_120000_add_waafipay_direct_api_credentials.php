<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWaafipayDirectApiCredentials extends Migration
{
    public function up()
    {
        // Direct wallet API (API_PURCHASE) credentials — synchronous USSD/PIN
        // flow, used by the storefront when configured (preferred over HPP).
        Schema::table('payment_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_settings', 'waafipay_api_user_id')) {
                $table->string('waafipay_api_user_id', 32)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'waafipay_api_key')) {
                $table->text('waafipay_api_key')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            foreach (['waafipay_api_user_id', 'waafipay_api_key'] as $column) {
                if (Schema::hasColumn('payment_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
