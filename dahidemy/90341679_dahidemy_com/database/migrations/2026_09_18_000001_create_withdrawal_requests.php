<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A user's available balance is deliberately NOT stored here - it's
     * always calculated as (sum of their rewarded cash_withdrawal
     * referrals) minus (sum of their approved/paid withdrawal requests).
     * See WithdrawalService::availableBalanceFor(). This table only
     * records the requests themselves, so the balance can never drift
     * out of sync with the underlying referral records.
     */
    public function up(): void
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('institute_id');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('institute_id')->references('id')->on('institutes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};