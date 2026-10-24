<?php

namespace Tests\Feature;

use App\Support\ApiCache;
use App\Support\Status;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Covers the two things that make the admin panel trustworthy and fast:
 *
 *  - institute status is read strictly (only 1 is active), so a row stored as 0
 *    OR as 2 is shown — and filtered — as inactive;
 *  - backend responses are cached, deduplicated within a request, and dropped
 *    the moment anything is written or the admin clears the cache.
 */
class CacheAndStatusTest extends TestCase
{
    protected function asAdmin(): static
    {
        return $this->withSession([
            config('saas.auth.token_key') => 'test-token',
            config('saas.auth.user_key') => ['name' => 'Test Admin', 'email' => 'admin@example.com'],
        ]);
    }

    /** A two-row institute list: one active, one inactive. */
    private function fakeInstitutes(int $inactiveStatus = 0): void
    {
        Http::fake([
            '*/api/institutes*' => Http::response(['status' => true, 'data' => [
                'current_page' => 1, 'per_page' => 10, 'total' => 2,
                'data' => [
                    ['id' => 1, 'name' => 'Active Academy', 'email' => 'a@a.test', 'domain' => 'a.test', 'status' => 1, 'branches' => []],
                    ['id' => 2, 'name' => 'Dormant College', 'email' => 'd@d.test', 'domain' => 'd.test', 'status' => $inactiveStatus, 'branches' => []],
                ],
            ]]),
            '*' => Http::response(['status' => true, 'data' => []]),
        ]);
    }

    /** How many calls hit an endpoint containing $needle. */
    private function callsTo(string $needle): int
    {
        return collect(Http::recorded())
            ->filter(fn ($pair) => str_contains($pair[0]->url(), $needle))
            ->count();
    }

    /* ---------------------------------------------------------------- Status */

    public function test_only_status_one_is_active(): void
    {
        $this->assertTrue(Status::isActive(1));
        $this->assertTrue(Status::isActive('1'));

        // 0 is what deactivating writes; 2 is what the schema documented. Both
        // are inactive, and 2 is exactly the value a boolean cast got wrong.
        $this->assertFalse(Status::isActive(0));
        $this->assertFalse(Status::isActive(2));
        $this->assertFalse(Status::isActive('2'));
        $this->assertFalse(Status::isActive(null));
    }

    public function test_institute_stored_as_two_is_shown_as_inactive(): void
    {
        $this->fakeInstitutes(inactiveStatus: 2);

        $response = $this->asAdmin()->get('/admin/institutes');

        $response->assertOk();
        $response->assertSee('Dormant College');
        $response->assertSee('Inactive');
        // The inactive row carries its own marker, not just a pill.
        $response->assertSee('border-l-rose-400', false);
        // …and the toggle offers to activate it, rather than deactivate it.
        $response->assertSee('title="Activate"', false);
    }

    public function test_status_filter_is_passed_to_the_backend(): void
    {
        $this->fakeInstitutes();

        $this->asAdmin()->get('/admin/institutes?status=0')->assertOk();

        Http::assertSent(fn ($r) => str_contains($r->url(), '/api/institutes')
            && str_contains($r->url(), 'status=0'));
    }

    public function test_an_invalid_status_filter_is_ignored(): void
    {
        $this->fakeInstitutes();

        $this->asAdmin()->get('/admin/institutes?status=drop-table')->assertOk();

        Http::assertSent(fn ($r) => str_contains($r->url(), '/api/institutes')
            && ! str_contains($r->url(), 'status='));
    }

    /* ---------------------------------------------------------------- Cache */

    public function test_ttl_is_clamped_to_the_configured_window(): void
    {
        $cache = app(ApiCache::class);

        config(['saas.cache.ttl' => 60]);           // below the floor
        $this->assertSame(1800, $cache->ttl());     // → 30 minutes

        config(['saas.cache.ttl' => 999999]);       // above the ceiling
        $this->assertSame(86400, $cache->ttl());    // → 1 day

        config(['saas.cache.ttl' => 3600]);         // inside the window
        $this->assertSame(3600, $cache->ttl());

        config(['saas.cache.ttl' => 0]);            // 0 switches caching off
        $this->assertSame(0, $cache->ttl());
        $this->assertFalse($cache->enabled());
    }

    public function test_a_repeated_read_is_served_from_cache(): void
    {
        config(['saas.cache.enabled' => true, 'saas.cache.ttl' => 1800]);
        $this->fakeInstitutes();

        $this->asAdmin()->get('/admin/institutes')->assertOk();
        $this->asAdmin()->get('/admin/institutes')->assertOk();

        $this->assertSame(1, $this->callsTo('/api/institutes?'), 'The second page view should not re-hit the backend.');
    }

    public function test_the_same_endpoint_is_fetched_once_per_request_even_with_caching_off(): void
    {
        config(['saas.cache.enabled' => false]);

        Http::fake(['*' => Http::response(['status' => true, 'data' => [
            'total_institutes' => 5, 'current_month_revenue' => 199, 'last_month_revenue' => 299,
        ]])]);

        // The home page reads saas-stats twice: once for the counters, once for
        // the revenue chart. Two consumers, one request — the API-client N+1.
        $this->get('/')->assertOk();

        $this->assertSame(
            1,
            $this->callsTo('/api/saas-stats'),
            'saas-stats should be fetched once per request, not once per consumer.'
        );
    }

    public function test_a_write_invalidates_cached_reads(): void
    {
        config(['saas.cache.enabled' => true, 'saas.cache.ttl' => 1800]);
        $this->fakeInstitutes();

        $this->asAdmin()->get('/admin/institutes')->assertOk();
        $this->assertSame(1, $this->callsTo('/api/institutes?'));

        // Toggling status is a write, even though the backend models it as a GET.
        $this->asAdmin()->post('/admin/institutes/2/toggle-status')->assertRedirect();

        $this->asAdmin()->get('/admin/institutes')->assertOk();

        $this->assertSame(
            2,
            $this->callsTo('/api/institutes?'),
            'After a write the list must be re-fetched, not served stale.'
        );
    }

    public function test_a_post_write_also_invalidates_cached_reads(): void
    {
        config(['saas.cache.enabled' => true, 'saas.cache.ttl' => 1800]);

        Http::fake([
            '*/api/get-saas-settings' => Http::response(['status' => true, 'data' => [
                'site_name' => 'Mighty School', 'site_title' => 'SaaS', 'email' => 'a@b.test', 'phone' => '0123456',
            ]]),
            '*' => Http::response(['status' => true, 'message' => 'Saved']),
        ]);

        $this->asAdmin()->get('/admin/settings')->assertOk();
        $this->assertSame(1, $this->callsTo('/api/get-saas-settings'));

        $this->asAdmin()->post('/admin/settings', [
            'site_name' => 'Renamed', 'site_title' => 'SaaS', 'email' => 'a@b.test', 'phone' => '0123456',
        ])->assertRedirect();

        $this->asAdmin()->get('/admin/settings')->assertOk();

        $this->assertSame(
            2,
            $this->callsTo('/api/get-saas-settings'),
            'Saving settings must re-read them, so the page shows what was just stored.'
        );
    }

    public function test_clear_cache_button_clears_both_sides(): void
    {
        config(['saas.cache.enabled' => true, 'saas.cache.ttl' => 1800]);
        $this->fakeInstitutes();

        $this->asAdmin()->get('/admin/institutes')->assertOk();

        $this->asAdmin()->post('/admin/cache/clear')
            ->assertRedirect()
            ->assertSessionHas('success');

        // The backend's own caches are cleared in the same click…
        Http::assertSent(fn ($r) => str_contains($r->url(), '/api/system/clear-cache'));

        // …and the panel re-fetches on the next read.
        $this->asAdmin()->get('/admin/institutes')->assertOk();
        $this->assertSame(2, $this->callsTo('/api/institutes?'));
    }

    public function test_clear_cache_reports_a_backend_failure_honestly(): void
    {
        Http::fake([
            '*/api/system/clear-cache' => Http::response(['status' => false, 'message' => 'Unauthorised.'], 500),
            '*' => Http::response(['status' => true, 'data' => []]),
        ]);

        $this->asAdmin()->post('/admin/cache/clear')
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_guests_cannot_clear_the_cache(): void
    {
        Http::fake();

        $this->post('/admin/cache/clear')->assertRedirect(route('login'));

        Http::assertNothingSent();
    }
}
