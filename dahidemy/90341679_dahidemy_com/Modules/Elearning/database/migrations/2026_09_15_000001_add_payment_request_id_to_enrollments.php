<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // References Modules\Gateways\Models\PaymentRequest.id (a uuid) —
            // stored as a plain string rather than a real foreign key, same
            // convention as the rest of this app's schema (no FK constraints).
            $table->string('payment_request_id', 36)->nullable()->after('amount_paid');
            $table->index('payment_request_id', 'enrollments_payment_request_idx');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('payment_request_id');
        });
    }
};
