<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ReferralSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SaaS Super Admin CRUD for referral reward rules - one row per referrer
 * role (institute, student, parent, teacher, accountant, staff, or any
 * other role name in use). Fully dynamic: adding a new role elsewhere in
 * the system just means the admin can add a new row here for it, no code
 * change required.
 */
class ReferralSettingController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->responseSuccess(
            ReferralSetting::orderBy('referrer_role')->get(),
            'Referral settings fetched successfully.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'referrer_role' => ['required', 'string', 'max:100', 'unique:referral_settings,referrer_role'],
            'reward_type' => ['required', 'in:days,cash,points,percentage'],
            'reward_value' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $setting = ReferralSetting::create($validated);

        return $this->responseSuccess($setting, 'Referral setting created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        return $this->responseSuccess(
            ReferralSetting::findOrFail($id),
            'Referral setting fetched successfully.'
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $setting = ReferralSetting::findOrFail($id);

        $validated = $request->validate([
            'referrer_role' => ['sometimes', 'required', 'string', 'max:100', 'unique:referral_settings,referrer_role,'.$id],
            'reward_type' => ['sometimes', 'required', 'in:days,cash,points,percentage'],
            'reward_value' => ['sometimes', 'required', 'numeric', 'min:0'],
            'status' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $setting->update($validated);

        return $this->responseSuccess($setting, 'Referral setting updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        ReferralSetting::findOrFail($id)->delete();

        return $this->responseSuccess([], 'Referral setting deleted successfully.');
    }
}