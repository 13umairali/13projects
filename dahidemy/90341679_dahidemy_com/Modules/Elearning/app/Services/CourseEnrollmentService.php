<?php

namespace Modules\Elearning\Services;

use App\Services\ApplicantNotificationService;
use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\Institute;
use Modules\Elearning\Models\Course;
use Modules\Elearning\Models\Enrollment;
use Modules\Gateways\Models\PaymentRequest;

class CourseEnrollmentService
{
    /**
     * Same verified gateway map used by EventEnrollmentService — every
     * institution-configured gateway this app ships, mapped to its actual
     * initiation path. Kept as its own copy here (rather than a shared
     * dependency) so this module works standalone even if EventEnrollment
     * isn't installed.
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

    public function price(Course $course): float
    {
        return (float) ($course->offer_price ?: $course->regular_price ?: 0);
    }

    public function isFree(Course $course): bool
    {
        return $course->payment_type === 'free';
    }

    /**
     * Recurring/subscription billing needs webhook-driven recurring charges
     * that this one-shot gateway flow doesn't support — deliberately out of
     * scope here rather than building something that silently only charges
     * once. Only 'free' and 'one_time' courses can self-checkout on the
     * website; 'recurring_payment' courses show but direct visitors to
     * contact the institute instead.
     */
    public function canSelfCheckout(Course $course): bool
    {
        return in_array($course->payment_type, ['free', 'one_time'], true);
    }

    public function alreadyEnrolled(Course $course, int $userId): bool
    {
        return Enrollment::where('course_id', $course->id)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->exists();
    }

    public function availableGateways(int $instituteId): array
    {
        $rows = DB::table('settings')
            ->where('institute_id', $instituteId)
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

    public function enrollFree(Course $course, int $userId): Enrollment
    {
        $enrollment = Enrollment::create([
            'institute_id' => $course->institute_id,
            'user_id' => $userId,
            'course_id' => $course->id,
            'enrollment_type' => 'one_time',
            'enrollment_date' => now()->toDateString(),
            'amount_paid' => 0,
            'status' => 'active',
        ]);

        $this->notifyEnrollment($enrollment, $course);

        return $enrollment;
    }

    public function initiatePaidCheckout(Course $course, int $userId, string $gatewayKey): string
    {
        if (! isset(self::GATEWAY_PATHS[$gatewayKey])) {
            throw new \InvalidArgumentException('Unsupported or unconfigured payment gateway.');
        }

        $enrollment = Enrollment::create([
            'institute_id' => $course->institute_id,
            'user_id' => $userId,
            'course_id' => $course->id,
            'enrollment_type' => 'one_time',
            'enrollment_date' => now()->toDateString(),
            'amount_paid' => 0,
            'status' => 'inactive', // flips to 'active' once the gateway confirms payment
        ]);

        // No query string here — the gateway controllers build the final
        // redirect as `$external_redirect_link . '?flag=...&&token=...'`, a
        // plain string concatenation, so a URL that already contains "?"
        // would end up with two and break.
        $returnUrl = route('courses-page.thankyou', $course->slug);

        $payment = PaymentRequest::create([
            'institute_id' => $course->institute_id,
            'payment_amount' => $this->price($course),
            'currency_code' => 'usd',
            'payment_type' => 'course_enrollment',
            'attribute' => 'Course Enrollment: '.$course->title,
            'attribute_id' => $enrollment->id,
            'payment_platform' => 'web',
            'external_redirect_link' => $returnUrl,
            'success_hook' => 'course_enrollment_paid',
            'failure_hook' => 'course_enrollment_failed',
            'is_paid' => 0,
        ]);

        $enrollment->update(['payment_request_id' => $payment->id]);

        return url(self::GATEWAY_PATHS[$gatewayKey]).'?payment_id='.$payment->id;
    }

    public function markPaid(PaymentRequest $payment): void
    {
        $enrollment = Enrollment::where('payment_request_id', $payment->id)->first();
        if (! $enrollment || $enrollment->status === 'active') {
            return;
        }

        $enrollment->update([
            'status' => 'active',
            'amount_paid' => $payment->payment_amount,
        ]);

        $course = Course::find($enrollment->course_id);
        if ($course) {
            $this->notifyEnrollment($enrollment, $course);
        }
    }

    public function markFailed(PaymentRequest $payment): void
    {
        $enrollment = Enrollment::where('payment_request_id', $payment->id)->first();
        if (! $enrollment || $enrollment->status !== 'inactive') {
            return;
        }

        $enrollment->update(['status' => 'blocked']);
    }

    private function notifyEnrollment(Enrollment $enrollment, Course $course): void
    {
        $user = $enrollment->user;
        $institute = Institute::find($course->institute_id);
        if (! $user || ! $institute) {
            return;
        }

        $isPaid = ! $this->isFree($course);
        $lines = [
            "You're enrolled in \"{$course->title}\"!",
        ];
        if ($isPaid) {
            $lines[] = 'Amount paid: '.number_format((float) $enrollment->amount_paid, 2);
        }

        $this->notifier->notify(
            instituteId: $course->institute_id,
            branchId: null,
            name: $user->name ?: 'Student',
            email: $user->email,
            phone: $user->phone,
            subject: "You're enrolled — {$course->title}",
            lines: $lines,
            smsMessage: "You're enrolled in \"{$course->title}\".",
            whatsappEvent: $isPaid ? 'course_enrollment_paid' : 'course_enrollment_confirmed',
            whatsappParams: ['course_title' => $course->title, 'student_name' => $user->name],
            referenceType: 'course_enrollment',
            referenceId: $enrollment->id,
            schoolName: $institute->name,
        );
    }
}
