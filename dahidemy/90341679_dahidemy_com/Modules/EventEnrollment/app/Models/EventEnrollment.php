<?php

namespace Modules\EventEnrollment\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Academic\Models\Event;

class EventEnrollment extends Model
{
    protected $table = 'event_enrollments';

    public const STATUS_CONFIRMED = 1; // free event, nothing to pay

    public const STATUS_PENDING_PAYMENT = 2;

    public const STATUS_PAID = 3;

    public const STATUS_FAILED = 4;

    protected $fillable = [
        'event_id',
        'institute_id',
        'branch_id',
        'name',
        'email',
        'phone',
        'ticket_code',
        'amount_paid',
        'currency_code',
        'payment_status',
        'payment_request_id',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function isConfirmed(): bool
    {
        return in_array((int) $this->payment_status, [self::STATUS_CONFIRMED, self::STATUS_PAID], true);
    }

    public function statusLabel(): string
    {
        return match ((int) $this->payment_status) {
            self::STATUS_CONFIRMED, self::STATUS_PAID => 'Confirmed',
            self::STATUS_PENDING_PAYMENT => 'Payment Pending',
            self::STATUS_FAILED => 'Payment Failed',
            default => 'Unknown',
        };
    }

    public static function generateTicketCode(): string
    {
        do {
            $code = 'EVT-'.strtoupper(bin2hex(random_bytes(4)));
        } while (self::where('ticket_code', $code)->exists());

        return $code;
    }
}
