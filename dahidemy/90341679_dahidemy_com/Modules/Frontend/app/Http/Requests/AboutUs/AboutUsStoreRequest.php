<?php

namespace Modules\Frontend\Http\Requests\AboutUs;

use Illuminate\Foundation\Http\FormRequest;

class AboutUsStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:255',
            'image' => 'nullable|image',
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
