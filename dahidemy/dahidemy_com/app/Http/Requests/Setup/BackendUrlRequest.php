<?php

namespace App\Http\Requests\Setup;

use App\Http\Middleware\RedirectIfBackendApiConfigured;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared validation for both wizard steps — testing and saving take the same
 * input, so the rules live once. Authorisation is handled upstream by
 * {@see RedirectIfBackendApiConfigured}.
 */
class BackendUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalise before validating, so "  http://host:8000/ " passes and is
     * stored in the same shape the tester probed.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('url')) {
            $this->merge([
                'url' => rtrim(trim((string) $this->input('url')), '/'),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'url' => [
                'required',
                'string',
                'max:255',
                // The protocol list is what rejects ftp://, javascript: and the
                // other schemes we cannot speak — bare 'url' accepts them all.
                'url:http,https',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url.required' => 'Enter the address of your backend API.',
            'url.url' => 'That does not look like a valid URL. Include the scheme, for example http://127.0.0.1:8000',
            'url.max' => 'That URL is too long.',
        ];
    }

    /**
     * The validated backend URL.
     *
     * Named backendUrl(), not url(): Illuminate\Http\Request::url() already
     * means "the URL of this request", and shadowing it here would break
     * anything downstream that asks a request where it came from.
     */
    public function backendUrl(): string
    {
        return (string) $this->validated()['url'];
    }
}
