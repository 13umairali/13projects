<?php

namespace App\Http\Middleware;

use App\Helpers\InstituteHelper;
use App\Support\InstallState;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only serve web pages on hosts that belong to a registered institute (or to the platform itself).
 *
 * Before this, InstituteHelper::resolveInstituteFromRequest() fell back to "the first active
 * institute" for ANY host, so with wildcard DNS every made-up subdomain (zsas.dahidemy.com) or any
 * domain pointed at this server opened a working site showing institute #1's content.
 *
 *  - unknown host                         -> 404 (this middleware)
 *  - platform hosts (APP_URL host, APP_ROOT_DOMAIN, www, TENANT_DEFAULT_HOSTS) are always allowed
 *  - deleted / deactivated institutes keep being handled by the existing global CheckInstituteActive
 *    middleware (503 "institute unavailable" page), which runs before this one.
 *
 * Kill switch: TENANT_REJECT_UNKNOWN_HOSTS=false.
 */
class RejectUnknownInstituteHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.tenant_reject_unknown_hosts', true) || $request->is('up')) {
            return $next($request);
        }

        // Fresh installs (no marker, no users, no DB yet) must still reach the installer.
        if (! InstallState::installed() || ! Schema::hasTable('institutes')) {
            return $next($request);
        }

        $host = strtolower(trim((string) $request->getHost(), ". \t\n\r\0\x0B"));
        $host = str_starts_with($host, 'www.') ? substr($host, 4) : $host;

        if (InstituteHelper::isPlatformHost($host) || $this->isLocalDev($host)) {
            return $next($request);
        }

        $institute = InstituteHelper::findInstituteByStrictHost($request);

        if (! $institute) {
            return $this->deny(404, 'Site not found', 'This address is not linked to any institute.');
        }

        return $next($request);
    }

    private function isLocalDev(string $host): bool
    {
        return app()->environment(['local', 'testing']) && in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    /** Self-contained page: no layout, no institute lookup, and the host is never echoed back. */
    private function deny(int $status, string $title, string $message): Response
    {
        $html = '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            .'<meta name="viewport" content="width=device-width, initial-scale=1">'
            .'<meta name="robots" content="noindex"><title>'.e($title).'</title>'
            .'<style>body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;'
            .'font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:#f5f7fa;color:#1e2a3a}'
            .'main{text-align:center;padding:32px}h1{font-size:64px;margin:0;color:#3b6cf6}'
            .'p{font-size:18px;margin:12px 0 0;color:#5a6a7e}</style></head><body data-tenant-reject="'.$status.'"><main>'
            .'<h1>'.$status.'</h1><h2>'.e($title).'</h2><p>'.e($message).'</p></main></body></html>';

        return response($html, $status, ['Content-Type' => 'text/html; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }
}
