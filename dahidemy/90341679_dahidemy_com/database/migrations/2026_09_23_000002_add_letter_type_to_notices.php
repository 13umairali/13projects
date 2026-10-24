<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Add the ability to create customized Notices, Circulars,
     * Appointment Letters, and Dismissal Letters with dynamic tags..."
     * Reuses the existing `notices` table/feature rather than building
     * four separate ones — all four are fundamentally the same thing
     * (a written communication with a title and body, optionally sent to
     * someone), differentiated by `type` and, for the two letter types
     * that address one specific staff member rather than broadcasting to
     * a user_type group, `recipient_staff_id`.
     */
    public function up(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            $table->string('type', 30)->default('Notice')->after('title');
            $table->unsignedBigInteger('recipient_staff_id')->nullable()->after('type');
            $table->boolean('display_on_website')->default(false)->after('recipient_staff_id');
        });

        // Every row that already exists was a plain "Notice" under the old
        // schema and was already being shown, unfiltered, on the public
        // homepage (WebsiteController) -- backfill display_on_website=true
        // for existing rows only, so this migration doesn't silently hide
        // notices institutes are already relying on being public. New
        // rows default to false (opt-in) precisely because two of the new
        // types, Appointment Letter and Dismissal Letter, are sensitive
        // staff documents that must never be public by accident.
        \Illuminate\Support\Facades\DB::table('notices')->update(['display_on_website' => true]);
    }

    public function down(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            $table->dropColumn(['type', 'recipient_staff_id', 'display_on_website']);
        });
    }
};
