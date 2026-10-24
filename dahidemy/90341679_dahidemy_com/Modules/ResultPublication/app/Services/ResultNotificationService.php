<?php

namespace Modules\ResultPublication\Services;

use App\Services\ApplicantNotificationService;
use Modules\Academic\Models\StudentSession;
use Modules\Authentication\Models\Institute;
use Modules\ParentModule\Models\ParentModel;
use Modules\ResultPublication\Models\ResultPublication;

class ResultNotificationService
{
    public function __construct(
        private readonly ApplicantNotificationService $notifier,
    ) {}

    /**
     * Notify every student (and their parent, if one exists) in the
     * publication's class/section. Safe to call more than once — the
     * publication's notified_at guard means the caller only invokes this
     * the moment it's first approved.
     */
    public function notifyClassSection(ResultPublication $publication): int
    {
        $institute = Institute::find($publication->institute_id);
        if (! $institute) {
            return 0;
        }

        $sessions = StudentSession::where('institute_id', $publication->institute_id)
            ->where('class_id', $publication->class_id)
            ->when($publication->section_id, fn ($q) => $q->where('section_id', $publication->section_id))
            ->when(get_option('academic_year'), fn ($q) => $q->where('session_id', get_option('academic_year')))
            ->with('student.user')
            ->get();

        $notified = 0;
        $subjectLabel = $publication->subject?->subject_name ?: 'All Subjects';
        $examLabel = $publication->exam?->name ?: 'Exam';

        foreach ($sessions as $session) {
            $student = $session->student;
            if (! $student) {
                continue;
            }

            $studentUser = $student->user;
            $studentName = trim(($student->first_name ?? '').' '.($student->last_name ?? ''));

            $lines = [
                "Your result for \"{$examLabel}\" ({$subjectLabel}) has been published.",
                $publication->description ?: '',
            ];

            if ($studentUser && ($studentUser->email || $studentUser->phone)) {
                $this->notifier->notify(
                    instituteId: $publication->institute_id,
                    branchId: $publication->branch_id,
                    name: $studentName ?: 'Student',
                    email: $studentUser->email,
                    phone: $studentUser->phone,
                    subject: "Result Published — {$examLabel}",
                    lines: $lines,
                    smsMessage: "Your result for \"{$examLabel}\" ({$subjectLabel}) has been published. Log in to view it.",
                    whatsappEvent: 'result_published',
                    whatsappParams: ['student_name' => $studentName, 'exam_name' => $examLabel, 'subject_name' => $subjectLabel],
                    referenceType: 'result_publication_student',
                    referenceId: $publication->id.'-'.$student->id,
                    schoolName: $institute->name,
                );
                $notified++;
            }

            $parent = ParentModel::where('student_id', $student->id)->with('user')->first();
            $parentUser = $parent?->user;
            if ($parentUser && ($parentUser->email || $parentUser->phone)) {
                $this->notifier->notify(
                    instituteId: $publication->institute_id,
                    branchId: $publication->branch_id,
                    name: $parent->parent_name ?: 'Guardian',
                    email: $parentUser->email,
                    phone: $parentUser->phone ?: $parent->parent_phone,
                    subject: "{$studentName}'s Result Published — {$examLabel}",
                    lines: [
                        "{$studentName}'s result for \"{$examLabel}\" ({$subjectLabel}) has been published.",
                        $publication->description ?: '',
                    ],
                    smsMessage: "{$studentName}'s result for \"{$examLabel}\" ({$subjectLabel}) has been published.",
                    whatsappEvent: 'result_published',
                    whatsappParams: ['student_name' => $studentName, 'exam_name' => $examLabel, 'subject_name' => $subjectLabel],
                    referenceType: 'result_publication_parent',
                    referenceId: $publication->id.'-'.$student->id,
                    schoolName: $institute->name,
                );
                $notified++;
            }
        }

        return $notified;
    }
}
