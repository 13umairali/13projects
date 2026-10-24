<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PWA identity + launch overrides the Settings → PWA page already edited
     * but the backend never persisted (only the two icons were saved, so every
     * other field reset to its placeholder on reload).
     *
     * All are NULL-by-default overrides: empty means "fall back to the app name
     * / the built-in per-surface default", which is what the page's
     * placeholders show. pwa_enabled defaults to true so existing installs stay
     * installable exactly as before.
     */
    private const COLUMNS = [
        'pwa_name'             => 190,
        'pwa_short_name'       => 60,
        'pwa_description'      => 190,
        'pwa_start_url'        => 190,
        'pwa_display'          => 20,
        'pwa_orientation'      => 20,
        'pwa_theme_color'      => 20,
        'pwa_background_color' => 20,
    ];

    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'pwa_enabled')) {
                $table->boolean('pwa_enabled')->default(true);
            }
            foreach (self::COLUMNS as $column => $length) {
                if (! Schema::hasColumn('settings', $column)) {
                    $table->string($column, $length)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (array_merge(['pwa_enabled'], array_keys(self::COLUMNS)) as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
