<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\OnboardingService;
use App\Support\ApiPaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super-admin review of the onboarding queue. Institutes that register through
 * the public form wait here until approved, which provisions the tenant on the
 * backend. All state lives on the backend — this controller only proxies.
 */
class OnboardingController extends Controller
{
    public function __construct(
        protected OnboardingService $onboardings,
    ) {}

    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true)
            ? (int) $request->query('per_page')
            : 10;

        $response = $this->onboardings->paginate([
            'per_page' => $perPage,
            'page' => (int) $request->query('page', 1) ?: 1,
        ]);

        return view('admin.onboarding.index', [
            'onboardings' => ApiPaginator::fromApi($response->data([]), $request),
            'reachable' => $response->successful(),
            'perPage' => $perPage,
        ]);
    }

    public function approve(string $id): RedirectResponse
    {
        $response = $this->onboardings->approve($id);

        return redirect()
            ->route('admin.onboarding.index')
            ->with(
                $response->successful() ? 'success' : 'error',
                $response->successful()
                    ? ($response->message ?: 'Onboarding approved — the institute has been provisioned.')
                    : ($response->message ?: 'Approval failed. Please try again.')
            );
    }
}
