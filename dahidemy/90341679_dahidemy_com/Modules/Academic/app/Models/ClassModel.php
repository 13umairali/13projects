<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassModel extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = ['institute_id', 'branch_id', 'class_name', 'status', 'max_groups'];

    /**
     * How many distinct Student Groups this class is currently associated
     * with, based on subjects that have been configured with a group_id
     * for this class. Used to enforce max_groups when adding a new
     * class+group pairing (e.g. via Subject creation).
     */
    public function groupsInUseCount(): int
    {
        return Subject::where('class_id', $this->id)
            ->whereNotNull('group_id')
            ->distinct('group_id')
            ->count('group_id');
    }

    /**
     * Whether a new subject/config for this class using $groupId would
     * push the class over its configured max_groups cap. A null max_groups
     * means "no cap". Returns false (allowed) for groups already in use,
     * since re-using an existing group never increases the count.
     */
    public function wouldExceedGroupLimit(?int $groupId): bool
    {
        if (! $groupId || ! $this->max_groups) {
            return false;
        }

        $inUse = Subject::where('class_id', $this->id)
            ->whereNotNull('group_id')
            ->distinct()
            ->pluck('group_id');

        if ($inUse->contains($groupId)) {
            return false;
        }

        return $inUse->count() >= $this->max_groups;
    }

    public function sections(): HasMany
    {
        // Was previously a belongsTo(StudentSession::class, ...) — wrong model
        // entirely (StudentSession, not Section) and the wrong relation type for
        // a one-class-to-many-sections relationship. Section::class() already
        // points back here correctly; this is its proper inverse.
        return $this->hasMany(Section::class, 'class_id', 'id');
    }

    public function studentSessions(): HasMany
    {
        return $this->hasMany(StudentSession::class, 'class_id', 'id');
    }
}
