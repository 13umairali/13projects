<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'last_login_ip')) {
            Schema::table('users', function (Blueprint $table) {
                // No ->after('signup_ip'): that column is added by the later
                // add_username_and_mailbox_fields migration, so referencing it
                // here made a fresh install fail on this migration.
                $table->string('last_login_ip', 45)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'last_login_ip')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('last_login_ip'));
        }
    }
};
