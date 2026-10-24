<?php

namespace App\Services\Setup;

use App\Services\Setup\Exceptions\EnvironmentWriteException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Orchestrates first-run installation.
 *
 * The only class that knows the *sequence*: probe, persist, refresh. It leans
 * on {@see BackendConnectionTester} and {@see EnvironmentService} for the work
 * and stays free of both HTTP and file-handling detail, so the controller can
 * stay free of all three.
 */
class SetupManager
{
    public function __construct(
        protected EnvironmentService $env,
        protected BackendConnectionTester $tester,
    ) {}

    /**
     * Has the panel been installed?
     *
     * Reads config (not the file) so a config-cached production deploy answers
     * from the cache like every other read, and so tests can drive it.
     */
    public function isConfigured(): bool
    {
        return (bool) config('saas.api.configured');
    }

    /**
     * May the wizard run? Only before installation, or when an administrator
     * has deliberately unlocked it server-side via SAAS_SETUP_ENABLED=true.
     */
    public function isUnlocked(): bool
    {
        return ! $this->isConfigured() || (bool) config('saas.setup.enabled');
    }

    public function test(string $url): ConnectionResult
    {
        return $this->tester->test($url);
    }

    public function normalize(string $url): string
    {
        return $this->tester->normalize($url);
    }

    /**
     * Verify then persist. The probe is re-run here rather than trusting the
     * browser's earlier "Test connection": the client could post a different
     * URL than the one it tested, and saving an unverified URL is exactly the
     * state the wizard exists to prevent.
     *
     * @throws EnvironmentWriteException when .env cannot be written.
     */
    public function save(string $url): ConnectionResult
    {
        $url = $this->normalize($url);

        $result = $this->test($url);

        if (! $result->ok) {
            return $result;
        }

        $this->env->updateEnv([config('saas.setup.env_key', 'FRONTEND_API_URL') => $url]);

        $this->refresh($url);

        return $result;
    }

    /* -----------------------------------------------------------------
     |  Internals
     | ----------------------------------------------------------------- */

    /**
     * Make the new URL live — both for the rest of this request and for the
     * next one.
     */
    protected function refresh(string $url): void
    {
        // This request has already booted with the old (empty) config, and the
        // response we are about to return redirects into the app. Without this,
        // that very next hop would still see an unconfigured panel and bounce
        // straight back to /setup — the classic installer redirect loop.
        config([
            'saas.api.configured' => true,
            'saas.api.base_url' => $url,
        ]);

        $this->clearCaches();
    }

    /**
     * Drop every cache that could still serve the pre-install state.
     *
     * Best-effort by design: a host with disabled proc functions or a read-only
     * bootstrap/cache can fail these while the .env write itself succeeded. The
     * install is already valid at that point, so we log and carry on rather
     * than show a failure the operator cannot act on.
     */
    protected function clearCaches(): void
    {
        // config:clear must run first — while a stale bootstrap/cache/config.php
        // exists, Laravel ignores .env entirely and the new URL would never load.
        $commands = ['config:clear', 'cache:clear', 'route:clear', 'view:clear'];

        // Only re-cache config if this deployment was config-cached to begin
        // with. Creating the cache on a host that never had one silently freezes
        // .env for every later edit — a nasty surprise to leave behind.
        if (app()->configurationIsCached()) {
            $commands[] = 'config:cache';
        }

        foreach ($commands as $command) {
            try {
                Artisan::call($command);
            } catch (Throwable $e) {
                Log::warning('[SaaS setup] cache command failed', [
                    'command' => $command,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
