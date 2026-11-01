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
        Schema::create('job_employment_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('icon')->nullable();
            $table->boolean('enable')->default(false);
        });

        Schema::create('job_employment_type_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('job_employment_type_id');
            $table->string('locale', 191)->index();
            $table->string('title');

            $table->foreign('job_employment_type_id', 'job_employment_type_id')->on('job_employment_types')->references('id')->onDelete('cascade');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->foreign('employment_type_id')->on('job_employment_types')->references('id')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_employment_types');
        Schema::dropIfExists('job_employment_type_translations');
    }
};
