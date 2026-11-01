<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_category_filters', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('category_id')->unsigned();

            $table->foreign('category_id')->references('id')->on('job_categories')->onDelete('cascade');
        });

        Schema::create('job_category_filter_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('job_category_filter_id');
            $table->string('locale', 191)->index();
            $table->string('title');

            $table->foreign('job_category_filter_id', 'job_category_filter_id')->on('job_category_filters')->references('id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_category_filters');
        Schema::dropIfExists('job_category_filter_translations');
    }
};
