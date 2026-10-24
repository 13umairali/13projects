<?php

namespace Modules\Academic\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffCategoryCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('staff_categories')
                    ->where('institute_id', get_institute_id())
                    ->where('branch_id', get_branch_id()),
            ],
            'sort_order' => 'nullable|integer|min:0',
        ];
    }
}
