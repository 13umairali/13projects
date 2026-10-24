<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The single platform-wide default row (scope_type = 'platform'). An
 * institute may optionally override any of these fields for its own
 * users - see WithdrawalSetting::effectiveFor().
 */
class WithdrawalSettingController extends Controller
{
    public function show(): JsonResponse
    {
        $setting = WithdrawalSetting::where('scope_type', 'platform')->first();

        return $this->responseSuccess($setting, 'Withdrawal settings fetched successfully.');
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0'],
            'enabled_methods' => ['nullable', 'array'],
            'enabled_methods.*' => ['string', 'in:bank_transfer,paypal,mobile_money,other'],
        ]);

        $setting = WithdrawalSetting::updateOrCreate(
            ['scope_type' => 'platform', 'scope_id' => null],
            [
                'min_amount' => $validated['min_amount'] ?? null,
                'max_amount' => $validated['max_amount'] ?? null,
                'enabled_methods' => $validated['enabled_methods'] ?? null,
            ]
        );

        return $this->responseSuccess($setting, 'Withdrawal settings updated successfully.');
    }
}