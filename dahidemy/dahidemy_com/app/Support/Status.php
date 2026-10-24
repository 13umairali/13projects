<?php

namespace App\Support;

/**
 * The one place that decides what "active" means across the panel.
 *
 * The backend is not self-consistent about institute status, so a boolean cast
 * is not safe:
 *
 *   - the migration comments the column as `1=Active, 2=InActive`;
 *   - InstituteController::statusUpdate toggles it as `status == 1 ? 0 : 1`,
 *     so deactivating actually writes 0;
 *   - the dashboard counts inactive rows as `status = 2`.
 *
 * A row can therefore be inactive as 0 OR as 2, and `(bool) 2` is true — which
 * is why an inactive institute used to render with an "Active" pill. Only 1 is
 * active here; every other value is inactive.
 */
final class Status
{
    public const ACTIVE = 1;

    public const INACTIVE = 0;

    /** Strictly 1 (int, string or bool-true) — everything else is inactive. */
    public static function isActive(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array($value, [1, '1', 'active', 'Active'], true);
    }

    public static function label(mixed $value): string
    {
        return self::isActive($value) ? 'Active' : 'Inactive';
    }
}
