<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('central');

        if ($schema->hasTable('tenant_impersonation_logs')) {
            return;
        }

        $schema->create('tenant_impersonation_logs', function (Blueprint $table) {
            $table->id();

            // Single-use login token, stored hashed (sha256). Cleared once consumed.
            $table->string('token_hash', 64)->nullable()->unique();

            // Admin identity is denormalized so the audit trail survives admin deletion.
            $table->unsignedBigInteger('central_user_id')->nullable()->index();
            $table->string('admin_name');
            $table->string('admin_email')->nullable();

            // Target tenant + user (tenant users live in per-tenant DBs, so no FK).
            $table->string('tenant_id', 36)->index();
            $table->string('tenant_name')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            // pending -> active -> ended | expired | failed
            $table->string('status', 20)->default('pending')->index();
            $table->string('ended_by', 20)->nullable(); // exit | logout | expired
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            // State-changing requests performed during the session: [{t, method, path, status}]
            $table->json('actions')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'status']);
        });

        // The FK is a convenience only — admin identity is already denormalized above,
        // so a legacy central_users table that cannot take it must not abort the whole
        // migration run and leave every later migration unapplied.
        try {
            $schema->table('tenant_impersonation_logs', function (Blueprint $table) {
                $table->foreign('central_user_id')
                    ->references('id')->on('central_users')
                    ->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // Keep going without the constraint.
        }
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('tenant_impersonation_logs');
    }
};
