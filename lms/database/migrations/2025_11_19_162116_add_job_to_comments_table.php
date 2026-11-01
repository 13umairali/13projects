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
        Schema::table('comments', function (Blueprint $table) {
            $table->integer('job_id')->unsigned()->nullable()->after('event_id');

            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
        });

        Schema::table('comments_reports', function (Blueprint $table) {
            $table->integer('job_id')->unsigned()->nullable()->after('event_id');

            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
        });
    }


};
