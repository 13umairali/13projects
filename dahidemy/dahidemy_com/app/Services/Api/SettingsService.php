<?php

namespace App\Services\Api;

/**
 * Authenticated client for global SaaS settings (site details, theme, mail).
 *
 * Backend contract:
 *   GET  get-saas-settings              current settings map (public)
 *   POST saas-settings-update           update arbitrary settings key/values
 *   GET  saas-settings-mail-config      current mail configuration
 *   POST saas-settings-mail-update      update mail configuration
 *
 * Logo uploads are deliberately absent: logos are owned by BrandImageService
 * (institute image SaaS settings), which serves every logo slot in the system.
 */
class SettingsService extends BaseApiService
{
    public function all(): ApiResponse
    {
        return $this->cachedGet('get-saas-settings');
    }

    public function update(array $data): ApiResponse
    {
        return $this->post('saas-settings-update', $data);
    }

    public function mailConfig(): ApiResponse
    {
        return $this->cachedGet('saas-settings-mail-config');
    }

    public function updateMailConfig(array $data): ApiResponse
    {
        return $this->post('saas-settings-mail-update', $data);
    }
}
