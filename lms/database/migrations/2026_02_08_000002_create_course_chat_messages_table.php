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
        Schema::create('course_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('webinar_id');
            $table->unsignedInteger('sender_id');
            $table->unsignedBigInteger('reply_to_id')->nullable();
            $table->text('message');
            $table->enum('type', ['text', 'voice', 'file', 'image', 'video'])->default('text');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->unsignedInteger('voice_duration')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();

            $table->foreign('webinar_id')->references('id')->on('webinars')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reply_to_id')->references('id')->on('course_chat_messages')->onDelete('set null');

            $table->index(['webinar_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_chat_messages');
    }
};
