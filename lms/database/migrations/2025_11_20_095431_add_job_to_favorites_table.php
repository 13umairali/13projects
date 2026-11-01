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
        Schema::table('favorites', function (Blueprint $table) {
            $table->integer('job_id')->unsigned()->nullable()->after('upcoming_course_id');

            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
        });
    }

};
