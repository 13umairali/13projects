<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMigration extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'student_id',
        'type',
        'class_id',
        'section_id',
        'group_id',
        'prev_class_id',
        'prev_section_id',
        'prev_group_id',
        'prev_roll',
        'new_roll',
        'academic_year',
        'transferred_to_school_name',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'prev_class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'prev_section_id');
    }

    /**
     * The destination class/section (what the student was migrated TO) --
     * class()/section() above only cover where they migrated FROM. Added
     * alongside the Export feature and the "Migrated From"/"Migrated To"
     * labeling requested in the original Migration redesign ask.
     */
    public function migratedToClass(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function migratedToSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}
