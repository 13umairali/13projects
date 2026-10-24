<?php

namespace App\Services\License;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LicenseManager
{
    public function check()
    {

        return Cache::remember(
            'license_status',
            now()->addHours(12),
            function () {

                return $this->validate();
            }
        );
    }

    private function validate()
    {

        try {

            $response = Http::withHeaders([

                'X-System-Key' => $this->signature(),

            ])
                ->timeout(8)
                ->post(
                    config('license.server_url'),
                    [

                        'code' => config('license.purchase_code'),

                        'domain' => request()->getHost(),

                        'url' => config('app.url'),

                    ]
                );

            if ($response->successful()) {

                return $response->json();
            }
        } catch (\Throwable $e) {
        }

        return [

            'status' => false,

        ];
    }

    private function signature()
    {

        return hash_hmac(
            'sha256',

            config('app.key'),

            config('license.purchase_code')

        );
    }
}
