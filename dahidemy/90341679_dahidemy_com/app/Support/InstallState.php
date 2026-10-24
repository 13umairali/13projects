<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Single source of truth for "is this application already installed?".
 *
 * Previously the ONLY signal was the file storage/mightySchool. If a deployment or clean-up
 * touched storage/ the file vanished, every page redirected to /install, and the (unauthenticated)
 * installer - which runs db:wipe - became reachable on a live system.
 *
 * Now the application also counts as installed when the database already contains users.
 * If that is the case and the marker is missing, the marker is restored.
 */
class InstallState
{
    public static function marker(): string
    {
        return storage_path('mightySchool');
    }

    public static function installed(): bool
    {
        if (file_exists(self::marker())) {
            return true;
        }

        try {
            if (Schema::hasTable('users') && DB::table('users')->exists()) {
                Log::warning('Install marker was missing but users exist - treating app as installed and restoring marker.');
                @file_put_contents(self::marker(), 'Welcome to  Software');

                return true;
            }
        } catch (\Throwable $e) {
            // No database yet (fresh install) - not installed.
        }

        return false;
    }
}
