<?php

namespace Modules\Academic\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ClassCreateRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'class_name' => 'required|max:20',
            // Course Grouping cap: how many distinct Student Groups this
            // class may be paired with (e.g. Kindergarten -> 2, Nursery/Prep
            // -> 3). Left blank = no limit, matching existing classes'
            // current unrestricted behaviour.
            'max_groups' => 'nullable|integer|min:1|max:255',
        ];
    }
}
