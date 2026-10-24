<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('director_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('director_name')->nullable();
            $table->string('designation')->nullable();
            $table->text('message')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique('institute_id', 'director_messages_institute_unq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('director_messages');
    }
};
