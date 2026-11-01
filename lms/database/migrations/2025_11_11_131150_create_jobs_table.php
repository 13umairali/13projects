<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->integer('creator_id')->unsigned();
            $table->integer('category_id')->unsigned()->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('icon')->nullable();
            $table->enum('video_demo_source', \App\Models\File::$fileSources)->nullable();
            $table->longText('video_demo')->nullable();

            $table->integer('company_size')->unsigned()->nullable();
            $table->integer('hiring_count')->unsigned()->nullable();
            $table->bigInteger('hiring_end_date')->unsigned()->nullable();
            $table->boolean('private')->default(false);

            $table->integer('employment_type_id')->unsigned()->nullable();
            $table->integer('experience_level_id')->unsigned()->nullable();

            $table->enum('work_arrangement', ['on_site', 'remote', 'hybrid'])->default('on_site');
            $table->enum('payment_type', ['fixed_amount', 'range', 'negotiable'])->default('fixed_amount');
            $table->decimal('fixed_salary', 15, 2)->nullable();
            $table->decimal('min_salary', 15, 2)->nullable();
            $table->decimal('max_salary', 15, 2)->nullable();
            $table->enum('payment_period', ['hour', 'day', 'month', 'year', 'project'])->default('hour');

            $table->text('message_for_reviewer')->nullable();
            $table->enum('status', ['publish', 'draft', 'pending', 'unpublish', 'canceled', 'rejected'])->default('draft');
            $table->bigInteger('created_at')->unsigned();
            $table->bigInteger('updated_at')->unsigned();

            $table->foreign('creator_id')->on('users')->references('id')->cascadeOnDelete();
            $table->foreign('category_id')->on('job_categories')->references('id')->cascadeOnDelete();
        });

        Schema::create('job_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('job_id')->unsigned();
            $table->string('locale', 191)->index();
            $table->string('title');
            $table->text('seo_description')->nullable();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->text('important_notes')->nullable();

            $table->foreign('job_id')->on('jobs')->references('id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_translations');
    }
};
