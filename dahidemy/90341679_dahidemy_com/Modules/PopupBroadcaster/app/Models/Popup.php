<?php

namespace Modules\PopupBroadcaster\Models;

use Illuminate\Database\Eloquent\Model;

class Popup extends Model
{
    protected $table = 'popups';

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 2;

    public const AUDIENCE_OPTIONS = [
        'everyone' => 'Everyone (including visitors not logged in)',
        'logged_in_only' => 'Any logged-in user',
        'students_only' => 'Students only',
        'parents_only' => 'Parents only',
    ];

    public const FREQUENCY_OPTIONS = [
        'every_visit' => 'Every page load while active',
        'once_per_session' => 'Once per browser session',
        'once_ever' => 'Once per browser, ever',
    ];

    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'message',
        'image',
        'button_text',
        'button_link',
        'audience',
        'display_frequency',
        'start_at',
        'end_at',
        'priority',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        return (int) $this->status === self::STATUS_ACTIVE;
    }

    public function isWithinSchedule(): bool
    {
        $now = now();

        if ($this->start_at && $now->lt($this->start_at)) {
            return false;
        }

        if ($this->end_at && $now->gt($this->end_at)) {
            return false;
        }

        return true;
    }

    /**
     * Every popup this institute currently has scheduled to run, regardless
     * of who's viewing — audience filtering by logged-in user type happens
     * separately (see forVisitor()) so this stays a simple, cacheable query.
     */
    public function scopeCurrentlyScheduled($query, int $instituteId)
    {
        return $query->where('institute_id', $instituteId)
            ->where('status', self::STATUS_ACTIVE)
            ->where(function ($q) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', now());
            })
            ->orderByDesc('priority');
    }

    /**
     * Filters an already-scheduled collection down to whichever ones this
     * specific visitor (guest or a given user_type) is allowed to see.
     */
    public static function visibleToUserType(iterable $popups, ?string $userType): array
    {
        $result = [];
        foreach ($popups as $popup) {
            $audience = $popup->audience;
            $visible = match ($audience) {
                'everyone' => true,
                'logged_in_only' => $userType !== null,
                'students_only' => $userType === 'Student',
                'parents_only' => $userType === 'Parent',
                default => false,
            };

            if ($visible) {
                $result[] = $popup;
            }
        }

        return $result;
    }
}
