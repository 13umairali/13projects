<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\InternalReferral;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Academic\Models\StudentSession;

/**
 * The institute admin's full visibility into member referrals - who
 * referred whom, both people's contact details, and (if the referred
 * person joined as a student) their current class/section. Separate
 * from InstituteReferralController, which handles the OTHER kind of
 * referral (referring a new institute to the platform).
 */
class InternalReferralController extends Controller
{
    public function index(Request $request): View
    {
        $instituteId = get_institute_id();

        $query = InternalReferral::with(['referrer', 'referredUser'])
            ->where('institute_id', $instituteId)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $referrals = $query->paginate(20)->withQueryString();

        // A student's current class/section isn't on the user record
        // itself - it lives on their active StudentSession for the
        // current academic year, so it's looked up separately per row
        // rather than forcing every referral (most of which aren't
        // students at all) through a join it doesn't need.
        $currentSessionId = (int) get_option('academic_year');
        $referrals->getCollection()->transform(function (InternalReferral $referral) use ($currentSessionId) {
            $referral->referred_class_label = null;

            if ($referral->referred_user_type === 'student') {
                $session = StudentSession::with(['class', 'section'])
                    ->whereHas('student', fn ($q) => $q->where('user_id', $referral->referred_user_id))
                    ->where('session_id', $currentSessionId)
                    ->first();

                if ($session) {
                    $referral->referred_class_label = trim(
                        ($session->class?->name ?? '').' '.($session->section?->name ?? '')
                    );
                }
            }

            return $referral;
        });

        return view('authentication::referral.internal-referrals', [
            'referrals' => $referrals,
            'status' => $request->query('status', ''),
        ]);
    }

    public function approve(int $id): RedirectResponse
    {
        $referral = $this->findOwned($id);
        $referral->markRewarded();

        return redirect()->route('internal-referrals.index')->with('success', 'Reward approved.');
    }

    public function reject(int $id): RedirectResponse
    {
        $referral = $this->findOwned($id);
        $referral->reject();

        return redirect()->route('internal-referrals.index')->with('success', 'Reward rejected.');
    }

    protected function findOwned(int $id): InternalReferral
    {
        return InternalReferral::where('institute_id', get_institute_id())->findOrFail($id);
    }
}