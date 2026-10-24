<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Rename 'Transport Members' (currently broken due to no students) to
     * accurately reflect passengers/conductors." Confirmed before building:
     * transport_members.student_id was a hard NOT NULL column, so there was
     * genuinely no way to represent a Conductor (or any non-student
     * passenger) at all -- this was a real schema limitation, not a bug in
     * the existing create-form code.
     *
     * Mirrors the pattern already used and working in `library_members` in
     * this exact codebase (member_type + nullable type-specific foreign
     * keys) rather than inventing a new shape, for consistency.
     *
     * Design assumption stated explicitly since this wasn't confirmed with
     * the client: a Conductor is a staff member who rides a route but is
     * not billed a fare (fare_amount defaults to 0 for staff/conductor
     * members) and does not get a Fee Card entry the way a student
     * passenger does. If conductors should actually be paid via payroll
     * for this duty or tracked differently, that's a follow-up decision,
     * not something guessed at here.
     */
    public function up(): void
    {
        Schema::table('transport_members', function (Blueprint $table) {
            $table->string('member_type', 20)->default('student')->after('branch_id');
            $table->unsignedBigInteger('staff_id')->nullable()->after('student_id');
        });

        // ->change() on the existing columns would need doctrine/dbal,
        // which isn't a dependency of this project -- raw SQL avoids
        // adding that just for this.
        DB::statement('ALTER TABLE transport_members MODIFY student_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE transport_members MODIFY fare_amount DECIMAL(8,2) NULL DEFAULT 0');

        // Every existing row is a student passenger (that was the only
        // option until now) -- make that explicit rather than leaving
        // member_type to rely on the column default alone.
        DB::table('transport_members')->update(['member_type' => 'student']);
    }

    public function down(): void
    {
        Schema::table('transport_members', function (Blueprint $table) {
            $table->dropColumn(['member_type', 'staff_id']);
        });

        DB::statement('ALTER TABLE transport_members MODIFY student_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE transport_members MODIFY fare_amount DECIMAL(8,2) NOT NULL');
    }
};
