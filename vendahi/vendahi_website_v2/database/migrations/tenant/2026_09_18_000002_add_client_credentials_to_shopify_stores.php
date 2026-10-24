<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Connect a shop with a Dev Dashboard app instead of a pasted token.
 *
 * Since 1 January 2026 Shopify no longer lets a merchant create a custom app
 * in the admin, which was the only place a permanent `shpat_` Admin API token
 * was ever shown. New apps come from the Dev Dashboard and hand out a client
 * id + client secret; the ERP exchanges those for a token itself (client
 * credentials grant) and the token dies after 24 hours.
 *
 * So the store keeps the pair, and `access_token` becomes a cache of the last
 * token fetched with it, valid until `token_expires_at`. Stores connected
 * before 2026 with a pasted token have no pair and keep working unchanged.
 *
 * Tenant migration: shopify_stores lives in each tenant's database.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('shopify_stores', function (Blueprint $table) {
            if (! Schema::hasColumn('shopify_stores', 'client_id')) {
                $table->string('client_id')->nullable()->after('access_token');
            }
            if (! Schema::hasColumn('shopify_stores', 'client_secret')) {
                $table->text('client_secret')->nullable()->after('client_id');
            }
            if (! Schema::hasColumn('shopify_stores', 'token_expires_at')) {
                $table->dateTime('token_expires_at')->nullable()->after('client_secret');
            }
        });
    }

    public function down()
    {
        Schema::table('shopify_stores', function (Blueprint $table) {
            foreach (['client_id', 'client_secret', 'token_expires_at'] as $column) {
                if (Schema::hasColumn('shopify_stores', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
