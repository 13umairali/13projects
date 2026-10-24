<?php

namespace Modules\Transport\Http\Requests\TransportMember;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Transport\Models\TransportMember;

class TransportMemberStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // member_type distinguishes a student passenger from a staff
            // "Conductor" (or any other non-student passenger) -- see the
            // migration comment for the full reasoning. Defaults to
            // 'student' so existing forms that don't send this field yet
            // keep working exactly as before.
            'member_type' => 'nullable|in:'.TransportMember::TYPE_STUDENT.','.TransportMember::TYPE_STAFF,
            'student_id' => [
                Rule::requiredIf(fn () => $this->input('member_type', TransportMember::TYPE_STUDENT) === TransportMember::TYPE_STUDENT),
                'nullable',
                'integer',
            ],
            'staff_id' => [
                Rule::requiredIf(fn () => $this->input('member_type') === TransportMember::TYPE_STAFF),
                'nullable',
                'integer',
            ],
            'assigned_route_id' => 'required|string|max:191',
            'assigned_stop_id' => 'required|numeric',
            // A Conductor rides the route but isn't billed a fare -- see
            // the migration comment. Only required for a student passenger.
            'fare_amount' => [
                Rule::requiredIf(fn () => $this->input('member_type', TransportMember::TYPE_STUDENT) === TransportMember::TYPE_STUDENT),
                'nullable',
                'string',
            ],
            'status' => 'nullable|string',
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
