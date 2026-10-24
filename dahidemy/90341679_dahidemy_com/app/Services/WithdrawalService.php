<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\WithdrawalRequest;
use App\Models\WithdrawalSetting;

/**
 * A user's withdrawable balance is always derived, never stored directly:
 * everything they've actually earned (rewarded cash_withdrawal-type
 * institute referrals) minus everything they've already successfully
 * withdrawn (approved or paid requests). This means the number shown can
 * never drift from what actually happened - there's no separate ledger
 * to fall out of sync.
 */
class WithdrawalService
{
    public function availableBalanceFor(int $userId): float
    {
        $earned = (float) Referral::where('referrer_type', 'user')
            ->where('referrer_id', $userId)
            ->where('institute_reward_type', 'cash_withdrawal')
            ->where('institute_reward_status', 'rewarded')
            ->sum('institute_reward_value');

        $withdrawn = (float) WithdrawalRequest::where('user_id', $userId)
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');

        return round($earned - $withdrawn, 2);
    }

    /**
     * $payoutDetails is a structured array whose expected keys depend on
     * $payoutMethod (account_holder_name/bank_name/account_number/
     * routing_code for bank_transfer, paypal_email for paypal,
     * provider_name/phone_number for mobile_money, description for
     * other) - stored as JSON in the existing text column rather than
     * needing a schema change per method.
     *
     * @throws \InvalidArgumentException if the amount is outside the
     *         effective min/max for this institute, exceeds the actual
     *         available balance, or the chosen method isn't enabled -
     *         never trust any of this from the client alone.
     */
    public function requestWithdrawal(int $userId, int $instituteId, float $amount, string $payoutMethod, array $payoutDetails): WithdrawalRequest
    {
        $settings = WithdrawalSetting::effectiveFor($instituteId);
        $available = $this->availableBalanceFor($userId);

        if ($amount <= 0 || $amount > $available) {
            throw new \InvalidArgumentException("Requested amount exceeds your available balance of \${$available}.");
        }

        if ($settings['min_amount'] && $amount < (float) $settings['min_amount']) {
            throw new \InvalidArgumentException("The minimum withdrawal amount is \${$settings['min_amount']}.");
        }

        if ($settings['max_amount'] && $amount > (float) $settings['max_amount']) {
            throw new \InvalidArgumentException("The maximum withdrawal amount is \${$settings['max_amount']}.");
        }

        if (! in_array($payoutMethod, $settings['enabled_methods'], true)) {
            throw new \InvalidArgumentException('That payout method is not currently available.');
        }

        return WithdrawalRequest::create([
            'user_id' => $userId,
            'institute_id' => $instituteId,
            'amount' => $amount,
            'payout_method' => $payoutMethod,
            'payout_details' => json_encode($payoutDetails),
            'status' => 'pending',
        ]);
    }

    public function approve(WithdrawalRequest $request): void
    {
        $request->update(['status' => 'approved', 'processed_at' => now()]);
    }

    public function markPaid(WithdrawalRequest $request, ?string $method = null, ?string $reference = null): void
    {
        $request->update([
            'status' => 'paid',
            'payment_method' => $method,
            'payment_reference' => $reference,
            'processed_at' => now(),
        ]);
    }

    public function reject(WithdrawalRequest $request, ?string $notes = null): void
    {
        $request->update(['status' => 'rejected', 'notes' => $notes, 'processed_at' => now()]);
    }
}