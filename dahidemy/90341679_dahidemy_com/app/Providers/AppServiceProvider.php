<?php

namespace App\Providers;

use App\Helpers\InstituteHelper;
use App\Observers\BranchObserver;
use App\Observers\InstituteObserver;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Modules\Authentication\Models\Branch;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\InstituteImageSetting;
use Modules\Authentication\Models\User;
use Modules\Elearning\Models\Lesson;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Every institute and every user (any role) gets a unique referral
        // code the moment they're created - see UserObserver/
        // InstituteObserver for why this is an observer rather than
        // hooked into every individual "create X" controller.
        User::observe(UserObserver::class);
        Institute::observe(InstituteObserver::class);
        Branch::observe(BranchObserver::class);

        Relation::morphMap([
            'lesson' => Lesson::class,
        ]);

        Passport::personalAccessTokensExpireIn(now()->addMonths(1));

        // Ensure database is configured before running queries
        try {
            if (DB::connection()->getPdo()) {
                // Register factory namespace for the modular structure
                Factory::guessFactoryNamesUsing(function (string $modelName) {
                    return 'Modules\\Authentication\\Database\\Factories\\'.class_basename($modelName).'Factory';
                });

                // Share the acting institute's branding/settings with every view. Public
                // frontend requests have no authenticated tenant context, so resolve the
                // active institute from the current host first and fall back to the logged-in
                // institute only when that is available.
                view()->composer('*', function ($view): void {
                    $request = app('request');
                    $instituteId = null;

                    if (Auth::check()) {
                        $instituteId = get_institute_id();
                    }

                    if (! $instituteId && $request) {
                        $institute = InstituteHelper::resolveInstituteFromRequest($request);
                        $instituteId = $institute?->id;
                    }

                    $instituteImage = null;
                    $settings = collect();

                    if ($instituteId && Schema::hasTable('institute_image_settings')) {
                        $instituteImage = InstituteImageSetting::query()
                            ->where('institute_id', $instituteId)
                            ->latest()
                            ->first();
                    }

                    if ($instituteId && Schema::hasTable('settings')) {
                        $settings = DB::table('settings')
                            ->where('institute_id', $instituteId)
                            ->pluck('value', 'name');
                    }

                    $view->with(compact('instituteImage', 'settings'));
                });
            }
        } catch (\Exception $e) {
            // ❌ Prevent crash when DB is not set up
        }

        Schema::defaultStringLength(191);
    }
}