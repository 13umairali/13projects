<?php

namespace Modules\FormBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DynamicForm extends Model
{
    use SoftDeletes;

    protected $table = 'form_builder_forms';

    public const STATUS_PUBLISHED = 1;

    public const STATUS_DRAFT = 2;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'name',
        'slug',
        'type',
        'description',
        'submit_button_text',
        'success_message',
        'notify_email',
        'icon',
        'status',
        'show_on_homepage',
        'created_by',
    ];

    protected $casts = [
        'show_on_homepage' => 'boolean',
    ];

    public function fields()
    {
        return $this->hasMany(DynamicFormField::class, 'form_id')->orderBy('sort_order');
    }

    public function submissions()
    {
        return $this->hasMany(DynamicFormSubmission::class, 'form_id');
    }

    public function isPublished(): bool
    {
        return (int) $this->status === self::STATUS_PUBLISHED;
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeOnHomepage($query)
    {
        return $query->where('show_on_homepage', true);
    }
}
