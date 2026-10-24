<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "For Events, add a 'Display on Website' toggle." Confirmed before
     * building: no such column existed, and the public homepage
     * (WebsiteController) showed every event for the institute with no
     * filter at all. Defaults to true so existing events keep behaving
     * exactly as they do today (all currently visible) -- this only adds
     * the ability to hide one going forward, it doesn't hide anything
     * retroactively.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_public')->default(true)->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
};
