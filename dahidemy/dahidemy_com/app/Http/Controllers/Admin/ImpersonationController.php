<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\ImpersonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

/**
 * "Login as school" — mints a one-time backend login link for an institute's
 * admin user and sends the SaaS admin's browser there. Holds no business
 * logic; the token, expiry and single-use enforcement all live on the backend,
 * which also re-verifies that the caller is a platform admin before issuing a
 * link (this panel's session guard is the first gate, the backend is the second).
 *
 * The SaaS admin's own panel session is never handed to the backend, so it
 * survives untouched: "Back to SaaS Admin" simply returns the browser here,
 * where the admin is still logged in.
 */
class ImpersonationController extends Controller
{
    public function __construct(
        protected ImpersonationService $impersonation,
    ) {}

    public function start(string $institute): RedirectResponse
    {
        // Tell the backend where "Back to SaaS Admin" should land: straight back
        // to the institute list in this panel. The backend validates this URL's
        // host before trusting it, so a stray value can never become an open
        // redirect off the platform.
        $returnUrl = route('admin.institutes.index');

        $response = $this->impersonation->start($institute, $returnUrl);

        $url = is_array($response->data()) ? ($response->data()['url'] ?? null) : null;

        if (! $response->successful() || ! $url) {
            Log::warning('[impersonation] panel could not start impersonation', [
                'institute_id' => $institute,
                'status' => $response->statusCode,
                'message' => $response->message,
            ]);

            return back()->with('error', $response->message ?: 'Could not start impersonation.');
        }

        return redirect()->away($url);
    }
}
