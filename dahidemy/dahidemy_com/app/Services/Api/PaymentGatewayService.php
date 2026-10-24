<?php

namespace App\Services\Api;

/**
 * Authenticated client for SaaS payment-gateway configuration (SAASSetting rows
 * of type payment_config). Each row holds a gateway's credentials, mode and
 * status.
 *
 * Backend contract (Modules/Gateways/routes/api.php):
 *   GET  saas-digital-payment                 list gateway configs
 *   PUT  saas-digital-payment/{settingId}     update credentials (payment_info, mode, status)
 *   GET  saas-digital-payment-status/{settingId}  toggle active/inactive
 */
class PaymentGatewayService extends BaseApiService
{
    public function list(): ApiResponse
    {
        return $this->cachedGet('saas-digital-payment');
    }

    public function update(int|string $id, array $data): ApiResponse
    {
        return $this->put("saas-digital-payment/{$id}", $data);
    }

    /** A write, despite being a GET on the backend — never cached, always invalidates. */
    public function toggleStatus(int|string $id): ApiResponse
    {
        return $this->action("saas-digital-payment-status/{$id}");
    }
}
