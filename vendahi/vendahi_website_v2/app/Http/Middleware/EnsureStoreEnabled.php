<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsureStoreEnabled
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $enabled = DB::table('store_settings')->where('id', 1)->value('enabled');
        } catch (\Throwable) {
            // Table may not exist yet
            return $next($request);
        }

        if (! $enabled) {
            // Root-domain mode: "/" is the storefront, so a disabled store
            // would turn the tenant homepage into a dead 404. Send staff to
            // the app instead (guests land on the login page from there).
            if (store_is_root_domain() && $request->path() === '/') {
                return redirect('/app/dashboard');
            }

            abort(404);
        }

        return $next($request);
    }
}
