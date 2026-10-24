<?php

namespace App\Observers;

use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\Picklist;
use Modules\Authentication\Models\Branch;

class BranchObserver
{
    /**
     * A systemic gap: several "reference/default data" tables are scoped
     * per institute (institute_id NOT NULL) but nothing ever seeds them for
     * a new institute -- only the one institute that was hand-seeded when
     * this database was set up has any rows in them. Every institute
     * created since has had completely empty dropdowns for all of these,
     * which shows up as unrelated-looking bugs in unrelated-looking
     * places: "Designation filter not working" (picklists), "Cannot click
     * Merit Process Type in Exam Create" (merit_process_types -- there was
     * nothing to click), unexplained gaps in Assessment Domain / Domain
     * Assessment screens (assessment_domains / assessment_items), and
     * presumably confusing/empty Leave Type options in HR (leave_types).
     *
     * Found by checking every institute-scoped table in the database and
     * confirming which ones only ever had data for institute_id = 1 -- this
     * is every one of those EXCEPT `policies` (Privacy/Cookie/Terms &
     * Conditions text) and `settings`/`contacts`/`testimonials`, which are
     * left alone deliberately: legal policy text and public-facing CMS
     * content are not safe or appropriate to auto-generate for someone
     * else's institute, and are already handled by
     * InstituteContentCloningService for the CMS case.
     *
     * A branch (not the institute) is the seeding trigger because
     * branch_id is required on all of these tables and doesn't exist yet
     * at the point the Institute record itself is created -- every
     * institute-creation path already creates a "Main Branch" immediately
     * after, so this covers all of them without duplicating this list in
     * three different places.
     */
    public function created(Branch $branch): void
    {
        $instituteId = $branch->institute_id;
        $branchId = $branch->id;
        $now = now();

        $this->seedPicklists($instituteId, $branchId, $now);
        $this->seedSimpleTable('merit_process_types', $instituteId, $branchId, $now, [
            ['type' => 'Total Mark(Sequential)', 'serial' => 1],
            ['type' => 'Grade Point(Sequential)', 'serial' => 1],
        ]);
        $this->seedSimpleTable('leave_types', $instituteId, $branchId, $now, [
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
        $this->seedAssessmentDomains($instituteId, $branchId, $now);
        $this->seedSimpleTable('staff_categories', $instituteId, $branchId, $now, [
            ['name' => 'Administration', 'sort_order' => 10],
            ['name' => 'Teaching Staff', 'sort_order' => 20],
            ['name' => 'Supporting Staff', 'sort_order' => 30],
        ]);
    }

    private function seedPicklists(int $instituteId, int $branchId, $now): void
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

        $existingTypes = Picklist::where('institute_id', $instituteId)
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
            Picklist::insert($rows);
        }
    }

    /** @param array<int, array<string, mixed>> $rows */
    private function seedSimpleTable(string $table, int $instituteId, int $branchId, $now, array $rows): void
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

    private function seedAssessmentDomains(int $instituteId, int $branchId, $now): void
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
}
