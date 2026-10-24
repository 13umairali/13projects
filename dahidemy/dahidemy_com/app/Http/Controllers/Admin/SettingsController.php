<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\InstituteService;
use App\Services\Api\SettingsService;
use App\Support\ResourceRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Super-admin global settings — every key/value the backend exposes on
 * GET /get-saas-settings, written back through POST /saas-settings-update.
 *
 * Values are never defaulted or invented here: the form shows exactly what the
 * backend holds, and only the keys below are ever sent back. Logos are NOT
 * managed here — they live on the branding page (admin.branding.index), which
 * owns all four logo slots plus the banner.
 *
 * Two backend quirks are absorbed in this class rather than leaked to the view:
 *
 *  - The dark text colour is stored under the misspelled key `dark_text_colo`
 *    (see the backend UtilitySeeder), while SAASSettingUpdateRequest validates
 *    `dark_text_color`. We read whichever the backend returns and write both, so
 *    the value round-trips whichever spelling the consuming app reads.
 *  - Payment gateway rows (bkash, stripe, …) also come back in this payload, but
 *    they carry mode/status columns the settings endpoint cannot write. They are
 *    shown read-only and edited on the payments page, which owns that contract.
 */
class SettingsController extends Controller
{
    /** Gateway keys the settings payload may carry; edited on the payments page. */
    private const GATEWAY_KEYS = [
        'bkash',
        'paystack',
        'razor_pay',
        'stripe',
        'ssl_commerz',
        'pvit',
        'paypal',
        'paymob_accept',
        'flutterwave',
        'senang_pay',
        'paytm',
    ];

    /** The misspelled key the backend actually stores the dark text colour under. */
    private const DARK_TEXT_TYPO = 'dark_text_colo';

    private const DARK_TEXT = 'dark_text_color';

    /** Editable mail keys surfaced in the mail form. */
    private const MAIL_FIELDS = [
        'MAIL_MAILER' => 'Mailer',
        'MAIL_HOST' => 'Host',
        'MAIL_PORT' => 'Port',
        'MAIL_USERNAME' => 'Username',
        'MAIL_PASSWORD' => 'Password',
        'MAIL_FROM_ADDRESS' => 'From address',
        'MAIL_FROM_NAME' => 'From name',
    ];

    /**
     * Every editable setting, grouped as it is rendered. `rules` are the frontend
     * validation rules; `url` marks fields whose scheme is auto-completed.
     */
    private function groups(): array
    {
        $phone = ['nullable', 'string', 'max:255', 'regex:/^[0-9+()\-.\s]{6,25}$/'];
        $url = ['nullable', 'url', 'max:255'];
        $color = ['nullable', 'string', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'];

        return [
            'general' => [
                'title' => 'General',
                'subtitle' => 'Identity and contact details for the platform.',
                'fields' => [
                    'site_name' => ['label' => 'Site name', 'rules' => ['required', 'string', 'max:255']],
                    'site_title' => ['label' => 'Site title', 'rules' => ['required', 'string', 'max:255']],
                    'email' => ['label' => 'Email', 'type' => 'email', 'rules' => ['required', 'email', 'max:255']],
                    'phone' => ['label' => 'Phone', 'rules' => ['required', 'string', 'max:255', 'regex:/^[0-9+()\-.\s]{6,25}$/']],
                    'office_phone' => ['label' => 'Office phone', 'rules' => $phone],
                    'language' => ['label' => 'Language', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'Locale code, e.g. en.'],
                    'currency_symbol' => ['label' => 'Currency symbol', 'rules' => ['nullable', 'string', 'max:255']],
                    'disabled_website' => [
                        'label' => 'Public website',
                        'type' => 'select',
                        'options' => ['no' => 'Enabled', 'yes' => 'Disabled'],
                        'rules' => ['nullable', 'in:yes,no'],
                        'help' => 'Disabling takes the marketing site offline.',
                    ],
                    'address' => ['label' => 'Address', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:255'], 'span' => true],
                    'google_map' => ['label' => 'Google map', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'Map link or embed URL.', 'span' => true],
                    'copyright_text' => ['label' => 'Copyright text', 'rules' => ['nullable', 'string', 'max:255'], 'span' => true],
                    'header_notice' => ['label' => 'Header notice', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'Shown as a banner across the site.', 'span' => true],
                ],
            ],
            'seo' => [
                'title' => 'SEO & Analytics',
                'subtitle' => 'Meta tags and tracking scripts injected on the marketing site.',
                'fields' => [
                    'meta_title' => ['label' => 'Meta title', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'Falls back to Site title if left blank.'],
                    'meta_description' => ['label' => 'Meta description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000'], 'span' => true],
                    'meta_keywords' => ['label' => 'Meta keywords', 'rules' => ['nullable', 'string', 'max:500'], 'span' => true],
                    'og_image' => ['label' => 'Social share image (OG image) URL', 'type' => 'url', 'rules' => ['nullable', 'url', 'max:500'], 'url' => true, 'span' => true],
                    'ga4_id' => ['label' => 'Google Analytics 4 Measurement ID', 'rules' => ['nullable', 'string', 'max:50'], 'help' => 'e.g. G-XXXXXXXXXX'],
                    'gtm_id' => ['label' => 'Google Tag Manager Container ID', 'rules' => ['nullable', 'string', 'max:50'], 'help' => 'e.g. GTM-XXXXXXX'],
                    'meta_pixel_id' => ['label' => 'Meta (Facebook) Pixel ID', 'rules' => ['nullable', 'string', 'max:50']],
                ],
            ],
            'social' => [
                'title' => 'Social links',
                'subtitle' => 'Profiles linked from the marketing site footer.',
                'fields' => [
                    'facebook_link' => ['label' => 'Facebook', 'type' => 'url', 'rules' => $url, 'url' => true],
                    'twitter_link' => ['label' => 'Twitter / X', 'type' => 'url', 'rules' => $url, 'url' => true],
                    'youtube_link' => ['label' => 'YouTube', 'type' => 'url', 'rules' => $url, 'url' => true],
                    'google_plus_link' => ['label' => 'Google', 'type' => 'url', 'rules' => $url, 'url' => true],
                    'whats_app_link' => ['label' => 'WhatsApp', 'type' => 'url', 'rules' => $url, 'url' => true, 'span' => true],
                ],
            ],
            'app' => [
                'title' => 'Mobile app',
                'subtitle' => 'Version and store links for the companion app.',
                'fields' => [
                    'app_version' => ['label' => 'App version', 'rules' => ['nullable', 'string', 'max:255']],
                    'app_url' => ['label' => 'App URL', 'type' => 'url', 'rules' => $url, 'url' => true],
                    'play_store_link' => ['label' => 'Play Store link', 'type' => 'url', 'rules' => $url, 'url' => true],
                    'app_store_link' => ['label' => 'App Store link', 'type' => 'url', 'rules' => $url, 'url' => true],
                ],
            ],
            'theme' => [
                'title' => 'Theme colours',
                'subtitle' => 'Palette applied by the apps that consume these settings.',
                'colors' => true,
                'fields' => [
                    'primary_color' => ['label' => 'Primary', 'rules' => $color],
                    'secondary_color' => ['label' => 'Secondary', 'rules' => $color],
                    'primary_container_color' => ['label' => 'Primary container', 'rules' => $color],
                    'text_color' => ['label' => 'Text', 'rules' => $color],
                    'dark_primary_color' => ['label' => 'Dark primary', 'rules' => $color],
                    'dark_secondary_color' => ['label' => 'Dark secondary', 'rules' => $color],
                    'dark_container_color' => ['label' => 'Dark container', 'rules' => $color],
                    self::DARK_TEXT_TYPO => ['label' => 'Dark text', 'rules' => $color],
                    'sidebar_selected_bg_color' => ['label' => 'Sidebar selected background', 'rules' => $color],
                    'sidebar_selected_text_color' => ['label' => 'Sidebar selected text', 'rules' => $color],
                ],
            ],
            'deployment' => [
                'title' => 'Deployment',
                'subtitle' => 'Credentials used to publish tenant sites.',
                'fields' => [
                    'vercel_project_id' => ['label' => 'Vercel project ID', 'rules' => ['nullable', 'string', 'max:255']],
                    'vercel_token' => ['label' => 'Vercel token', 'type' => 'password', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'Leave blank to keep the stored token.'],
                ],
            ],
            'backups' => [
                'title' => 'Database backups',
                'subtitle' => 'Automatic and manual database backups, stored locally or on an S3-compatible destination (AWS S3 or Cloudflare R2).',
                'fields' => [
                    'backup_destination' => [
                        'label' => 'Destination',
                        'type' => 'select',
                        'options' => ['local' => 'Local disk', 's3' => 'S3 / Cloudflare R2'],
                        'rules' => ['nullable', 'in:local,s3'],
                    ],
                    'backup_auto_enabled' => [
                        'label' => 'Automatic daily backup',
                        'type' => 'select',
                        'options' => ['no' => 'Disabled', 'yes' => 'Enabled'],
                        'rules' => ['nullable', 'in:yes,no'],
                    ],
                    'backup_retention_days' => ['label' => 'Retention (days)', 'rules' => ['nullable', 'integer', 'min:1', 'max:365'], 'help' => 'Older backups are pruned automatically.'],
                    'backup_aws_bucket' => ['label' => 'Bucket', 'rules' => ['nullable', 'string', 'max:255']],
                    'backup_aws_region' => ['label' => 'Region', 'rules' => ['nullable', 'string', 'max:100'], 'help' => "Use 'auto' for Cloudflare R2."],
                    'backup_aws_endpoint' => ['label' => 'Endpoint URL', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'Required for R2; leave blank for AWS S3.', 'span' => true],
                    'backup_aws_key' => ['label' => 'Access key ID', 'rules' => ['nullable', 'string', 'max:255']],
                    'backup_aws_secret' => ['label' => 'Secret access key', 'type' => 'password', 'rules' => ['nullable', 'string', 'max:255'], 'help' => 'Leave blank to keep the stored secret.'],
                    'backup_aws_use_path_style' => [
                        'label' => 'Path-style endpoint',
                        'type' => 'select',
                        'options' => ['no' => 'No', 'yes' => 'Yes'],
                        'rules' => ['nullable', 'in:yes,no'],
                        'help' => 'Enable for most R2/self-hosted S3-compatible endpoints.',
                    ],
                ],
            ],
            'school_setup' => [
                'title' => 'Default school setup',
                'subtitle' => 'When a new school is created, copy settings, grades, policies, and website content from this template school.',
                'fields' => [
                    'default_template_institute_id' => [
                        'label' => 'Template school',
                        'type' => 'select',
                        'options' => [],
                        'rules' => ['nullable'],
                        'help' => 'Leave empty to seed built-in defaults instead of copying an existing school.',
                        'span' => true,
                    ],
                ],
            ],
        ];
    }

    public function __construct(
        protected SettingsService $settings,
        protected InstituteService $institutes,
    ) {}

    public function index(): View
    {
        $response = $this->settings->all();
        $current = (array) $response->data([]);

        $groups = $this->groups();
        $groups['school_setup']['fields']['default_template_institute_id']['options'] = $this->instituteOptions();
        $values = Arr::only($current, $this->editableKeys($groups));

        // We write the dark text colour under both spellings; show the correct one
        // when it exists, since it is the fresher of the two.
        if (filled($current[self::DARK_TEXT] ?? null)) {
            $values[self::DARK_TEXT_TYPO] = $current[self::DARK_TEXT];
        }

        return view('admin.settings.index', [
            'groups' => $groups,
            'values' => $values,
            'gateways' => $this->gatewayStatus($current),
            'reachable' => $response->successful(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $groups = $this->groups();

        $rules = [];
        foreach ($groups as $group) {
            foreach ($group['fields'] as $key => $field) {
                $rules[$key] = $field['rules'];
            }
        }

        // "facebook.com/acme" is what an admin types; make it a URL before the
        // url rule sees it, so a scheme-less entry is corrected, not rejected.
        $request->merge($this->withSchemes($request, $groups));

        $data = $request->validate($rules, [
            'phone.regex' => 'The phone may only contain digits, spaces and + ( ) - characters.',
            'office_phone.regex' => 'The office phone may only contain digits, spaces and + ( ) - characters.',
            '*.regex' => 'The :attribute must be a hex colour such as #2563eb.',
        ]);

        // An empty token means "keep what's stored" — never blank it out by accident.
        if (blank($data['vercel_token'] ?? null)) {
            unset($data['vercel_token']);
        }

        // Write the dark text colour under both spellings: the backend stores the
        // typo'd key but only validates the correct one, so sending just one loses
        // the value on one side or the other.
        if (array_key_exists(self::DARK_TEXT_TYPO, $data)) {
            $data[self::DARK_TEXT] = $data[self::DARK_TEXT_TYPO];
        }

        // Send every editable key, empty ones included: clearing a field must
        // clear it on the backend too, and null would be dropped by validated().
        $payload = array_map(fn ($v) => $v ?? '', $data);

        $response = $this->settings->update($payload);

        return $this->finish($response, 'admin.settings.index', 'Settings saved.');
    }

    public function mail(): View
    {
        $response = $this->settings->mailConfig();
        $current = (array) $response->data([]);

        return view('admin.settings.mail', [
            'fields' => self::MAIL_FIELDS,
            'values' => Arr::only($current, array_keys(self::MAIL_FIELDS)),
            'reachable' => $response->successful(),
        ]);
    }

    public function updateMail(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (array_keys(self::MAIL_FIELDS) as $key) {
            $rules[$key] = ['nullable', 'string', 'max:255'];
        }

        $data = $request->validate($rules);

        $response = $this->settings->updateMailConfig($data);

        return $this->finish($response, 'admin.settings.mail', 'Mail configuration saved.');
    }

    /* -----------------------------------------------------------------
     |  Helpers
     | ----------------------------------------------------------------- */

    /** @return array<int|string, string> */
    private function instituteOptions(): array
    {
        $response = $this->institutes->paginate([
            'perPage' => 200,
            'orderBy' => 'name',
            'order' => 'asc',
            'status' => '1',
        ]);

        return collect(ResourceRegistry::normalizeList($response->data([])))
            ->mapWithKeys(function ($institute) {
                $id = $institute['id'] ?? null;
                $name = $institute['name'] ?? ('School #'.$id);

                return $id ? [(string) $id => $name] : [];
            })
            ->all();
    }

    /** @return list<string> */
    private function editableKeys(array $groups): array
    {
        return collect($groups)
            ->flatMap(fn (array $group) => array_keys($group['fields']))
            ->push(self::DARK_TEXT) // so the correctly-spelled row can be read back
            ->all();
    }

    /**
     * Prefix a scheme onto any url-typed field the admin typed without one.
     */
    private function withSchemes(Request $request, array $groups): array
    {
        $patched = [];

        foreach ($groups as $group) {
            foreach ($group['fields'] as $key => $field) {
                if (! ($field['url'] ?? false)) {
                    continue;
                }

                $value = trim((string) $request->input($key, ''));

                if ($value !== '' && ! preg_match('#^https?://#i', $value)) {
                    $patched[$key] = 'https://'.$value;
                }
            }
        }

        return $patched;
    }

    /**
     * Which payment gateways the backend holds configuration for. The settings
     * payload carries them as a JSON string, an object, or null — all three are
     * safe here, and none of them are editable on this page.
     *
     * @return list<array{key: string, label: string, configured: bool}>
     */
    private function gatewayStatus(array $current): array
    {
        return collect(self::GATEWAY_KEYS)
            ->map(function (string $key) use ($current) {
                $raw = $current[$key] ?? null;

                if (is_string($raw)) {
                    $decoded = json_decode($raw, true);
                    $raw = is_array($decoded) ? $decoded : ($raw === '' ? null : $raw);
                }

                return [
                    'key' => $key,
                    'label' => Str::headline($key),
                    'configured' => filled($raw),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * No explicit cache flush: a successful write already invalidates every
     * cached read in BaseApiService, so saved settings show up immediately on
     * both the admin pages and the marketing site.
     */
    private function finish($response, string $route, string $success): RedirectResponse
    {
        if ($response->successful()) {
            return redirect()->route($route)->with('success', $response->message ?: $success);
        }

        return back()
            ->withInput()
            ->withErrors($response->validationErrors())
            ->with('error', $response->message ?: 'Save failed. Please try again.');
    }
}
