<?php

namespace App\Http\Middleware;

use App\Helpers\InstituteHelper;
use Closure;
use Illuminate\Http\Request;

class CheckInstituteActive
{
    /**
     * Runs on every request. Only actually does anything when the current
     * domain exactly matches a real institute that is currently deactivated
     * (status = 0) or has been deleted — in that specific case, it shows a
     * dedicated "temporarily unavailable" page and stops the request there.
     *
     * Everything else (the main SaaS domain, an unmapped domain, a domain
     * belonging to an active institute) passes straight through unaffected.
     */
    public function handle(Request $request, Closure $next)
    {
        $institute = InstituteHelper::findInstituteByExactDomainIncludingInactive($request);

        if (! $institute) {
            return $next($request);
        }

        $isDeleted = (bool) $institute->deleted_at;
        $isDeactivated = ! $institute->status;

        if ($isDeleted || $isDeactivated) {
            return response()->view('errors.institute-unavailable', [
                'institute' => $institute,
            ], 503);
        }

        return $next($request);
    }
}