<?php

namespace App\Models;

use App\Models\Bundle;
use App\Models\Event;
use App\Models\Webinar;
use App\User;
use Illuminate\Database\Eloquent\Model;

class PublicChatConversation extends Model
{
    protected $table = 'public_chat_conversations';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id', 'id');
    }

    public function messages()
    {
        return $this->hasMany(PublicChatMessage::class, 'conversation_id', 'id');
    }

    public function latestMessage()
    {
        return $this->hasOne(PublicChatMessage::class, 'conversation_id', 'id')
            ->latest('created_at');
    }

    /**
     * Get the source model (course, bundle, or event)
     */
    public function getSource()
    {
        switch ($this->source_type) {
            case 'course':
                return Webinar::find($this->source_id);
            case 'bundle':
                return Bundle::find($this->source_id);
            case 'event':
                return Event::find($this->source_id);
            default:
                return null;
        }
    }

    /**
     * Get source title
     */
    public function getSourceTitle()
    {
        $source = $this->getSource();
        return $source ? $source->title : '';
    }

    /**
     * Get unread messages count for a user
     */
    public function unreadCount($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('created_at', '>', $this->getLastReadAt($userId))
            ->count();
    }

    /**
     * Get last read timestamp for a user (simple approach using cache)
     */
    private function getLastReadAt($userId)
    {
        return cache()->get("public_chat_read_{$this->id}_{$userId}", '1970-01-01');
    }

    /**
     * Mark conversation as read for a user
     */
    public function markAsRead($userId)
    {
        cache()->put("public_chat_read_{$this->id}_{$userId}", now()->toDateTimeString(), 60 * 24 * 30);
    }
}
