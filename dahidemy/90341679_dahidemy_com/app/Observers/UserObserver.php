<?php

namespace App\Observers;

use App\Services\ReferralService;
use Modules\Authentication\Models\User;

class UserObserver
{
    /**
     * Every user - student, parent, teacher, accountant, staff, institute
     * admin, any role - gets their own referral code automatically, no
     * matter which of the many "create X" controllers created them.
     */
    public function created(User $user): void
    {
        app(ReferralService::class)->generateCodeFor('user', $user->id);
    }
}