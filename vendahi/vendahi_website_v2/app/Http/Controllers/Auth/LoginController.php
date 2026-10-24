<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserLoginSession;
use App\Support\LoginSessionKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Where to send a staff member after a successful login.
     *
     * Resolved per request rather than stored in $redirectTo: in root-domain
     * mode '/' is the storefront, so the constant would land staff on the
     * shop front instead of the admin app.
     */
    protected function redirectTo(): string
    {
        return app_home_url();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    /**
     * Get the needed authorization credentials from the request.
     *
     * @return array
     */
    protected function credentials(\Illuminate\Http\Request $request)
    {
        return ['email' => $request->{$this->username()}, 'password' => $request->password, 'statut' => 1];
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {

        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = (bool) $request->input('remember', false);

        if (Auth::attempt($this->credentials($request), $remember)) {
            // Regenerate the web session ID to prevent fixation
            $request->session()->regenerate();

            // Record the session for Settings > Login Devices. It must be keyed
            // the way the api guard can recompute it (see LoginSessionKey): the
            // admin authenticates with Passport's cookie guard, so there is no
            // oauth_access_tokens row to key off. updateOrCreate() matches on
            // access_token_id because that is the column carrying the unique index.
            try {
                $user = Auth::guard('web')->user();
                $sessionKey = LoginSessionKey::fromSession($request);

                if ($user && $sessionKey) {
                    UserLoginSession::query()->updateOrCreate(
                        ['access_token_id' => $sessionKey],
                        [
                            'user_id' => (int) $user->id,
                            'session_id' => $request->session()->getId(),
                            'ip_address' => $request->ip(),
                            'user_agent' => (string) ($request->userAgent() ?? ''),
                            'logged_in_at' => now(),
                            'last_activity_at' => now(),
                            'revoked_at' => null,
                        ]
                    );
                }
            } catch (\Throwable $e) {
                // Never break login if tracking fails
            }

            return redirect()->intended($this->redirectTo());
        }

        // Failed login
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'These credentials do not match our records.',
            ], 422);
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        // 0) If this session was a super-admin impersonation, close its audit trail
        try {
            $state = $request->session()->get(\App\Http\Controllers\ImpersonationController::SESSION_KEY);
            if (! empty($state['log_id'])) {
                $log = \App\Models\Central\ImpersonationLog::find($state['log_id']);
                if ($log && $log->isActive()) {
                    $log->end('logout');
                }
                \App\Http\Controllers\ImpersonationController::clearCacheFlagIfIdle();
            }
        } catch (\Throwable $e) {
            // Never break logout if audit bookkeeping fails
        }

        // 1) Explicitly log out the web guard
        $user = Auth::guard('web')->user();

        if ($user) {
            // Clear remember token to avoid automatic re-authentication
            $user->setRememberToken(null);
            $user->save();
        }

        // 1b) Close this device's row in Settings > Login Devices. Without it the
        //     session stays listed as active forever, because the web session is
        //     only a file on disk and nothing else can observe it ending.
        try {
            $sessionKey = LoginSessionKey::fromSession($request);
            if ($user && $sessionKey) {
                UserLoginSession::query()
                    ->where('user_id', (int) $user->id)
                    ->where(function ($q) use ($sessionKey, $request) {
                        $q->where('access_token_id', $sessionKey)
                            ->orWhere('session_id', $request->session()->getId());
                    })
                    ->whereNull('revoked_at')
                    ->update(['revoked_at' => now(), 'updated_at' => now()]);
            }
        } catch (\Throwable $e) {
            // Never break logout if tracking fails
        }

        Auth::guard('web')->logout();

        // 2) Fully invalidate the web session
        //    - clears all session data
        //    - regenerates the session ID
        $request->session()->invalidate();

        // 3) Regenerate CSRF token for the new empty session
        $request->session()->regenerateToken();

        // 4) For SPA (AJAX) logout calls, return JSON and let the frontend
        //    perform a full-page navigation with window.location.replace('/login')
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        // 5) For classic form logouts, redirect to /login
        return redirect()->route('login');
    }
    
    
    

    /**
     * Get the login username to be used by the controller.
     */
    public function username()
    {
        return 'email';
    }
}
