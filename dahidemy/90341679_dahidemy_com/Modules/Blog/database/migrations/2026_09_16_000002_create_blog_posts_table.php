<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();

            $table->string('title');
            $table->string('slug');
            $table->string('excerpt', 500)->nullable();
            $table->longText('content')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('author_name')->nullable();

            $table->smallInteger('status')->default(2)->comment('1=Published, 2=Draft');
            $table->dateTime('published_at')->nullable();
            $table->unsignedInteger('views')->default(0);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['institute_id', 'slug'], 'blog_posts_institute_slug_unq');
            $table->index(['institute_id', 'status', 'published_at'], 'blog_posts_inst_status_pub_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
