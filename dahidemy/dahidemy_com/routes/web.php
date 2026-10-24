<?php

use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BrandImageController;
use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DomainRequestController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\InstituteController;
use App\Http\Controllers\Admin\OnboardingController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ReferralController;
use App\Http\Controllers\Admin\WithdrawalSettingsController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WebsiteController;
use App\Support\ResourceRegistry;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Marketing website
|--------------------------------------------------------------------------
*/

Route::controller(WebsiteController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/features', 'features')->name('features');
    Route::get('/pricing', 'pricing')->name('pricing');
    Route::get('/faq', 'faq')->name('faq');
    Route::get('/about', 'about')->name('about');

    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'contactStore')->name('contact.store')->middleware('throttle:10,1');

    Route::get('/policy/{type}', 'policy')->name('policy');

    Route::get('/register', 'registerShow')->name('register.show');
    Route::post('/register', 'registerStore')->name('register.store')->middleware('throttle:6,1');
    Route::get('/register/thank-you', 'registerThankYou')->name('register.thank-you');
    Route::get('/register/confirm', 'registerConfirm')->name('register.confirm');

    Route::post('/newsletter', 'newsletter')->name('newsletter.subscribe')->middleware('throttle:10,1');
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::controller(AuthController::class)->group(function () {
    Route::get('/3648/login', 'showLogin')->name('login');
    Route::post('/3648/login', 'login')->name('login.store')->middleware('throttle:6,1');
    Route::post('/logout', 'logout')->name('logout');

    // First-run: provision the default SaaS admin on the backend.
    Route::post('/activate', 'activate')->name('activation.activate')->middleware('throttle:6,1');

    Route::get('/forgot-password', 'showForgot')->name('password.request');
    Route::post('/forgot-password', 'sendReset')->name('password.email')->middleware('throttle:6,1');
});

/*
|--------------------------------------------------------------------------
| SaaS admin panel (bearer-token protected)
|--------------------------------------------------------------------------
*/
Route::middleware('admin.auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Cache — one button, both sides
    |--------------------------------------------------------------------------
    |
    | Clears the panel's cached API responses and the backend's own caches.
    | Writes clear the panel cache automatically, so this is only needed when the
    | backend changed underneath us (a direct DB edit, another admin, a deploy).
    |
    */
    Route::post('cache/clear', [CacheController::class, 'clear'])
        ->middleware('throttle:10,1')
        ->name('cache.clear');

    /*
    |--------------------------------------------------------------------------
    | Database backups — manual trigger + remote listing
    |--------------------------------------------------------------------------
    */
    Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('backups/run', [BackupController::class, 'run'])->name('backups.run');

    /*
    |--------------------------------------------------------------------------
    | Tenant management — institutes & branches
    |--------------------------------------------------------------------------
    */
    Route::post('institutes/{institute}/toggle-status', [InstituteController::class, 'toggleStatus'])
        ->name('institutes.toggle-status');
    Route::post('institutes/{institute}/impersonate', [ImpersonationController::class, 'start'])
        ->middleware('throttle:10,1')
        ->name('institutes.impersonate');
    Route::resource('institutes', InstituteController::class);

    /*
    |--------------------------------------------------------------------------
    | Domain management — school-submitted custom domain requests
    |--------------------------------------------------------------------------
    */
    Route::prefix('domain-requests')->name('domain-requests.')->group(function () {
        Route::get('/', [DomainRequestController::class, 'index'])->name('index');
        Route::post('{id}/approve', [DomainRequestController::class, 'approve'])->name('approve');
        Route::post('{id}/reject', [DomainRequestController::class, 'reject'])->name('reject');
    });

    /*
    |--------------------------------------------------------------------------
    | Contact inbox — messages from the public site's contact form
    |--------------------------------------------------------------------------
    |
    | Read-only: the backend exposes a list endpoint and nothing else, so there
    | is no show/seen/delete route to hang here.
    |
    */
    Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');

    /*
    |--------------------------------------------------------------------------
    | Onboarding queue — review & approve pending institutes
    |--------------------------------------------------------------------------
    */
    Route::get('onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('onboarding/{id}/approve', [OnboardingController::class, 'approve'])->name('onboarding.approve');

    /*
    |--------------------------------------------------------------------------
    | Subscriptions — upgrade requests & manual upgrades
    |--------------------------------------------------------------------------
    */
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('requests', [SubscriptionController::class, 'requests'])->name('requests');
        Route::post('requests/{id}/approve', [SubscriptionController::class, 'approveRequest'])->name('requests.approve');
        Route::get('upgrade', [SubscriptionController::class, 'create'])->name('upgrade');
        Route::post('upgrade', [SubscriptionController::class, 'store'])->name('upgrade.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Referrals — institutes AND any individual user role, reviewed/approved
    |--------------------------------------------------------------------------
    */
    Route::prefix('referrals')->name('referrals.')->group(function () {
        Route::get('/', [ReferralController::class, 'index'])->name('index');
        Route::post('{id}/approve', [ReferralController::class, 'approve'])->name('approve');
        Route::post('{id}/reject', [ReferralController::class, 'reject'])->name('reject');

        Route::get('settings', [ReferralController::class, 'settings'])->name('settings');
        Route::post('settings', [ReferralController::class, 'storeSetting'])->name('settings.store');
        Route::put('settings/{id}', [ReferralController::class, 'updateSetting'])->name('settings.update');
        Route::delete('settings/{id}', [ReferralController::class, 'destroySetting'])->name('settings.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Withdrawal settings — platform-wide limits/methods for cash_withdrawal rewards
    |--------------------------------------------------------------------------
    */
    Route::prefix('withdrawal-settings')->name('withdrawal-settings.')->group(function () {
        Route::get('/', [WithdrawalSettingsController::class, 'edit'])->name('edit');
        Route::put('/', [WithdrawalSettingsController::class, 'update'])->name('update');
    });

    /*
    |--------------------------------------------------------------------------
    | Payment gateways — SaaS credentials & status
    |--------------------------------------------------------------------------
    */
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentGatewayController::class, 'index'])->name('index');
        Route::get('{id}/edit', [PaymentGatewayController::class, 'edit'])->name('edit');
        Route::put('{id}', [PaymentGatewayController::class, 'update'])->name('update');
        Route::post('{id}/toggle-status', [PaymentGatewayController::class, 'toggleStatus'])->name('toggle-status');
    });

    /*
    |--------------------------------------------------------------------------
    | Logos & branding — institute image SaaS settings
    |--------------------------------------------------------------------------
    |
    | The single source of every logo in the system: admin sidebar, landing
    | navbar and footer (light + dark variants) plus the banner.
    |
    */
    Route::get('branding', [BrandImageController::class, 'index'])->name('branding.index');
    Route::post('branding', [BrandImageController::class, 'update'])->name('branding.update');

    /*
    |--------------------------------------------------------------------------
    | Settings — SaaS key/value settings & mail configuration
    |--------------------------------------------------------------------------
    |
    | Logos are not managed here: every logo slot lives on the branding page
    | above, which owns that contract end to end.
    |
    */
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->name('update');
        Route::get('mail', [SettingsController::class, 'mail'])->name('mail');
        Route::post('mail', [SettingsController::class, 'updateMail'])->name('mail.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Website content (CMS) — config-driven CRUD
    |--------------------------------------------------------------------------
    |
    | A single generic controller manages every module declared in
    | config/admin_resources.php (banners, cores, solutions, ...). The
    | {resource} segment is constrained to registered keys so unknown slugs
    | 404 at the router instead of hitting the controller.
    |
    */
    Route::prefix('content')->name('content.')
        ->where(['resource' => implode('|', ResourceRegistry::keys())])
        ->group(function () {
            Route::get('{resource}', [ResourceController::class, 'index'])->name('index');
            Route::get('{resource}/create', [ResourceController::class, 'create'])->name('create');
            Route::post('{resource}', [ResourceController::class, 'store'])->name('store');
            Route::get('{resource}/{id}/edit', [ResourceController::class, 'edit'])->name('edit');
            Route::put('{resource}/{id}', [ResourceController::class, 'update'])->name('update');
            Route::delete('{resource}/{id}', [ResourceController::class, 'destroy'])->name('destroy');
        });
});

/*
|--------------------------------------------------------------------------
| SEO
|--------------------------------------------------------------------------
*/
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');