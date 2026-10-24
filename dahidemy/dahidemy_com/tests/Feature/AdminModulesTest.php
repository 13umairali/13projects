<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Exercises the onboarding, subscription, payment-gateway and settings admin
 * modules end-to-end through the real HTTP + middleware stack, with the backend
 * API faked.
 */
class AdminModulesTest extends TestCase
{
    protected function asAdmin(): static
    {
        return $this->withSession([
            config('saas.auth.token_key') => 'test-token',
            config('saas.auth.user_key') => ['name' => 'Test Admin', 'email' => 'admin@example.com'],
        ]);
    }

    /* ---------------------------------------------------------------- Onboarding */

    public function test_onboarding_index_lists_pending_registrations(): void
    {
        Http::fake([
            '*/api/onboardings-list*' => Http::response(['status' => true, 'data' => [
                'current_page' => 1, 'per_page' => 10, 'total' => 1,
                'data' => [[
                    'id' => 5,
                    'collected_data' => [
                        'institute_name' => 'Green Valley School', 'institute_email' => 'hi@gv.com',
                        'institute_domain' => 'greenvalley.com', 'user_name' => 'Sara Ahmed',
                    ],
                    'created_at' => '2026-07-01T10:00:00Z',
                ]],
            ]]),
        ]);

        $this->asAdmin()->get('/admin/onboarding')
            ->assertOk()
            ->assertSee('Green Valley School')
            ->assertSee('greenvalley.com')
            ->assertSee('Sara Ahmed');
    }

    public function test_onboarding_approve_calls_backend_and_redirects(): void
    {
        Http::fake([
            '*/api/onboardings-approve/5' => Http::response(['status' => true, 'message' => 'Approved']),
        ]);

        $this->asAdmin()->post('/admin/onboarding/5/approve')
            ->assertRedirect(route('admin.onboarding.index'))
            ->assertSessionHas('success');

        Http::assertSent(fn ($r) => $r->method() === 'GET' && str_contains($r->url(), '/api/onboardings-approve/5'));
    }

    /* ---------------------------------------------------------------- Subscriptions */

    public function test_subscription_requests_lists_pending_upgrades(): void
    {
        Http::fake([
            '*/api/subscription/request-upgrade-list*' => Http::response(['status' => true, 'data' => [
                'current_page' => 1, 'per_page' => 15, 'total' => 1,
                'data' => [[
                    'id' => 4, 'amount_paid' => 49, 'extra_days' => 0,
                    'institute' => ['name' => 'Riverside Academy', 'email' => 'admin@riverside.edu'],
                    'plan' => ['name' => 'Growth'],
                ]],
            ]]),
        ]);

        $this->asAdmin()->get('/admin/subscriptions/requests')
            ->assertOk()
            ->assertSee('Riverside Academy')
            ->assertSee('Growth');
    }

    public function test_subscription_request_approve_calls_backend(): void
    {
        Http::fake([
            '*/api/subscription/approve-request/4' => Http::response(['status' => true, 'message' => 'Approved']),
        ]);

        $this->asAdmin()->post('/admin/subscriptions/requests/4/approve')
            ->assertRedirect(route('admin.subscriptions.requests'))
            ->assertSessionHas('success');

        Http::assertSent(fn ($r) => $r->method() === 'GET' && str_contains($r->url(), '/api/subscription/approve-request/4'));
    }

    public function test_manual_upgrade_posts_payload_to_backend(): void
    {
        Http::fake([
            '*/api/subscription/upgrade' => Http::response(['status' => true, 'message' => 'Subscription updated successfully.']),
        ]);

        $this->asAdmin()->post('/admin/subscriptions/upgrade', [
            'institute_id' => 1,
            'plan_id' => 3,
            'extra_days' => 30,
            'payment_method' => 'Bank Transfer',
            'amount_paid' => 149,
            'notes' => 'Manual upgrade',
        ])
            ->assertRedirect(route('admin.subscriptions.upgrade'))
            ->assertSessionHas('success');

        Http::assertSent(fn ($r) => $r->method() === 'POST'
            && str_contains($r->url(), '/api/subscription/upgrade')
            && $r['institute_id'] == 1 && $r['plan_id'] == 3);
    }

    public function test_manual_upgrade_validation_blocks_missing_fields(): void
    {
        Http::fake();

        $this->asAdmin()->post('/admin/subscriptions/upgrade', [])
            ->assertSessionHasErrors(['institute_id', 'plan_id', 'payment_method', 'amount_paid']);

        Http::assertNothingSent();
    }

    /* ---------------------------------------------------------------- Payment gateways */

    public function test_payment_gateways_index_lists_configs(): void
    {
        Http::fake([
            '*/api/saas-digital-payment' => Http::response(['status' => true, 'data' => [
                ['id' => 22, 'name' => 'bkash', 'mode' => 'test', 'status' => '1', 'payment_info' => ['app_key' => 'abc']],
            ]]),
        ]);

        $this->asAdmin()->get('/admin/payments')
            ->assertOk()
            ->assertSee('bkash');
    }

    public function test_payment_gateway_update_sends_put(): void
    {
        Http::fake([
            '*/api/saas-digital-payment/22' => Http::response(['status' => true, 'message' => 'Updated']),
        ]);

        $this->asAdmin()->put('/admin/payments/22', [
            'payment_info' => ['app_key' => 'new-key', 'app_secret' => 'new-secret'],
            'mode' => 'live',
            'status' => '1',
        ])
            ->assertRedirect(route('admin.payments.index'))
            ->assertSessionHas('success');

        Http::assertSent(fn ($r) => $r->method() === 'PUT'
            && str_contains($r->url(), '/api/saas-digital-payment/22')
            && data_get($r->data(), 'payment_info.app_key') === 'new-key'
            && $r['mode'] === 'live');
    }

    /* ---------------------------------------------------------------- Settings */

    /** The full settings payload the backend returns, gateway nulls included. */
    private function settingsPayload(array $overrides = []): array
    {
        return array_merge([
            'site_name' => 'Mighty School',
            'site_title' => 'School Management SaaS',
            'phone' => '+880 1700 000000',
            'office_phone' => '',
            'email' => 'hello@mighty.test',
            'language' => 'en',
            'google_map' => '',
            'address' => '12 Campus Road',
            'currency_symbol' => '$',
            'logo' => 'logo.png',
            'disabled_website' => 'no',
            'copyright_text' => '© 2026 Mighty School',
            'facebook_link' => 'https://facebook.com/mighty',
            'google_plus_link' => '',
            'youtube_link' => '',
            'whats_app_link' => '',
            'twitter_link' => '',
            'header_notice' => '',
            'app_version' => '1.4.0',
            'app_url' => 'https://app.mighty.test',
            'play_store_link' => '',
            'app_store_link' => '',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'primary_container_color' => '',
            'dark_primary_color' => '',
            'dark_secondary_color' => '',
            'dark_container_color' => '',
            'text_color' => '#ffffff',
            'dark_text_colo' => '#f8fafc',
            'sidebar_selected_bg_color' => null,
            'sidebar_selected_text_color' => null,
            'vercel_project_id' => 'prj_123',
            'vercel_token' => 'secret-token',
            'bkash' => null,
            'stripe' => '{"key":"sk_test"}',
            'paypal' => null,
        ], $overrides);
    }

    /** The form payload an admin submits from the settings page. */
    private function settingsForm(array $overrides = []): array
    {
        return array_merge([
            'site_name' => 'Mighty School',
            'site_title' => 'School Management SaaS',
            'email' => 'hello@mighty.test',
            'phone' => '+880 1700 000000',
            'primary_color' => '#2563eb',
            'dark_text_colo' => '#f8fafc',
        ], $overrides);
    }

    public function test_settings_index_renders_every_value_from_the_backend(): void
    {
        Http::fake([
            '*/api/get-saas-settings' => Http::response(['status' => true, 'data' => $this->settingsPayload()]),
        ]);

        $response = $this->asAdmin()->get('/admin/settings');

        $response->assertOk();
        $response->assertSee('Save settings');
        $response->assertSee('Mighty School');
        $response->assertSee('School Management SaaS');
        $response->assertSee('hello@mighty.test');
        $response->assertSee('+880 1700 000000');
        $response->assertSee('12 Campus Road');
        $response->assertSee('https://facebook.com/mighty');
        $response->assertSee('#2563eb');   // colour picker shows the stored colour
        $response->assertSee('#f8fafc');   // stored under the backend's misspelled key
        $response->assertSee('prj_123');

        // Null gateways must not blow up, and a configured one is flagged.
        $response->assertSee('Payment gateways');
        $response->assertSee('Not set');
        $response->assertSee('Configured');

        // A stored secret is never echoed back into the page.
        $response->assertDontSee('secret-token');
    }

    public function test_settings_page_has_no_logo_upload(): void
    {
        Http::fake([
            '*/api/get-saas-settings' => Http::response(['status' => true, 'data' => $this->settingsPayload()]),
        ]);

        $response = $this->asAdmin()->get('/admin/settings');

        $response->assertOk();
        $response->assertDontSee('Upload logo');
        $response->assertDontSee('Site logo');
        $response->assertDontSee('multipart/form-data', false);

        // The route itself is gone, not merely hidden.
        $this->assertFalse(app('router')->has('admin.settings.logo'));
    }

    public function test_settings_update_sends_the_full_editable_payload(): void
    {
        Http::fake([
            '*/api/saas-settings-update' => Http::response(['status' => true, 'message' => 'Saved']),
        ]);

        $this->asAdmin()->post('/admin/settings', $this->settingsForm([
            'site_name' => 'New Name',
            'facebook_link' => 'facebook.com/mighty',  // typed without a scheme
            'header_notice' => '',                     // cleared on purpose
        ]))
            ->assertRedirect(route('admin.settings.index'))
            ->assertSessionHas('success');

        Http::assertSent(function ($r) {
            return $r->method() === 'POST'
                && str_contains($r->url(), '/api/saas-settings-update')
                && $r['site_name'] === 'New Name'
                && $r['facebook_link'] === 'https://facebook.com/mighty' // scheme completed
                && $r['header_notice'] === ''                            // cleared, not dropped
                && $r['dark_text_colo'] === '#f8fafc'                    // the row the backend stores
                && $r['dark_text_color'] === '#f8fafc'                   // the key it validates
                && ! array_key_exists('logo', $r->data())                // logos are not managed here
                && ! array_key_exists('vercel_token', $r->data());       // blank secret left untouched
        });
    }

    public function test_settings_update_validates_required_fields_and_formats(): void
    {
        Http::fake();

        $this->asAdmin()->post('/admin/settings', [
            'site_name' => '',
            'site_title' => '',
            'email' => 'not-an-email',
            'phone' => 'call me',
            'primary_color' => 'blue',
        ])->assertSessionHasErrors(['site_name', 'site_title', 'email', 'phone', 'primary_color']);

        Http::assertNothingSent();
    }

    public function test_settings_update_surfaces_a_backend_failure(): void
    {
        Http::fake([
            '*/api/saas-settings-update' => Http::response(['status' => false, 'message' => 'Token expired.'], 500),
        ]);

        $this->asAdmin()->post('/admin/settings', $this->settingsForm())
            ->assertSessionHas('error', 'Token expired.');
    }

    public function test_mail_settings_update_posts(): void
    {
        Http::fake([
            '*/api/saas-settings-mail-update' => Http::response(['status' => true, 'message' => 'Saved']),
        ]);

        $this->asAdmin()->post('/admin/settings/mail', ['MAIL_HOST' => 'smtp.test', 'MAIL_PORT' => '2525'])
            ->assertRedirect(route('admin.settings.mail'))
            ->assertSessionHas('success');

        Http::assertSent(fn ($r) => str_contains($r->url(), '/api/saas-settings-mail-update')
            && $r['MAIL_HOST'] === 'smtp.test');
    }

    /* ---------------------------------------------------------------- Guests */

    public function test_guests_cannot_reach_admin_modules(): void
    {
        $this->get('/admin/onboarding')->assertRedirect(route('login'));
        $this->get('/admin/subscriptions/requests')->assertRedirect(route('login'));
        $this->get('/admin/payments')->assertRedirect(route('login'));
        $this->get('/admin/settings')->assertRedirect(route('login'));
    }
}
