<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "If transferring to another school, require a 'School Name' input
     * field." There was previously no way to capture this at all -- the
     * Migration screen only supported moving a student to a different
     * class/section within this institute. This adds a genuinely separate
     * "Transfer to Another School" option alongside the existing merit /
     * no-merit migration types, with its own School Name field.
     *
     * Scope note, stated explicitly: this migration adds the *data
     * capture* (the school name is now recorded against the migration
     * record). It deliberately does NOT change what happens to the
     * student's current class/section session on this type of transfer
     * (e.g. whether it should be marked inactive/withdrawn) -- that
     * touches student_sessions status logic used by attendance, exams,
     * and fee reports across the system, and changing it without
     * confirming the intended behavior with the institute risks silently
     * breaking those other reports. Capturing the school name now is safe
     * and immediately useful either way.
     */
    public function up(): void
    {
        Schema::table('student_migrations', function (Blueprint $table) {
            $table->string('transferred_to_school_name', 191)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('student_migrations', function (Blueprint $table) {
            $table->dropColumn('transferred_to_school_name');
        });
    }
};
