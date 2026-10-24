<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Academic\Models\WhatsAppLog;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected WhatsAppLog $whatsAppLog) {}

    public function handle(WhatsAppService $whatsAppService): void
    {
        $whatsAppService->send($this->whatsAppLog);
    }
}
