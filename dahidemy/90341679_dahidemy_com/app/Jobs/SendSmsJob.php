<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Academic\Models\SmsLog;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $smsLog;

    public function __construct(SmsLog $smsLog)
    {
        $this->smsLog = $smsLog;
    }

    public function handle()
    {
        // sent_sms() always returns an array (even on failure), so checking
        // truthiness of $response alone always marked the log as "sent".
        $response = sent_sms($this->smsLog?->receiver, $this->smsLog?->message);

        if (! empty($response['success'])) {
            $this->smsLog->status = 1;
            $this->smsLog->save();
        }
    }
}
