<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\SystemService;
use App\Support\ApiCache;
use Illuminate\Http\RedirectResponse;

/**
 * The "Clear cache" button in the admin header.
 *
 * One click clears both sides: this panel's cached API responses, and the
 * backend's own caches (config/route/view/data) via its system endpoint. If the
 * backend call fails the panel cache is still cleared — a half-clear is reported
 * honestly rather than swallowed.
 */
class CacheController extends Controller
{
    public function __construct(
        protected ApiCache $cache,
        protected SystemService $system,
    ) {}

    public function clear(): RedirectResponse
    {
        $this->cache->flush();

        $response = $this->system->clearCache();

        if ($response->successful()) {
            return back()->with('success', 'Cache cleared — panel and backend.');
        }

        return back()->with(
            'error',
            'Panel cache cleared, but the backend cache could not be: '
                .($response->message ?: 'the API did not respond.')
        );
    }
}
