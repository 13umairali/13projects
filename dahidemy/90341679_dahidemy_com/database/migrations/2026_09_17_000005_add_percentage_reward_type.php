<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Adds 'percentage' alongside the existing days/cash/points reward
     * types - when a referral's reward_type is 'percentage', the actual
     * reward is calculated as reward_value% of whatever the referred
     * institute's subscribed plan actually costs, computed at approval
     * time (see ReferralService::approveReward).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE referral_settings MODIFY reward_type ENUM('days', 'cash', 'points', 'percentage')");
        DB::statement("ALTER TABLE referrals MODIFY reward_type ENUM('days', 'cash', 'points', 'percentage') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE referral_settings MODIFY reward_type ENUM('days', 'cash', 'points')");
        DB::statement("ALTER TABLE referrals MODIFY reward_type ENUM('days', 'cash', 'points') NULL");
    }
};