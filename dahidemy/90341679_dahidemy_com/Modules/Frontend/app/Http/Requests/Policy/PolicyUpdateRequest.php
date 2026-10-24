<?php

namespace Modules\Frontend\Http\Requests\Policy;

use Illuminate\Foundation\Http\FormRequest;

class PolicyUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => 'required|string|max:20',
            'description' => 'required|string',
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
