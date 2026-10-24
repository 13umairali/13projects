<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Authentication\Models\User;

/**
 * Shows referrals made by users belonging to THIS institute (students,
 * parents, teachers, accountants, staff...) where an institute-level
 * reward rule applies. Approving here only affects institute_reward_* -
 * the separate SaaS-level reward on the same referral is reviewed and
 * approved independently by the SaaS Super Admin.
 */
class InstituteReferralController extends Controller
{
    public function index(Request $request): View
    {
        $instituteId = get_institute_id();

        $userIds = User::where('institute_id', $instituteId)->pluck('id');

        $query = Referral::where('referrer_type', 'user')
            ->whereIn('referrer_id', $userIds)
            ->whereNotNull('institute_reward_type')
            ->latest();

        if ($request->filled('status')) {
            $query->where('institute_reward_status', $request->query('status'));
        }

        return view('authentication::referral.institute-referrals', [
            'referrals' => $query->paginate(20)->withQueryString(),
            'status' => $request->query('status', ''),
        ]);
    }

    public function approve(int $id): RedirectResponse
    {
        $referral = $this->findOwnedReferral($id);
        $referral->markInstituteRewarded();

        return redirect()->route('institute-referrals.index')
            ->with('success', 'Reward approved.');
    }

    public function reject(int $id): RedirectResponse
    {
        $referral = $this->findOwnedReferral($id);
        $referral->rejectInstituteReward();

        return redirect()->route('institute-referrals.index')
            ->with('success', 'Reward rejected.');
    }

    /**
     * Guards against one institute approving/rejecting a referral that
     * belongs to a different institute's user - the referrer must be one
     * of THIS institute's own users.
     */
    protected function findOwnedReferral(int $id): Referral
    {
        $instituteId = get_institute_id();
        $userIds = User::where('institute_id', $instituteId)->pluck('id');

        return Referral::where('referrer_type', 'user')
            ->whereIn('referrer_id', $userIds)
            ->findOrFail($id);
    }
}