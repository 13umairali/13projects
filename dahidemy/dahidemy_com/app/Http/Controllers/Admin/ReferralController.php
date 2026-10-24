<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\ReferralAdminService;
use App\Support\ApiPaginator;
use App\Support\ResourceRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super-admin referral management: review referrals raised by any
 * institute or any individual user (student, parent, teacher, accountant,
 * staff...), approve/reject them, and configure what each referrer role
 * earns. Everything is proxied to the backend referral endpoints.
 */
class ReferralController extends Controller
{
    public function __construct(protected ReferralAdminService $referrals) {}

    /* -----------------------------------------------------------------
     |  Referral review queue
     | ----------------------------------------------------------------- */

    public function index(Request $request): View
    {
        $status = trim((string) $request->query('status', ''));

        $response = $this->referrals->list(array_filter([
            'status' => $status,
            'page' => (int) $request->query('page', 1) ?: 1,
        ], fn ($v) => $v !== '' && $v !== null));

        return view('admin.referrals.index', [
            'referrals' => ApiPaginator::fromApi($response->data([]), $request),
            'reachable' => $response->successful(),
            'status' => $status,
        ]);
    }

    public function approve(string $id): RedirectResponse
    {
        $response = $this->referrals->approve($id);

        return redirect()
            ->route('admin.referrals.index')
            ->with(
                $response->successful() ? 'success' : 'error',
                $response->successful()
                    ? ($response->message ?: 'Referral approved and rewarded.')
                    : ($response->message ?: 'Approval failed. Please try again.')
            );
    }

    public function reject(string $id): RedirectResponse
    {
        $response = $this->referrals->reject($id);

        return redirect()
            ->route('admin.referrals.index')
            ->with(
                $response->successful() ? 'success' : 'error',
                $response->successful()
                    ? ($response->message ?: 'Referral rejected.')
                    : ($response->message ?: 'Rejection failed. Please try again.')
            );
    }

    /* -----------------------------------------------------------------
     |  Reward settings per referrer role
     | ----------------------------------------------------------------- */

    public function settings(): View
    {
        $response = $this->referrals->settings();
        $roleResponse = $this->referrals->roleOptions();

        return view('admin.referrals.settings', [
            'settings' => ResourceRegistry::normalizeList($response->data([])),
            'roleOptions' => $roleResponse->successful() ? ($roleResponse->data([]) ?: []) : [],
            'reachable' => $response->successful(),
        ]);
    }

    public function storeSetting(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'referrer_role' => ['required', 'string', 'max:100'],
            'reward_type' => ['required', 'in:days,cash,points,percentage'],
            'reward_value' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['status'] = $request->boolean('status');

        $response = $this->referrals->createSetting($data);

        return redirect()
            ->route('admin.referrals.settings')
            ->with(
                $response->successful() ? 'success' : 'error',
                $response->successful()
                    ? ($response->message ?: 'Referral setting created.')
                    : ($response->message ?: 'Save failed. Please review the form and try again.')
            );
    }

    public function updateSetting(Request $request, string $id): RedirectResponse
    {
        $data = $request->validate([
            'referrer_role' => ['required', 'string', 'max:100'],
            'reward_type' => ['required', 'in:days,cash,points,percentage'],
            'reward_value' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['status'] = $request->boolean('status');

        $response = $this->referrals->updateSetting($id, $data);

        return redirect()
            ->route('admin.referrals.settings')
            ->with(
                $response->successful() ? 'success' : 'error',
                $response->successful()
                    ? ($response->message ?: 'Referral setting updated.')
                    : ($response->message ?: 'Update failed. Please review the form and try again.')
            );
    }

    public function destroySetting(string $id): RedirectResponse
    {
        $response = $this->referrals->deleteSetting($id);

        return redirect()
            ->route('admin.referrals.settings')
            ->with(
                $response->successful() ? 'success' : 'error',
                $response->successful()
                    ? ($response->message ?: 'Referral setting deleted.')
                    : ($response->message ?: 'Delete failed.')
            );
    }
}