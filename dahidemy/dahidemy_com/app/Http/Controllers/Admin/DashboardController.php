<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\SaasAdminService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected SaasAdminService $admin
    ) {}

    public function index(Request $request)
    {
        $response = $this->admin->dashboard($request->integer('year') ?: null);
        $data = $response->data([]);

        return view('admin.dashboard', [
            'reachable' => $response->successful(),
            'statistics' => data_get($data, 'statistics', []),
            'charts' => data_get($data, 'charts', []),
            'latestSubscriptions' => collect(data_get($data, 'latest_subscriptions', [])),
            'expiringSubscriptions' => collect(data_get($data, 'expiring_subscriptions', [])),
            'recentPayments' => collect(data_get($data, 'recent_payments', [])),
            'topInstitutes' => collect(data_get($data, 'top_paying_institutes', [])),
        ]);
    }
}
