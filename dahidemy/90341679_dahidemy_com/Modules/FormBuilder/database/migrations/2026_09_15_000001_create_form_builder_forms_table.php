<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_builder_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('name');
            $table->string('slug', 191);
            $table->string('type', 50)->default('custom'); // registration, job_application, admission, feedback, custom
            $table->text('description')->nullable();
            $table->string('submit_button_text', 100)->default('Submit');
            $table->text('success_message')->nullable();
            $table->string('notify_email')->nullable();
            $table->string('icon', 100)->nullable(); // font-awesome class for the homepage card
            $table->smallInteger('status')->default(2)->comment('1=Published, 2=Draft');
            $table->boolean('show_on_homepage')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // A slug only needs to be unique within a single institute, not globally,
            // since every tenant manages its own set of forms.
            $table->unique(['institute_id', 'slug']);
            $table->index(['institute_id', 'status', 'show_on_homepage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_builder_forms');
    }
};
