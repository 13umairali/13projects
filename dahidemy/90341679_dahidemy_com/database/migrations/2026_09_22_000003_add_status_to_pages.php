<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The "Status" (Active/Inactive) selector on the Page create/edit forms
     * was completely non-functional: it wasn't in PageStoreRequest's or
     * PageUpdateRequest's validation rules, wasn't in Page::$fillable, and
     * the `pages` table has no status column at all — so whatever an admin
     * picked was silently discarded at the very first step and never
     * reached the database. The pages index also displayed
     * `$page->page_status`, a column/accessor that has never existed,
     * which is why the Status column on that list always appeared blank.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
