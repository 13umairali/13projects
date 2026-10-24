<?php

namespace App\Services\Api;

/**
 * Authenticated client for subscription upgrades and upgrade requests.
 *
 * Backend contract (Modules/Authentication/routes/api.php):
 *   POST subscription/upgrade                     manual upgrade (institute_id, plan_id, extra_days, payment_method, amount_paid, notes)
 *   GET  subscription/request-upgrade-list        paginated pending upgrade requests (per_page, search)
 *   GET  subscription/approve-request/{id}        approve a pending upgrade request
 *   POST subscription/payment-upgrade             pay + upgrade (institute_id, plan_id, upgrade_request_id?, ...)
 */
class SubscriptionService extends BaseApiService
{
    /** Manual (super-admin) upgrade of an institute's subscription. */
    public function upgrade(array $data): ApiResponse
    {
        return $this->post('subscription/upgrade', $data);
    }

    /** Pending upgrade requests raised by institutes. */
    public function requestList(array $query = []): ApiResponse
    {
        return $this->cachedGet('subscription/request-upgrade-list', $query);
    }

    /** Approve a pending upgrade request by id. A write, despite being a GET. */
    public function approveRequest(int|string $id): ApiResponse
    {
        return $this->action("subscription/approve-request/{$id}");
    }

    /** Process a payment and upgrade (optionally tied to a pending request). */
    public function paymentUpgrade(array $data): ApiResponse
    {
        return $this->post('subscription/payment-upgrade', $data);
    }
}
