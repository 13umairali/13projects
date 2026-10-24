<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fbr_logs')) {
            return;
        }

        Schema::create('fbr_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fbr_document_id')->nullable()->index();
            $table->string('action', 50);                 // post_invoice, validate_invoice, test_connection, reference
            $table->string('endpoint')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->json('request_meta')->nullable();     // safe request metadata (never the bearer token)
            $table->longText('response_body')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fbr_logs');
    }
};
