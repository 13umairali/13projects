<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Separate from referral_settings (which is global, SaaS-admin-owned,
     * and only about referring a NEW INSTITUTE to the platform). This
     * table is scoped per institute_id and controlled entirely by that
     * institute's own admin - for internal referrals, e.g. a student
     * referring another student to enroll. Reward types here are
     * institute-native concepts (a fee discount, points, or a cash
     * withdrawal request) rather than the SaaS-level days/cash/points/
     * percentage-of-plan-price.
     */
    public function up(): void
    {
        Schema::create('institute_referral_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->string('referrer_role');
            $table->enum('reward_type', ['fee_discount', 'points', 'cash_withdrawal']);
            $table->decimal('reward_value', 12, 2)->default(0);
            $table->boolean('is_percentage')->default(false);
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['institute_id', 'referrer_role']);
            $table->foreign('institute_id')->references('id')->on('institutes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institute_referral_settings');
    }
};