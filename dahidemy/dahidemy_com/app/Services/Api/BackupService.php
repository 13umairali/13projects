<?php

namespace App\Services\Api;

/**
 * Authenticated client for the backend database backup endpoints (FEAT-019).
 *
 * Backend contract (Modules/SystemConfiguration/routes/api.php):
 *   POST administration-backup-run    dumps + stores to the configured destination (local or S3/R2)
 *   GET  administration-backups       lists backups currently on the remote destination
 */
class BackupService extends BaseApiService
{
    public function run(): ApiResponse
    {
        return $this->post('administration-backup-run');
    }

    public function list(): ApiResponse
    {
        return $this->cachedGet('administration-backups');
    }
}
