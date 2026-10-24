<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Services\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SaaS Super Admin's referral review queue - lists every referral with the
 * referrer's actual name resolved (institute name, or user name + role),
 * and lets the admin approve/reward one. Deliberately not a generic CRUD
 * resource: approving a referral is a specific action with side effects
 * (see ReferralService::approveReward), not a plain field update.
 */
class ReferralController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Referral::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $referrals = $query->paginate(25);

        $referrals->getCollection()->transform(function (Referral $referral) {
            $referrer = $referral->referrer();

            return [
                'id' => $referral->id,
                'referrer_type' => $referral->referrer_type,
                'referrer_role' => $referral->referrer_role,
                'referrer_name' => $referrer?->name ?? '(deleted)',
                'referrer_email' => $referrer?->email ?? null,
                'referral_code_used' => $referral->referral_code_used,
                'referred_institute' => $referral->referredInstitute?->name,
                'status' => $referral->status,
                'reward_type' => $referral->reward_type,
                'reward_value' => $referral->reward_value,
                'converted_at' => $referral->converted_at,
                'rewarded_at' => $referral->rewarded_at,
                'created_at' => $referral->created_at,
            ];
        });

        return $this->responseSuccess($referrals, 'Referrals fetched successfully.');
    }

    public function approve(int $id, ReferralService $service): JsonResponse
    {
        $referral = Referral::findOrFail($id);

        if ($referral->status === 'rejected') {
            return $this->responseError([], 'A rejected referral cannot be approved.', 422);
        }

        $service->approveReward($referral);

        return $this->responseSuccess($referral->fresh(), 'Referral approved and rewarded.');
    }

    public function reject(int $id): JsonResponse
    {
        $referral = Referral::findOrFail($id);
        $referral->update(['status' => 'rejected']);

        return $this->responseSuccess($referral, 'Referral rejected.');
    }
}