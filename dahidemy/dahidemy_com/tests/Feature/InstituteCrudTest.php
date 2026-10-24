<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * SaaS admin institute management, driven through the real HTTP + session +
 * middleware stack with the backend API faked. Covers the flow the brief
 * cares about: sign in as SaaS admin -> list -> create -> update -> activate/
 * deactivate -> (impersonation is covered separately in ImpersonationTest).
 */
class InstituteCrudTest extends TestCase
{
    protected function asAdmin(): static
    {
        return $this->withSession([
            config('saas.auth.token_key') => 'test-token',
            config('saas.auth.user_key') => ['name' => 'Test Admin', 'email' => 'admin@example.com'],
        ]);
    }

    public function test_saas_admin_can_sign_in_against_the_backend(): void
    {
        Http::fake([
            '*/api/saas-login' => Http::response(['status' => true, 'data' => [
                'token' => 'a-real-bearer-token',
                'user' => ['name' => 'SAAS Admin', 'email' => 'saasadmin@gmail.com', 'user_type' => 'SAAS Admin'],
            ]]),
        ]);

        $this->post('/login', ['email' => 'saasadmin@gmail.com', 'password' => '12345678'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertSame('a-real-bearer-token', session(config('saas.auth.token_key')));
        Http::assertSent(fn ($r) => str_contains($r->url(), '/api/saas-login'));
    }

    public function test_bad_credentials_are_surfaced_without_a_session(): void
    {
        Http::fake(['*/api/saas-login' => Http::response(['status' => false, 'message' => 'The provided credentials are incorrect..'], 401)]);

        $this->from(route('login'))
            ->post('/login', ['email' => 'x@y.com', 'password' => 'nope'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertNull(session(config('saas.auth.token_key')));
    }

    public function test_guest_cannot_reach_institute_list(): void
    {
        $this->get('/admin/institutes')->assertRedirect(route('login'));
    }

    public function test_admin_sees_the_institute_list_from_the_backend(): void
    {
        Http::fake([
            '*/api/institutes*' => Http::response(['status' => true, 'data' => [
                'current_page' => 1, 'per_page' => 10, 'total' => 1,
                'data' => [[
                    'id' => 1, 'name' => 'Green Valley School', 'domain' => 'greenvalley.com',
                    'email' => 'hi@gv.com', 'status' => true, 'plan_name' => 'Standard',
                ]],
            ]]),
        ]);

        $this->asAdmin()->get('/admin/institutes')
            ->assertOk()
            ->assertSee('Green Valley School')
            ->assertSee('greenvalley.com');
    }

    public function test_admin_can_create_an_institute(): void
    {
        Http::fake([
            '*/api/institute-types' => Http::response(['status' => true, 'data' => []]),
            '*/api/institutes' => Http::response(['status' => true, 'data' => ['id' => 9]]),
        ]);

        $this->asAdmin()->post('/admin/institutes', [
            'institute_name' => 'New School', 'institute_email' => 'new@school.com',
            'institute_phone' => '01711111111', 'institute_type' => 'Institute',
            'institute_domain' => 'new-school.com', 'institute_address' => 'Somewhere',
            'user_name' => 'Owner Admin', 'user_email' => 'owner@school.com', 'user_phone' => '01722222222',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertRedirect(route('admin.institutes.index'))->assertSessionHas('success');

        Http::assertSent(fn ($r) => $r->method() === 'POST' && str_contains($r->url(), '/api/institutes'));
    }

    public function test_create_validation_errors_bounce_back(): void
    {
        Http::fake(['*/api/institute-types' => Http::response(['status' => true, 'data' => []])]);

        $this->asAdmin()->from(route('admin.institutes.create'))
            ->post('/admin/institutes', ['institute_name' => ''])
            ->assertRedirect(route('admin.institutes.create'))
            ->assertSessionHasErrors();
    }

    public function test_admin_can_update_an_institute(): void
    {
        Http::fake([
            '*/api/institutes/7' => Http::response(['status' => true, 'data' => ['id' => 7, 'name' => 'Renamed']]),
        ]);

        $this->asAdmin()->put('/admin/institutes/7', [
            'name' => 'Renamed', 'phone' => '01799999999', 'domain' => 'renamed.com', 'address' => 'X',
        ])->assertRedirect(route('admin.institutes.index'))->assertSessionHas('success');
    }

    public function test_a_stale_session_token_bounces_the_admin_to_login(): void
    {
        // Backend says the bearer token is no longer valid.
        Http::fake(['*/api/institutes*' => Http::response(['status' => false, 'message' => 'Unauthenticated.'], 401)]);

        $this->asAdmin()->get('/admin/institutes')->assertRedirect(route('login'));

        // …and the dead token is cleared from the session.
        $this->assertNull(session(config('saas.auth.token_key')));
    }

    public function test_admin_can_toggle_institute_status(): void
    {
        Http::fake([
            '*/api/institutes-status-update/7' => Http::response(['status' => true, 'message' => 'Institute status updated successfully.']),
        ]);

        $this->asAdmin()->from(route('admin.institutes.index'))
            ->post('/admin/institutes/7/toggle-status')
            ->assertRedirect(route('admin.institutes.index'))
            ->assertSessionHas('success');

        Http::assertSent(fn ($r) => str_contains($r->url(), '/api/institutes-status-update/7'));
    }
}
