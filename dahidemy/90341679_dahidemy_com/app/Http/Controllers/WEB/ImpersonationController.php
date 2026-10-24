<?php

namespace App\Http\Controllers\WEB;

use App\Helpers\InstituteHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Authentication\Models\User;

/**
 * Consumes the single-use link minted by POST api/institutes/{id}/impersonate and signs the browser
 * in as that institute's admin. Every failure looks the same (generic message, back to /login).
 */
class ImpersonationController extends Controller
{
    public function consume(Request $request, string $token): RedirectResponse
    {
        $fail = fn () => redirect()->route('login')->with('error', 'This sign-in link is invalid or has expired.');

        if (! preg_match('/^[A-Za-z0-9]{64}$/', $token) || ! Schema::hasTable('impersonation_tokens')) {
            return $fail();
        }

        $row = DB::table('impersonation_tokens')->where('token_hash', hash('sha256', $token))->first();

        if (! $row || $row->used_at || now()->gt($row->expires_at)) {
            return $fail();
        }

        // The link only works on the institute's own domain, so it cannot be replayed elsewhere.
        $hostInstitute = InstituteHelper::findInstituteByStrictHost($request);

        if (! $hostInstitute || (int) $hostInstitute->id !== (int) $row->institute_id) {
            return $fail();
        }

        // Atomic single use: only one request can flip used_at from NULL.
        $claimed = DB::table('impersonation_tokens')
            ->where('id', $row->id)
            ->whereNull('used_at')
            ->update([
                'used_at' => now(),
                'used_ip' => $request->ip(),
                'used_user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
                'updated_at' => now(),
            ]);

        if (! $claimed) {
            return $fail();
        }

        $user = User::query()
            ->where('id', $row->target_user_id)
            ->where('institute_id', $row->institute_id)
            ->where('status', 1)
            ->first();

        if (! $user) {
            return $fail();
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        $request->session()->put('impersonation', [
            'impersonator_id' => (int) $row->impersonator_id,
            'institute_id' => (int) $row->institute_id,
            'institute_name' => $hostInstitute->name,
            'return_url' => $row->return_url,
            'started_at' => now()->toIso8601String(),
        ]);

        Log::info('[impersonation] session started', [
            'impersonator_id' => $row->impersonator_id,
            'institute_id' => $row->institute_id,
            'user_id' => $user->id,
            'ip' => $request->ip(),
        ]);

        return redirect('dashboard');
    }

    public function leave(Request $request): RedirectResponse
    {
        $impersonation = $request->session()->get('impersonation');

        Log::info('[impersonation] session ended', [
            'impersonator_id' => $impersonation['impersonator_id'] ?? null,
            'institute_id' => $impersonation['institute_id'] ?? null,
            'user_id' => Auth::id(),
        ]);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $returnUrl = $impersonation['return_url'] ?? null;

        return $returnUrl ? redirect()->away($returnUrl) : redirect()->route('login');
    }
}
