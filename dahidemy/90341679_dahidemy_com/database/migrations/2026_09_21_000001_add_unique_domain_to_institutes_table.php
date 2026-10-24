<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * institutes.domain had no unique index, so two institutes could claim the same domain and the
 * domain -> institute resolver would pick one arbitrarily.
 *
 * If duplicate domains already exist the migration FAILS with the list of offending domains and is
 * not recorded as run, so it can simply be re-run after the duplicates are fixed.
 * Note: soft-deleted institutes keep their domain, so a deleted institute's domain cannot be
 * re-used until that row is force-deleted or its domain cleared.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('institutes') || ! Schema::hasColumn('institutes', 'domain')) {
            return;
        }

        $dupes = DB::table('institutes')
            ->select('domain')
            ->whereNotNull('domain')
            ->groupBy('domain')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('domain');

        if ($dupes->isNotEmpty()) {
            // Fail loudly. Returning quietly would let Laravel record this migration as "run" without
            // creating the index, and every later `migrate` would say "Nothing to migrate".
            throw new RuntimeException(
                'Cannot add the unique index on institutes.domain: these domains are used by more than one institute: '
                .$dupes->implode(', ').'. Fix or clear the duplicates (also check soft-deleted rows), then run '
                .'"php artisan migrate" again. This migration was NOT recorded as run.'
            );
        }

        if (! empty(DB::select("SHOW INDEX FROM institutes WHERE Key_name = 'institutes_domain_unique'"))) {
            return;
        }

        Schema::table('institutes', function ($table) {
            $table->unique('domain', 'institutes_domain_unique');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('institutes') && ! empty(DB::select("SHOW INDEX FROM institutes WHERE Key_name = 'institutes_domain_unique'"))) {
            Schema::table('institutes', function ($table) {
                $table->dropUnique('institutes_domain_unique');
            });
        }
    }
};
