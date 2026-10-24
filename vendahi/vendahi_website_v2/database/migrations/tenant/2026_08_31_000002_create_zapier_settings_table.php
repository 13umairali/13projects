<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zapier integration: per-tenant API key + the acting user Zapier requests run
 * as. A `source` tag on webhooks separates Zapier's REST-hook subscriptions
 * from the ones managed on the Webhooks settings page.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('zapier_settings')) {
            Schema::create('zapier_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->boolean('enabled')->default(false);
                // sha256 of the API key; the key itself is shown once on generation.
                $table->string('api_key_hash', 64)->nullable();
                // Display hint so the admin can recognise the active key.
                $table->string('api_key_last4', 8)->nullable();
                // Zapier requests are authorized as this user (permissions apply).
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamp('key_generated_at')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('webhooks', function (Blueprint $table) {
            if (! Schema::hasColumn('webhooks', 'source')) {
                $table->string('source', 32)->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zapier_settings');

        Schema::table('webhooks', function (Blueprint $table) {
            if (Schema::hasColumn('webhooks', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
