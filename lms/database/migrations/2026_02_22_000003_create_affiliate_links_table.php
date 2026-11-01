<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateLinksTable extends Migration
{
    public function up()
    {
        Schema::create('course_affiliate_links', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('type', 20)->default('course');
            $table->integer('item_id')->unsigned();
            $table->integer('created_at')->unsigned();

            $table->index(['user_id', 'type', 'item_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_affiliate_links');
    }
}
