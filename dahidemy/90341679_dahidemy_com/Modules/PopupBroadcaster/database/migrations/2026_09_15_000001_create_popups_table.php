<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->string('title');
            $table->text('message')->nullable();
            $table->string('image')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_link')->nullable();

            // everyone | students_only | parents_only | logged_in_only
            $table->string('audience', 20)->default('everyone');

            // every_visit | once_per_session | once_ever (per browser)
            $table->string('display_frequency', 20)->default('once_per_session');

            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->integer('priority')->default(0);
            $table->smallInteger('status')->default(1)->comment('1=Active, 2=Inactive');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['institute_id', 'status', 'start_at', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popups');
    }
};
