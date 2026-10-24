<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->smallInteger('status')->default(1)->comment('1=Active, 2=Inactive');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['institute_id', 'slug'], 'blog_categories_institute_slug_unq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_categories');
    }
};
