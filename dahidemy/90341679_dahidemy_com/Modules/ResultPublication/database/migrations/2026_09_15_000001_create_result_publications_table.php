<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_publications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id')->nullable(); // null = whole class
            $table->unsignedBigInteger('subject_id')->nullable(); // null = combined/all-subjects result
            $table->unsignedBigInteger('exam_id'); // Modules\Academic\Models\Exam — the term/exam this result belongs to

            // If this publication is really just pointing at an existing
            // online exam's own results (already scored by the system),
            // rather than a separate upload.
            $table->unsignedBigInteger('online_exam_id')->nullable();

            $table->string('title');
            $table->text('description')->nullable();

            // physical  = an uploaded scanned/photographed mark sheet or PDF
            // online    = a pointer at an existing OnlineExam's results
            // summary   = just a written announcement, no file/link
            $table->string('type', 20)->default('summary');
            $table->string('file')->nullable();

            // 1=Pending, 2=Approved, 3=Rejected
            $table->smallInteger('status')->default(1);

            $table->unsignedBigInteger('submitted_by')->nullable(); // teacher's user id
            $table->unsignedBigInteger('reviewed_by')->nullable(); // admin's user id
            $table->text('remarks')->nullable(); // admin's note, shown either way
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable(); // set the moment it's approved
            $table->timestamp('notified_at')->nullable(); // guards against double-notifying

            $table->timestamps();

            $table->index(['institute_id', 'class_id', 'section_id', 'status'], 'result_pubs_class_section_status_idx');
            $table->index(['institute_id', 'status'], 'result_pubs_institute_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_publications');
    }
};
