<?php

namespace Modules\Authentication\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;

class PlanStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'student_limit' => 'required|integer|min:0',
            'branch_limit' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0|max:999999.99',
            // Required unless this is genuinely marked as a custom plan -
            // leaving this blank on a normal plan previously meant it
            // expired the instant it was created (addDays(null) resolves
            // to "now").
            'duration_days' => 'required_if:is_custom,0|nullable|integer|min:1',
            'trial_days' => 'nullable|integer|min:0',
            'is_custom' => 'required|boolean',
            'is_free' => 'required|boolean',
            'status' => 'nullable|in:1,2',
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