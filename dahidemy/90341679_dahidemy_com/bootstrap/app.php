<?php

use App\Http\Middleware\CheckInstallation;
use App\Http\Middleware\CheckInstallationStatus;
use App\Http\Middleware\CheckInstituteActive;
use App\Http\Middleware\CheckSubscriptionExpiry;
use App\Http\Middleware\CheckLicense;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\EnforceDemoMode;
use App\Http\Middleware\EnforcePlanLimit;
use App\Http\Middleware\EnsureSaasAdmin;
use App\Http\Middleware\EnsureModuleEnabled;
use App\Http\Middleware\ParentModelClass;
use App\Http\Middleware\RejectUnknownInstituteHost;
use App\Http\Middleware\Student;
use App\Http\Middleware\Teacher;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            if (file_exists(base_path('routes/system.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/system.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            'parent' => ParentModelClass::class,
            'teacher' => Teacher::class,
            'student' => Student::class,
            'checkInstallation' => CheckInstallation::class,
            'checkInstallationStatus' => CheckInstallationStatus::class,
            'check.subscription' => CheckSubscription::class,
            'plan.limit' => EnforcePlanLimit::class,
            'demo.restrict' => EnforceDemoMode::class,
            'module.enabled' => EnsureModuleEnabled::class,
            'saas.admin' => EnsureSaasAdmin::class,
        ]);

        // Reject hosts that are not a registered institute BEFORE any page code runs.
        $middleware->web(prepend: [
            RejectUnknownInstituteHost::class,
        ]);

        $middleware->append([
            HandleCors::class,
            CheckInstituteActive::class,
            CheckSubscriptionExpiry::class,
            // CheckLicense::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('demo:reset')->hourly();
    })->create();