<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Structure staff into multiple categories, each institute can manage
     * edit, create, delete as per their need" -- e.g. Administration,
     * Teaching Staff, Supporting Staff. This is deliberately a new,
     * separate table from `departments`: departments in this codebase are
     * already used for a different purpose (academic subject departments
     * -- Bangla, Math, Physics, etc, confirmed from the actual seeded
     * data), not staff-type grouping. Reusing that table would conflate
     * two genuinely different classifications (a Math-department teacher
     * is also "Teaching Staff" -- those are independent axes).
     */
    public function up(): void
    {
        Schema::create('staff_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('name', 100);
            $table->unsignedInteger('sort_order')->default(100);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['institute_id', 'branch_id']);
        });

        Schema::table('staffs', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_category_id')->nullable()->after('department_id');
        });

        // Seed every existing branch with the three example categories from
        // the request, so this is immediately useful rather than an empty
        // list every institute has to populate themselves first. New
        // branches get the same defaults going forward via
        // app/Observers/BranchObserver.php.
        $now = now();
        $branches = \Illuminate\Support\Facades\DB::table('branches')->select('id', 'institute_id')->orderBy('id')->get();
        foreach ($branches as $branch) {
            \Illuminate\Support\Facades\DB::table('staff_categories')->insert([
                ['institute_id' => $branch->institute_id, 'branch_id' => $branch->id, 'name' => 'Administration', 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
                ['institute_id' => $branch->institute_id, 'branch_id' => $branch->id, 'name' => 'Teaching Staff', 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
                ['institute_id' => $branch->institute_id, 'branch_id' => $branch->id, 'name' => 'Supporting Staff', 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->dropColumn('staff_category_id');
        });

        Schema::dropIfExists('staff_categories');
    }
};
