<?php

namespace App\Services\Api;

/**
 * Authenticated client for the backend onboarding queue. Institutes that sign
 * up through the public register form land here as pending onboardings until a
 * super-admin approves them (which provisions the tenant + owner login).
 *
 * Backend contract (Modules/Authentication/routes/api.php):
 *   GET  onboardings-list                 paginated pending onboardings (per_page)
 *   GET  onboardings-approve/{id}         approve + provision the institute
 */
class OnboardingService extends BaseApiService
{
    public function paginate(array $query = []): ApiResponse
    {
        return $this->cachedGet('onboardings-list', $query);
    }

    /** A write, despite being a GET on the backend — never cached, always invalidates. */
    public function approve(int|string $id): ApiResponse
    {
        return $this->action("onboardings-approve/{$id}");
    }
}
