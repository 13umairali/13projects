<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\InstituteReferralSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Authentication\Models\Institute;

/**
 * Each institute admin decides, for THEIR OWN institute only, what a
 * student/parent/teacher/accountant/staff/any role earns for TWO
 * distinct actions - referral_type distinguishes them:
 *   - refer_institute: referring a brand new INSTITUTE to the platform
 *     (this reward is separate from, and in addition to, whatever the
 *     SaaS admin also gives for the same action)
 *   - refer_member: referring a new person to enroll/join THIS SAME
 *     institute (a student referring a student, a teacher referring a
 *     colleague, etc)
 * Deliberately separate from ReferralSetting, which is the global,
 * SaaS-admin-owned table for refer_institute only - this one is scoped
 * to institute_id and never touched by the SaaS admin.
 */
class InstituteReferralSettingController extends Controller
{
    public function index(): View
    {
        $instituteId = get_institute_id();

        $referralSettings = InstituteReferralSetting::where('institute_id', $instituteId)
            ->orderBy('referral_type')
            ->orderBy('referrer_role')
            ->get();

        return view('authentication::referral.institute-settings', [
            'referralSettings' => $referralSettings,
            'internalReferralEnabled' => (bool) Institute::where('id', $instituteId)->value('internal_referral_enabled'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'referrer_role' => ['required', 'string', 'max:100'],
            'referral_type' => ['required', 'in:refer_institute,refer_member'],
            'reward_type' => ['required', 'in:fee_discount,points,cash_withdrawal'],
            'reward_value' => ['required', 'numeric', 'min:0'],
            'is_percentage' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['institute_id'] = get_institute_id();
        $validated['is_percentage'] = $request->boolean('is_percentage');

        InstituteReferralSetting::updateOrCreate(
            [
                'institute_id' => $validated['institute_id'],
                'referrer_role' => $validated['referrer_role'],
                'referral_type' => $validated['referral_type'],
            ],
            $validated
        );

        return redirect()->route('institute-referral-settings.index')
            ->with('success', 'Referral reward rule saved.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $setting = InstituteReferralSetting::where('institute_id', get_institute_id())->findOrFail($id);

        $validated = $request->validate([
            'reward_type' => ['required', 'in:fee_discount,points,cash_withdrawal'],
            'reward_value' => ['required', 'numeric', 'min:0'],
            'is_percentage' => ['nullable', 'boolean'],
            'status' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_percentage'] = $request->boolean('is_percentage');
        $validated['status'] = $request->boolean('status');

        $setting->update($validated);

        return redirect()->route('institute-referral-settings.index')
            ->with('success', 'Referral reward rule updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        InstituteReferralSetting::where('institute_id', get_institute_id())->findOrFail($id)->delete();

        return redirect()->route('institute-referral-settings.index')
            ->with('success', 'Referral reward rule deleted.');
    }

    /**
     * Turns the "refer a new member to join this institute" feature on
     * or off. When off, the referral code field is meant to be hidden
     * on every institute form (admission, staff creation, etc.) and any
     * code entered is silently ignored rather than rejected - see
     * InternalReferralService::recordReferral(). Never affects the
     * separate refer_institute (platform-level) referral system, which
     * has no on/off switch since it's core to the SaaS admin's own
     * program.
     */
    public function toggleInternalReferrals(Request $request): RedirectResponse
    {
        Institute::where('id', get_institute_id())
            ->update(['internal_referral_enabled' => $request->boolean('enabled')]);

        return redirect()->route('institute-referral-settings.index')
            ->with('success', $request->boolean('enabled')
                ? 'Member referrals are now enabled - the referral code field will appear on your institute\'s forms.'
                : 'Member referrals are now disabled.');
    }
}