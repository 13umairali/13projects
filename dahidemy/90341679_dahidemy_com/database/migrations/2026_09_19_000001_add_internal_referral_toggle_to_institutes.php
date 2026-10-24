<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * When off, the referral code field is hidden from every institute
     * form (admission, staff creation, etc.) and any code entered is
     * ignored rather than rejected - a code shouldn't block someone
     * joining just because a feature got turned off after the link was
     * shared.
     */
    public function up(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->boolean('internal_referral_enabled')->default(false)->after('referral_code');
        });
    }

    public function down(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->dropColumn('internal_referral_enabled');
        });
    }
};