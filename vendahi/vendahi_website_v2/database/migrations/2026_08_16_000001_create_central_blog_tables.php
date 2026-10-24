<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('central');

        if (! $schema->hasTable('blog_categories')) {
            $schema->create('blog_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('blog_posts')) {
            $schema->create('blog_posts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('blog_category_id')->nullable()->index();
                $table->unsignedBigInteger('central_user_id')->nullable()->index();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('excerpt')->nullable();
                $table->longText('content')->nullable();
                $table->string('cover_image')->nullable();
                $table->string('meta_title')->nullable();
                $table->string('meta_description', 300)->nullable();
                $table->boolean('is_published')->default(false);
                $table->timestamp('published_at')->nullable();
                $table->timestamps();

                $table->index(['is_published', 'published_at']);
            });

            // Both relations are read through Eloquent and tolerate a missing
            // parent, so a legacy central_users table that cannot take the
            // constraint must not abort the run and leave every later
            // migration unapplied.
            foreach ([['blog_category_id', 'blog_categories'], ['central_user_id', 'central_users']] as [$column, $parent]) {
                try {
                    $schema->table('blog_posts', function (Blueprint $table) use ($column, $parent) {
                        $table->foreign($column)->references('id')->on($parent)->nullOnDelete();
                    });
                } catch (\Throwable $e) {
                    // Keep going without this constraint.
                }
            }
        }
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('blog_posts');
        Schema::connection('central')->dropIfExists('blog_categories');
    }
};
