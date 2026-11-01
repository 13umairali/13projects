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
        Schema::create('job_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->nullable();
            $table->string('icon')->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->boolean('enable')->default(false);
        });

        Schema::create('job_category_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('job_category_id');
            $table->string('locale', 191)->index();
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->string('bottom_seo_title')->nullable();
            $table->text('bottom_seo_description')->nullable();

            $table->foreign('job_category_id', 'job_category_id')->on('job_categories')->references('id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_categories');
        Schema::dropIfExists('job_category_translations');
    }
};
