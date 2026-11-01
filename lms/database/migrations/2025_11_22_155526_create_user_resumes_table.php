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
        Schema::create('user_resumes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('attach')->nullable();
            $table->bigInteger('created_at')->unsigned();

            $table->foreign('user_id')->on('users')->references('id')->cascadeOnDelete();
        });

        Schema::create('user_resume_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_resume_id');
            $table->string('locale', 191)->index();
            $table->string('title');

            $table->foreign('user_resume_id', 'user_resume_id_trans')->on('user_resumes')->references('id')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_resumes');
        Schema::dropIfExists('user_resume_translations');
    }
};
