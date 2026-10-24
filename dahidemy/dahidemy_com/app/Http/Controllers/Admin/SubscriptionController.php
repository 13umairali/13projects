<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\AdminResourceService;
use App\Services\Api\InstituteService;
use App\Services\Api\SubscriptionService;
use App\Support\ApiPaginator;
use App\Support\ResourceRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super-admin subscription management: review + approve institute upgrade
 * requests, and perform manual (staff-initiated) upgrades. Everything is
 * proxied to the backend subscription endpoints.
 */
class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptions,
        protected InstituteService $institutes,
        protected AdminResourceService $resources,
    ) {}

    /* -----------------------------------------------------------------
     |  Upgrade requests raised by institutes
     | ----------------------------------------------------------------- */

    public function requests(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true)
            ? (int) $request->query('per_page')
            : 15;

        $response = $this->subscriptions->requestList(array_filter([
            'per_page' => $perPage,
            'search' => $search,
            'page' => (int) $request->query('page', 1) ?: 1,
        ], fn ($v) => $v !== '' && $v !== null));

        return view('admin.subscriptions.requests', [
            'requests' => ApiPaginator::fromApi($response->data([]), $request),
            'reachable' => $response->successful(),
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }

    public function approveRequest(string $id): RedirectResponse
    {
        $response = $this->subscriptions->approveRequest($id);

        return redirect()
            ->route('admin.subscriptions.requests')
            ->with(
                $response->successful() ? 'success' : 'error',
                $response->successful()
                    ? ($response->message ?: 'Upgrade request approved.')
                    : ($response->message ?: 'Approval failed. Please try again.')
            );
    }

    /* -----------------------------------------------------------------
     |  Manual (staff-initiated) upgrade
     | ----------------------------------------------------------------- */

    public function create(): View
    {
        return view('admin.subscriptions.upgrade', [
            'instituteOptions' => $this->instituteOptions(),
            'planOptions' => $this->planOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'institute_id' => ['required'],
            'plan_id' => ['required'],
            'extra_days' => ['nullable', 'integer', 'min:0'],
            'payment_method' => ['required', 'string', 'max:100'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $response = $this->subscriptions->upgrade($data);

        if ($response->successful()) {
            return redirect()
                ->route('admin.subscriptions.upgrade')
                ->with('success', $response->message ?: 'Subscription upgraded successfully.');
        }

        return back()
            ->withInput()
            ->withErrors($response->validationErrors())
            ->with('error', $response->message ?: 'Upgrade failed. Please review the form and try again.');
    }

    /* -----------------------------------------------------------------
     |  Option helpers
     | ----------------------------------------------------------------- */

    /** [id => "Name (domain)"] for the institute <select>. */
    private function instituteOptions(): array
    {
        $rows = ResourceRegistry::normalizeList(
            $this->institutes->paginate(['perPage' => 200])->data([])
        );

        return collect($rows)
            ->mapWithKeys(fn ($row) => [
                (string) ($row['id'] ?? '') => trim(($row['name'] ?? 'Institute').' — '.($row['domain'] ?? '#'.($row['id'] ?? ''))),
            ])
            ->filter(fn ($label, $id) => $id !== '')
            ->all();
    }

    /** [id => "Name — $price"] for the plan <select>. */
    private function planOptions(): array
    {
        $rows = ResourceRegistry::normalizeList(
            $this->resources->list('plans', ['per_page' => 200])->data([])
        );

        return collect($rows)
            ->mapWithKeys(function ($row) {
                $label = $row['name'] ?? 'Plan';
                if (isset($row['price'])) {
                    $label .= ' — '.rtrim(rtrim(number_format((float) $row['price'], 2), '0'), '.');
                }

                return [(string) ($row['id'] ?? '') => $label];
            })
            ->filter(fn ($label, $id) => $id !== '')
            ->all();
    }
}
