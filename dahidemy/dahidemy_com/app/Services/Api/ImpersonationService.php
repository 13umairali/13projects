<?php

namespace App\Services\Api;

/**
 * Authenticated client for the backend impersonation endpoint (FEAT-015).
 *
 * Backend contract (Modules/Authentication/routes/api.php):
 *   POST institutes/{id}/impersonate   mints a short-lived, single-use login
 *                                       link for that institute's admin user
 *
 * The call is made with the SaaS admin's own bearer token, so the backend can
 * (and does) verify that the caller is a platform admin before issuing a link.
 * We also hand it a `return_url` — where "Back to SaaS Admin" should land once
 * the operator is done — which the backend validates and stores against the
 * one-time token.
 */
class ImpersonationService extends BaseApiService
{
    public function start(int|string $instituteId, ?string $returnUrl = null): ApiResponse
    {
        return $this->post("institutes/{$instituteId}/impersonate", array_filter([
            'return_url' => $returnUrl,
        ]));
    }
}
