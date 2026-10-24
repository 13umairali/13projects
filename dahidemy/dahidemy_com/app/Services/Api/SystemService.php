<?php

namespace App\Services\Api;

/**
 * Backend housekeeping endpoints.
 *
 * Backend contract (Modules/Authentication/routes/api.php):
 *   GET system/clear-cache    clears the backend's config/route/view/data caches
 *
 * The backend answers this one with {"status": "success"} rather than the usual
 * envelope; ApiResponse treats that truthy status as success, so callers see a
 * normal successful response.
 */
class SystemService extends BaseApiService
{
    /** Not cachable by definition — and it invalidates our own cache too. */
    public function clearCache(): ApiResponse
    {
        return $this->action('system/clear-cache');
    }
}
