<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('section_key', 50);
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['institute_id', 'section_key'], 'homepage_sections_inst_key_unq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
    }
};
