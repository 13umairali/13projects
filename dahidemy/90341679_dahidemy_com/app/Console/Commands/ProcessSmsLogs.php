<?php

namespace App\Console\Commands;

use App\Jobs\SendSmsJob;
use Illuminate\Console\Command;
use Modules\Academic\Models\SmsLog;

class ProcessSmsLogs extends Command
{
    protected $signature = 'sms:process-logs';

    protected $description = 'Process unsent SMS logs';

    public function handle()
    {
        $smsLogs = SmsLog::where('status', 0)->get();

        foreach ($smsLogs as $smsLog) {
            dispatch(new SendSmsJob($smsLog));
        }

        $this->info('SMS logs processed successfully.');
    }
}
