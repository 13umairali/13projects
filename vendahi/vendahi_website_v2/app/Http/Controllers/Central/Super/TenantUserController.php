<?php

namespace App\Http\Controllers\Central\Super;

use App\Http\Controllers\Controller;
use App\Models\Central\ImpersonationLog;
use App\Models\User;
use App\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantUserController extends Controller
{
    /**
     * GET /super/tenants/{tenant}/users
     * List all users for a given tenant.
     */
    public function index(Request $request, Tenant $tenant): View
    {
        $tenant->load('domains');

        $users = [];
        $error = null;

        try {
            $tenant->run(function () use (&$users, $request) {
                $query = User::with('roles');

                if ($search = $request->input('search')) {
                    $query->where(function ($q) use ($search) {
                        $q->where('firstname', 'like', "%{$search}%")
                          ->orWhere('lastname', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('username', 'like', "%{$search}%");
                    });
                }

                if ($request->has('status') && $request->input('status') !== '') {
                    $query->where('statut', (int) $request->input('status'));
                }

                $users = $query->orderBy('id')->get();
            });
        } catch (\Throwable $e) {
            $error = 'Could not connect to tenant database. The workspace may not be provisioned yet.';
        }

        return view('central.super.tenants.users', compact('tenant', 'users', 'error'));
    }

    /**
     * POST /super/tenants/{tenant}/users/{userId}/block
     */
    public function block(Tenant $tenant, int $userId): RedirectResponse
    {
        $userName = '';

        $tenant->run(function () use ($userId, &$userName) {
            $user = User::findOrFail($userId);
            $user->update(['statut' => 0]);
            $userName = trim($user->firstname . ' ' . $user->lastname);
        });

        return back()->with('success', "User \"{$userName}\" has been blocked.");
    }

    /**
     * POST /super/tenants/{tenant}/users/{userId}/unblock
     */
    public function unblock(Tenant $tenant, int $userId): RedirectResponse
    {
        $userName = '';

        $tenant->run(function () use ($userId, &$userName) {
            $user = User::findOrFail($userId);
            $user->update(['statut' => 1]);
            $userName = trim($user->firstname . ' ' . $user->lastname);
        });

        return back()->with('success', "User \"{$userName}\" has been unblocked.");
    }

    /**
     * POST /super/tenants/{tenant}/users/{userId}/password
     */
    public function changePassword(Request $request, Tenant $tenant, int $userId): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $userName = '';
        $hashed = Hash::make($validated['password']);

        $tenant->run(function () use ($userId, $hashed, &$userName) {
            $user = User::findOrFail($userId);
            $user->update(['password' => $hashed]);
            $userName = trim($user->firstname . ' ' . $user->lastname);
        });

        return back()->with('success', __('central.PasswordChangedFor', ['name' => $userName]));
    }

    /**
     * POST /super/tenants/{tenant}/users/{userId}/impersonate
     *
     * Issue a single-use, short-lived login token for the tenant user and
     * redirect the admin to the tenant domain, where the token is exchanged
     * for a normal web session. The user's password is never read or changed.
     */
    public function impersonate(Request $request, Tenant $tenant, int $userId): RedirectResponse
    {
        if (! $tenant->isActive()) {
            return back()->with('error', __('central.ImpersonateTenantNotActive'));
        }

        $tenantUrl = $tenant->getTenantUrl();
        if (! $tenantUrl) {
            return back()->with('error', __('central.ImpersonateNoDomain'));
        }

        // Resolve the target user inside the tenant DB (password stays untouched).
        $target = null;
        try {
            $tenant->run(function () use ($userId, &$target) {
                if ($user = User::find($userId)) {
                    $target = [
                        'name'   => trim($user->firstname . ' ' . $user->lastname),
                        'email'  => (string) $user->email,
                        'statut' => (int) $user->statut,
                    ];
                }
            });
        } catch (\Throwable $e) {
            return back()->with('error', __('central.ImpersonateTenantDbError'));
        }

        if (! $target) {
            return back()->with('error', __('central.ImpersonateUserNotFound'));
        }

        if ($target['statut'] !== 1) {
            return back()->with('error', __('central.ImpersonateUserBlocked'));
        }

        $admin = Auth::guard('central')->user();
        $token = Str::random(64);

        ImpersonationLog::create([
            'token_hash'      => hash('sha256', $token),
            'central_user_id' => $admin->id,
            'admin_name'      => (string) $admin->name,
            'admin_email'     => (string) $admin->email,
            'tenant_id'       => (string) $tenant->id,
            'tenant_name'     => $tenant->company_name ?? ($tenant->domains()->first()->domain ?? $tenant->id),
            'user_id'         => $userId,
            'user_name'       => $target['name'],
            'user_email'      => $target['email'],
            'ip_address'      => $request->ip(),
            'user_agent'      => Str::limit((string) ($request->userAgent() ?? ''), 490, ''),
            'status'          => ImpersonationLog::STATUS_PENDING,
        ]);

        return redirect()->away($tenantUrl . '/impersonate/' . $token);
    }
}
