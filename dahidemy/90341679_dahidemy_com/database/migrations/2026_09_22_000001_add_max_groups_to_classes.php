<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Student Groups (student_groups table) are institute-wide — there is
     * no class_id on that table at all, so historically any class could be
     * paired with any number of groups (through subjects.group_id /
     * subject_configs.group_id) with no limit. Institutes need to cap this
     * per class — e.g. Kindergarten should only ever offer 2 groups,
     * Nursery/Prep only 3 — since offering unlimited groups on early-years
     * classes doesn't make academic sense and was a source of data-entry
     * mistakes. NULL means "no cap" (unchanged behaviour) so this is fully
     * backward compatible with existing classes.
     */
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_groups')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('max_groups');
        });
    }
};
