<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unlike the platform-level referrals table (which often records a
     * referral before the referred institute even exists, since payment
     * can be delayed), an internal referral's target already exists by
     * the time this row is created - the admission/creation form
     * collects the code at the same moment it creates the new user, so
     * referred_user_id is set immediately, never left pending.
     */
    public function up(): void
    {
        Schema::create('internal_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->unsignedBigInteger('referrer_user_id');
            $table->string('referrer_user_type'); // snapshot: Student, Teacher, Parent, Staff, Accountant...
            $table->string('referral_code_used');
            $table->unsignedBigInteger('referred_user_id');
            $table->string('referred_user_type'); // snapshot: what the referred person joined as
            $table->enum('status', ['enrolled', 'rewarded', 'rejected'])->default('enrolled');
            $table->enum('reward_type', ['fee_discount', 'points', 'cash_withdrawal'])->nullable();
            $table->decimal('reward_value', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('rewarded_at')->nullable();
            $table->timestamps();

            $table->index(['institute_id', 'referrer_user_id']);
            $table->index(['institute_id', 'referred_user_id']);
            $table->foreign('institute_id')->references('id')->on('institutes')->cascadeOnDelete();
            $table->foreign('referrer_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('referred_user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_referrals');
    }
};