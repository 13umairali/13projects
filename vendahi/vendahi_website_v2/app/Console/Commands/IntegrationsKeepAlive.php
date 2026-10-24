<?php

namespace App\Console\Commands;

use App\Models\SallaSetting;
use App\Models\XeroSetting;
use Illuminate\Console\Command;

/**
 * Keeps OAuth-based integration connections alive for every tenant.
 *
 * Why: refresh tokens expire on idle — Salla's after 1 month, Xero's after 60
 * days. A tenant who doesn't sync for a while would lose the connection
 * silently. One authenticated ping per connector per day refreshes tokens
 * through the connectors' own clients (which serialize refreshes behind
 * locks), so the expiry clock keeps resetting. Google Sheets refresh tokens
 * do not expire on idle and need no ping.
 */
class IntegrationsKeepAlive extends Command
{
    protected $signature = 'integrations:keep-alive';

    protected $description = 'Ping connected Salla/Xero accounts on every tenant so their OAuth refresh tokens never expire on idle';

    public function handle(): int
    {
        foreach (\App\Tenant::all() as $tenant) {
            try {
                tenancy()->initialize($tenant);
                $this->pingSalla($tenant->id);
                $this->pingXero($tenant->id);
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

    private function pingSalla(string $tenantId): void
    {
        try {
            $settings = SallaSetting::query()->first();
            if (! $settings || ! $settings->enabled || ! $settings->isConnected()) {
                return;
            }

            $result = \App\Services\Salla\SyncService::make($settings)->testConnection();
            $this->line("tenant {$tenantId}: salla ping ".(($result['ok'] ?? false) ? 'ok' : 'FAILED — '.($result['error'] ?? '?')));
            if (! ($result['ok'] ?? false)) {
                \App\Services\Salla\SyncService::make($settings)->log('keep_alive', 'warning', 'Keep-alive ping failed: '.($result['error'] ?? 'unknown'));
            }
        } catch (\Throwable $e) {
            $this->warn("tenant {$tenantId}: salla ping error — ".$e->getMessage());
        }
    }

    private function pingXero(string $tenantId): void
    {
        try {
            $settings = XeroSetting::query()->first();
            if (! $settings || ! $settings->enabled || ! $settings->isConnected()) {
                return;
            }

            $result = \App\Services\Xero\SyncService::make($settings)->testConnection();
            $this->line("tenant {$tenantId}: xero ping ".(($result['ok'] ?? false) ? 'ok' : 'FAILED — '.($result['error'] ?? '?')));
            if (! ($result['ok'] ?? false)) {
                \App\Services\Xero\SyncService::make($settings)->log('keep_alive', 'warning', 'Keep-alive ping failed: '.($result['error'] ?? 'unknown'));
            }
        } catch (\Throwable $e) {
            $this->warn("tenant {$tenantId}: xero ping error — ".$e->getMessage());
        }
    }
}
