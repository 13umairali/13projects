<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Academic\Models\WhatsAppLog;
use Modules\Academic\Models\WhatsAppTemplate;
use Twilio\Rest\Client as TwilioClient;

class WhatsAppService
{
    public const MAX_RETRIES = 5;

    /**
     * Render the template for $event, and queue a pending WhatsAppLog for later sending.
     * Returns null (and creates nothing) if the module is disabled, the template is missing,
     * or an identical notification for this reference was already queued/sent.
     */
    public function queue(
        string $phone,
        string $event,
        array $parameters = [],
        $studentId = null,
        ?string $referenceType = null,
        $referenceId = null
    ): ?WhatsAppLog {
        if (! $phone) {
            return null;
        }

        $setting = whatsappSetting();
        if (! $setting || ! $setting->status) {
            return null;
        }

        if ($referenceType && $referenceId) {
            $alreadyQueued = WhatsAppLog::where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->whereIn('status', ['pending', 'queued', 'sent'])
                ->exists();

            if ($alreadyQueued) {
                return null;
            }
        }

        $template = WhatsAppTemplate::where('institute_id', get_institute_id())
            ->where('event', $event)
            ->where('status', 1)
            ->first();

        if (! $template) {
            Log::warning('WhatsApp template not found for event: '.$event);

            return null;
        }

        $message = $template->message;
        foreach ($parameters as $key => $value) {
            $message = str_replace('{{'.$key.'}}', (string) $value, $message);
        }

        return WhatsAppLog::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'student_id' => $studentId,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'phone' => $phone,
            'message' => $message,
            'status' => 'pending',
            'retry_count' => 0,
        ]);
    }

    /**
     * Actually deliver a queued WhatsAppLog via the configured provider.
     */
    public function send(WhatsAppLog $log): bool
    {
        $setting = whatsappSetting();
        if (! $setting || ! $setting->status) {
            $log->update(['status' => 'failed', 'response' => 'WhatsApp module disabled or not configured.']);

            return false;
        }

        $result = match ($setting->provider) {
            'twilio' => $this->sendViaTwilio($setting, $log),
            default => $this->sendViaMeta($setting, $log),
        };

        if ($result['success']) {
            $log->update([
                'status' => 'sent',
                'response' => json_encode($result['response']),
            ]);

            return true;
        }

        $log->update([
            'status' => 'failed',
            'response' => json_encode($result['response']),
            'retry_count' => $log->retry_count + 1,
        ]);

        return false;
    }

    /**
     * Test the currently configured provider's credentials without sending a message.
     */
    public function testConnection(): array
    {
        $setting = whatsappSetting();
        if (! $setting) {
            return ['success' => false, 'message' => 'No active WhatsApp setting found.'];
        }

        try {
            if ($setting->provider === 'twilio') {
                $client = new TwilioClient($setting->business_account_id, $setting->access_token);
                $client->api->v2010->account->fetch();

                return ['success' => true, 'message' => 'Twilio credentials verified successfully.'];
            }

            $response = Http::withToken($setting->access_token)
                ->get('https://graph.facebook.com/v21.0/'.$setting->phone_number_id, [
                    'fields' => 'verified_name,display_phone_number',
                ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Meta WhatsApp Cloud API connection verified successfully.'];
            }

            return ['success' => false, 'message' => 'Connection failed: '.$response->body()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendViaMeta($setting, WhatsAppLog $log): array
    {
        $response = Http::withToken($setting->access_token)
            ->post('https://graph.facebook.com/v21.0/'.$setting->phone_number_id.'/messages', [
                'messaging_product' => 'whatsapp',
                'to' => $log->phone,
                'type' => 'text',
                'text' => ['body' => $log->message],
            ]);

        $data = $response->json();
        if (! $response->successful()) {
            Log::error('WhatsApp (Meta) send failed', ['status' => $response->status(), 'response' => $data]);
        }

        return [
            'success' => $response->successful(),
            'response' => ['http_code' => $response->status(), 'data' => $data],
        ];
    }

    private function sendViaTwilio($setting, WhatsAppLog $log): array
    {
        try {
            $client = new TwilioClient($setting->business_account_id, $setting->access_token);
            $message = $client->messages->create(
                'whatsapp:'.$log->phone,
                [
                    'from' => 'whatsapp:'.$setting->phone_number_id,
                    'body' => $log->message,
                ]
            );

            return [
                'success' => true,
                'response' => ['sid' => $message->sid, 'status' => $message->status],
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp (Twilio) send failed', ['message' => $e->getMessage()]);

            return ['success' => false, 'response' => ['error' => $e->getMessage()]];
        }
    }
}
