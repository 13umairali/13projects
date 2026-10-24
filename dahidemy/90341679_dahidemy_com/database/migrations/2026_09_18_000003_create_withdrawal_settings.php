<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row for the platform-wide default (scope_type = 'platform',
     * scope_id = null), set by the SaaS Super Admin. Optionally, one row
     * per institute (scope_type = 'institute', scope_id = institute id)
     * that overrides it - an institute can set a tighter minimum, a
     * lower maximum, or disable a payout method the platform otherwise
     * allows. See WithdrawalSetting::effectiveFor().
     */
    public function up(): void
    {
        Schema::create('withdrawal_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('scope_type', ['platform', 'institute']);
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->decimal('min_amount', 12, 2)->nullable();
            $table->decimal('max_amount', 12, 2)->nullable();
            $table->json('enabled_methods')->nullable();
            $table->timestamps();

            $table->unique(['scope_type', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_settings');
    }
};