<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fbr_documents')) {
            return;
        }

        Schema::create('fbr_documents', function (Blueprint $table) {
            $table->id();

            // Source business document: App\Models\Sale
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');

            $table->string('invoice_ref')->nullable();        // local sale Ref
            $table->string('invoice_type', 20)->default('Sale Invoice'); // Sale Invoice | Debit Note
            $table->string('fbr_invoice_number')->nullable(); // IRN issued by FBR
            $table->string('environment', 20)->default('sandbox');

            $table->string('status', 20)->default('pending'); // pending | valid | invalid | error
            $table->string('status_code', 10)->nullable();    // 00 valid | 01 invalid
            $table->longText('error')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response')->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            $table->unique(['source_type', 'source_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fbr_documents');
    }
};
