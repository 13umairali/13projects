<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_exam_attempts', function (Blueprint $table) {
            // Every suspicious event (tab switch, exited fullscreen, copy/paste
            // attempt, right-click) gets appended here as
            // {type, at, detail}. This is a deterrent + reporting tool, not a
            // guarantee — anything client-side can be bypassed by a determined
            // user with dev tools, so it's presented to reviewers as
            // "flagged for review", never used to silently fail a student.
            $table->json('integrity_violations')->nullable()->after('answers');
            $table->unsignedInteger('violation_count')->default(0)->after('integrity_violations');
        });

        Schema::table('online_exams', function (Blueprint $table) {
            $table->boolean('proctoring_enabled')->default(false)->after('is_active');
            $table->unsignedInteger('max_violations_before_auto_submit')->nullable()->after('proctoring_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('online_exam_attempts', function (Blueprint $table) {
            $table->dropColumn(['integrity_violations', 'violation_count']);
        });

        Schema::table('online_exams', function (Blueprint $table) {
            $table->dropColumn(['proctoring_enabled', 'max_violations_before_auto_submit']);
        });
    }
};
