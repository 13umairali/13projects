<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Sidebar brand: show/hide the logo and its rendered size (px).
            // Defaults match the historical hard-coded 32x32 sidebar logo.
            if (! Schema::hasColumn('settings', 'sidebar_show_logo')) {
                $table->boolean('sidebar_show_logo')
                    ->default(true)
                    ->after('hide_site_name');
            }
            if (! Schema::hasColumn('settings', 'sidebar_logo_width')) {
                $table->unsignedSmallInteger('sidebar_logo_width')
                    ->default(32)
                    ->after('sidebar_show_logo');
            }
            if (! Schema::hasColumn('settings', 'sidebar_logo_height')) {
                $table->unsignedSmallInteger('sidebar_logo_height')
                    ->default(32)
                    ->after('sidebar_logo_width');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (['sidebar_logo_height', 'sidebar_logo_width', 'sidebar_show_logo'] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
