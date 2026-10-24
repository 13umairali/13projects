<?php

namespace App\Console\Commands;

use App\Models\GoogleSheetSetting;
use App\Services\GoogleSheets\ExportService;
use Illuminate\Console\Command;

/**
 * Nightly Google Sheets snapshot for every tenant that opted in
 * (auto_export on, account connected, spreadsheet chosen). Each tab is
 * rewritten in full — same result as pressing every Export button.
 */
class GoogleSheetsAutoExport extends Command
{
    protected $signature = 'google-sheets:auto-export';

    protected $description = 'Run the nightly Google Sheets export for every tenant with auto-export enabled';

    public function handle(): int
    {
        foreach (\App\Tenant::all() as $tenant) {
            try {
                tenancy()->initialize($tenant);

                $settings = GoogleSheetSetting::query()->first();
                if (
                    ! $settings || ! $settings->enabled || ! $settings->auto_export
                    || ! $settings->isConnected() || empty($settings->spreadsheet_id)
                ) {
                    continue;
                }

                $service = ExportService::make($settings);
                $summary = $service->exportAllTabs();
                $line = collect($summary)->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ');
                $failed = (bool) preg_grep('/^failed:/', $summary);
                $this->line("tenant {$tenant->id}: {$line}");
                $service->log('auto_export.run', $failed ? 'warning' : 'info', 'Nightly export: '.$line);
            } catch (\Throwable $e) {
                $this->warn("tenant {$tenant->id}: ".$e->getMessage());
                report($e);
            } finally {
                try {
                    tenancy()->end();
                } catch (\Throwable $e) {
                    // Nothing to clean up.
                }
            }
        }

        return self::SUCCESS;
    }
}
