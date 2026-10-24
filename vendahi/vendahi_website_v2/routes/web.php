<?php

use App\Http\Controllers\Admin\StoreSettingsController as AdminStoreSettings;
use App\Http\Controllers\Api\Store\AccountPagesController;
use App\Http\Controllers\Api\Store\CheckoutController;
use App\Http\Controllers\Api\Store\MessageController;
use App\Http\Controllers\Api\Store\CustomerReturnsController;
use App\Http\Controllers\Api\Store\CustomerReviewsController;
use App\Http\Controllers\Api\Store\MyOrdersApiController;
use App\Http\Controllers\Api\Store\QuoteRequestsController;
use App\Http\Controllers\Api\Store\NewsletterController;
use App\Http\Controllers\QuickBooksController;
use App\Http\Controllers\RealEstateStoreController;
use App\Http\Controllers\StoreAuthController;
use App\Http\Controllers\StoreFrontController;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

// ------------------------------------------------------------------\\
// Passport::routes();

// Login route will be defined explicitly below with middleware

Route::get('password/find/{token}', 'PasswordResetController@find');

// Route::middleware(['web','auth:web','Is_Active'])->group(function () {
//     Route::get('/admin/store/settings', [AdminStoreSettings::class, 'show']);
//     Route::post('/admin/store/settings', [AdminStoreSettings::class, 'update']);
// });

$installed = Storage::disk('public')->exists('installed');

// ------------------------------------------------------------------\\
// ONLINE STORE ROUTES (Only if installed)
if ($installed === true) {

    Route::middleware(['web', 'request.safety', 'store.enabled'])->group(function () {

        Route::prefix('online_store')->group(function () {

            Route::get('/lang/{locale}', function ($locale) {
                $supported = ['en', 'fr', 'es', 'ar'];

                // Use provided locale if supported, otherwise fallback to 'en'
                $chosen = in_array($locale, $supported, true) ? $locale : 'en';

                // Store in session
                session(['locale' => $chosen]);

                // Optionally persist for a year via cookie
                Cookie::queue('locale', $chosen, 60 * 24 * 365, '/');

                return back();
            })->name('lang.switch');

            Route::get('/', [StoreFrontController::class, 'index'])->name('store.index');
            Route::get('/shop', [StoreFrontController::class, 'shop'])->name('store.shop');
            Route::get('/search/suggestions', [StoreFrontController::class, 'searchSuggestions'])->name('store.search.suggestions');
            Route::get('/contact', [StoreFrontController::class, 'contact'])->name('store.contact');
            Route::post('/contact', [StoreFrontController::class, 'sendContact'])->name('store.contact.send');
            Route::post('/store/orders', [CheckoutController::class, 'store'])->name('store.orders.store');
            Route::post('/store/payment-intent', [CheckoutController::class, 'createPaymentIntent'])->name('store.payment.intent');
            Route::post('/store/razorpay-order', [CheckoutController::class, 'createRazorpayOrder'])->name('store.razorpay.order');
            Route::post('/store/xpay-intent', [CheckoutController::class, 'createXpayIntent'])->name('store.xpay.intent');
            Route::get('/collections/{slug}', [StoreFrontController::class, 'collection'])->name('store.collection.show');

            // Flash sales, CMS pages, product reviews (public, approved only) and quote requests
            Route::get('/flash-sales', [StoreFrontController::class, 'flashSales'])->name('store.flash_sales');
            Route::get('/pages/{slug}', [StoreFrontController::class, 'page'])->name('store.page');
            Route::get('/products/{id}/reviews', [CustomerReviewsController::class, 'productReviews'])
                ->whereNumber('id')->name('store.product.reviews');
            Route::post('/quote-request', [QuoteRequestsController::class, 'store'])
                ->middleware('throttle:8,1')->name('store.quote.request');
            // Live checkout totals: shipping methods for the region, coupon, tax
            Route::post('/store/checkout-quote', [CheckoutController::class, 'quote'])->name('store.checkout.quote');

            // Real Estate theme storefront (active when StoreSetting->theme = real_estate)
            Route::get('/properties', [RealEstateStoreController::class, 'listings'])->name('store.realestate.listings');
            Route::post('/properties/inquiry', [RealEstateStoreController::class, 'inquiry'])->name('store.realestate.inquiry');
            Route::get('/properties/{slug}', [RealEstateStoreController::class, 'show'])->name('store.realestate.show');

            Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
            Route::post('/contact/send', [MessageController::class, 'store'])->name('store.contact.send');

            // Account pages (require login on 'store' guard)
            Route::middleware(['web', 'auth:store'])->group(function () {
                Route::view('/checkout', 'store.checkout')->name('checkout');
                Route::view('/thank-you', 'store.thank-you')->name('store.thankyou');

                Route::get('/account', [AccountPagesController::class, 'account'])->name('account');

                Route::put('/account', [AccountPagesController::class, 'update'])->name('account.update');

                Route::get('/account/orders', [AccountPagesController::class, 'orders'])
                    ->name('account.orders');

                Route::get('/account/orders/{id}', function ($id) {
                    // $s is likely shared via view composer; if not, fetch StoreSetting here
                    return view('store.order-show', ['id' => $id]);
                })->name('account.order.show');

                // Customer's own orders (JSON for the account orders table)
                Route::get('/my/orders', [MyOrdersApiController::class, 'index'])
                    ->name('my_orders.index');
                // (Optional) details endpoint if you add a “view” drawer/page:
                Route::get('/my/orders/{id}', [MyOrdersApiController::class, 'show'])
                    ->name('my_orders.show');

                // Offline payments: the shopper's proof of a GCash / bank transfer.
                Route::get('/my/orders/{id}/payment-proofs', [\App\Http\Controllers\Api\Store\PaymentProofController::class, 'index'])
                    ->name('my_orders.proofs.index');
                Route::post('/my/orders/{id}/payment-proofs', [\App\Http\Controllers\Api\Store\PaymentProofController::class, 'store'])
                    ->name('my_orders.proofs.store');

                // Address book (shipping zones + tax rates resolve from it)
                Route::put('/account/address', [AccountPagesController::class, 'updateAddress'])->name('account.address.update');

                // Returns / cancellations (customer requests only; refunds are admin-approved)
                Route::get('/account/orders/{id}/return-eligibility', [CustomerReturnsController::class, 'eligibility'])
                    ->name('account.order.return.eligibility');
                Route::post('/account/orders/{id}/cancel', [CustomerReturnsController::class, 'cancel'])
                    ->name('account.order.cancel');
                Route::post('/account/orders/{id}/return', [CustomerReturnsController::class, 'requestReturn'])
                    ->name('account.order.return');
                Route::view('/account/returns', 'store.account-returns')->name('account.returns');
                Route::get('/my/returns', [CustomerReturnsController::class, 'myReturns'])
                    ->name('my_returns.index');

                // Product reviews (verified purchase)
                Route::get('/account/orders/{id}/reviewable', [CustomerReviewsController::class, 'reviewable'])
                    ->name('account.order.reviewable');
                Route::post('/account/orders/{id}/review', [CustomerReviewsController::class, 'submit'])
                    ->name('account.order.review');
            });

            // Auth pages (only for guests of 'store' guard)
            Route::middleware('guest:store')->group(function () {
                Route::get('/login', [StoreAuthController::class, 'showLogin'])->name('store.login.show');
                Route::post('/login', [StoreAuthController::class, 'login'])->name('store.login');

                Route::get('/register', [StoreAuthController::class, 'showRegister'])->name('store.register.show');
                Route::post('/register', [StoreAuthController::class, 'register'])->name('store.register');

            });

            // Logout (must be logged in on 'store')
            Route::post('/logout', [StoreAuthController::class, 'logout'])
                ->middleware('auth:store')->name('store.logout');

        });
    });

} else {
    // if not installed: redirect all /online_store requests to /setup
    Route::any('/online_store/{any?}', function () {
        return redirect('/setup');
    })->where('any', '.*');
}

// ------------------------------------------------------------------\\

$installed = Storage::disk('public')->exists('installed');

if ($installed === false) {
    Route::get('/setup', [
        'uses' => 'SetupController@viewCheck',
    ])->name('setup');

    Route::get('/setup/step-1', [
        'uses' => 'SetupController@viewStep1',
    ]);

    Route::post('/setup/step-2', [
        'as' => 'setupStep1', 'uses' => 'SetupController@setupStep1',
    ]);

    Route::post('/setup/testDB', [
        'as' => 'testDB', 'uses' => 'TestDbController@testDB',
    ]);

    Route::get('/setup/step-2', [
        'uses' => 'SetupController@viewStep2',
    ]);

    Route::get('/setup/step-3', [
        'uses' => 'SetupController@viewStep3',
    ]);

    Route::get('/setup/finish', function () {

        return view('setup.finishedSetup');
    });

    Route::get('/setup/getNewAppKey', [
        'as' => 'getNewAppKey', 'uses' => 'SetupController@getNewAppKey',
    ]);

    Route::get('/setup/getPassport', [
        'as' => 'getPassport', 'uses' => 'SetupController@getPassport',
    ]);

    Route::get('/setup/getMegrate', [
        'as' => 'getMegrate', 'uses' => 'SetupController@getMegrate',
    ]);

    Route::post('/setup/step-3', [
        'as' => 'setupStep2', 'uses' => 'SetupController@setupStep2',
    ]);

    Route::post('/setup/step-4', [
        'as' => 'setupStep3', 'uses' => 'SetupController@setupStep3',
    ]);

    Route::post('/setup/step-5', [
        'as' => 'setupStep4', 'uses' => 'SetupController@setupStep4',
    ]);

    Route::post('/setup/lastStep', [
        'as' => 'lastStep', 'uses' => 'SetupController@lastStep',
    ]);

    Route::get('setup/lastStep', function () {
        return redirect('/setup', 301);
    });

} else {
    Route::any('/setup/{vue}', function () {
        abort(403);
    });
}

Route::group(['middleware' => ['web', 'auth:web', 'Is_Active']], function () {

    // QuickBooks OAuth + status
    Route::get('/quickbooks/connect', [QuickBooksController::class, 'connect'])->name('quickbooks.connect');
    Route::get('/quickbooks/callback', [QuickBooksController::class, 'callback'])->name('quickbooks.callback');

    // Salla OAuth
    Route::get('/salla/connect', [\App\Http\Controllers\Integrations\SallaOAuthController::class, 'connect'])->name('salla.connect');
    Route::get('/salla/callback', [\App\Http\Controllers\Integrations\SallaOAuthController::class, 'callback'])->name('salla.callback');

    // Xero OAuth
    Route::get('/xero/connect', [\App\Http\Controllers\Integrations\XeroOAuthController::class, 'connect'])->name('xero.connect');
    Route::get('/xero/callback', [\App\Http\Controllers\Integrations\XeroOAuthController::class, 'callback'])->name('xero.callback');

    // Google Sheets OAuth
    Route::get('/google-sheets/connect', [\App\Http\Controllers\Integrations\GoogleSheetsOAuthController::class, 'connect'])->name('google_sheets.connect');
    Route::get('/google-sheets/callback', [\App\Http\Controllers\Integrations\GoogleSheetsOAuthController::class, 'callback'])->name('google_sheets.callback');

    // Google Calendar OAuth (for booking events)
    Route::get('/google-calendar/connect', [\App\Http\Controllers\GoogleCalendarConnectController::class, 'connect'])->name('google_calendar.connect');
    Route::get('/google-calendar/callback', [\App\Http\Controllers\GoogleCalendarConnectController::class, 'callback'])->name('google_calendar.callback');
    Route::get('/google-calendar/disconnect', [\App\Http\Controllers\GoogleCalendarConnectController::class, 'disconnect'])->name('google_calendar.disconnect');
});

// ------------------------------------------------------------------\\
// Client Portal - if no portal auth, send directly to login (no Vue app load)
Route::get('/portal/{vue?}', function (\Illuminate\Http\Request $request, $vue = null) {
    $installed = Storage::disk('public')->exists('installed');
    if ($installed === false) {
        return redirect('/setup');
    }
    $guestSegments = ['login', 'set-password'];
    $isGuestPath = in_array($vue, $guestSegments, true);
    if (! \Illuminate\Support\Facades\Auth::guard('portal')->check() && ! $isGuestPath) {
        return redirect('/portal/login');
    }
    return view('portal');
})->where('vue', '.*')->middleware(['web']);

// ------------------------------------------------------------------\\

Route::group(['middleware' => ['web', 'auth:web', 'Is_Active', 'request.safety']], function () {

    Route::get('/login', function () {
        $installed = Storage::disk('public')->exists('installed');
        if ($installed === false) {
            return redirect('/setup');
        } else {
            return redirect('/login');
        }
    });

    Route::get('/{vue?}',
        function () {
            $installed = Storage::disk('public')->exists('installed');

            if ($installed === false) {
                return redirect('/setup');
            } else {
                return view('layouts.master');
            }
        })->where('vue', '^(?!api|setup|update|password|online_store|customer-display|quickbooks|salla|xero|google-sheets|portal).*$');

});

// Laravel 12 compatibility: define auth routes explicitly (laravel/ui optional)
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Password Reset Routes...
Route::get('password/reset', 'Auth\\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\\ResetPasswordController@reset')->name('password.update');

// Email Verification Routes...
Route::get('email/verify', 'Auth\\VerificationController@show')->name('verification.notice');
Route::get('email/verify/{id}/{hash}', 'Auth\\VerificationController@verify')->name('verification.verify');
Route::post('email/resend', 'Auth\\VerificationController@resend')->name('verification.resend');

// Update routes removed — now served exclusively from routes/central.php
// behind the auth.central (super admin) middleware.

// -------------------- Public Customer Display (token-guarded) --------------------
// Standalone public page that mounts its own Vue app. Does not affect existing SPA.
Route::get('/customer-display', function (HttpRequest $request) {
    $token = $request->query('token');
    $expected = Cache::store(config('cache.default'))->get('customer_display_token');
    if (! $token || $token !== $expected) {
        abort(403, 'Unauthorized display access');
    }

    return view('customer_display');
})->middleware(['web']);
