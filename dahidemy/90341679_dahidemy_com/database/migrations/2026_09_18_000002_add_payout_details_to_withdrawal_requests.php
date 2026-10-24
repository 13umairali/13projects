<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The requester's own payment destination - where THEY want the
     * money sent (bank account, PayPal email, mobile money number,
     * etc.). Separate from payment_method/payment_reference already on
     * this table, which record what the ADMIN actually did when marking
     * a request paid - these two are different people's information for
     * different purposes.
     */
    public function up(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->string('payout_method')->nullable()->after('amount');
            $table->text('payout_details')->nullable()->after('payout_method');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropColumn(['payout_method', 'payout_details']);
        });
    }
};