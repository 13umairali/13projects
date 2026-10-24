<?php

namespace App\Providers;

use App\Services\Api\AdminResourceService;
use App\Services\Api\AuthenticationService;
use App\Services\Api\BrandImageService;
use App\Services\Api\ContactService;
use App\Services\Api\InstituteService;
use App\Services\Api\OnboardingService;
use App\Services\Api\PaymentGatewayService;
use App\Services\Api\SaasAdminService;
use App\Services\Api\SaasContentService;
use App\Services\Api\SettingsService;
use App\Services\Api\SubscriptionService;
use App\Services\Setup\BackendConnectionTester;
use App\Services\Setup\EnvironmentService;
use App\Services\Setup\SetupManager;
use App\Support\ApiCache;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Boots the SaaS panel: binds the API service layer and shares global
 * marketing branding (name, logo, contact, social links, navigation) with
 * every landing view so individual controllers never have to re-fetch it.
 */
class SaasServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Singleton so its per-request memo (duplicate-call guard) lives exactly
        // as long as the request that fills it.
        $this->app->singleton(ApiCache::class);

        $this->app->singleton(SaasContentService::class);
        $this->app->singleton(AuthenticationService::class);
        $this->app->singleton(ContactService::class);
        $this->app->singleton(AdminResourceService::class);
        $this->app->singleton(SaasAdminService::class);
        $this->app->singleton(InstituteService::class);
        $this->app->singleton(OnboardingService::class);
        $this->app->singleton(SubscriptionService::class);
        $this->app->singleton(PaymentGatewayService::class);
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(BrandImageService::class);

        $this->registerSetupServices();
    }

    /**
     * First-run installer (App\Services\Setup). Bound here rather than resolved
     * automatically because EnvironmentService needs the .env path injected —
     * which also lets tests point it at a temporary file instead of the real one.
     */
    protected function registerSetupServices(): void
    {
        $this->app->singleton(
            EnvironmentService::class,
            fn ($app) => new EnvironmentService($app->environmentFilePath())
        );

        $this->app->singleton(BackendConnectionTester::class);
        $this->app->singleton(SetupManager::class);
    }

    public function boot(): void
    {
        $this->shareBranding();
        $this->registerBladeHelpers();
    }

    /**
     * Globally share $brand, $socials, $nav and $authed so they are available
     * in every view AND every Blade component — including child @section
     * content, which view composers bound to a layout do not reach. Content is
     * cached + fallback-guarded, so this is cheap and never throws.
     */
    protected function shareBranding(): void
    {
        // Skip API work in the console (migrations, queue workers, tests), and
        // before installation — with no backend URL configured there is nothing
        // to fetch, and asking anyway would make every setup-wizard page wait
        // out a connect timeout against a host that does not exist.
        if ($this->app->runningInConsole() || ! config('saas.api.configured')) {
            View::share('brand', config('saas.brand'));
            View::share('socials', array_filter(config('saas.brand.socials'), fn ($v) => $v && $v !== '#'));
            View::share('nav', $this->marketingNav());
            View::share('authed', false);

            return;
        }

        $branding = app(SaasContentService::class)->branding();

        View::share('brand', $branding);
        View::share('socials', array_filter($branding['socials'], fn ($v) => $v && $v !== '#'));
        View::share('nav', $this->marketingNav());
        View::share('authed', app(AuthenticationService::class)->check());
    }

    protected function registerBladeHelpers(): void
    {
        // {{ setting('site_name', 'Fallback') }} style access in any view.
        Blade::directive('setting', function ($expression) {
            return "<?php echo e(app(\\App\\Services\\Api\\SaasContentService::class)->setting($expression)); ?>";
        });
    }

    /**
     * Primary marketing navigation. Kept here so it is defined once and shared
     * everywhere; swap for a CMS-driven menu later without touching views.
     */
    protected function marketingNav(): array
    {
        return [
            ['label' => 'Features', 'route' => 'features'],
            ['label' => 'Pricing', 'route' => 'pricing'],
            ['label' => 'Solutions', 'route' => 'features', 'fragment' => 'solutions'],
            ['label' => 'FAQ', 'route' => 'faq'],
            ['label' => 'About', 'route' => 'about'],
            ['label' => 'Contact', 'route' => 'contact'],
        ];
    }
}
