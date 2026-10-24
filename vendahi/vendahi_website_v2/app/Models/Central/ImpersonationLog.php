<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

/**
 * Audit log for super-admin "login as tenant user" (impersonation) sessions.
 * Lives in the central DB so it is readable from both central and tenant context.
 */
class ImpersonationLog extends Model
{
    protected $connection = 'central';

    protected $table = 'tenant_impersonation_logs';

    /** Seconds a freshly issued login token stays valid (single redirect hop). */
    public const TOKEN_TTL = 120;

    /** Active sessions with no activity for this long are shown/marked as expired. */
    public const STALE_HOURS = 12;

    /** Hard cap on stored per-session actions (oldest kept, newest dropped). */
    public const MAX_ACTIONS = 300;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE  = 'active';
    public const STATUS_ENDED   = 'ended';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_FAILED  = 'failed';

    protected $fillable = [
        'token_hash', 'central_user_id', 'admin_name', 'admin_email',
        'tenant_id', 'tenant_name', 'user_id', 'user_name', 'user_email',
        'ip_address', 'user_agent', 'status', 'ended_by',
        'started_at', 'last_activity_at', 'ended_at', 'actions',
    ];

    protected $casts = [
        'actions'          => 'array',
        'started_at'       => 'datetime',
        'last_activity_at' => 'datetime',
        'ended_at'         => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(CentralUser::class, 'central_user_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Mark the session as finished.
     */
    public function end(string $by): void
    {
        $this->forceFill([
            'status'   => self::STATUS_ENDED,
            'ended_by' => $by,
            'ended_at' => now(),
        ])->save();
    }

    /**
     * Append one state-changing request to the session's action trail.
     */
    public function recordAction(string $method, string $path, int $statusCode): void
    {
        $actions = $this->actions ?? [];

        if (count($actions) < self::MAX_ACTIONS) {
            $actions[] = [
                't'      => now()->toDateTimeString(),
                'method' => $method,
                'path'   => $path,
                'status' => $statusCode,
            ];
        }

        $this->forceFill([
            'actions'          => $actions,
            'last_activity_at' => now(),
        ])->save();
    }

    /**
     * Lazy sweep: expire stale pending tokens and abandoned active sessions.
     * Called from the audit UI so the list never shows ghost "active" rows.
     */
    public static function sweepStale(): void
    {
        static::query()
            ->where('status', self::STATUS_PENDING)
            ->where('created_at', '<', now()->subSeconds(self::TOKEN_TTL))
            ->update(['status' => self::STATUS_EXPIRED, 'token_hash' => null]);

        static::query()
            ->where('status', self::STATUS_ACTIVE)
            ->where(function ($q) {
                $q->where('last_activity_at', '<', now()->subHours(self::STALE_HOURS))
                  ->orWhere(function ($q2) {
                      $q2->whereNull('last_activity_at')
                         ->where('started_at', '<', now()->subHours(self::STALE_HOURS));
                  });
            })
            ->update([
                'status'   => self::STATUS_EXPIRED,
                'ended_by' => 'expired',
                'ended_at' => now(),
            ]);
    }
}
