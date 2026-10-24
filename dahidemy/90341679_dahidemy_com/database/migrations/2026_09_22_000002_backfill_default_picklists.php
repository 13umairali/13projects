<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * One-time backfill matching app/Observers/BranchObserver.php, which
     * now seeds default reference data (picklists, merit_process_types,
     * leave_types, assessment_domains/items) for every NEW branch. That
     * only helps institutes created from here on. Every institute that
     * already exists today -- other than the one institute that happened
     * to be hand-seeded with this data -- has had completely empty
     * dropdowns for all of these this whole time. This migration applies
     * the same defaults to every existing branch that doesn't already have
     * data of these types, so currently live institutes get fixed too, not
     * just future signups. See BranchObserver for the full reasoning on
     * why these specific tables and not others (policies, settings, etc.).
     */
    public function up(): void
    {
        $branches = DB::table('branches')->select('id', 'institute_id')->get();
        $now = now();

        foreach ($branches as $branch) {
            $this->seedPicklists($branch->institute_id, $branch->id, $now);
            $this->seedSimpleTable('merit_process_types', $branch->institute_id, $branch->id, $now, [
                ['type' => 'Total Mark(Sequential)', 'serial' => 1],
                ['type' => 'Grade Point(Sequential)', 'serial' => 1],
            ]);
            $this->seedSimpleTable('leave_types', $branch->institute_id, $branch->id, $now, [
                ['name' => 'Medical Leave'],
                ['name' => 'Vacation Leave'],
                ['name' => 'Emergency Leave'],
                ['name' => 'Maternity/Paternity Leave'],
                ['name' => 'Study Leave'],
                ['name' => 'Sick Leave'],
                ['name' => 'Family Leave'],
                ['name' => 'Special Leave'],
                ['name' => 'Educational Leave'],
                ['name' => 'Bereavement Leave'],
                ['name' => 'Religious Leave'],
                ['name' => 'Extracurricular Leave'],
            ]);
            $this->seedAssessmentDomains($branch->institute_id, $branch->id, $now);
        }
    }

    private function seedPicklists($instituteId, $branchId, $now): void
    {
        $defaults = [
            'Designation' => [
                'Department Head', 'Lecturer', 'Associate Professor',
                'Professor', 'Principal', 'Director of administration and student guidance',
            ],
            'Staff Designation' => [
                'Accounts Office', 'Exam Office', 'IT Office', 'Library',
                'Storekeeper', 'Lab Assistant', 'Maintenance', 'Academic Office',
            ],
            'Religion' => ['Islam', 'Christianity', 'Hinduism', 'Buddhism', 'Others'],
        ];

        $existingTypes = DB::table('picklists')
            ->where('institute_id', $instituteId)
            ->whereIn('type', array_keys($defaults))
            ->distinct()
            ->pluck('type')
            ->all();

        $rows = [];
        foreach ($defaults as $type => $values) {
            if (in_array($type, $existingTypes, true)) {
                continue;
            }
            foreach ($values as $value) {
                $rows[] = [
                    'institute_id' => $instituteId,
                    'branch_id' => $branchId,
                    'type' => $type,
                    'value' => $value,
                    'slug' => str($value)->slug(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows !== []) {
            DB::table('picklists')->insert($rows);
        }
    }

    private function seedSimpleTable(string $table, $instituteId, $branchId, $now, array $rows): void
    {
        if (DB::table($table)->where('institute_id', $instituteId)->exists()) {
            return;
        }

        $insert = array_map(fn ($row) => array_merge($row, [
            'institute_id' => $instituteId,
            'branch_id' => $branchId,
            'created_at' => $now,
            'updated_at' => $now,
        ]), $rows);

        DB::table($table)->insert($insert);
    }

    private function seedAssessmentDomains($instituteId, $branchId, $now): void
    {
        if (DB::table('assessment_domains')->where('institute_id', $instituteId)->exists()) {
            return;
        }

        $scale = json_encode(['Excellent', 'Very Good', 'Good', 'Satisfactory', 'Needs Improvement']);

        $domains = [
            'cognitive' => ['name' => 'Cognitive Domain', 'order' => 1, 'items' => [
                'Subject Knowledge', 'Problem Solving', 'Written Expression',
            ]],
            'affective' => ['name' => 'Affective Domain', 'order' => 2, 'items' => [
                'Discipline', 'Cooperation', 'Responsibility', 'Attendance Attitude', 'Leadership',
            ]],
            'psychomotor' => ['name' => 'Psychomotor Domain', 'order' => 3, 'items' => [
                'Practical Skills', 'Handwriting', 'ICT Practical Skills', 'Art & Craft',
            ]],
        ];

        foreach ($domains as $domainType => $domain) {
            $domainId = DB::table('assessment_domains')->insertGetId([
                'institute_id' => $instituteId,
                'branch_id' => $branchId,
                'session_id' => null,
                'domain_type' => $domainType,
                'name' => $domain['name'],
                'is_active' => 1,
                'display_order' => $domain['order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $itemRows = [];
            foreach ($domain['items'] as $order => $itemName) {
                $itemRows[] = [
                    'institute_id' => $instituteId,
                    'branch_id' => $branchId,
                    'assessment_domain_id' => $domainId,
                    'name' => $itemName,
                    'max_score' => 5.00,
                    'rating_type' => 'scale',
                    'grade_scale' => $scale,
                    'remarks_enabled' => 1,
                    'display_order' => $order + 1,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('assessment_items')->insert($itemRows);
        }
    }

    /**
     * Deliberately no down(): this only inserts rows that plausibly didn't
     * exist before (types were entirely missing for that institute), and
     * reversing it risks deleting data an institute has since started
     * actually using and customizing.
     */
    public function down(): void {}
};
