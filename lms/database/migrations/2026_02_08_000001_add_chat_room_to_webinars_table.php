<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webinars', function (Blueprint $table) {
            if (!Schema::hasColumn('webinars', 'chat_room')) {
                $table->boolean('chat_room')->default(false)->after('forum');
            }
            if (!Schema::hasColumn('webinars', 'chat_room_closed')) {
                $table->boolean('chat_room_closed')->default(false)->after('chat_room');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('webinars', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('webinars', 'chat_room')) {
                $columns[] = 'chat_room';
            }
            if (Schema::hasColumn('webinars', 'chat_room_closed')) {
                $columns[] = 'chat_room_closed';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
