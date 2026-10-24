<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Repairs installs where 2026_08_22_000001_add_landing_page_toggle_to_general_settings_table
 * is recorded as run but the columns are missing (the original ALTER added both columns in
 * one statement and positioned them with after('landing_custom_font_path'), so on any install
 * without that reference column the whole statement failed).
 *
 * Because the original migration is already in the migrations table on those installs it can
 * never re-run, so the columns are added here, each one guarded independently.
 */
return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('central');

        if (! $schema->hasTable('general_settings')) {
            return;
        }

        if (! $schema->hasColumn('general_settings', 'landing_page_enabled')) {
            $schema->table('general_settings', function (Blueprint $table) use ($schema) {
                $column = $table->boolean('landing_page_enabled')->default(true);

                if ($schema->hasColumn('general_settings', 'landing_custom_font_path')) {
                    $column->after('landing_custom_font_path');
                }
            });
        }

        if (! $schema->hasColumn('general_settings', 'landing_redirect_url')) {
            $schema->table('general_settings', function (Blueprint $table) {
                $table->string('landing_redirect_url', 500)->nullable()->after('landing_page_enabled');
            });
        }
    }

    public function down(): void
    {
        // The original migration owns these columns; nothing to reverse here.
    }
};
