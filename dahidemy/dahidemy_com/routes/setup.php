<?php

use App\Http\Controllers\Setup\SetupController;
use App\Http\Middleware\RedirectIfBackendApiConfigured;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| First-run setup wizard
|--------------------------------------------------------------------------
|
| Captures the backend API URL and writes it to .env. Kept in its own file
| because these are the only routes that must work while the panel is
| unconfigured — every other route is blocked by EnsureBackendApiConfigured
| until this wizard has run.
|
| The whole group is sealed by RedirectIfBackendApiConfigured once installed,
| so an unauthenticated stranger cannot repoint a live panel at their own
| backend and harvest the logins typed into it.
|
| Throttled because these endpoints are necessarily public and the test probe
| makes this server issue outbound HTTP to an attacker-chosen host — an
| unlimited version is a port scanner with our IP on it.
|
*/

Route::middleware(RedirectIfBackendApiConfigured::class)
    ->prefix('setup')
    ->name('setup.')
    ->group(function () {
        Route::get('/', [SetupController::class, 'show'])->name('show');

        Route::post('/test', [SetupController::class, 'test'])
            ->middleware('throttle:20,1')
            ->name('test');

        Route::post('/save', [SetupController::class, 'save'])
            ->middleware('throttle:10,1')
            ->name('save');
    });
