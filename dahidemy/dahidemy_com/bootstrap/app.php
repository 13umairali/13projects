<?php

use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Middleware\EnsureBackendApiConfigured;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Registered after web.php so the installer's own routes exist even
            // though EnsureBackendApiConfigured (below) blocks everything else.
            Route::middleware('web')->group(base_path('routes/setup.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => EnsureAdminAuthenticated::class,
        ]);

        // Appended to the web group, so it runs after session start and can
        // therefore redirect with flash state. The panel is an API client with
        // nothing to show until the backend URL is set, so this gate covers
        // every web route rather than being opted into per route.
        $middleware->web(append: [
            EnsureBackendApiConfigured::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // This replaces Laravel's default (expectsJson), so any path that needs
        // JSON errors must be named here. setup/* is the wizard's test + save
        // endpoints: they are called with fetch() and answer JSON, so a
        // validation failure has to be 422 JSON — the default HTML branch would
        // redirect the XHR back to '/' and the wizard would report nothing.
        // GET /setup itself is excluded, so the page keeps its HTML error view.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->is('setup/*'),
        );
    })->create();
