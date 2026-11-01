<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('public_chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('instructor_id');
            $table->string('source_type', 20); // course, bundle, event
            $table->unsignedInteger('source_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'instructor_id', 'source_type', 'source_id'], 'unique_conversation');
            $table->index(['instructor_id', 'updated_at']);
        });

        Schema::create('public_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedInteger('sender_id');
            $table->unsignedBigInteger('reply_to_id')->nullable();
            $table->text('message');
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('public_chat_conversations')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reply_to_id')->references('id')->on('public_chat_messages')->onDelete('set null');
            $table->index(['conversation_id', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('enable_public_chat')->default(false)->after('role_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('public_chat_messages');
        Schema::dropIfExists('public_chat_conversations');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('enable_public_chat');
        });
    }
};
