<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The public institution register page collects the full onboarding payload and
 * submits it to the backend frontend/onboarding endpoint.
 */
class PublicRegisterTest extends TestCase
{
    public function test_register_page_renders(): void
    {
        Http::fake([
            '*/api/saas/institute-types' => Http::response(['status' => true, 'data' => [
                ['id' => 1, 'name' => 'Coaching Center'],
            ]]),
            '*/api/saas/*' => Http::response(['status' => true, 'data' => []]),
            '*/api/get-saas-settings' => Http::response(['status' => true, 'data' => []]),
        ]);

        $this->get('/register')
            ->assertOk()
            ->assertSee('Register your institution')
            ->assertSee('Coaching Center');
    }

    public function test_register_submits_onboarding_payload(): void
    {
        Http::fake([
            '*/api/frontend/onboarding' => Http::response(['status' => true, 'message' => 'Onboarding request submitted successfully.']),
        ]);

        $response = $this->post('/register', [
            'institute_name' => 'Sunrise Academy',
            'institute_email' => 'admin@sunrise.test',
            'institute_phone' => '01700000000',
            'institute_domain' => 'sunrise.test',
            'institute_type' => 'Coaching Center',
            'user_name' => 'Rafiq Khan',
            'user_email' => 'rafiq@sunrise.test',
            'user_phone' => '01800000000',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('register.show'));
        $response->assertSessionHas('success');

        Http::assertSent(fn ($r) => $r->method() === 'POST'
            && str_contains($r->url(), '/api/frontend/onboarding'));
    }

    public function test_register_validation_blocks_missing_required_fields(): void
    {
        Http::fake();

        $this->post('/register', ['institute_name' => ''])
            ->assertSessionHasErrors(['institute_name', 'institute_email', 'user_name', 'password', 'terms']);

        Http::assertNothingSent();
    }
}
