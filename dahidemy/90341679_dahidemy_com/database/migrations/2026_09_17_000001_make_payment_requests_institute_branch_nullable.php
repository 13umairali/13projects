<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * institute_id/branch_id were NOT NULL with no default, and both carry
     * an ON DELETE CASCADE foreign key - so a placeholder value (like 0)
     * would violate the foreign key constraint outright, and NULL would
     * violate the NOT NULL constraint. A subscription/onboarding payment
     * (payment_type = 'subscription_payment') genuinely has no institute
     * yet at the moment the payment is created - the institute is only
     * provisioned after payment succeeds - so these columns must be
     * nullable for that one payment type to be storable at all.
     */
    public function up(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('institute_id')->nullable()->change();
            $table->unsignedBigInteger('branch_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('institute_id')->nullable(false)->change();
            $table->unsignedBigInteger('branch_id')->nullable(false)->change();
        });
    }
};