<?php

// app/Http/Middleware/SetSessionConfig.php

namespace App\Http\Middleware;

use Closure;
use Config;

class SetSessionConfig
{
    public function handle($request, Closure $next)
    {
        $host = (string) $request->getHost();


        Config::set('session.domain', null);

        // The storefront keeps its own session cookie, scoped to its base
        // path (default /online_store or the tenant's custom path).
        if (is_store_request($request)) {
            Config::set('session.path', '/'.store_path());
            Config::set('session.cookie', 'store_session');
        } else {
            Config::set('session.path', '/');
            Config::set('session.cookie', 'web_session');
        }

        return $next($request);
    }
}
