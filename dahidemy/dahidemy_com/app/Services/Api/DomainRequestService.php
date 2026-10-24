<?php

namespace App\Services\Api;

/**
 * Authenticated client for the backend Domain Request endpoints (FEAT-016).
 *
 * Backend contract (Modules/Authentication/routes/api.php):
 *   GET  domain-requests                    paginated list (status=pending|approved|rejected, per_page)
 *   POST domain-requests/{id}/approve       approve (review_note optional) — writes institutes.domain
 *   POST domain-requests/{id}/reject        reject (review_note optional)
 */
class DomainRequestService extends BaseApiService
{
    public function paginate(array $query = []): ApiResponse
    {
        return $this->cachedGet('domain-requests', $query);
    }

    public function approve(int|string $id, ?string $reviewNote = null): ApiResponse
    {
        return $this->post("domain-requests/{$id}/approve", ['review_note' => $reviewNote]);
    }

    public function reject(int|string $id, ?string $reviewNote = null): ApiResponse
    {
        return $this->post("domain-requests/{$id}/reject", ['review_note' => $reviewNote]);
    }
}
