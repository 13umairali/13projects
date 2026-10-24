<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * One row per referrer role - fully dynamic, editable by the SaaS
         * Super Admin. "role" is a plain string (institute, student,
         * parent, teacher, accountant, staff, or any other role name
         * already in use) rather than an enum, since role names in this
         * system are configurable and this must not need a migration
         * every time a new role is added elsewhere.
         */
        Schema::create('referral_settings', function (Blueprint $table) {
            $table->id();
            $table->string('referrer_role');
            $table->enum('reward_type', ['days', 'cash', 'points']);
            $table->decimal('reward_value', 12, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('referrer_role');
        });

        /**
         * The actual record of one referral. referrer_type distinguishes
         * "an institute referred this" from "an individual user (any
         * role) referred this" - referrer_role is a snapshot taken at
         * referral time, so a later role change (or the user leaving)
         * never rewrites referral history.
         */
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->enum('referrer_type', ['institute', 'user']);
            $table->unsignedBigInteger('referrer_id');
            $table->string('referrer_role')->nullable();
            $table->string('referral_code_used');
            $table->unsignedBigInteger('referred_institute_id')->nullable();
            $table->enum('status', ['pending', 'converted', 'rewarded', 'rejected'])->default('pending');
            $table->enum('reward_type', ['days', 'cash', 'points'])->nullable();
            $table->decimal('reward_value', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('rewarded_at')->nullable();
            $table->timestamps();

            $table->index(['referrer_type', 'referrer_id']);
            $table->foreign('referred_institute_id')->references('id')->on('institutes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('referral_settings');
    }
};