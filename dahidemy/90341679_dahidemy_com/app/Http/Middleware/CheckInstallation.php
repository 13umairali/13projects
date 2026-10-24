<?php

namespace App\Http\Middleware;

use Closure;

class CheckInstallation
{
    public function handle($request, Closure $next)
    {
        if (! \App\Support\InstallState::installed()) {
            return redirect('/install');
        }

        return $next($request);
    }
}
