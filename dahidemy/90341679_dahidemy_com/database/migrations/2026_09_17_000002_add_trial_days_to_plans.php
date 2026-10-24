<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * trial_days is deliberately separate from duration_days:
     * duration_days is how long a plan's billing period/validity lasts
     * once active; trial_days is how many free days a NEW sign-up gets on
     * that plan before it's treated as a real, billable subscription.
     * Every plan can have its own independently-configured trial length.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('trial_days')->nullable()->after('duration_days');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('trial_days');
        });
    }
};