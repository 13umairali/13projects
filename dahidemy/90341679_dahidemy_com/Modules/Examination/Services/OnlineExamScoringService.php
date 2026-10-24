<?php

namespace Modules\Examination\Services;

use Modules\Examination\Models\OnlineExam;
use Modules\Examination\Models\OnlineExamAttempt;
use Modules\Examination\Models\OnlineExamQuestion;

class OnlineExamScoringService
{
    public function convertScore(float $obtained, float $onlineFullMarks, float $academicFullMarks, bool $enabled): float
    {
        if (! $enabled || $onlineFullMarks <= 0) {
            return round($obtained, 2);
        }

        return round(($obtained / $onlineFullMarks) * $academicFullMarks, 2);
    }

    public function startAttempt(OnlineExam $onlineExam, int $studentId, ?int $userId = null): OnlineExamAttempt
    {
        if (! $onlineExam->isOpen()) {
            throw new \Exception('This online exam is not currently available.');
        }

        $submittedCount = OnlineExamAttempt::where('online_exam_id', $onlineExam->id)
            ->where('student_id', $studentId)
            ->whereIn('status', [OnlineExamAttempt::STATUS_SUBMITTED, OnlineExamAttempt::STATUS_AUTO_SUBMITTED])
            ->count();

        if ($submittedCount >= (int) $onlineExam->attempt_limit) {
            throw new \Exception('You have already reached the maximum number of attempts.');
        }

        $existing = OnlineExamAttempt::where('online_exam_id', $onlineExam->id)
            ->where('student_id', $studentId)
            ->where('status', OnlineExamAttempt::STATUS_STARTED)
            ->latest('id')
            ->first();

        if ($existing && ! $existing->isExpired()) {
            return $existing;
        }

        if ($existing && $existing->isExpired()) {
            return $this->finalizeAttempt($existing, $existing->answers ?? [], true);
        }

        return OnlineExamAttempt::create([
            'institute_id' => $onlineExam->institute_id,
            'branch_id' => $onlineExam->branch_id,
            'online_exam_id' => $onlineExam->id,
            'student_id' => $studentId,
            'user_id' => $userId,
            'answers' => [],
            'status' => OnlineExamAttempt::STATUS_STARTED,
            'started_at' => now(),
            'expires_at' => now()->addMinutes((int) $onlineExam->duration_minutes),
        ]);
    }

    public function saveAnswers(OnlineExamAttempt $attempt, array $answers): OnlineExamAttempt
    {
        if ($attempt->isSubmitted()) {
            throw new \Exception('This attempt has already been submitted.');
        }

        if ($attempt->isExpired()) {
            return $this->finalizeAttempt($attempt, $answers, true);
        }

        $attempt->update(['answers' => $answers]);

        return $attempt;
    }

    public function submit(OnlineExamAttempt $attempt, array $answers, bool $auto = false): OnlineExamAttempt
    {
        if ($attempt->isSubmitted()) {
            throw new \Exception('This attempt has already been submitted.');
        }

        return $this->finalizeAttempt($attempt, $answers, $auto || $attempt->isExpired());
    }

    public function questionsForAttempt(OnlineExam $onlineExam)
    {
        $questions = $onlineExam->questions()->where('is_active', true)->get();

        if ($onlineExam->random_questions) {
            $questions = $questions->shuffle()->values();
        }

        return $questions->map(function (OnlineExamQuestion $question) use ($onlineExam) {
            $options = $question->options ?? [];
            if ($onlineExam->random_options && is_array($options)) {
                $keys = array_keys($options);
                shuffle($keys);
                $shuffled = [];
                foreach ($keys as $key) {
                    $shuffled[$key] = $options[$key];
                }
                $options = $shuffled;
            }

            return [
                'id' => $question->id,
                'type' => $question->type,
                'question_text' => $question->question_text,
                'image' => $question->image,
                'options' => $options,
                'marks' => (float) $question->marks,
                'difficulty' => $question->difficulty,
            ];
        });
    }

    private function finalizeAttempt(OnlineExamAttempt $attempt, array $answers, bool $auto): OnlineExamAttempt
    {
        $onlineExam = $attempt->onlineExam()->with('questions')->first();
        $score = 0.0;
        $correct = 0;
        $incorrect = 0;
        $skipped = 0;

        foreach ($onlineExam->questions->where('is_active', true) as $question) {
            $given = $this->answerForQuestion($answers, (int) $question->id);

            if ($given === null || $given === [] || $given === '') {
                $skipped++;

                continue;
            }

            if (! $question->isAutoScorable()) {
                $skipped++;

                continue;
            }

            if ($this->isCorrect($question, $given)) {
                $score += (float) $question->marks;
                $correct++;
            } else {
                $incorrect++;
                if ($onlineExam->negative_marking) {
                    $score -= (float) $onlineExam->negative_marks_per_wrong;
                }
            }
        }

        $score = max(0, round($score, 2));
        $converted = $this->convertScore(
            $score,
            (float) ($onlineExam->online_full_marks ?: $onlineExam->total_marks),
            (float) ($onlineExam->academic_full_marks ?: $onlineExam->total_marks),
            (bool) $onlineExam->conversion_enabled
        );

        $submittedAt = now();
        $timeTaken = $attempt->started_at
            ? max(0, $attempt->started_at->diffInSeconds($submittedAt))
            : 0;

        $attempt->update([
            'answers' => $answers,
            'score' => $score,
            'converted_score' => $converted,
            'correct_count' => $correct,
            'incorrect_count' => $incorrect,
            'skipped_count' => $skipped,
            'status' => $auto ? OnlineExamAttempt::STATUS_AUTO_SUBMITTED : OnlineExamAttempt::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'time_taken' => $timeTaken,
            'is_passed' => $score >= (float) $onlineExam->pass_marks,
        ]);

        return $attempt->fresh();
    }

    private function answerForQuestion(array $answers, int $questionId): mixed
    {
        foreach ($answers as $answer) {
            if ((int) ($answer['question_id'] ?? 0) === $questionId) {
                return $answer['selected_options'] ?? $answer['answer'] ?? null;
            }
        }

        return $answers[$questionId] ?? null;
    }

    private function isCorrect(OnlineExamQuestion $question, mixed $given): bool
    {
        $correct = $question->correct_answer ?? [];
        $correct = is_array($correct) ? $correct : [$correct];
        $given = is_array($given) ? $given : [$given];

        $normalize = function (array $values): array {
            $values = array_map(fn ($value) => strtolower(trim((string) $value)), $values);
            sort($values);

            return array_values($values);
        };

        return $normalize($correct) === $normalize($given);
    }
}
