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
        Schema::create('job_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('job_id')->unsigned();
            $table->integer('user_resume_id')->unsigned();
            $table->longText('description')->nullable();

            $table->integer('moderator_id')->unsigned()->nullable()->comment("Someone who has approved or rejected");
            $table->integer('job_reason_id')->unsigned()->nullable();
            $table->longText('reason_description')->nullable();

            $table->boolean('pinned')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->bigInteger('created_at')->unsigned();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
        });

        Schema::create('job_request_share_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job_request_id')->unsigned();
            $table->integer('certificate_id')->unsigned()->nullable();
            $table->integer('course_id')->unsigned()->nullable();

            $table->foreign('job_request_id')->references('id')->on('job_requests')->onDelete('cascade');
            $table->foreign('certificate_id')->references('id')->on('certificates')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('webinars')->onDelete('cascade');
        });

        Schema::create('job_request_attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job_request_id')->unsigned();
            $table->string('title');
            $table->text('file');

            $table->foreign('job_request_id')->references('id')->on('job_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_requests');
        Schema::dropIfExists('job_request_share_items');
        Schema::dropIfExists('job_request_attachments');
    }
};
