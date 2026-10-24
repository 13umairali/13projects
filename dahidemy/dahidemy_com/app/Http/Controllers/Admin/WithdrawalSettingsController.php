<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\WithdrawalSettingsAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The single platform-wide default that every institute inherits from
 * unless they've set their own override (Institute Referrals ->
 * Withdrawal Settings on their side).
 */
class WithdrawalSettingsController extends Controller
{
    protected const ALL_METHODS = [
        'bank_transfer' => 'Bank Transfer',
        'paypal' => 'PayPal',
        'mobile_money' => 'Mobile Money',
        'other' => 'Other',
    ];

    public function edit(WithdrawalSettingsAdminService $withdrawalSettings): View
    {
        $response = $withdrawalSettings->show();
        $setting = $response->successful() ? $response->data([]) : null;

        return view('admin.withdrawal-settings.edit', [
            'setting' => $setting,
            'allMethods' => self::ALL_METHODS,
            'reachable' => $response->successful(),
        ]);
    }

    public function update(Request $request, WithdrawalSettingsAdminService $withdrawalSettings): RedirectResponse
    {
        $validated = $request->validate([
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0'],
            'enabled_methods' => ['nullable', 'array'],
            'enabled_methods.*' => ['string', 'in:bank_transfer,paypal,mobile_money,other'],
        ]);

        $response = $withdrawalSettings->update($validated);

        return redirect()->route('admin.withdrawal-settings.edit')
            ->with(
                $response->successful() ? 'success' : 'error',
                $response->successful()
                    ? ($response->message ?: 'Withdrawal settings updated.')
                    : ($response->message ?: 'Update failed. Please review the form and try again.')
            );
    }
}