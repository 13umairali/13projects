<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\WithdrawalRequest;
use App\Models\WithdrawalSetting;
use App\Services\ReferralService;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Available to every logged-in user regardless of role - student, parent,
 * teacher, accountant, staff, institute admin. Shows their own referral
 * code/link, referral history, and - since a "cash_withdrawal" reward is
 * only meaningful if the person can actually request the payout - their
 * withdrawable balance and past withdrawal requests too.
 */
class MyReferralController extends Controller
{
    public function index(ReferralService $referrals, WithdrawalService $withdrawals): View
    {
        $user = auth()->user();

        $code = $user->referral_code ?: $referrals->generateCodeFor('user', $user->id);

        $history = Referral::where('referrer_type', 'user')
            ->where('referrer_id', $user->id)
            ->latest()
            ->get();

        $withdrawalHistory = WithdrawalRequest::where('user_id', $user->id)->latest()->get();

        return view('authentication::referral.my-referrals', [
            'code' => $code,
            'referralLink' => rtrim(config('app.marketing_url', 'https://dahidemy.com'), '/').'/register?ref='.$code,
            'history' => $history,
            'availableBalance' => $withdrawals->availableBalanceFor($user->id),
            'withdrawalHistory' => $withdrawalHistory,
            'withdrawalSettings' => WithdrawalSetting::effectiveFor($user->institute_id),
        ]);
    }

    public function requestWithdrawal(Request $request, WithdrawalService $withdrawals): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payout_method' => ['required', 'string', 'in:bank_transfer,paypal,mobile_money,other'],
            // Only the fields relevant to the chosen method are actually
            // required - enforced below, since Laravel's required_if only
            // checks presence, not "this group of fields together".
            'account_holder_name' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'routing_code' => ['nullable', 'string', 'max:100'],
            'paypal_email' => ['nullable', 'email', 'max:255'],
            'provider_name' => ['nullable', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $method = $validated['payout_method'];
        $payoutDetails = match ($method) {
            'bank_transfer' => array_filter([
                'account_holder_name' => $validated['account_holder_name'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
                'routing_code' => $validated['routing_code'] ?? null,
            ]),
            'paypal' => array_filter([
                'paypal_email' => $validated['paypal_email'] ?? null,
            ]),
            'mobile_money' => array_filter([
                'provider_name' => $validated['provider_name'] ?? null,
                'phone_number' => $validated['phone_number'] ?? null,
            ]),
            default => array_filter([
                'description' => $validated['description'] ?? null,
            ]),
        };

        $requiredCounts = [
            'bank_transfer' => 4,
            'paypal' => 1,
            'mobile_money' => 2,
            'other' => 1,
        ];
        if (count($payoutDetails) < $requiredCounts[$method]) {
            return redirect()->route('my-referrals.index')
                ->withInput()
                ->with('error', 'Please fill in all the payment details for the method you selected.');
        }

        $user = auth()->user();

        try {
            $withdrawals->requestWithdrawal(
                $user->id,
                $user->institute_id,
                (float) $validated['amount'],
                $method,
                $payoutDetails
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('my-referrals.index')->with('error', $e->getMessage());
        }

        return redirect()->route('my-referrals.index')
            ->with('success', 'Withdrawal request submitted - your institute will review it shortly.');
    }
}