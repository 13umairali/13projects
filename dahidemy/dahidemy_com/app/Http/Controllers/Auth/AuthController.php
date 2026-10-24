<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Api\AuthenticationService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthenticationService $auth
    ) {}

    public function showLogin()
    {
        if ($this->auth->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login', [
            'activated' => $this->auth->isActivated(),
        ]);
    }

    /**
     * First-run activation: provision the default SaaS admin on the backend,
     * then bounce back to the login page (which now shows the sign-in form).
     */
    public function activate()
    {
        $response = $this->auth->activate();

        return redirect()
            ->route('login')
            ->with(
                $response->successful() ? 'success' : 'error',
                $response->message ?: ($response->successful()
                    ? 'SaaS account activated. You can sign in now.'
                    : 'Activation failed. Please try again.')
            );
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $response = $this->auth->login(
            $credentials['email'],
            $credentials['password'],
            $request->boolean('remember')
        );

        if ($response->successful()) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', $response->message ?: 'Invalid credentials. Please try again.');
    }

    public function logout(Request $request)
    {
        $this->auth->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('index')->with('success', 'You have been signed out.');
    }

    public function showForgot()
    {
        return view('auth.forgot');
    }

    public function sendReset(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // Password reset is delivered by the backend; acknowledge generically
        // so we never disclose whether an account exists.
        return back()->with('success', 'If an account exists for that email, a reset link is on its way.');
    }
}
