<?php

namespace App\Services;

use App\Models\InstituteReferralSetting;
use App\Models\InternalReferral;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\User;

/**
 * Handles referrals WITHIN an institute - a student, parent, teacher, or
 * staff member referring someone new to join that same institute. This
 * is entirely separate from ReferralService, which handles referring a
 * new INSTITUTE to the platform. The two use the same referral_code
 * column on users, but resolve and validate completely differently:
 * this one is scoped to a single institute and enforces who is allowed
 * to refer whom.
 */
class InternalReferralService
{
    /**
     * Which referred_user_type values each referrer_user_type is
     * permitted to bring in. Not user-configurable by design - these are
     * safety rules (a student shouldn't be able to refer their way into
     * hiring a teacher), not reward preferences.
     */
    protected const ALLOWED_TARGETS = [
        'student' => ['student'],
        'parent' => ['student'],
        'teacher' => ['student', 'teacher', 'staff', 'accountant'],
    ];

    public function isEnabled(int $instituteId): bool
    {
        return (bool) Institute::where('id', $instituteId)->value('internal_referral_enabled');
    }

    /**
     * Finds who a code belongs to, but ONLY within this specific
     * institute - a code that belongs to a person at a different
     * institute must never work here, even though the same
     * users.referral_code column is used platform-wide for the SaaS-level
     * referral system too.
     */
    public function resolveCodeWithinInstitute(string $code, int $instituteId): ?User
    {
        return User::where('referral_code', strtoupper(trim($code)))
            ->where('institute_id', $instituteId)
            ->first();
    }

    /**
     * @return string|null null if the referral is allowed; otherwise a
     *         human-readable reason it was blocked.
     */
    public function validationError(string $referrerUserType, string $referredUserType): ?string
    {
        $referrerKey = strtolower($referrerUserType);
        $referredKey = strtolower($referredUserType);

        $allowed = self::ALLOWED_TARGETS[$referrerKey] ?? null;

        // A role with no explicit rule (e.g. Accountant, or any other
        // custom staff role) defaults to the same restriction as
        // students/parents: they may only refer students. This is the
        // conservative default the requirements implied for "other
        // roles" beyond student/parent and teacher.
        $allowed ??= ['student'];

        if (! in_array($referredKey, $allowed, true)) {
            return ucfirst($referrerKey)." accounts can only refer new students - not {$referredUserType}.";
        }

        return null;
    }

    /**
     * Called at the moment a new person is actually created (student
     * admission, staff creation, etc.) - unlike the platform-level
     * referral flow, the referred person already exists by the time this
     * runs, so there's no "pending, not yet linked" state to manage.
     *
     * Silently does nothing (returns null) for: referrals disabled at
     * this institute, an empty/invalid code, a code belonging to someone
     * outside this institute, or a code used on oneself - none of these
     * should block the person from being created, they just mean no
     * referral gets recorded.
     *
     * @throws \InvalidArgumentException only for a genuine rule
     *         violation (wrong referrer/referred role combination) -
     *         this one SHOULD surface to the person filling out the
     *         form, since it means the code they entered isn't valid for
     *         what they're doing.
     */
    public function recordReferral(?string $code, int $instituteId, int $referredUserId, string $referredUserType): ?InternalReferral
    {
        if (! $code || ! $this->isEnabled($instituteId)) {
            return null;
        }

        $referrer = $this->resolveCodeWithinInstitute($code, $instituteId);
        if (! $referrer || $referrer->id === $referredUserId) {
            return null;
        }

        $error = $this->validationError($referrer->user_type, $referredUserType);
        if ($error) {
            throw new \InvalidArgumentException($error);
        }

        $setting = InstituteReferralSetting::forInstituteAndRole(
            $instituteId,
            strtolower($referrer->user_type),
            'refer_member'
        );

        return InternalReferral::create([
            'institute_id' => $instituteId,
            'referrer_user_id' => $referrer->id,
            'referrer_user_type' => $referrer->user_type,
            'referral_code_used' => $code,
            'referred_user_id' => $referredUserId,
            'referred_user_type' => $referredUserType,
            'status' => 'enrolled',
            'reward_type' => $setting?->reward_type,
            'reward_value' => $setting?->reward_value,
        ]);
    }
}