<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->unsignedBigInteger('branch_id')->nullable();

            // Self-referential, one level deep (top-level item -> dropdown
            // children) — matches the one level of nesting the site's nav
            // already uses for "Academics". No FK constraint, consistent
            // with the rest of this app's schema.
            $table->unsignedBigInteger('parent_id')->nullable();

            $table->string('label');

            // home            -> the site's front page
            // anchor          -> a section on the homepage, e.g. "#about"
            // route           -> a known named route (admission, results, gallery, events, forms, contact, faq-anchor)
            // custom_url      -> any URL the admin types in, internal or external
            // academics_pages -> special: renders the existing auto-generated
            //                    dropdown of the institute's custom Pages
            $table->string('link_type', 30)->default('custom_url');
            $table->string('link_value')->nullable();

            $table->boolean('open_new_tab')->default(false);
            $table->integer('sort_order')->default(0);
            $table->smallInteger('status')->default(1)->comment('1=Active, 2=Inactive');

            $table->timestamps();

            $table->index(['institute_id', 'parent_id', 'status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_menus');
    }
};
