<?php

namespace App\Http\Middleware;

use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;

class Student
{
    use ResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if the authenticated user is not a student
        if (auth()->user()->user_type != 'Student') {
            return $this->responseError([], _lang('You are not authorized to access this feature!'), 403);
        }

        return $next($request);
    }
}
