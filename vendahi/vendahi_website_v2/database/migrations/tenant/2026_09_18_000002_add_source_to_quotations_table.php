<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Where a quotation came from: NULL/'' = created by staff in the back office,
 * 'portal' = requested by a customer from the client portal.
 *
 * Staff-created quotations start life as statut='pending' too, so the Ref
 * prefix used to be the only way to tell a customer request apart — too
 * fragile to hang the admin notification badge on.
 */
return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('quotations', 'source')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->string('source', 20)->nullable()->after('statut')->index();
            });

            // Requests made before this column existed: the portal has always
            // written this Ref prefix, so they are recoverable. Runs AFTER the
            // ALTER — inside the Blueprint closure the column does not exist yet.
            DB::table('quotations')
                ->where('Ref', 'LIKE', 'QT_REQ\_%')
                ->update(['source' => 'portal']);
        }
    }

    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (Schema::hasColumn('quotations', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
