<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\ContactService;
use App\Support\ApiPaginator;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The contact-form inbox: messages submitted from the public marketing site.
 *
 * Read-only, because the backend offers no write route for a message — no
 * mark-as-seen, no reply, no delete. Sorting, searching and paging are all done
 * by the backend (saas-contacts accepts search, sort_by, sort_order, per_page),
 * so a large inbox never has to be pulled into the panel to be filtered.
 */
class ContactController extends Controller
{
    public function __construct(
        protected ContactService $contacts,
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'per_page' => $this->perPage($request),
            'sort_by' => in_array($request->query('sort_by'), ContactService::SORTABLE, true)
                ? $request->query('sort_by')
                : 'created_at',
            'sort_order' => $request->query('sort_order') === 'asc' ? 'asc' : 'desc',
        ];

        $response = $this->contacts->paginate(array_filter([
            'search' => $filters['search'],
            'per_page' => $filters['per_page'],
            'sort_by' => $filters['sort_by'],
            'sort_order' => $filters['sort_order'],
            'page' => (int) $request->query('page', 1) ?: 1,
        ], fn ($value) => $value !== '' && $value !== null));

        return view('admin.contacts.index', [
            'contacts' => ApiPaginator::fromApi($response->data([]), $request),
            'reachable' => $response->successful(),
            'filters' => $filters,
        ]);
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 10);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
    }
}
