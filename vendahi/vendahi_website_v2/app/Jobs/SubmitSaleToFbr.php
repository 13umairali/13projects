<?php

namespace App\Jobs;

use App\Services\Fbr\FbrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Reports a Sale to the Pakistan FBR Digital Invoicing gateway after
 * creation. Runs inline when QUEUE_CONNECTION=sync; the guards inside
 * processQuietly() make it a no-op for tenants that have not enabled the
 * FBR integration.
 */
class SubmitSaleToFbr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    /**
     * @param string $sourceClass App\Models\Sale
     */
    public function __construct(public string $sourceClass, public int $sourceId)
    {
    }

    public function handle(FbrService $service): void
    {
        if (! class_exists($this->sourceClass)) {
            return;
        }

        $source = $this->sourceClass::query()
            ->where('deleted_at', '=', null)
            ->find($this->sourceId);

        if ($source) {
            $service->processQuietly($source);
        }
    }
}
