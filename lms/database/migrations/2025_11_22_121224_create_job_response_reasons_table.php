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
        Schema::create('job_response_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['approve', 'reject'])->default('approve');
            $table->boolean('enable')->default(false);
            $table->bigInteger('created_at')->unsigned();
        });

        Schema::create('job_response_reason_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('job_response_reason_id');
            $table->string('locale', 191)->index();
            $table->string('title');

            $table->foreign('job_response_reason_id', 'job_response_reason_id')->on('job_response_reasons')->references('id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_response_reasons');
        Schema::dropIfExists('job_response_reason_translations');
    }
};
