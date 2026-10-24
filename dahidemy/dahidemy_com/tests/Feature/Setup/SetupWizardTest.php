<?php

namespace Tests\Feature\Setup;

use App\Services\Setup\EnvironmentService;
use App\Services\Setup\SetupManager;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Covers the first-run installer end to end:
 *
 *  - an unconfigured panel is unusable and always lands on /setup;
 *  - a configured panel seals the wizard shut;
 *  - a URL is only saved once it has been proven to be a live backend;
 *  - the .env rewrite updates in place, keeps comments, and never duplicates.
 */
class SetupWizardTest extends TestCase
{
    private string $envPath;

    protected function setUp(): void
    {
        parent::setUp();

        // A scratch .env per test so the real one is never touched, even if an
        // assertion fails halfway through a rewrite.
        $this->envPath = storage_path('framework/testing/.env.'.uniqid());

        File::ensureDirectoryExists(dirname($this->envPath));

        $this->app->instance(
            EnvironmentService::class,
            new EnvironmentService($this->envPath)
        );

        // SetupManager holds EnvironmentService, so it must be rebuilt from the
        // container after the swap above or it keeps the real path.
        $this->app->forgetInstance(SetupManager::class);

        Artisan::swap(new class
        {
            public function call($command, array $parameters = [], $outputBuffer = null)
            {
                return 0;
            }

            public function __call($method, $arguments)
            {
                return 0;
            }
        });
    }

    protected function tearDown(): void
    {
        if (isset($this->envPath) && File::exists($this->envPath)) {
            File::delete($this->envPath);
        }

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Helpers
     | ----------------------------------------------------------------- */

    private function unconfigured(): void
    {
        config(['saas.api.configured' => false, 'saas.setup.enabled' => false]);
    }

    private function configured(): void
    {
        config(['saas.api.configured' => true, 'saas.setup.enabled' => false]);
    }

    private function writeEnv(string $contents): void
    {
        File::put($this->envPath, $contents);
    }

    private function env(): string
    {
        return File::get($this->envPath);
    }

    /** A backend that answers the health probe like the real one does. */
    private function fakeHealthyBackend(): void
    {
        Http::fake([
            '*/api/check-status' => Http::response([
                'status' => true,
                'message' => 'API and Database are working perfectly',
            ]),
        ]);
    }

    /* -----------------------------------------------------------------
     |  The gate
     | ----------------------------------------------------------------- */

    public function test_unconfigured_panel_redirects_every_page_to_the_wizard(): void
    {
        $this->unconfigured();

        foreach (['/', '/pricing', '/login', '/admin'] as $path) {
            $this->get($path)->assertRedirect(route('setup.show'));
        }
    }

    public function test_unconfigured_panel_keeps_the_health_check_alive(): void
    {
        $this->unconfigured();

        // A load balancer must not conclude the container is dead just because
        // it has not been installed yet.
        $this->get('/up')->assertOk();
    }

    public function test_wizard_is_reachable_while_unconfigured(): void
    {
        $this->unconfigured();

        $this->get('/setup')
            ->assertOk()
            ->assertSee('Connect your backend');
    }

    public function test_unconfigured_panel_answers_json_callers_without_redirecting(): void
    {
        $this->unconfigured();

        $this->getJson('/pricing')
            ->assertStatus(503)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('setup_url', route('setup.show'));
    }

    /* -----------------------------------------------------------------
     |  The lock
     | ----------------------------------------------------------------- */

    public function test_configured_panel_seals_the_wizard(): void
    {
        $this->configured();

        $this->get('/setup')->assertRedirect(route('index'));
        $this->post('/setup/test', ['url' => 'http://evil.test'])->assertRedirect(route('index'));
        $this->post('/setup/save', ['url' => 'http://evil.test'])->assertRedirect(route('index'));
    }

    public function test_configured_panel_cannot_be_repointed_even_by_a_json_caller(): void
    {
        $this->configured();
        $this->writeEnv("FRONTEND_API_URL=http://real.test\n");
        $this->fakeHealthyBackend();

        $this->postJson('/setup/save', ['url' => 'http://evil.test'])
            ->assertStatus(403);

        $this->assertStringNotContainsString('evil.test', $this->env());
    }

    public function test_administrator_can_reopen_the_wizard_explicitly(): void
    {
        config(['saas.api.configured' => true, 'saas.setup.enabled' => true]);

        $this->get('/setup')->assertOk();
    }

    /* -----------------------------------------------------------------
     |  Validation + probing
     | ----------------------------------------------------------------- */

    public function test_url_must_be_present_and_http(): void
    {
        $this->unconfigured();

        foreach (['', 'not-a-url', 'ftp://files.test', 'javascript:alert(1)'] as $bad) {
            $this->postJson('/setup/test', ['url' => $bad])
                ->assertStatus(422)
                ->assertJsonValidationErrors('url');
        }
    }

    public function test_test_endpoint_reports_a_healthy_backend(): void
    {
        $this->unconfigured();
        $this->fakeHealthyBackend();

        $this->postJson('/setup/test', ['url' => 'http://backend.test'])
            ->assertOk()
            ->assertJsonPath('ok', true);
    }

    public function test_whitespace_and_trailing_slashes_are_normalised_before_probing(): void
    {
        $this->unconfigured();
        $this->fakeHealthyBackend();

        $this->postJson('/setup/test', ['url' => '  http://backend.test///  '])
            ->assertOk()
            ->assertJsonPath('ok', true);

        Http::assertSent(fn ($request) => $request->url() === 'http://backend.test/api/check-status');
    }

    public function test_a_404_is_reported_as_the_wrong_address_not_a_dead_server(): void
    {
        $this->unconfigured();
        Http::fake(['*' => Http::response('Not Found', 404)]);

        $this->postJson('/setup/test', ['url' => 'http://backend.test'])
            ->assertStatus(422)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('status', 404);
    }

    public function test_a_500_is_reported_as_a_backend_fault(): void
    {
        $this->unconfigured();
        Http::fake(['*' => Http::response(['status' => false], 500)]);

        $this->postJson('/setup/test', ['url' => 'http://backend.test'])
            ->assertStatus(422)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('status', 500);
    }

    public function test_a_200_that_is_not_this_api_is_rejected(): void
    {
        $this->unconfigured();

        // Some other web server answering on that port. Reachable, but useless.
        Http::fake(['*' => Http::response('<html>Welcome to nginx</html>')]);

        $this->postJson('/setup/test', ['url' => 'http://backend.test'])
            ->assertStatus(422)
            ->assertJsonPath('ok', false);
    }

    public function test_a_backend_reporting_a_dead_database_is_rejected(): void
    {
        $this->unconfigured();

        Http::fake(['*' => Http::response([
            'status' => false,
            'message' => 'SQLSTATE[HY000] [1045] Access denied for user',
        ])]);

        $this->postJson('/setup/test', ['url' => 'http://backend.test'])
            ->assertStatus(422)
            ->assertJsonPath('ok', false);
    }

    public function test_connection_failures_are_explained_in_plain_language(): void
    {
        $this->unconfigured();

        Http::fake(function () {
            throw new ConnectionException(
                'cURL error 6: Could not resolve host: nope.test'
            );
        });

        $response = $this->postJson('/setup/test', ['url' => 'http://nope.test'])
            ->assertStatus(422)
            ->assertJsonPath('ok', false);

        $this->assertStringContainsString('could not be resolved', $response->json('message'));
    }

    /* -----------------------------------------------------------------
     |  Saving
     | ----------------------------------------------------------------- */

    public function test_saving_updates_the_existing_key_in_place_and_preserves_comments(): void
    {
        $this->unconfigured();
        $this->fakeHealthyBackend();

        $this->writeEnv(<<<'ENV'
        APP_NAME="Mighty School"

        # ---------------------------------------------------------------
        # SaaS Panel -> Backend API
        # ---------------------------------------------------------------
        FRONTEND_API_URL=
        SAAS_API_PREFIX=api
        ENV);

        $this->postJson('/setup/save', ['url' => 'http://backend.test'])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('redirect', route('login'));

        $env = $this->env();

        $this->assertStringContainsString('FRONTEND_API_URL=http://backend.test', $env);
        $this->assertStringContainsString('# SaaS Panel -> Backend API', $env);
        $this->assertStringContainsString('APP_NAME="Mighty School"', $env);
        $this->assertStringContainsString('SAAS_API_PREFIX=api', $env);

        // Exactly one definition, or Dotenv's parse order decides the config.
        $this->assertSame(1, substr_count($env, 'FRONTEND_API_URL='));
    }

    public function test_saving_appends_the_key_when_it_is_missing(): void
    {
        $this->unconfigured();
        $this->fakeHealthyBackend();
        $this->writeEnv("APP_NAME=Test\n");

        $this->postJson('/setup/save', ['url' => 'http://backend.test'])->assertOk();

        $this->assertStringContainsString('FRONTEND_API_URL=http://backend.test', $this->env());
    }

    /**
     * Regression: the writer used PHP_EOL, so a Windows host rewrote an entire
     * LF .env to CRLF — a one-key change became an 87-line diff.
     */
    public function test_saving_keeps_the_files_existing_line_endings(): void
    {
        $this->unconfigured();
        $this->fakeHealthyBackend();
        $this->writeEnv("APP_NAME=Test\nFRONTEND_API_URL=\nSAAS_API_PREFIX=api\n");

        $this->postJson('/setup/save', ['url' => 'http://backend.test'])->assertOk();

        $env = $this->env();

        $this->assertStringNotContainsString("\r", $env, 'An LF .env must not gain CR characters.');
        $this->assertSame(3, substr_count($env, "\n"));
    }

    /**
     * Regression: rtrim($contents, "\n") on a CRLF file left the \r behind and
     * the appended terminator produced "\r\r\n" — a bare CR that Dotenv reads
     * as part of the preceding value.
     */
    public function test_saving_a_crlf_file_keeps_crlf_and_adds_no_stray_carriage_return(): void
    {
        $this->unconfigured();
        $this->fakeHealthyBackend();
        $this->writeEnv("APP_NAME=Test\r\nFRONTEND_API_URL=\r\nSAAS_API_PREFIX=api\r\n");

        $this->postJson('/setup/save', ['url' => 'http://backend.test'])->assertOk();

        $env = $this->env();

        $this->assertStringEndsWith("SAAS_API_PREFIX=api\r\n", $env);
        $this->assertStringNotContainsString("\r\r", $env);
        // Every CR belongs to a CRLF pair — no bare CR anywhere.
        $this->assertSame(substr_count($env, "\r\n"), substr_count($env, "\r"));
    }

    public function test_saving_reads_back_exactly_what_was_written(): void
    {
        $this->unconfigured();
        $this->fakeHealthyBackend();
        $this->writeEnv("FRONTEND_API_URL=\n");

        $this->postJson('/setup/save', ['url' => 'http://backend.test'])->assertOk();

        // The value Dotenv will hand to config() must be the URL and nothing
        // else — no trailing \r, no quotes.
        $this->assertSame(
            'http://backend.test',
            $this->app->make(EnvironmentService::class)->get('FRONTEND_API_URL')
        );
    }

    public function test_an_unreachable_backend_is_never_saved(): void
    {
        $this->unconfigured();
        Http::fake(['*' => Http::response('nope', 500)]);
        $this->writeEnv("FRONTEND_API_URL=\n");

        $this->postJson('/setup/save', ['url' => 'http://backend.test'])
            ->assertStatus(422)
            ->assertJsonPath('ok', false);

        // The whole point of the wizard: a bad URL must not reach .env.
        $this->assertStringContainsString("FRONTEND_API_URL=\n", $this->env());
    }

    public function test_save_reprobes_rather_than_trusting_the_client(): void
    {
        $this->unconfigured();
        $this->fakeHealthyBackend();
        $this->writeEnv("FRONTEND_API_URL=\n");

        $this->postJson('/setup/save', ['url' => 'http://backend.test'])->assertOk();

        // A client could post a URL it never tested, so save must verify too.
        Http::assertSentCount(1);
    }

    public function test_an_unwritable_env_reports_the_real_problem(): void
    {
        $this->unconfigured();
        $this->fakeHealthyBackend();

        // No .env at all — the shape of "you forgot to copy .env.example".
        $this->app->instance(
            EnvironmentService::class,
            new EnvironmentService(storage_path('framework/testing/does-not-exist/.env'))
        );
        $this->app->forgetInstance(SetupManager::class);

        $this->postJson('/setup/save', ['url' => 'http://backend.test'])
            ->assertStatus(500)
            ->assertJsonPath('ok', false);
    }

    public function test_configuration_is_live_for_the_rest_of_the_request(): void
    {
        $this->unconfigured();
        $this->fakeHealthyBackend();
        $this->writeEnv("FRONTEND_API_URL=\n");

        $this->postJson('/setup/save', ['url' => 'http://backend.test'])->assertOk();

        // Without this the redirect target would still see an unconfigured
        // panel and bounce straight back to /setup.
        $this->assertTrue(config('saas.api.configured'));
        $this->assertSame('http://backend.test', config('saas.api.base_url'));
    }
}
