<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('institute_id');
            $table->unsignedBigInteger('branch_id')->nullable();

            // Guest enrollment — no login required, consistent with how
            // Admission Forms and Dynamic Forms already work on this site.
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('ticket_code', 20)->unique();
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('currency_code', 10)->nullable();

            // 1=Confirmed (free event, no payment needed)
            // 2=PendingPayment (paid event, checkout started)
            // 3=Paid (payment confirmed)
            // 4=Failed (payment failed/cancelled)
            $table->smallInteger('payment_status')->default(1);

            // References Modules\Gateways\Models\PaymentRequest.id (a uuid) —
            // stored as a plain string rather than a real foreign key since
            // the rest of this codebase doesn't use FK constraints either.
            $table->string('payment_request_id', 36)->nullable();

            $table->timestamps();

            $table->index(['event_id', 'payment_status']);
            $table->index('payment_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_enrollments');
    }
};
