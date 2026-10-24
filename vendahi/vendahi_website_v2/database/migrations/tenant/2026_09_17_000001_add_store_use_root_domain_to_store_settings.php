<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Root-domain mode: serve the storefront from the tenant host root ("/")
     * instead of /online_store or the custom base path. Off by default so
     * existing tenants keep the URL they already publish.
     */
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('store_settings', 'store_use_root_domain')) {
                $table->boolean('store_use_root_domain')->default(false)->after('store_url_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            if (Schema::hasColumn('store_settings', 'store_use_root_domain')) {
                $table->dropColumn('store_use_root_domain');
            }
        });
    }
};
