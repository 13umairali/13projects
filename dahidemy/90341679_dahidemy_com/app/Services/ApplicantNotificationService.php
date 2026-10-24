<?php

namespace App\Services;

use App\Mail\ApplicantNotificationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Academic\Models\SmsLog;
use Modules\Academic\Models\WhatsAppLog;
use Modules\Academic\Models\WhatsAppTemplate;
use Modules\Authentication\Models\Institute;

/**
 * Sends a "someone applied / their status changed" notification to an
 * applicant (not yet a logged-in user of the system) across whichever
 * channels are available: email, SMS, and WhatsApp.
 *
 * IMPORTANT — why this doesn't just call SmsService::queueLog() /
 * WhatsAppService::queue() directly:
 *
 * Both of those existing helpers resolve the current institute via
 * get_institute_id()/get_branch_id(), which are defined as:
 *   auth()->check() ? auth()->user()->institute_id : null
 *
 * That's correct for admin-triggered sends (the admin is logged in), but a
 * public form/admission submission has NO authenticated user — those helpers
 * would silently return null and the message would never be queued. This
 * service accepts institute/branch IDs explicitly instead, so it works
 * identically from an authenticated admin action (approve/reject) and from
 * an anonymous public submission.
 */
class ApplicantNotificationService
{
    /**
     * @param  string[]  $lines  Email body paragraphs, in order.
     * @param  array<string, string>  $whatsappParams  Placeholders for the WhatsApp template ({{key}} => value).
     */
    public function notify(
        int $instituteId,
        ?int $branchId,
        string $name,
        ?string $email,
        ?string $phone,
        string $subject,
        array $lines,
        ?string $smsMessage = null,
        ?string $whatsappEvent = null,
        array $whatsappParams = [],
        ?string $referenceType = null,
        int|string|null $referenceId = null,
        ?string $schoolName = null,
    ): void {
        if ($email) {
            $this->sendEmail($email, $name, $subject, $lines, $schoolName);
        }

        if ($phone && $smsMessage) {
            $this->queueSms($instituteId, $branchId, $phone, $smsMessage);
        }

        if ($phone && $whatsappEvent) {
            $this->queueWhatsApp($instituteId, $branchId, $phone, $whatsappEvent, $whatsappParams, $referenceType, $referenceId);
        }
    }

    private function sendEmail(string $email, string $name, string $subject, array $lines, ?string $schoolName): void
    {
        try {
            Mail::to($email)->send(new ApplicantNotificationMail($name, $subject, $lines, $schoolName));
        } catch (\Throwable $e) {
            // A misconfigured mail server should never block the underlying
            // submission/approval from completing.
            Log::warning('ApplicantNotificationService: email failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Queue an SMS by writing directly to sms_logs with an explicit institute
     * scope (see class docblock for why we don't call SmsService::queueLog()).
     * Actual delivery still happens via the existing sms:process-logs
     * scheduled command, unchanged.
     */
    private function queueSms(int $instituteId, ?int $branchId, string $phone, string $message): void
    {
        try {
            $senderId = Institute::find($instituteId)?->owner_id ?: 0;

            SmsLog::create([
                'institute_id' => $instituteId,
                'branch_id' => $branchId ?: 0,
                'receiver' => $phone,
                // 'Applicant' requires the sms_logs.user_type enum migration
                // shipped alongside this service (see database/migrations).
                'user_type' => 'Applicant',
                'message' => $message,
                'sender_id' => $senderId,
                'status' => 0,
            ]);
        } catch (\Throwable $e) {
            Log::warning('ApplicantNotificationService: SMS queue failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Queue a WhatsApp message by resolving the institute's active setting
     * and the template for $event directly (institute-scoped explicitly),
     * mirroring WhatsAppService::queue() without its auth()-dependent lookup.
     */
    private function queueWhatsApp(
        int $instituteId,
        ?int $branchId,
        string $phone,
        string $event,
        array $parameters,
        ?string $referenceType,
        int|string|null $referenceId,
    ): void {
        try {
            if ($referenceType && $referenceId) {
                $alreadyQueued = WhatsAppLog::where('reference_type', $referenceType)
                    ->where('reference_id', $referenceId)
                    ->whereIn('status', ['pending', 'queued', 'sent'])
                    ->exists();

                if ($alreadyQueued) {
                    return;
                }
            }

            $template = WhatsAppTemplate::where('institute_id', $instituteId)
                ->where('event', $event)
                ->where('status', 1)
                ->first();

            // No template configured for this event yet — this is expected
            // until an admin creates one under WhatsApp Templates, and is not
            // an error.
            if (! $template) {
                return;
            }

            $message = $template->message;
            foreach ($parameters as $key => $value) {
                $message = str_replace('{{'.$key.'}}', (string) $value, $message);
            }

            WhatsAppLog::create([
                'institute_id' => $instituteId,
                'branch_id' => $branchId,
                'student_id' => null,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'phone' => $phone,
                'message' => $message,
                'status' => 'pending',
                'retry_count' => 0,
            ]);
        } catch (\Throwable $e) {
            Log::warning('ApplicantNotificationService: WhatsApp queue failed', ['error' => $e->getMessage()]);
        }
    }
}
