<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\CentralLanguage;
use App\Models\Central\GeneralSetting;
use App\Models\Central\Plan;
use App\Services\LandingCmsService;
use App\Support\LandingPageTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandingPageController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->landingDisabledRedirect($request)) {
            return $redirect;
        }

        $view = $this->resolveLandingViewName($request);

        return $this->renderLandingView($view);
    }

    /**
     * When the landing page is disabled in the super settings, the central
     * root redirects to the configured custom URL (validated against loops on
     * save), or to the super login when none is set. Logged-in super admins
     * keep template preview access (?__landing_preview) so the CMS previews
     * still work while the public page is off.
     */
    protected function landingDisabledRedirect(Request $request): ?RedirectResponse
    {
        $setting = GeneralSetting::instance();

        if ($setting->landing_page_enabled ?? true) {
            return null;
        }

        if ($request->has('__landing_preview') && Auth::guard('central')->check()) {
            return null;
        }

        $url = trim((string) ($setting->landing_redirect_url ?? ''));

        return $url !== ''
            ? redirect()->away($url)
            : redirect()->route('central.login');
    }

    protected function resolveLandingViewName(Request $request): string
    {
        $defaultKey = GeneralSetting::resolvedLandingTemplateKey();
        $preview = $request->query('__landing_preview');

        if ($preview === '__default' || $preview === 'current') {
            return LandingPageTemplate::view($defaultKey);
        }

        if (is_string($preview) && LandingPageTemplate::isValid($preview) && $this->canUseLandingPreview($preview, $defaultKey)) {
            return LandingPageTemplate::view($preview);
        }

        return LandingPageTemplate::view($defaultKey);
    }

    protected function canUseLandingPreview(string $previewKey, string $defaultKey): bool
    {
        if (Auth::guard('central')->check()) {
            return true;
        }

        if ($previewKey === $defaultKey) {
            return true;
        }

        if (app()->isLocal()) {
            return true;
        }

        return filter_var(env('LANDING_TEMPLATE_PREVIEW_PUBLIC', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return array<string, mixed>
     */
    protected function landingViewData(): array
    {
        $cms = app(LandingCmsService::class);

        $hero            = $cms->getSection('hero');
        if ($hero && ! $hero->is_active) {
            $hero = null;
        }
        $featuresSection = $cms->getFeaturesSection();
        $featureItems    = $cms->getFeatureItems();
        $pricingSection  = $cms->getSection('pricing');
        $testimonials    = $cms->getTestimonials();
        $faqs            = $cms->getFaqs();
        $stats           = $cms->getStats();
        $howItWorksSection = $cms->getHowItWorksSection();
        $howItWorksSteps   = $cms->getHowItWorksSteps();
        $cta             = $cms->getSection('cta');
        if ($cta && ! $cta->is_active) {
            $cta = null;
        }
        $footer          = $cms->getSection('footer');
        $seo             = $cms->getSection('seo');

        $plans = Plan::public()->orderBy('price')->get();

        $features = [
            'is_active' => $featuresSection->is_active ?? false,
            'section'   => $featuresSection,
            'items'     => $featureItems,
        ];

        $pricing = [
            'is_active' => $pricingSection->is_active ?? true,
            'settings'  => $pricingSection,
            'plans'     => $plans,
        ];

        $howItWorks = [
            'is_active' => $howItWorksSection->is_active ?? true,
            'section'   => $howItWorksSection,
            'steps'     => $howItWorksSteps,
        ];

        $languages = CentralLanguage::active();
        $currentLocale = app()->getLocale();

        // Nav "Blog" link is shown only when there is something to read.
        // rescue(): the landing must survive before central blog migrations run.
        $hasBlogPosts = rescue(fn () => \App\Models\Central\BlogPost::published()->exists(), false, false);

        return compact(
            'hero', 'features', 'pricing', 'howItWorks',
            'testimonials', 'faqs', 'stats', 'cta', 'footer', 'seo', 'plans',
            'languages', 'currentLocale', 'hasBlogPosts'
        );
    }

    protected function renderLandingView(string $view): View
    {
        return view($view, $this->landingViewData());
    }

    public function privacyPolicy(): View
    {
        $cms = app(LandingCmsService::class);
        $privacy = $cms->getSection('privacy_policy');
        $footer  = $cms->getSection('footer');
        $seo     = $cms->getSection('seo');

        if (! $privacy || ! $privacy->is_active) {
            abort(404);
        }

        return view('central.privacy-policy', compact('privacy', 'footer', 'seo'));
    }

    public function termsConditions(): View
    {
        $cms = app(LandingCmsService::class);
        $terms  = $cms->getSection('terms_conditions');
        $footer = $cms->getSection('footer');
        $seo    = $cms->getSection('seo');

        if (! $terms || ! $terms->is_active) {
            abort(404);
        }

        return view('central.terms-conditions', compact('terms', 'footer', 'seo'));
    }
}
