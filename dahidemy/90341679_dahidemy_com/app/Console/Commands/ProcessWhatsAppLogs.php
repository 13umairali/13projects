<?php

namespace App\Console\Commands;

use App\Jobs\SendWhatsAppJob;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Modules\Academic\Models\WhatsAppLog;

class ProcessWhatsAppLogs extends Command
{
    protected $signature = 'whatsapp:process-logs';

    protected $description = 'Dispatch pending WhatsApp logs and re-queue failed logs that have not exceeded the retry limit';

    public function handle(): int
    {
        WhatsAppLog::where('status', 'failed')
            ->where('retry_count', '<', WhatsAppService::MAX_RETRIES)
            ->update(['status' => 'pending']);

        $pending = WhatsAppLog::where('status', 'pending')->get();

        foreach ($pending as $log) {
            $log->update(['status' => 'queued']);
            dispatch(new SendWhatsAppJob($log));
        }

        $this->info('WhatsApp logs processed: '.$pending->count());

        return self::SUCCESS;
    }
}
