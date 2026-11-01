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
        Schema::create('job_experience_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('icon')->nullable();
            $table->boolean('enable')->default(false);
        });

        Schema::create('job_experience_level_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('job_experience_level_id');
            $table->string('locale', 191)->index();
            $table->string('title');

            $table->foreign('job_experience_level_id', 'job_experience_level_id')->on('job_experience_levels')->references('id')->onDelete('cascade');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->foreign('experience_level_id')->on('job_experience_levels')->references('id')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_experience_levels');
        Schema::dropIfExists('job_experience_level_translations');
    }
};
