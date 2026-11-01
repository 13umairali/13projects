<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class CourseChatMessage extends Model
{
    protected $table = 'course_chat_messages';
    protected $guarded = ['id'];

    protected $casts = [
        'edited_at' => 'datetime',
    ];

    public function webinar()
    {
        return $this->belongsTo(Webinar::class, 'webinar_id', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function replyTo()
    {
        return $this->belongsTo(CourseChatMessage::class, 'reply_to_id', 'id');
    }

    public function replies()
    {
        return $this->hasMany(CourseChatMessage::class, 'reply_to_id', 'id');
    }
}
