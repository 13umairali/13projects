<?php

namespace Modules\Student\Http\Requests\Auth;

use App\Helpers\InstituteHelper;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentLoginRequest extends FormRequest
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
            'phone' => 'required|numeric|exists:users,phone',
            'password' => 'required',
            'device_name' => 'required',
            'platform' => 'nullable|in:mobile,web',
            'mac_id' => 'required_if:platform,mobile',
            'device_info' => 'required_if:platform,mobile',
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

        // Phone numbers are not unique across institutes. When the request arrives on an
        // institute's own domain, only look for the student inside that institute.
        $credentials = $this->only('phone', 'password');
        if ($hostInstitute = InstituteHelper::findInstituteByStrictHost($this)) {
            $credentials['institute_id'] = $hostInstitute->id;
        }

        // Proceed with normal authentication
        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'phone' => trans('auth.failed'),
            ]);
        }

        if (! InstituteHelper::userMayUseHost(Auth::user(), $this)) {
            Auth::guard('web')->logout();
            $this->session()->invalidate();
            $this->session()->regenerateToken();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'phone' => trans('auth.failed'),
            ]);
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
            'phone' => trans('auth.throttle', [
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
        return Str::transliterate(Str::lower($this->string('phone')).'|'.$this->ip());
    }
}
