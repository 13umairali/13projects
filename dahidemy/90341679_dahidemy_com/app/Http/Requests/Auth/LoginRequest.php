<?php

namespace App\Http\Requests\Auth;

use App\Helpers\InstituteHelper;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Authentication\Models\Institute;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Nothing previously checked whether the authenticated user's
        // institute was still active — a user whose institute had been
        // soft-deleted or deactivated by a SaaS/Super Admin could still log
        // in and fully use their dashboard. SaaS/Super Admin accounts have
        // no institute_id at all, so they're deliberately left unaffected.
        $user = Auth::user();

        // Tenant binding: a user may only sign in on the domain of THEIR OWN institute. The same
        // generic failure message is used so this cannot be used to probe which institute an
        // account belongs to.
        if ($user && ! InstituteHelper::userMayUseHost($user, $this)) {
            Auth::guard('web')->logout();
            $this->session()->invalidate();
            $this->session()->regenerateToken();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if ($user && $user->institute_id) {
            $institute = Institute::find($user->institute_id);

            if (! $institute || ! $institute->status) {
                Auth::guard('web')->logout();
                $this->session()->invalidate();
                $this->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => _lang('This institute is no longer active. Please contact support.'),
                ]);
            }
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}