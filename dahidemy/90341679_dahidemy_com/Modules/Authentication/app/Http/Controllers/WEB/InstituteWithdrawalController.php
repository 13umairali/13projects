<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Lets an institute admin see and act on withdrawal requests from their
 * own users. "Approve" marks the request approved (the institute intends
 * to pay it); "Mark Paid" records that the actual real-world payment
 * (bank transfer, cash, etc.) has now happened - the same two-step,
 * trust-based pattern used for every other manual payout in this system.
 */
class InstituteWithdrawalController extends Controller
{
    public function index(Request $request): View
    {
        $instituteId = get_institute_id();

        $query = WithdrawalRequest::where('institute_id', $instituteId)->with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return view('authentication::referral.institute-withdrawals', [
            'requests' => $query->paginate(20)->withQueryString(),
            'status' => $request->query('status', ''),
        ]);
    }

    public function approve(int $id, WithdrawalService $withdrawals): RedirectResponse
    {
        $withdrawals->approve($this->findOwnedRequest($id));

        return redirect()->route('institute-withdrawals.index')->with('success', 'Withdrawal approved.');
    }

    public function markPaid(Request $request, int $id, WithdrawalService $withdrawals): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => ['nullable', 'string', 'max:100'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $withdrawals->markPaid(
            $this->findOwnedRequest($id),
            $validated['payment_method'] ?? null,
            $validated['payment_reference'] ?? null
        );

        return redirect()->route('institute-withdrawals.index')->with('success', 'Marked as paid.');
    }

    public function reject(Request $request, int $id, WithdrawalService $withdrawals): RedirectResponse
    {
        $withdrawals->reject($this->findOwnedRequest($id), $request->input('notes'));

        return redirect()->route('institute-withdrawals.index')->with('success', 'Withdrawal rejected.');
    }

    /**
     * Guards against one institute approving/paying a request that
     * belongs to a different institute's user.
     */
    protected function findOwnedRequest(int $id): WithdrawalRequest
    {
        return WithdrawalRequest::where('institute_id', get_institute_id())->findOrFail($id);
    }
}