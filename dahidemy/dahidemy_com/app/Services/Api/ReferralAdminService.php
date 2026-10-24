<?php

namespace App\Services\Api;

/**
 * Authenticated client for referral review and reward settings.
 *
 * Backend contract (routes/api.php):
 *   GET  referrals                     paginated list, optional ?status=
 *   GET  referrals/{id}/approve        approve + apply the reward
 *   GET  referrals/{id}/reject         reject
 *   GET|POST|PUT|DELETE referral-settings[/{id}]   CRUD for reward rules per role
 */
class ReferralAdminService extends BaseApiService
{
    public function list(array $query = []): ApiResponse
    {
        return $this->cachedGet('referrals', $query);
    }

    /** Approve a referral and apply its reward. A write, despite being a GET. */
    public function approve(int|string $id): ApiResponse
    {
        return $this->action("referrals/{$id}/approve");
    }

    public function reject(int|string $id): ApiResponse
    {
        return $this->action("referrals/{$id}/reject");
    }

    public function settings(): ApiResponse
    {
        return $this->cachedGet('referral-settings');
    }

    /** Every distinct role name in use platform-wide - powers the Referrer Role dropdown. */
    public function roleOptions(): ApiResponse
    {
        return $this->cachedGet('role-options');
    }

    public function createSetting(array $data): ApiResponse
    {
        return $this->post('referral-settings', $data);
    }

    public function updateSetting(int|string $id, array $data): ApiResponse
    {
        return $this->put("referral-settings/{$id}", $data);
    }

    public function deleteSetting(int|string $id): ApiResponse
    {
        return $this->delete("referral-settings/{$id}");
    }
}