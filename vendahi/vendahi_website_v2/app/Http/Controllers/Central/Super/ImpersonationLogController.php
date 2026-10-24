<?php

namespace App\Http\Controllers\Central\Super;

use App\Http\Controllers\Controller;
use App\Models\Central\ImpersonationLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImpersonationLogController extends Controller
{
    /**
     * GET /super/impersonation-logs
     * Security audit trail of all "login as user" sessions.
     */
    public function index(Request $request): View
    {
        ImpersonationLog::sweepStale();

        $query = ImpersonationLog::query()->latest('id');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('admin_name', 'like', "%{$search}%")
                  ->orWhere('admin_email', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('tenant_name', 'like', "%{$search}%")
                  ->orWhere('tenant_id', 'like', "%{$search}%");
            });
        }

        if (($status = $request->input('status')) !== null && $status !== '') {
            $query->where('status', $status);
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('central.super.impersonation-logs.index', compact('logs'));
    }

    /**
     * GET /super/impersonation-logs/{log}
     */
    public function show(ImpersonationLog $log): View
    {
        return view('central.super.impersonation-logs.show', compact('log'));
    }
}
