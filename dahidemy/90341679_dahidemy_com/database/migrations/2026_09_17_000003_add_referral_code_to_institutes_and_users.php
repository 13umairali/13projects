<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every institute AND every user gets their own unique referral code -
     * this is deliberately on both tables, since either an institute
     * itself or any individual person within it (student, parent,
     * teacher, accountant, staff...) can be the one who refers a new
     * institute to the platform.
     */
    public function up(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->string('referral_code', 20)->nullable()->unique()->after('domain');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 20)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->dropColumn('referral_code');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('referral_code');
        });
    }
};