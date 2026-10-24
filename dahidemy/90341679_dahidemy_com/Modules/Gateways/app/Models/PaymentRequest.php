<?php

namespace Modules\Gateways\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    use HasUuid;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'receiver_id',
        'payment_amount',
        'gateway_callback_url',
        'success_hook',
        'failure_hook',
        'transaction_id',
        'currency_code',
        'payment_method',
        'payment_type',
        'additional_data',
        'is_paid',
        'payer_information',
        'external_redirect_link',
        'receiver_information',
        'attribute_id',
        'plan_id',
        'notes',
        'attribute',
        'payment_platform',
    ];

    protected $casts = [
        'payer_information' => 'array',
        'additional_data' => 'array',
    ];

    /**
     * Fixed allowlist of internal hook identifiers gateway controllers are permitted to invoke
     * after a payment settles. `success_hook`/`failure_hook` must never be treated as a raw
     * PHP callable name taken from client input — that allowed arbitrary function invocation.
     * Add entries here (e.g. 'confirm_subscription' => [SubscriptionService::class, 'confirm'])
     * as real internal callbacks are needed; nothing outside this list is ever called.
     */
    public static function allowedHooks(): array
    {
        return [
            'complete_onboarding' => [\App\Services\InstituteProvisioningCheckoutService::class, 'completeAfterPayment'],
            'complete_subscription_upgrade' => [\App\Services\SubscriptionUpgradeCheckoutService::class, 'completeAfterPayment'],
        ];
    }

    public function runSuccessHook(): void
    {
        $hooks = self::allowedHooks();
        if ($this->success_hook && isset($hooks[$this->success_hook])) {
            call_user_func($hooks[$this->success_hook], $this);
        }
    }

    public function runFailureHook(): void
    {
        $hooks = self::allowedHooks();
        if ($this->failure_hook && isset($hooks[$this->failure_hook])) {
            call_user_func($hooks[$this->failure_hook], $this);
        }
    }
}