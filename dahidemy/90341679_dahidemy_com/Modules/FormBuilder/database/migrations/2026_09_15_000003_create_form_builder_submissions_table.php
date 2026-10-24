<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_builder_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_id');
            $table->unsignedBigInteger('institute_id');
            $table->json('data'); // { field_key: value, ... } — schema-less, since fields vary per form
            $table->string('ip_address', 45)->nullable();
            $table->smallInteger('status')->default(1)->comment('1=New, 2=Reviewed');
            $table->timestamps();

            $table->index(['form_id', 'institute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_builder_submissions');
    }
};
