<?php

namespace App\Services\Api;

/**
 * Generic authenticated CRUD client for any backend apiResource. The concrete
 * endpoint (e.g. "saas-banners") is passed per call, so a single service backs
 * every config-driven admin module. The bearer token is attached automatically
 * by BaseApiService.
 */
class AdminResourceService extends BaseApiService
{
    public function list(string $endpoint, array $query = []): ApiResponse
    {
        return $this->cachedGet($endpoint, $query);
    }

    public function find(string $endpoint, int|string $id): ApiResponse
    {
        return $this->cachedGet("{$endpoint}/{$id}");
    }

    public function store(string $endpoint, array $data): ApiResponse
    {
        return $this->post($endpoint, $data);
    }

    public function update(string $endpoint, int|string $id, array $data): ApiResponse
    {
        return $this->put("{$endpoint}/{$id}", $data);
    }

    /**
     * Multipart variants, for modules whose schema includes a file field. JSON
     * cannot carry an UploadedFile, so anything with an image must go this way.
     */
    public function storeWithFiles(string $endpoint, array $data): ApiResponse
    {
        return $this->upload($endpoint, $data);
    }

    public function updateWithFiles(string $endpoint, int|string $id, array $data): ApiResponse
    {
        return $this->upload("{$endpoint}/{$id}", $data, 'put');
    }

    public function destroy(string $endpoint, int|string $id): ApiResponse
    {
        return $this->delete("{$endpoint}/{$id}");
    }
}
