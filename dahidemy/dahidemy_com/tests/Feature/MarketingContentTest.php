<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Guards the "no static data" contract: the public marketing site must render
 * only records supplied by the backend API. When the backend has nothing it
 * shows empty states — never fabricated demo content.
 */
class MarketingContentTest extends TestCase
{
    /** Demo strings that used to be hard-coded and must never reappear. */
    private const REMOVED_DEMO = [
        'Aisha Rahman', 'Bright Future College', 'Save 20+ hours a week',
        'Starter', 'Growth', 'Scale', '4,820', '$92,140', '96.4%',
    ];

    public function test_home_renders_empty_states_when_backend_has_no_data(): void
    {
        Http::fake([
            '*/api/get-saas-settings' => Http::response(['status' => true, 'data' => []]),
            '*/api/saas-stats' => Http::response(['status' => true, 'data' => []]),
            '*/api/saas/*' => Http::response(['status' => true, 'data' => []]),
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('No features published yet');
        $response->assertSee('No plans available yet');
        $response->assertSee('No testimonials yet');
        $response->assertSee('No FAQs published yet');
        $response->assertSee('No stats to show yet');

        foreach (self::REMOVED_DEMO as $needle) {
            $response->assertDontSee($needle);
        }
    }

    public function test_home_renders_real_records_from_the_backend(): void
    {
        Http::fake([
            '*/api/get-saas-settings' => Http::response(['status' => true, 'data' => [
                'site_name' => 'Mighty School',
            ]]),
            '*/api/saas-stats' => Http::response(['status' => true, 'data' => [
                'total_institutes' => 5,
                'active_institutes' => 5,
                'inactive_institutes' => 0,
                'total_plans' => 3,
                'active_subscriptions' => 5,
                'expired_subscriptions' => 0,
                'pending_subscriptions' => 0,
                'total_revenue' => 498,
                'current_month_revenue' => 199,
                'last_month_revenue' => 299,
                'revenue_growth' => -33.44,
            ]]),
            '*/api/saas/cores' => Http::response(['status' => true, 'data' => [
                ['id' => 1, 'title' => 'Admissions Suite', 'description' => 'Digital admissions.', 'icon' => 'user-plus'],
            ]]),
            '*/api/saas/packages' => Http::response(['status' => true, 'data' => [
                ['id' => 5, 'name' => 'Campus Pro', 'price' => 99, 'duration_days' => 30, 'student_limit' => 500, 'branch_limit' => 3],
            ]]),
            '*/api/saas/feedback' => Http::response(['status' => true, 'data' => [
                ['id' => 2, 'name' => 'Nadia Islam', 'designation' => 'Head, Cityscape School', 'comment' => 'Transformed our fee cycle.', 'rating' => 5],
            ]]),
            '*/api/saas/faqs' => Http::response(['status' => true, 'data' => [
                ['id' => 3, 'question' => 'Can I import my data?', 'answer' => 'Yes, guided imports are included.'],
            ]]),
            '*/api/saas/*' => Http::response(['status' => true, 'data' => []]),
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Admissions Suite');
        $response->assertSee('Campus Pro');
        $response->assertSee('500 students');      // feature derived from real limit
        $response->assertSee('Nadia Islam');
        $response->assertSee('Can I import my data?');
        $response->assertSee('Total institutions');     // counters sourced from saas-stats
        $response->assertSee('Inactive institutions');  // a real 0 is still a figure, not a gap
        $response->assertSee('Revenue momentum');       // month-on-month revenue chart
        $response->assertSee('-33.44%');

        $response->assertDontSee('No features published yet');
        $response->assertDontSee('No plans available yet');
        $response->assertDontSee('No stats to show yet');
    }
}
