<?php

namespace App\Http\Middleware;

use App\Services\Api\AuthenticationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the SaaS admin area. Authentication state is the presence of a
 * backend-issued bearer token in the session (see AuthenticationService).
 */
class EnsureAdminAuthenticated
{
    public function __construct(
        protected AuthenticationService $auth
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->auth->check()) {
            return redirect()
                ->guest(route('login'))
                ->with('error', 'Please sign in to access the dashboard.');
        }

        return $next($request);
    }
}
