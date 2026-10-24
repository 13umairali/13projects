<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_builder_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_id');
            $table->string('label');
            $table->string('field_key', 100); // machine name used as the submission's data array key
            $table->string('type', 30); // text, email, phone, number, textarea, select, radio, checkbox, date, file
            $table->string('placeholder')->nullable();
            $table->json('options')->nullable(); // choice list for select/radio/checkbox
            $table->boolean('is_required')->default(false);
            $table->string('width', 10)->default('full'); // full | half — for a simple 2-column layout
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['form_id', 'field_key']);
            $table->index('form_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_builder_fields');
    }
};
