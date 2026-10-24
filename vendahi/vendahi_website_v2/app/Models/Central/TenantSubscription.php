<?php

namespace App\Models\Central;

use App\Tenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Central (SaaS) tenant subscription to a plan — not tenant-scoped.
 */
class TenantSubscription extends Model
{
    protected $connection = 'central';

    protected $table = 'tenant_subscriptions';

    public const STATUS_PENDING   = 'pending';
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED   = 'expired';
    public const STATUS_TRIAL     = 'trial';
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * Allowed status transitions. Keys are the source status; values are the
     * statuses that source can move to. Used by transitionTo() to enforce a
     * single chokepoint for admin-driven status changes.
     */
    public const ALLOWED_TRANSITIONS = [
        self::STATUS_PENDING   => [self::STATUS_ACTIVE, self::STATUS_TRIAL, self::STATUS_CANCELLED, self::STATUS_FAILED],
        self::STATUS_TRIAL     => [self::STATUS_ACTIVE, self::STATUS_CANCELLED, self::STATUS_EXPIRED],
        self::STATUS_ACTIVE    => [self::STATUS_CANCELLED, self::STATUS_SUSPENDED, self::STATUS_EXPIRED],
        self::STATUS_CANCELLED => [self::STATUS_ACTIVE],
        self::STATUS_SUSPENDED => [self::STATUS_ACTIVE, self::STATUS_CANCELLED],
        self::STATUS_EXPIRED   => [self::STATUS_ACTIVE],
        self::STATUS_FAILED    => [self::STATUS_PENDING, self::STATUS_ACTIVE],
    ];

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'billing_cycle',
        'amount',
        'currency',
        'status',
        'starts_at',
        'trial_ends_at',
        'ends_at',
        'cancelled_at',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'starts_at'     => 'datetime',
        'trial_ends_at' => 'datetime',
        'ends_at'       => 'datetime',
        'cancelled_at'  => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(TenantBillingPayment::class, 'tenant_subscription_id');
    }

    /**
     * Subscriptions that currently entitle the tenant to their plan: active or
     * trial rows, plus cancelled rows whose paid period has not run out yet.
     *
     * A cancelled row only counts while ends_at is still in the future — the
     * same condition isCancelled() applies. Without the date check a
     * subscription cancelled while it was still pending (never paid for, so
     * ends_at is null) would resolve as the tenant's current plan and hand
     * them its features for free.
     */
    public function scopeGrantingAccess($query)
    {
        return $query->where(function ($q) {
            $q->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_TRIAL])
              ->orWhere(function ($q2) {
                  $q2->where('status', self::STATUS_CANCELLED)
                     ->whereNotNull('ends_at')
                     ->where('ends_at', '>', now());
              });
        });
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isActive(): bool
    {
        if ($this->status === self::STATUS_ACTIVE) {
            return !$this->ends_at || $this->ends_at->isFuture();
        }

        return $this->isOnTrial();
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED && $this->ends_at && $this->ends_at->isFuture();
    }

    public function canResume(): bool
    {
        return $this->isCancelled();
    }

    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast() && $this->status !== self::STATUS_ACTIVE;
    }

    public function daysRemaining(): int
    {
        if (!$this->ends_at) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->ends_at, false));
    }

    /**
     * Activate after successful payment (first-time activation).
     */
    public function activate(): void
    {
        $startsAt = now();
        // Free plans don't expire; paid plans run one billing cycle.
        $endsAt = $this->plan?->isFree()
            ? null
            : ($this->billing_cycle === 'yearly'
                ? $startsAt->copy()->addYear()
                : $startsAt->copy()->addMonth());

        $this->update([
            'status'        => self::STATUS_ACTIVE,
            'starts_at'     => $startsAt,
            'trial_ends_at' => null,
            'ends_at'       => $endsAt,
            'cancelled_at'  => null,
        ]);

        // This subscription replaces whatever the tenant was on before (free
        // trial, previous plan) — expire those rows so only one is live.
        static::where('tenant_id', $this->tenant_id)
            ->whereKeyNot($this->getKey())
            ->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_TRIAL])
            ->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Extend an already-active subscription by one billing cycle (renewal).
     * If ends_at is still in the future, extends from that date.
     * If ends_at has passed, extends from now.
     */
    public function renew(): void
    {
        $base = ($this->ends_at && $this->ends_at->isFuture())
            ? $this->ends_at->copy()
            : now();

        $newEndsAt = $this->billing_cycle === 'yearly'
            ? $base->addYear()
            : $base->addMonth();

        $this->update([
            'status'        => self::STATUS_ACTIVE,
            'trial_ends_at' => null,
            'ends_at'       => $newEndsAt,
            'cancelled_at'  => null,
        ]);
    }

    /**
     * Whether this subscription never went live: still pending (or failed)
     * with no billing period and no trial behind it. Typically a plan change
     * whose payment was never completed.
     */
    public function wasNeverActivated(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_FAILED], true)
            && ! $this->ends_at
            && ! $this->trial_ends_at;
    }

    /**
     * The status a cancellation should actually land on.
     *
     * "Cancelled" means "paid for, keep access until ends_at". A subscription
     * that was never activated has no such period to honour, so it goes to
     * "failed" instead — leaving it as a cancelled row with a null ends_at
     * would make it the newest row matching grantingAccess() and silently
     * promote the tenant onto the plan they never paid for.
     */
    public function cancellationStatus(): string
    {
        return $this->wasNeverActivated() ? self::STATUS_FAILED : self::STATUS_CANCELLED;
    }

    /**
     * Cancel subscription — remains active until ends_at.
     */
    public function cancel(): void
    {
        $this->update([
            'status'       => $this->cancellationStatus(),
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Resume a cancelled subscription (only if billing period hasn't expired).
     */
    public function resume(): void
    {
        if (! $this->canResume()) {
            return;
        }

        $this->update([
            'status'       => self::STATUS_ACTIVE,
            'cancelled_at' => null,
        ]);
    }

    /**
     * Whether moving from the current status to $newStatus is allowed.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        if ($this->status === $newStatus) {
            return true;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$this->status] ?? [];

        return in_array($newStatus, $allowed, true);
    }

    /**
     * Apply a status transition with extra attributes. Returns false if the
     * transition is not allowed; the caller is responsible for surfacing the
     * error to the user.
     *
     * Resume of a cancelled subscription (cancelled → active) requires that
     * ends_at is still in the future, mirroring canResume().
     */
    public function transitionTo(string $newStatus, array $extra = []): bool
    {
        if (! $this->canTransitionTo($newStatus)) {
            return false;
        }

        if ($this->status === self::STATUS_CANCELLED && $newStatus === self::STATUS_ACTIVE) {
            if (! $this->ends_at || $this->ends_at->isPast()) {
                return false;
            }
            $extra['cancelled_at'] = null;
        }

        // Cancelling goes through cancellationStatus() here too, so the admin
        // edit form cannot produce a cancelled row that cancel() would have
        // downgraded to failed.
        if ($newStatus === self::STATUS_CANCELLED) {
            // Decide against the values this transition is about to write, so
            // an admin who sets an end date and cancels in the same edit still
            // gets a real cancellation.
            $newStatus = (clone $this)->fill($extra)->cancellationStatus();
            $extra['cancelled_at'] = $extra['cancelled_at'] ?? now();
        }

        $this->update(array_merge($extra, ['status' => $newStatus]));

        return true;
    }
}
