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
        Schema::create('job_category_filter_options', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('filter_id')->unsigned();
            $table->integer('order')->unsigned()->nullable();

            $table->foreign('filter_id')->references('id')->on('job_category_filters')->onDelete('cascade');
        });

        Schema::create('job_category_filter_option_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('filter_option_id');
            $table->string('locale', 191)->index();
            $table->string('title');

            $table->foreign('filter_option_id', 'filter_option_id')->on('job_category_filter_options')->references('id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_category_filter_options');
        Schema::dropIfExists('job_category_filter_option_translations');
    }
};
