<?php

namespace App\Services\Api;

/**
 * Authenticated client for the backend Institute (tenant) endpoints. The bearer
 * token and multipart handling are provided by BaseApiService; controllers only
 * deal with plain arrays + UploadedFile values.
 *
 * Backend contract (Modules/Authentication/routes/api.php):
 *   GET    institutes                         paginated list (perPage, search, orderBy, order, page)
 *   POST   institutes                         create (multipart: institute + owner + logo/avatar)
 *   GET    institutes/{id}                    show
 *   PUT    institutes/{id}                    update (name, email, phone, domain, address, logo)
 *   DELETE institutes/{id}                    delete
 *   GET    institutes-status-update/{id}      toggle active/inactive
 */
class InstituteService extends BaseApiService
{
    public function paginate(array $query = []): ApiResponse
    {
        return $this->cachedGet('institutes', $query);
    }

    public function find(int|string $id): ApiResponse
    {
        return $this->cachedGet("institutes/{$id}");
    }

    public function create(array $data): ApiResponse
    {
        return $this->upload('institutes', $data);
    }

    public function update(int|string $id, array $data): ApiResponse
    {
        return $this->upload("institutes/{$id}", $data, 'put');
    }

    public function destroy(int|string $id): ApiResponse
    {
        return $this->delete("institutes/{$id}");
    }

    /** A write, despite being a GET on the backend — never cached, always invalidates. */
    public function toggleStatus(int|string $id): ApiResponse
    {
        return $this->action("institutes-status-update/{$id}");
    }

    /**
     * Institute types for the create/filter dropdowns. Returns the raw
     * ApiResponse so the caller can normalise the paginator envelope.
     */
    public function types(): ApiResponse
    {
        return $this->cachedGet('institute-types', ['perPage' => 200]);
    }
}
