<?php

namespace App\Services\Api;

use App\Support\ImageUrl;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Public marketing / landing content consumed from the backend SaaS endpoints
 * (all under /api/saas/* plus /api/get-saas-settings).
 *
 * Everything is cached and degrades gracefully: if the backend is unreachable
 * the site still renders with sensible fallback content, so the marketing page
 * is never blank.
 */
class SaasContentService extends BaseApiService
{
    /* -----------------------------------------------------------------
     |  Global settings & branding
     | ----------------------------------------------------------------- */

    /**
     * Raw key => value settings map from the backend.
     */
    public function settings(): array
    {
        return $this->remember('settings', function () {
            $res = $this->get('get-saas-settings');

            return $res->successful() && is_array($res->data()) ? $res->data() : [];
        }, []);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->settings(), $key, $default);
    }

    /**
     * Branding merged from config defaults + backend overrides. Never empty.
     *
     * Every image key is already run through ImageUrl, so views can drop the
     * value straight into src="" — no path juggling, no scheme guessing. An
     * image the backend has not set resolves to null, and the view is expected
     * to render its empty state (a wordmark, an initial) rather than a
     * placeholder file.
     */
    public function branding(): array
    {
        $s = $this->settings();
        $brand = config('saas.brand');

        $images = $this->brandImages($s);

        // Key order matters: the backend's own settings rows (site_name, email,
        // facebook_link, …) come first, then the legacy aliases, then the config
        // default. Whatever an admin saves on the settings page is what shows.
        return [
            'name' => $this->firstFilled($s, ['site_name', 'app_name', 'name'], $brand['name']),
            'tagline' => $this->firstFilled($s, ['site_title', 'site_tagline', 'tagline', 'slogan'], $brand['tagline']),
            'description' => $this->firstFilled($s, ['site_description', 'meta_description', 'description'], $brand['description']),
            'meta_title' => $this->firstFilled($s, ['meta_title'], null),
            'meta_keywords' => $this->firstFilled($s, ['meta_keywords'], null),
            'ga4_id' => $this->firstFilled($s, ['ga4_id'], null),
            'gtm_id' => $this->firstFilled($s, ['gtm_id'], null),
            'meta_pixel_id' => $this->firstFilled($s, ['meta_pixel_id'], null),
            'email' => $this->firstFilled($s, ['email', 'site_email', 'contact_email'], $brand['email']),
            'phone' => $this->firstFilled($s, ['phone', 'site_phone', 'contact_phone'], $brand['phone']),
            'office_phone' => $this->firstFilled($s, ['office_phone'], null),
            'address' => $this->firstFilled($s, ['address', 'site_address'], $brand['address']),
            'copyright' => $this->firstFilled($s, ['copyright_text'], null),
            'notice' => $this->firstFilled($s, ['header_notice'], null),
            'socials' => [
                'facebook' => $this->firstFilled($s, ['facebook_link', 'facebook', 'social_facebook'], $brand['socials']['facebook']),
                'twitter' => $this->firstFilled($s, ['twitter_link', 'twitter', 'social_twitter', 'x'], $brand['socials']['twitter']),
                'youtube' => $this->firstFilled($s, ['youtube_link', 'youtube', 'social_youtube'], $brand['socials']['youtube']),
                'whatsapp' => $this->firstFilled($s, ['whats_app_link'], null),
                'linkedin' => $this->firstFilled($s, ['linkedin', 'social_linkedin'], $brand['socials']['linkedin']),
                'instagram' => $this->firstFilled($s, ['instagram', 'social_instagram'], $brand['socials']['instagram']),
            ],
        ] + $images;
    }

    /**
     * Raw institute_image_s_a_a_s_settings row from the backend's public
     * endpoint. Columns hold bare filenames under institute_image_settings/.
     */
    public function instituteImages(): array
    {
        return $this->remember('institute-images', function () {
            $res = $this->get('frontend/institute-image-saas-settings');

            return $res->successful() && is_array($res->data()) ? $res->data() : [];
        }, []);
    }

    /**
     * The branding image slots, resolved to URLs.
     *
     * Sourced from institute-image-saas-settings, with the generic settings map
     * as a fallback for installs that predate that table. Each slot degrades to
     * the next-best REAL image, never to a bundled file: dark falls back to
     * light, footer falls back to header, and anything still unset stays null so
     * the view renders its own mark instead of a placeholder.
     */
    protected function brandImages(array $s): array
    {
        $i = $this->instituteImages();

        // The two sources live in different directories on the backend disk:
        // the image-settings row under institute_image_settings/, the legacy
        // settings logo under logos/. Resolve each against its own dir.
        $fromImages = fn (string $key) => saas_image($i[$key] ?? null, ImageUrl::DIR_IMAGE_SETTINGS);
        $fromSettings = fn (array $keys) => saas_image($this->firstFilled($s, $keys, null), ImageUrl::DIR_LOGOS);

        $headerLight = $fromImages('header_logo_light_theme')
            ?? $fromSettings(['site_logo', 'logo', 'app_logo']);
        $headerDark = $fromImages('header_logo_dark_theme') ?? $headerLight;
        $footerLight = $fromImages('footer_logo_light_theme') ?? $headerLight;
        $footerDark = $fromImages('footer_logo_dark_theme') ?? $footerLight;

        return [
            'logo' => $headerLight, // alias: pre-dates the 4-slot scheme
            'header_logo_light' => $headerLight,
            'header_logo_dark' => $headerDark,
            'footer_logo_light' => $footerLight,
            'footer_logo_dark' => $footerDark,
            'banner' => $fromImages('banner_image'),
            'favicon' => $fromSettings(['site_favicon', 'favicon']),
            'og_image' => $fromSettings(['og_image', 'meta_image', 'share_image']) ?? $headerLight,
        ];
    }

    /* -----------------------------------------------------------------
     |  Section content
     | ----------------------------------------------------------------- */

    public function banners(): Collection
    {
        return $this->list('saas/banners');
    }

    /** Pricing packages (Plan model). */
    public function packages(): Collection
    {
        return $this->list('saas/packages')
            ->map(fn ($p) => $this->normalizePackage((array) $p))
            ->values();
    }

    /** "Core" feature highlights. */
    public function cores(): Collection
    {
        return $this->list('saas/cores');
    }

    /** Solutions / modules the platform covers. */
    public function solutions(): Collection
    {
        return $this->list('saas/solutions');
    }

    /** Benefits / value props. */
    public function benefits(): Collection
    {
        return $this->list('saas/benefits');
    }

    /** Panel role showcases. */
    public function panels(): Collection
    {
        return $this->list('saas/panels');
    }

    /** Customer feedback / testimonials. */
    public function feedback(): Collection
    {
        return $this->list('saas/feedback');
    }

    public function faqs(): Collection
    {
        return $this->list('saas/faqs');
    }

    public function instituteTypes(): Collection
    {
        return $this->list('saas/institute-types');
    }

    /**
     * Legal policy by type: privacy | cookie | terms.
     */
    public function policy(string $type): ?array
    {
        $valid = ['privacy', 'cookie', 'terms'];
        $type = in_array($type, $valid, true) ? $type : 'privacy';

        return $this->remember("policy.$type", function () use ($type) {
            $res = $this->get('saas/policies', ['type' => $type]);

            return $res->successful() ? $res->data() : null;
        }, null);
    }

    /* -----------------------------------------------------------------
     |  Public write actions (not cached)
     | ----------------------------------------------------------------- */

    /** Public newsletter / email capture. */
    public function subscribe(array $payload): ApiResponse
    {
        return $this->post('saas/subscribe', $payload);
    }

    /**
     * Public institution register / onboarding. Multipart so the institute logo
     * and owner avatar upload alongside the text fields. Lands in the backend
     * onboarding queue for super-admin approval.
     */
    public function onboarding(array $payload): ApiResponse
    {
        return $this->upload('frontend/onboarding', $payload);
    }

    /**
     * Paid-plan sign-up, step 1: does NOT create the institute yet. Returns
     * a Stripe Checkout URL to redirect the browser to.
     */
    public function onboardingCheckout(array $payload): ApiResponse
    {
        return $this->upload('frontend/onboarding/checkout', $payload);
    }

    /**
     * Paid-plan sign-up, step 2: called after the browser returns from
     * Stripe. The backend verifies the payment server-side before ever
     * creating the institute.
     */
    public function onboardingConfirm(string $paymentId, string $sessionId): ApiResponse
    {
        return $this->post('frontend/onboarding/confirm', [
            'payment_id' => $paymentId,
            'session_id' => $sessionId,
        ]);
    }

    /** Landing contact form -> backend frontend contact endpoint. */
    public function contact(array $payload): ApiResponse
    {
        return $this->post('saas/contacts', $payload);
    }

    /* -----------------------------------------------------------------
     |  Cache helpers
     | ----------------------------------------------------------------- */

    /**
     * Fetch a list endpoint and cache it. Returns an empty collection when the
     * backend has no records or is unreachable — never fabricated demo data.
     */
    protected function list(string $endpoint): Collection
    {
        $key = 'list.'.str_replace('/', '.', $endpoint);

        $data = $this->remember($key, function () use ($endpoint) {
            $res = $this->get($endpoint);

            return $res->successful() ? $res->collect()->all() : null;
        }, null);

        return collect($data ?? [])->filter(fn ($row) => $this->isPublishable((array) $row))->values();
    }

    /**
     * Guard against the backend's public endpoints, which return rows the site
     * must not show.
     *
     * The SaaS content models do not use the SoftDeletes trait even though their
     * tables have a deleted_at column — the repositories stamp it by hand, so
     * Eloquent never filters it and a "deleted" record keeps being served. The
     * same endpoints ignore status. Filter both here so a deleted or deactivated
     * record cannot reappear on the marketing site.
     */
    protected function isPublishable(array $row): bool
    {
        if (filled($row['deleted_at'] ?? null)) {
            return false;
        }

        // status is 1=active / 0=inactive across the SaaS tables. Rows with no
        // status column at all (e.g. FAQs) are always publishable.
        if (array_key_exists('status', $row) && $row['status'] !== null) {
            return in_array($row['status'], [1, '1', true, 'active'], true);
        }

        return true;
    }

    /**
     * The one place that decides whether SaaS content is cached at all.
     * Flip SAAS_CACHE_ENABLED=false (or SAAS_CACHE_TTL=0) in .env to bypass.
     */
    public function cacheEnabled(): bool
    {
        return $this->cache->enabled();
    }

    protected function remember(string $key, callable $resolver, mixed $default): mixed
    {
        return $this->cache->remember($key, $resolver, $default) ?? $default;
    }

    /**
     * Drop every cached backend response. Content, admin lists and settings all
     * share one generation counter, so this clears the lot.
     */
    public function flush(): void
    {
        $this->cache->flush();
    }

    protected function firstFilled(array $settings, array $keys, mixed $default): mixed
    {
        foreach ($keys as $key) {
            $value = Arr::get($settings, $key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
    }

    protected function normalizePackage(array $p): array
    {
        $duration = (int) ($p['duration_days'] ?? 30);
        $cycle = match (true) {
            $duration >= 360 => 'year',
            $duration >= 85 => 'quarter',
            default => 'month',
        };

        return [
            'id' => $p['id'] ?? null,
            'name' => $p['name'] ?? 'Plan',
            'description' => $p['description'] ?? null,
            'price' => (float) ($p['price'] ?? 0),
            'is_free' => (bool) ($p['is_free'] ?? false),
            'is_custom' => (bool) ($p['is_custom'] ?? false),
            'student_limit' => $p['student_limit'] ?? null,
            'branch_limit' => $p['branch_limit'] ?? null,
            'duration_days' => $duration,
            'cycle' => $cycle,
            'features' => $this->packageFeatures($p),
        ];
    }

    protected function packageFeatures(array $p): array
    {
        // Prefer an explicit feature list supplied by the backend.
        $explicit = $p['features'] ?? null;
        if (is_array($explicit)) {
            return array_values(array_filter(array_map(fn ($f) => trim((string) $f), $explicit), fn ($f) => $f !== ''));
        }
        if (is_string($explicit) && trim($explicit) !== '') {
            return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $explicit)), fn ($f) => $f !== ''));
        }

        // Otherwise derive bullets from the plan's real, backend-provided limits.
        // (0 / null limits mean "unlimited" per the plan schema.)
        $features = [];

        if (array_key_exists('student_limit', $p)) {
            $students = (int) $p['student_limit'];
            $features[] = $students > 0 ? number_format($students).' students' : 'Unlimited students';
        }

        if (array_key_exists('branch_limit', $p)) {
            $branches = (int) $p['branch_limit'];
            $features[] = $branches > 0 ? number_format($branches).' branches' : 'Unlimited branches';
        }

        $duration = (int) ($p['duration_days'] ?? 0);
        if ($duration > 0) {
            $features[] = $duration.'-day billing cycle';
        }

        return $features;
    }

    /* -----------------------------------------------------------------
     |  Marketing stats (real data only)
     | ----------------------------------------------------------------- */

    /**
     * Raw platform counters from the backend's public saas-stats endpoint.
     * Empty when the backend is unreachable.
     */
    public function saasStats(): array
    {
        return $this->remember('saas-stats', function () {
            $res = $this->get('saas-stats');

            return $res->successful() && is_array($res->data()) ? $res->data() : [];
        }, []);
    }

    /**
     * The landing "by the numbers" counters, in display order (two rows of four).
     *
     * Only keys the backend actually returned are emitted: a zero is a real
     * figure and is kept, while a missing key is dropped rather than defaulted —
     * the view then renders an empty state instead of fabricated numbers.
     */
    public function stats(): Collection
    {
        $s = $this->saasStats();

        $map = [
            ['key' => 'total_institutes', 'label' => 'Total institutions'],
            ['key' => 'active_institutes', 'label' => 'Active institutions'],
            ['key' => 'inactive_institutes', 'label' => 'Inactive institutions'],
            ['key' => 'total_plans', 'label' => 'Plans available'],
            ['key' => 'active_subscriptions', 'label' => 'Active subscriptions'],
            ['key' => 'expired_subscriptions', 'label' => 'Expired subscriptions'],
            ['key' => 'pending_subscriptions', 'label' => 'Pending subscriptions'],
            ['key' => 'total_revenue', 'label' => 'Total revenue', 'money' => true],
        ];

        return collect($map)
            ->filter(fn (array $row) => $this->hasStat($s, $row['key']))
            ->map(fn (array $row) => [
                'value' => (float) $s[$row['key']],
                'label' => $row['label'],
                'prefix' => ($row['money'] ?? false) ? $this->currency() : '',
                'suffix' => '',
            ])
            ->values();
    }

    /**
     * This-month vs last-month subscription revenue and the growth delta, for
     * the landing revenue chart. Null when the backend exposes none of them.
     */
    public function revenue(): ?array
    {
        $s = $this->saasStats();

        $keys = ['current_month_revenue', 'last_month_revenue', 'revenue_growth'];

        if (! collect($keys)->contains(fn (string $key) => $this->hasStat($s, $key))) {
            return null;
        }

        return [
            'current' => (float) ($s['current_month_revenue'] ?? 0),
            'last' => (float) ($s['last_month_revenue'] ?? 0),
            'total' => (float) ($s['total_revenue'] ?? 0),
            'growth' => (float) ($s['revenue_growth'] ?? 0),
            'currency' => $this->currency(),
        ];
    }

    protected function hasStat(array $stats, string $key): bool
    {
        return array_key_exists($key, $stats)
            && $stats[$key] !== null
            && $stats[$key] !== ''
            && is_numeric($stats[$key]);
    }

    /** Currency symbol from backend settings; empty when none is configured. */
    protected function currency(): string
    {
        return (string) $this->firstFilled(
            $this->settings(),
            ['currency_symbol', 'currency_sign', 'currency'],
            ''
        );
    }
}