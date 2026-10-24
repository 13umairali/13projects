<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Protocol & Requests module — internal messaging / administrative protocol /
 * ticketing. A request (complaint, memo, letter, enquiry, …) is submitted by a
 * staff member or a customer, lands on a desk and moves desk to desk
 * (receive → acknowledge → respond/forward/return → approve/reject → close)
 * with every movement recorded as an event.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('protocol_desks')) {
            Schema::create('protocol_desks', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('description', 500)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps(6);
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('protocol_desk_users')) {
            Schema::create('protocol_desk_users', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('desk_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps(6);

                $table->unique(['desk_id', 'user_id']);
                $table->foreign('desk_id')->references('id')->on('protocol_desks')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('protocol_requests')) {
            Schema::create('protocol_requests', function (Blueprint $table) {
                $table->id();
                $table->string('Ref', 30)->nullable();
                // complaint | request | memo | letter | document | enquiry | other
                $table->string('type', 30)->default('request');
                $table->string('subject', 255);
                $table->text('body')->nullable();
                // low | normal | high | urgent
                $table->string('priority', 20)->default('normal');
                // open | received | acknowledged | in_progress | approved | rejected | closed
                $table->string('status', 30)->default('open');
                // Who submitted it: a staff user, or a customer (portal / logged by staff)
                $table->unsignedBigInteger('submitted_by_user_id')->nullable();
                $table->unsignedBigInteger('client_id')->nullable();
                $table->string('submitter_name', 150)->nullable();
                $table->string('submitter_email', 150)->nullable();
                // staff | portal
                $table->string('channel', 20)->default('staff');
                $table->unsignedBigInteger('current_desk_id')->nullable();
                $table->unsignedBigInteger('previous_desk_id')->nullable();
                $table->unsignedBigInteger('assigned_to_user_id')->nullable();
                $table->date('due_date')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamp('acknowledged_at')->nullable();
                $table->timestamp('decided_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->unsignedBigInteger('closed_by_user_id')->nullable();
                $table->timestamp('last_activity_at')->nullable();
                $table->timestamps(6);
                $table->softDeletes();

                $table->index(['status', 'current_desk_id']);
                $table->index('client_id');
                $table->index('submitted_by_user_id');
                $table->index('assigned_to_user_id');
            });
        }

        if (! Schema::hasTable('protocol_request_events')) {
            Schema::create('protocol_request_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('request_id');
                // submitted | received | acknowledged | note | response | reply | forwarded |
                // reassigned | returned | approved | rejected | closed | reopened | attachment | updated
                $table->string('action', 30);
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('client_id')->nullable();
                $table->unsignedBigInteger('from_desk_id')->nullable();
                $table->unsignedBigInteger('to_desk_id')->nullable();
                $table->unsignedBigInteger('assigned_to_user_id')->nullable();
                $table->text('note')->nullable();
                // Internal notes are never shown to the customer who submitted the request.
                $table->boolean('is_internal')->default(false);
                $table->timestamps(6);

                $table->index('request_id');
                $table->foreign('request_id')->references('id')->on('protocol_requests')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('protocol_request_attachments')) {
            Schema::create('protocol_request_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('request_id');
                $table->unsignedBigInteger('event_id')->nullable();
                $table->string('file_name');
                $table->string('file_path');
                $table->string('file_type', 30)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
                $table->unsignedBigInteger('uploaded_by_client_id')->nullable();
                $table->timestamps(6);

                $table->index('request_id');
                $table->foreign('request_id')->references('id')->on('protocol_requests')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('protocol_request_attachments');
        Schema::dropIfExists('protocol_request_events');
        Schema::dropIfExists('protocol_requests');
        Schema::dropIfExists('protocol_desk_users');
        Schema::dropIfExists('protocol_desks');
    }
};
