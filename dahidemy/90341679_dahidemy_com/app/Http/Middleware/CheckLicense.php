<?php

namespace App\Http\Middleware;

use App\Services\License\LicenseManager;
use Closure;

class CheckLicense
{
    public function handle($request, Closure $next)
    {

        $result =
            app(LicenseManager::class)->check();

        if (
            empty($result)
            ||
            ! isset($result['status'])
            ||
            $result['status'] !== true
        ) {

            return response()->json([

                'error' => base64_encode(
                    encrypt(
                        [
                            'code' => 'SYSTEM_ERROR',
                            'message' => 'Application configuration error',
                        ]
                    )
                ),

            ], 403);
        }

        return $next($request);
    }
}
