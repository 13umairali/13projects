<?php

namespace Modules\Authentication\Http\Requests\Institute;

use Illuminate\Foundation\Http\FormRequest;

class InstituteUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'nullable',
                'email',
                'max:100',
            ],

            'phone' => [
                'required',
                'string',
                'max:15',
            ],

            'domain' => [
                'required',
                'string',
                'max:100',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],
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
