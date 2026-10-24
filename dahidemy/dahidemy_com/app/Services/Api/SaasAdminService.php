<?php

namespace App\Services\Api;

/**
 * Authenticated admin data from the backend (bearer token attached
 * automatically by BaseApiService). This is the seam the CRUD modules
 * (institutes, packages, subscriptions, ...) will extend in the next phase.
 */
class SaasAdminService extends BaseApiService
{
    public function dashboard(?int $year = null): ApiResponse
    {
        return $this->cachedGet('saas-dashboard-data', array_filter(['year' => $year]));
    }
}
