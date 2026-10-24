<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * institute_referral_settings originally covered one thing: what a
     * role earns for referring a new INSTITUTE to the platform. Now it
     * also needs to cover a second, distinct thing: what a role earns
     * for referring a new STUDENT/TEACHER/STAFF member to enroll at this
     * same institute - a different action with potentially a different
     * reward. referral_type distinguishes the two; the unique constraint
     * is widened so a role can have one rule per type.
     *
     * Every step here is defensive because this migration failed
     * partway through twice already on this install, for two different
     * genuine MySQL reasons: (1) MySQL refuses to drop an index that's
     * still the only one supporting a foreign key on that column, so the
     * old unique index must never be dropped before the new one exists,
     * and (2) MySQL identifiers have a hard 64-character limit -
     * Laravel's auto-generated name for the three-column unique index
     * here is 75 characters, so an explicit short name is required. This
     * version also tolerates re-running after any of the previous
     * partial attempts - it won't try to re-add a column or index that's
     * already there.
     */
    private const NEW_INDEX = 'irs_institute_role_type_unique';
    private const OLD_INDEX = 'institute_referral_settings_institute_id_referrer_role_unique';

    public function up(): void
    {
        if (! Schema::hasColumn('institute_referral_settings', 'referral_type')) {
            Schema::table('institute_referral_settings', function (Blueprint $table) {
                $table->enum('referral_type', ['refer_institute', 'refer_member'])->default('refer_institute')->after('referrer_role');
            });
        }

        // Add the new, wider index FIRST - institute_id always has an
        // index to satisfy its foreign key at every point in between.
        if (! $this->indexExists('institute_referral_settings', self::NEW_INDEX)) {
            Schema::table('institute_referral_settings', function (Blueprint $table) {
                $table->unique(['institute_id', 'referrer_role', 'referral_type'], self::NEW_INDEX);
            });
        }

        // Only now is it safe to drop the old, narrower one.
        if ($this->indexExists('institute_referral_settings', self::OLD_INDEX)) {
            Schema::table('institute_referral_settings', function (Blueprint $table) {
                $table->dropUnique(self::OLD_INDEX);
            });
        }
    }

    public function down(): void
    {
        // Same FK-safety rule in reverse: add the old, narrower index
        // back FIRST, before dropping the wider one.
        if (! $this->indexExists('institute_referral_settings', self::OLD_INDEX)) {
            Schema::table('institute_referral_settings', function (Blueprint $table) {
                $table->unique(['institute_id', 'referrer_role'], self::OLD_INDEX);
            });
        }

        if ($this->indexExists('institute_referral_settings', self::NEW_INDEX)) {
            Schema::table('institute_referral_settings', function (Blueprint $table) {
                $table->dropUnique(self::NEW_INDEX);
            });
        }

        if (Schema::hasColumn('institute_referral_settings', 'referral_type')) {
            Schema::table('institute_referral_settings', function (Blueprint $table) {
                $table->dropColumn('referral_type');
            });
        }
    }

    protected function indexExists(string $table, string $indexName): bool
    {
        $result = \Illuminate\Support\Facades\DB::select(
            'SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?',
            [$indexName]
        );

        return count($result) > 0;
    }
};