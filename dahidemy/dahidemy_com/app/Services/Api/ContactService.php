<?php

namespace App\Services\Api;

/**
 * Authenticated client for the public contact-form inbox.
 *
 * Backend contract (Modules/Authentication/routes/api.php):
 *   GET  saas-contacts    paginated messages (per_page, search, sort_by, sort_order, page)
 *   POST contacts         public submission — sent by the landing contact form
 *
 * The backend exposes no show/update/delete route for a message, so the panel
 * reads this inbox and nothing more. The whole message body already arrives in
 * the list payload, which is why there is no find() here.
 */
class ContactService extends BaseApiService
{
    /** Columns the backend will sort by; anything else falls back to id. */
    public const SORTABLE = ['id', 'name', 'email', 'phone', 'subject', 'created_at'];

    public function paginate(array $query = []): ApiResponse
    {
        return $this->cachedGet('saas-contacts', $query);
    }
}
