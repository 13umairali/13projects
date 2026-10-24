<?php

namespace Modules\Authentication\Http\Requests\Institute;

use Illuminate\Foundation\Http\FormRequest;

class InstituteStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Institute Info
            'institute_name' => ['required', 'string', 'max:100'],
            'institute_email' => ['required', 'email', 'max:100', 'unique:institutes,email'],
            'institute_phone' => ['required', 'string', 'max:20', 'unique:institutes,phone'],
            'institute_type' => ['required', 'string', 'max:100'],
            'institute_domain' => ['required', 'string', 'max:100', 'unique:institutes,domain'],
            'institute_address' => ['nullable', 'string', 'max:255'],

            // Files
            'institute_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Owner Info
            'user_name' => ['required', 'string', 'max:100'],
            'user_email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'user_phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'user_avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Password
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
