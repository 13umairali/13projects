<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('central');

        // Each column is added in its own statement and guarded, so a missing
        // reference column can no longer take the whole migration down with it.
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
        $schema = Schema::connection('central');

        $drop = array_values(array_filter(
            ['landing_page_enabled', 'landing_redirect_url'],
            fn ($column) => $schema->hasColumn('general_settings', $column)
        ));

        if ($drop === []) {
            return;
        }

        $schema->table('general_settings', function (Blueprint $table) use ($drop) {
            $table->dropColumn($drop);
        });
    }
};
