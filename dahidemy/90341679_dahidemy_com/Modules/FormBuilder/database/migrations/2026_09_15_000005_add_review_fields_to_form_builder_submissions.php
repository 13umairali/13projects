<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_builder_submissions', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('status');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('remarks');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('form_builder_submissions', function (Blueprint $table) {
            $table->dropColumn(['remarks', 'reviewed_by', 'reviewed_at']);
        });
    }
};
