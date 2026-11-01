<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class PublicChatMessage extends Model
{
    protected $table = 'public_chat_messages';
    protected $guarded = ['id'];

    public function conversation()
    {
        return $this->belongsTo(PublicChatConversation::class, 'conversation_id', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function replyTo()
    {
        return $this->belongsTo(PublicChatMessage::class, 'reply_to_id', 'id');
    }
}
