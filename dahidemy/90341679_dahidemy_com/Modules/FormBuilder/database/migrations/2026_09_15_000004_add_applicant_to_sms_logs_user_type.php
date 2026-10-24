<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ApplicantNotificationService logs SMS sends to the existing sms_logs
 * table (so they show up in your existing SMS reports), but sms_logs.user_type
 * is an ENUM that didn't previously include anyone who isn't already a user
 * of the system. This adds 'Applicant' as a valid value — purely additive,
 * existing rows and values are untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sms_logs')) {
            return;
        }

        DB::statement("ALTER TABLE `sms_logs` MODIFY `user_type` ENUM('Teacher','Staff','Accountant','Librarian','Employee','Student','Applicant') NOT NULL DEFAULT 'Teacher'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('sms_logs')) {
            return;
        }

        DB::statement("ALTER TABLE `sms_logs` MODIFY `user_type` ENUM('Teacher','Staff','Accountant','Librarian','Employee','Student') NOT NULL DEFAULT 'Teacher'");
    }
};
