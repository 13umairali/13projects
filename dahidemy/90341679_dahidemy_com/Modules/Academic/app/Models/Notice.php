<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $table = 'notices';

    protected $fillable = ['institute_id', 'branch_id', 'title', 'type', 'recipient_staff_id', 'display_on_website', 'notice', 'date', 'image', 'created_by'];

    public function recipientStaff()
    {
        return $this->belongsTo(Staff::class, 'recipient_staff_id');
    }

    /**
     * Replaces dynamic tags like {{School Name}} / {{Staff Name}} /
     * {{Date}} in the notice body with real values. Only substitutes
     * tags it actually has a value for -- an unrecognized {{Tag}} is left
     * as-is rather than silently becoming blank, so a typo in a tag name
     * is visible and fixable instead of disappearing.
     */
    public function renderedBody(): string
    {
        $replacements = [
            '{{School Name}}' => get_option('school_name'),
            '{{Institute Name}}' => get_option('school_name'),
            '{{Date}}' => now()->format('d M, Y'),
        ];

        if ($this->recipient_staff_id && $this->recipientStaff) {
            $replacements['{{Staff Name}}'] = $this->recipientStaff->name;
            $replacements['{{Designation}}'] = $this->recipientStaff->designation;
        }

        return strtr($this->notice, $replacements);
    }

    public function userType()
    {
        return $this->hasMany(UserNotice::class, 'notice_id', 'id');
    }
}
