<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Modules\Academic\Models\Staff;
use Modules\Academic\Models\Student;

class TransportMember extends Model
{
    use BelongsToTenant;

    protected $table = 'transport_members';

    public const TABLE_NAME = 'transport_members';

    public const TYPE_STUDENT = 'student';

    public const TYPE_STAFF = 'staff';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'member_type',
        'student_id',
        'staff_id',
        'assigned_route_id',
        'assigned_stop_id',
        'seat_id',
        'transport_id',
        'fare_amount',
        'status',
    ];

    public function route()
    {
        return $this->belongsTo(BusRoute::class, 'assigned_route_id');
    }

    public function stop()
    {
        return $this->belongsTo(BusStop::class, 'assigned_stop_id');
    }

    public function seat()
    {
        return $this->belongsTo(BusSeat::class, 'seat_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * The passenger's display name regardless of whether they're a
     * student or a staff/conductor member -- so views don't need to
     * branch on member_type just to show a name.
     */
    public function getPassengerNameAttribute(): ?string
    {
        if ($this->member_type === self::TYPE_STAFF) {
            return $this->staff?->name;
        }

        return trim(($this->student?->first_name ?? '').' '.($this->student?->last_name ?? '')) ?: null;
    }

    public function collections()
    {
        return $this->hasMany(TransportCollection::class, 'transport_member_id');
    }
}
