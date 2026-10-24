<?php

namespace Modules\FormBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicFormField extends Model
{
    protected $table = 'form_builder_fields';

    public const TYPES = [
        'text' => 'Single Line Text',
        'textarea' => 'Paragraph Text',
        'email' => 'Email',
        'phone' => 'Phone Number',
        'number' => 'Number',
        'date' => 'Date',
        'select' => 'Dropdown',
        'radio' => 'Multiple Choice',
        'checkbox' => 'Checkboxes',
        'file' => 'File Upload',
    ];

    // Field types that require an "options" list (select/radio/checkbox).
    public const CHOICE_TYPES = ['select', 'radio', 'checkbox'];

    protected $fillable = [
        'form_id',
        'label',
        'field_key',
        'type',
        'placeholder',
        'options',
        'is_required',
        'width',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    public function form()
    {
        return $this->belongsTo(DynamicForm::class, 'form_id');
    }

    public function isChoiceType(): bool
    {
        return in_array($this->type, self::CHOICE_TYPES, true);
    }

    /**
     * Build the Laravel validation rule string for this field.
     */
    public function validationRules(): array
    {
        $rules = [$this->is_required ? 'required' : 'nullable'];

        switch ($this->type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'number':
                $rules[] = 'numeric';
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'file':
                $rules[] = 'file';
                $rules[] = 'max:5120'; // 5MB
                break;
            case 'checkbox':
                $rules[] = 'array';
                break;
            default:
                $rules[] = 'string';
                $rules[] = 'max:2000';
        }

        return $rules;
    }
}
