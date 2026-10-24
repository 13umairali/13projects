<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per "log in as institute admin" request made by a SAAS Admin. Doubles as the audit trail:
 * who minted it, for which institute/user, from which IP, and when/where it was used.
 * Only a SHA-256 hash of the token is stored, never the token itself.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('impersonation_tokens')) {
            return;
        }

        Schema::create('impersonation_tokens', function (Blueprint $table) {
            $table->id();
            $table->char('token_hash', 64)->unique();
            $table->unsignedBigInteger('institute_id')->index();
            $table->unsignedBigInteger('target_user_id');
            $table->unsignedBigInteger('impersonator_id')->index();
            $table->string('return_url', 500)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->string('created_ip', 45)->nullable();
            $table->string('used_ip', 45)->nullable();
            $table->string('used_user_agent', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impersonation_tokens');
    }
};
