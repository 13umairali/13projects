<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\DomainRequestService;
use App\Support\ApiPaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super-admin review of school-submitted custom domain requests (FEAT-016).
 * Every read/write is proxied to the backend through DomainRequestService.
 */
class DomainRequestController extends Controller
{
    public function __construct(
        protected DomainRequestService $domainRequests,
    ) {}

    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['pending', 'approved', 'rejected'], true)
            ? $request->query('status')
            : 'pending';

        $response = $this->domainRequests->paginate([
            'status' => $status,
            'per_page' => 15,
            'page' => (int) $request->query('page', 1) ?: 1,
        ]);

        return view('admin.domain-requests.index', [
            'requests' => ApiPaginator::fromApi($response->data([]), $request),
            'reachable' => $response->successful(),
            'status' => $status,
        ]);
    }

    public function approve(Request $request, string $id): RedirectResponse
    {
        $response = $this->domainRequests->approve($id, $request->input('review_note'));

        return back()->with(
            $response->successful() ? 'success' : 'error',
            $response->successful() ? ($response->message ?: 'Domain request approved.') : ($response->message ?: 'Approval failed.')
        );
    }

    public function reject(Request $request, string $id): RedirectResponse
    {
        $response = $this->domainRequests->reject($id, $request->input('review_note'));

        return back()->with(
            $response->successful() ? 'success' : 'error',
            $response->successful() ? ($response->message ?: 'Domain request rejected.') : ($response->message ?: 'Rejection failed.')
        );
    }
}
