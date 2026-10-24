<?php

namespace App\Services\Api;

/**
 * The platform-wide withdrawal limits/methods (scope_type = 'platform' on
 * the backend). Institutes may optionally override any of these fields
 * for their own users - see WithdrawalSetting::effectiveFor() on the
 * backend.
 */
class WithdrawalSettingsAdminService extends BaseApiService
{
    public function show(): ApiResponse
    {
        return $this->cachedGet('withdrawal-settings');
    }

    public function update(array $data): ApiResponse
    {
        return $this->put('withdrawal-settings', $data);
    }
}