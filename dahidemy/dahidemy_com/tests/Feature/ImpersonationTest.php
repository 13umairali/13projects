<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * "Login as institute" from the SaaS panel. The panel is a thin API client, so
 * these tests pin the contract it must honour:
 *
 *   - only a signed-in SaaS admin can trigger it (guests are bounced to login,
 *     and no backend call is made on their behalf);
 *   - it forwards a same-origin return_url so "Back to SaaS Admin" can bring the
 *     operator home;
 *   - a minted link is followed via an away-redirect, and never rendered inline;
 *   - a backend refusal (e.g. the caller is not actually a platform admin) is
 *     surfaced honestly instead of pretending success.
 */
class ImpersonationTest extends TestCase
{
    protected function asAdmin(): static
    {
        return $this->withSession([
            config('saas.auth.token_key') => 'test-token',
            config('saas.auth.user_key') => ['name' => 'Test Admin', 'email' => 'admin@example.com'],
        ]);
    }

    public function test_guests_cannot_start_impersonation(): void
    {
        Http::fake();

        $this->post('/admin/institutes/5/impersonate')->assertRedirect(route('login'));

        // The backend must never be asked to mint a link for an unauthenticated user.
        Http::assertNothingSent();
    }

    public function test_admin_is_redirected_to_the_minted_backend_link(): void
    {
        Http::fake([
            '*/api/institutes/5/impersonate' => Http::response(['status' => true, 'data' => [
                'url' => 'https://backend.test/impersonate/one-time-token',
                'expires_at' => now()->addMinutes(2)->toIso8601String(),
            ]]),
        ]);

        $this->asAdmin()
            ->post('/admin/institutes/5/impersonate')
            ->assertRedirect('https://backend.test/impersonate/one-time-token');
    }

    public function test_it_forwards_a_return_url_for_back_to_saas_admin(): void
    {
        Http::fake([
            '*/api/institutes/5/impersonate' => Http::response(['status' => true, 'data' => [
                'url' => 'https://backend.test/impersonate/one-time-token',
            ]]),
        ]);

        $this->asAdmin()->post('/admin/institutes/5/impersonate');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/institutes/5/impersonate')
                && ($request['return_url'] ?? null) === route('admin.institutes.index');
        });
    }

    public function test_a_backend_refusal_is_surfaced_and_no_redirect_off_site(): void
    {
        Http::fake([
            '*/api/institutes/5/impersonate' => Http::response([
                'status' => false,
                'message' => 'You are not authorized to impersonate institutes.',
            ], 403),
        ]);

        $this->asAdmin()
            ->from(route('admin.institutes.index'))
            ->post('/admin/institutes/5/impersonate')
            ->assertRedirect(route('admin.institutes.index'))
            ->assertSessionHas('error');
    }
}
