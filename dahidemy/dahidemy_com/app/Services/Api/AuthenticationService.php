<?php

namespace App\Services\Api;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;

/**
 * Authenticates SaaS admins against the backend (saas-login) and manages the
 * bearer token / user profile in the session. No local user table is used —
 * the backend remains the single source of truth.
 */
class AuthenticationService extends BaseApiService
{
    /**
     * Authenticate and persist the token + user on success.
     */
    public function login(string $email, string $password, bool $remember = false): ApiResponse
    {
        $res = $this->post('saas-login', [
            'email' => $email,
            'password' => $password,
        ]);

        if ($res->successful()) {
            $this->persist($res->data());

            if ($remember) {
                Session::put('saas_remember', true);
            }
        }

        return $res;
    }

    public function register(array $payload): ApiResponse
    {
        return $this->post('register', $payload);
    }

    /* -----------------------------------------------------------------
     |  First-run activation
     | ----------------------------------------------------------------- */

    /**
     * Has the first SaaS admin already been created on the backend?
     *
     * Fails "open" (assumes activated) so a transient API outage never traps
     * a healthy, already-provisioned install behind the activation screen.
     * The button only appears when the backend positively reports otherwise.
     */
    public function isActivated(): bool
    {
        $res = $this->get('saas-account-status');
        if (! $res->successful()) {
            return true;
        }

        return (bool) data_get($res->data, 'activated', false);
    }

    /**
     * Create the default SaaS admin on the backend. The endpoint is idempotent
     * — it returns "already activated" if the account exists — so a repeated
     * click is harmless. Returns the API message for display on the login page.
     */
    public function activate(): ApiResponse
    {
        return $this->get('saas-account-activation');
    }

    public function profile(): ApiResponse
    {
        return $this->get('profile');
    }

    public function updateProfile(array $payload): ApiResponse
    {
        return $this->post('profile-update', $payload);
    }

    public function changePassword(array $payload): ApiResponse
    {
        return $this->post('change-password', $payload);
    }

    public function logout(): ApiResponse
    {
        $res = $this->post('logout');
        $this->forget();

        return $res;
    }

    /* -----------------------------------------------------------------
     |  Session state
     | ----------------------------------------------------------------- */

    public function check(): bool
    {
        return (bool) Session::get(config('saas.auth.token_key'));
    }

    public function user(): ?array
    {
        return Session::get(config('saas.auth.user_key'));
    }

    protected function persist(mixed $data): void
    {
        $data = (array) $data;

        $token = Arr::get($data, 'token')
            ?? Arr::get($data, 'access_token')
            ?? Arr::get($data, 'api_token');

        $user = Arr::get($data, 'user', Arr::except($data, ['token', 'access_token', 'api_token']));

        if ($token) {
            Session::put(config('saas.auth.token_key'), $token);
        }

        Session::put(config('saas.auth.user_key'), $user);
    }

    protected function forget(): void
    {
        Session::forget(config('saas.auth.token_key'));
        Session::forget(config('saas.auth.user_key'));
        Session::forget('saas_remember');
    }
}
