<?php

namespace Modules\EventEnrollment\Services;

use App\Services\ApplicantNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\Event;
use Modules\Authentication\Models\Institute;
use Modules\EventEnrollment\Models\EventEnrollment;
use Modules\Gateways\Models\PaymentRequest;

class EventEnrollmentService
{
    /**
     * Every gateway this app ships, mapped from the Setting.name key an admin
     * configures under Payment Gateways to the actual initiation path each
     * controller listens on (verified against Modules/Gateways/routes/web.php
     * and each controller's constructor — every one of them reads the
     * `payment_id` query parameter the same way).
     */
    private const GATEWAY_PATHS = [
        'stripe' => '/payment/stripe/pay',
        'razor_pay' => '/payment/razor-pay/pay',
        'paypal' => '/payment/paypal/pay',
        'ssl_commerz' => '/payment/sslcommerz/pay',
        'paystack' => '/payment/paystack/pay',
        'bkash' => '/payment/bkash/make-payment',
        'flutterwave' => '/payment/flutterwave-v3/pay',
        'pvit' => '/payment/pvit/pay',
        'liqpay' => '/payment/liqpay/payment',
        'mercadopago' => '/payment/mercadopago/pay',
        'paymob_accept' => '/payment/paymob/pay',
        'paytm' => '/payment/paytm/pay',
        'senang_pay' => '/payment/senang-pay/pay',
    ];

    private const GATEWAY_LABELS = [
        'stripe' => 'Credit / Debit Card (Stripe)',
        'razor_pay' => 'Razorpay',
        'paypal' => 'PayPal',
        'ssl_commerz' => 'SSLCommerz',
        'paystack' => 'Paystack',
        'bkash' => 'bKash',
        'flutterwave' => 'Flutterwave',
        'pvit' => 'PVit',
        'liqpay' => 'LiqPay',
        'mercadopago' => 'Mercado Pago',
        'paymob_accept' => 'Paymob',
        'paytm' => 'Paytm',
        'senang_pay' => 'SenangPay',
    ];

    public function __construct(
        private readonly ApplicantNotificationService $notifier,
    ) {}

    /**
     * 'upcoming' | 'ongoing' | 'past', computed from start_date/end_date —
     * no stored status column needed, so this stays correct automatically
     * as time passes.
     */
    public function status(Event $event): string
    {
        $now = Carbon::now();
        $start = Carbon::parse($event->start_date);
        $end = Carbon::parse($event->end_date);

        // If no explicit end time was set, the end date defaults to
        // midnight (00:00) of that day — which means an event "ending"
        // today would already read as "past" the instant any time passes
        // on that same day. That's not what setting an end date of "today"
        // should mean, so treat a midnight end time as running through the
        // end of that day instead.
        if ($end->format('H:i:s') === '00:00:00') {
            $end = $end->copy()->endOfDay();
        }

        if ($now->lt($start)) {
            return 'upcoming';
        }

        if ($now->gt($end)) {
            return 'past';
        }

        return 'ongoing';
    }

    public function confirmedCount(Event $event): int
    {
        return EventEnrollment::where('event_id', $event->id)
            ->whereIn('payment_status', [EventEnrollment::STATUS_CONFIRMED, EventEnrollment::STATUS_PAID])
            ->count();
    }

    /** Null means unlimited. */
    public function seatsRemaining(Event $event): ?int
    {
        if (! $event->max_seats) {
            return null;
        }

        return max(0, (int) $event->max_seats - $this->confirmedCount($event));
    }

    public function isEnrollmentOpen(Event $event): bool
    {
        if (! $event->enrollment_enabled) {
            return false;
        }

        if ($this->status($event) === 'past') {
            return false;
        }

        if ($event->registration_deadline && Carbon::now()->gt(Carbon::parse($event->registration_deadline))) {
            return false;
        }

        $remaining = $this->seatsRemaining($event);

        return $remaining === null || $remaining > 0;
    }

    /**
     * Gateways the institute has actually turned on, limited to the ones this
     * service knows how to redirect to (see GATEWAY_PATHS).
     *
     * @return array<int, array{key: string, label: string}>
     */
    public function availableGateways(int $instituteId, ?int $branchId): array
    {
        $rows = DB::table('settings')
            ->where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->where('type', 'payment_config')
            ->where('status', 1)
            ->get(['name']);

        $gateways = [];
        foreach ($rows as $row) {
            if (isset(self::GATEWAY_PATHS[$row->name])) {
                $gateways[] = ['key' => $row->name, 'label' => self::GATEWAY_LABELS[$row->name]];
            }
        }

        return $gateways;
    }

    /**
     * Free event — confirm the seat immediately, no payment involved.
     */
    public function enrollFree(Event $event, array $data): EventEnrollment
    {
        $enrollment = EventEnrollment::create([
            'event_id' => $event->id,
            'institute_id' => $event->institute_id,
            'branch_id' => $event->branch_id,
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'ticket_code' => EventEnrollment::generateTicketCode(),
            'amount_paid' => 0,
            'payment_status' => EventEnrollment::STATUS_CONFIRMED,
        ]);

        $this->notifyEnrollment($enrollment, $event, confirmed: true);

        return $enrollment;
    }

    /**
     * Paid event — create a pending enrollment plus a PaymentRequest, and
     * return the URL to redirect the visitor to for the chosen gateway.
     */
    public function initiatePaidCheckout(Event $event, array $data, string $gatewayKey): string
    {
        if (! isset(self::GATEWAY_PATHS[$gatewayKey])) {
            throw new \InvalidArgumentException('Unsupported or unconfigured payment gateway.');
        }

        $enrollment = EventEnrollment::create([
            'event_id' => $event->id,
            'institute_id' => $event->institute_id,
            'branch_id' => $event->branch_id,
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'ticket_code' => EventEnrollment::generateTicketCode(),
            'amount_paid' => 0,
            'currency_code' => $event->currency_code,
            'payment_status' => EventEnrollment::STATUS_PENDING_PAYMENT,
        ]);

        // IMPORTANT: this URL must have NO query string of its own — the
        // gateway controllers build the final redirect as
        // `$external_redirect_link . '?flag=...&&token=...'`, a plain string
        // concatenation. A URL that already contains "?" here would end up
        // with two "?" and break. Ticket code goes in the path, not a
        // query param, for exactly this reason.
        $returnUrl = route('eventenrollment.thankyou', ['id' => $event->id, 'ticket' => $enrollment->ticket_code]);

        $payment = PaymentRequest::create([
            'institute_id' => $event->institute_id,
            'branch_id' => $event->branch_id,
            'payment_amount' => $event->price,
            'currency_code' => $event->currency_code ?: 'usd',
            'payment_type' => 'event_enrollment',
            'attribute' => 'Event Enrollment: '.$event->name,
            'attribute_id' => $enrollment->id,
            'payment_platform' => 'web',
            'external_redirect_link' => $returnUrl,
            'success_hook' => 'event_enrollment_paid',
            'failure_hook' => 'event_enrollment_failed',
            'payer_information' => [
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ],
            'is_paid' => 0,
        ]);

        $enrollment->update(['payment_request_id' => $payment->id]);

        return url(self::GATEWAY_PATHS[$gatewayKey]).'?payment_id='.$payment->id;
    }

    /**
     * Registered against PaymentRequest::allowedHooks()['event_enrollment_paid'].
     */
    public function markPaid(PaymentRequest $payment): void
    {
        $enrollment = EventEnrollment::where('payment_request_id', $payment->id)->first();
        if (! $enrollment || $enrollment->payment_status === EventEnrollment::STATUS_PAID) {
            return; // already processed, or not one of ours
        }

        $enrollment->update([
            'payment_status' => EventEnrollment::STATUS_PAID,
            'amount_paid' => $payment->payment_amount,
        ]);

        $event = Event::find($enrollment->event_id);
        if ($event) {
            $this->notifyEnrollment($enrollment, $event, confirmed: true);
        }
    }

    /**
     * Registered against PaymentRequest::allowedHooks()['event_enrollment_failed'].
     */
    public function markFailed(PaymentRequest $payment): void
    {
        $enrollment = EventEnrollment::where('payment_request_id', $payment->id)->first();
        if (! $enrollment || $enrollment->payment_status !== EventEnrollment::STATUS_PENDING_PAYMENT) {
            return;
        }

        $enrollment->update(['payment_status' => EventEnrollment::STATUS_FAILED]);
    }

    private function notifyEnrollment(EventEnrollment $enrollment, Event $event, bool $confirmed): void
    {
        $institute = Institute::find($enrollment->institute_id);
        $schoolName = $institute?->name;
        $isPaid = (bool) $event->is_paid;

        $lines = [
            $confirmed
                ? "You're confirmed for \"{$event->name}\"! Your ticket code is {$enrollment->ticket_code}."
                : "We've received your registration for \"{$event->name}\".",
            "When: ".Carbon::parse($event->start_date)->format('d M Y, h:i A'),
            "Where: {$event->location}",
        ];
        if ($isPaid) {
            $lines[] = 'Amount paid: '.number_format((float) $enrollment->amount_paid, 2).' '.strtoupper((string) $enrollment->currency_code);
        }

        $this->notifier->notify(
            instituteId: $enrollment->institute_id,
            branchId: $enrollment->branch_id,
            name: $enrollment->name,
            email: $enrollment->email,
            phone: $enrollment->phone,
            subject: "You're registered — {$event->name}",
            lines: $lines,
            smsMessage: "You're confirmed for \"{$event->name}\". Ticket: {$enrollment->ticket_code}",
            whatsappEvent: $isPaid ? 'event_enrollment_paid' : 'event_enrollment_confirmed',
            whatsappParams: [
                'event_name' => $event->name,
                'ticket_code' => $enrollment->ticket_code,
                'attendee_name' => $enrollment->name,
            ],
            referenceType: 'event_enrollment',
            referenceId: $enrollment->id,
            schoolName: $schoolName,
        );
    }
}
