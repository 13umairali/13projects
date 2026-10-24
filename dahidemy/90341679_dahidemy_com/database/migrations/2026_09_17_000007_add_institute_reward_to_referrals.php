<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The second, institute-level reward tracked on the same referral row
     * as the SaaS-level one (reward_type/reward_value/rewarded_at) - two
     * independent reward tracks for the same referral event: one the SaaS
     * admin owns, one the referrer's own institute owns, approved and
     * paid out separately. Enum matches institute_referral_settings.
     */
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->enum('institute_reward_type', ['fee_discount', 'points', 'cash_withdrawal'])->nullable()->after('reward_value');
            $table->decimal('institute_reward_value', 12, 2)->nullable()->after('institute_reward_type');
            $table->enum('institute_reward_status', ['pending', 'rewarded', 'rejected'])->nullable()->after('institute_reward_value');
            $table->timestamp('institute_rewarded_at')->nullable()->after('rewarded_at');
        });
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropColumn(['institute_reward_type', 'institute_reward_value', 'institute_reward_status', 'institute_rewarded_at']);
        });
    }
};