<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-time backfill for institutes created before the website design
 * settings (theme + brand colors) existed. New registrations already get
 * these from config('onboarding.institute_settings.defaults'); this migration
 * brings existing tenants up to the same baseline instead of leaving them
 * with a settings row that's simply missing, which is what previously made
 * the public site fall back to hardcoded defaults with no way to change them.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings') || ! Schema::hasTable('institutes')) {
            return;
        }

        $defaults = config('onboarding.institute_settings.defaults', []);

        $websiteDefaults = array_filter(
            $defaults,
            fn ($key) => str_starts_with($key, 'website_'),
            ARRAY_FILTER_USE_KEY
        );

        if ($websiteDefaults === []) {
            return;
        }

        $now = Carbon::now();

        $branches = DB::table('branches')->select('id', 'institute_id')->get();

        foreach ($branches as $branch) {
            $existing = DB::table('settings')
                ->where('institute_id', $branch->institute_id)
                ->where('branch_id', $branch->id)
                ->whereIn('name', array_keys($websiteDefaults))
                ->pluck('name')
                ->all();

            $rows = [];

            foreach ($websiteDefaults as $name => $value) {
                if (in_array($name, $existing, true)) {
                    continue;
                }

                $rows[] = [
                    'institute_id' => $branch->institute_id,
                    'branch_id' => $branch->id,
                    'type' => 'general',
                    'name' => $name,
                    'value' => (string) $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows !== []) {
                DB::table('settings')->insert($rows);
            }
        }
    }

    public function down(): void
    {
        // Intentionally left as a no-op: removing these would take working
        // theme/color settings away from institutes that have since changed
        // them, and the migration is additive-only (skips existing rows).
    }
};
