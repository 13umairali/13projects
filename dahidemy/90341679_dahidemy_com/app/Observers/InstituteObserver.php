<?php

namespace App\Observers;

use App\Services\ReferralService;
use Modules\Authentication\Models\Institute;

class InstituteObserver
{
    public function created(Institute $institute): void
    {
        app(ReferralService::class)->generateCodeFor('institute', $institute->id);
    }
}