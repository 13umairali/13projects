<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Lets an institute admin optionally override the platform-wide
 * withdrawal limits/methods for their own users - a tighter minimum, a
 * lower maximum, or disabling a method the platform otherwise allows.
 * Leaving a field blank falls through to whatever the SaaS admin has set
 * (see WithdrawalSetting::effectiveFor()).
 */
class InstituteWithdrawalSettingController extends Controller
{
    public function index(): View
    {
        $instituteId = get_institute_id();

        $setting = WithdrawalSetting::where('scope_type', 'institute')
            ->where('scope_id', $instituteId)
            ->first();

        $platform = WithdrawalSetting::where('scope_type', 'platform')->first();

        return view('authentication::referral.institute-withdrawal-settings', [
            'setting' => $setting,
            'platform' => $platform,
            'allMethods' => WithdrawalSetting::ALL_METHODS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0'],
            'enabled_methods' => ['nullable', 'array'],
            'enabled_methods.*' => ['string', 'in:bank_transfer,paypal,mobile_money,other'],
        ]);

        WithdrawalSetting::updateOrCreate(
            ['scope_type' => 'institute', 'scope_id' => get_institute_id()],
            [
                'min_amount' => $validated['min_amount'] ?? null,
                'max_amount' => $validated['max_amount'] ?? null,
                'enabled_methods' => $validated['enabled_methods'] ?? null,
            ]
        );

        return redirect()->route('institute-withdrawal-settings.index')
            ->with('success', 'Withdrawal settings updated.');
    }

    /**
     * Reverts to fully inheriting the platform's own settings, with no
     * institute-specific override at all.
     */
    public function reset(): RedirectResponse
    {
        WithdrawalSetting::where('scope_type', 'institute')
            ->where('scope_id', get_institute_id())
            ->delete();

        return redirect()->route('institute-withdrawal-settings.index')
            ->with('success', 'Reverted to the platform default settings.');
    }
}