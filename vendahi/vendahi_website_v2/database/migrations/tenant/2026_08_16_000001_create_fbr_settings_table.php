<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fbr_settings')) {
            return;
        }

        Schema::create('fbr_settings', function (Blueprint $table) {
            $table->id();

            // Master switches
            $table->boolean('enabled')->default(false);
            $table->boolean('auto_submit')->default(true);       // submit automatically on sale creation
            $table->string('environment', 20)->default('sandbox'); // sandbox | production

            // PRAL-issued security token (Bearer)
            $table->longText('bearer_token')->nullable();        // encrypted

            // Seller identity (invoice header fields)
            $table->string('seller_ntn_cnic', 20)->nullable();
            $table->string('seller_business_name')->nullable();  // falls back to Setting.CompanyName
            $table->string('seller_province', 60)->nullable();
            $table->string('seller_address')->nullable();

            // Sandbox test scenario (payload scenarioId; sandbox only)
            $table->string('scenario_id', 10)->default('SN026');

            // Item-level defaults used when a product has no FBR fields set
            $table->string('default_hs_code', 20)->nullable();
            $table->string('default_uom', 100)->default('Numbers, pieces, units');
            $table->string('default_sale_type', 100)->default('Goods at standard rate (default)');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fbr_settings');
    }
};
