<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Knowledge base articles are now read from the central database
     * (kb_articles), so feedback rows reference central article ids. The
     * foreign key to the tenant-local knowledge_base_articles table must go,
     * and feedback left over from the old tenant-local articles is removed so
     * it cannot be misattributed to unrelated central articles with the same id.
     */
    public function up(): void
    {
        if (! Schema::hasTable('knowledge_base_article_feedbacks')) {
            return;
        }

        Schema::table('knowledge_base_article_feedbacks', function (Blueprint $table) {
            $table->dropForeign('kb_article_feedbacks_article_id_foreign');
        });

        DB::table('knowledge_base_article_feedbacks')->delete();
    }

    public function down(): void
    {
        if (! Schema::hasTable('knowledge_base_article_feedbacks')) {
            return;
        }

        // Feedback rows keyed by central ids would violate the restored
        // constraint, so they are cleared first.
        DB::table('knowledge_base_article_feedbacks')->delete();

        Schema::table('knowledge_base_article_feedbacks', function (Blueprint $table) {
            $table->foreign(['knowledge_base_article_id'], 'kb_article_feedbacks_article_id_foreign')
                ->references(['id'])->on('knowledge_base_articles')
                ->onUpdate('restrict')->onDelete('cascade');
        });
    }
};
