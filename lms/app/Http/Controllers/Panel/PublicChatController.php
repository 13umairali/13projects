<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Event;
use App\Models\PublicChatConversation;
use App\Models\PublicChatMessage;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;

class PublicChatController extends Controller
{
    /**
     * Get conversations for the current user (works for both instructor & student)
     */
    private function getConversationsForUser($user)
    {
        if ($user->isTeacher() || $user->isOrganization()) {
            return PublicChatConversation::where('instructor_id', $user->id)
                ->with(['user', 'latestMessage'])
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        return PublicChatConversation::where('user_id', $user->id)
            ->with(['instructor', 'latestMessage'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Check if user has access to a conversation
     */
    private function userCanAccess($user, $conversation)
    {
        return $conversation->instructor_id == $user->id || $conversation->user_id == $user->id;
    }

    /**
     * Get the "other party" for display
     */
    private function getOtherParty($user, $conversation)
    {
        if ($user->id == $conversation->instructor_id) {
            return $conversation->user;
        }
        return $conversation->instructor;
    }

    public function index()
    {
        $user = auth()->user();

        $conversations = $this->getConversationsForUser($user);

        foreach ($conversations as $conversation) {
            $conversation->source_title = $conversation->getSourceTitle();
            $conversation->unread = $conversation->unreadCount($user->id);
            $conversation->other_party = $this->getOtherParty($user, $conversation);
        }

        $data = [
            'pageTitle' => trans('update.public_chat'),
            'conversations' => $conversations,
            'activeConversation' => null,
            'messages' => collect([]),
            'isInstructor' => $user->isTeacher() || $user->isOrganization(),
        ];

        return view('design_1.panel.public_chat.index', $data);
    }

    public function show($conversationId)
    {
        $user = auth()->user();

        $conversation = PublicChatConversation::with(['user', 'instructor'])->find($conversationId);

        if (empty($conversation) || !$this->userCanAccess($user, $conversation)) {
            abort(404);
        }

        $conversation->markAsRead($user->id);
        $conversation->source_title = $conversation->getSourceTitle();
        $conversation->other_party = $this->getOtherParty($user, $conversation);

        $conversations = $this->getConversationsForUser($user);

        foreach ($conversations as $conv) {
            $conv->source_title = $conv->getSourceTitle();
            $conv->unread = $conv->unreadCount($user->id);
            $conv->other_party = $this->getOtherParty($user, $conv);
        }

        $messages = PublicChatMessage::where('conversation_id', $conversationId)
            ->with(['sender', 'replyTo', 'replyTo.sender'])
            ->orderBy('created_at', 'asc')
            ->get();

        $data = [
            'pageTitle' => trans('update.public_chat'),
            'conversations' => $conversations,
            'activeConversation' => $conversation,
            'messages' => $messages,
            'isInstructor' => $user->isTeacher() || $user->isOrganization(),
        ];

        return view('design_1.panel.public_chat.index', $data);
    }

    /**
     * Start or find a conversation (for CTA button)
     */
    public function start($slug, $sourceType)
    {
        $user = auth()->user();

        $source = $this->resolveSource($slug, $sourceType);
        if (empty($source)) {
            abort(404);
        }

        $instructor = $this->getInstructor($source);
        if (empty($instructor) || !$instructor->enable_public_chat || !getFeaturesSettings('enable_public_chat_questions')) {
            abort(404);
        }

        $conversation = PublicChatConversation::firstOrCreate([
            'user_id' => $user->id,
            'instructor_id' => $instructor->id,
            'source_type' => $sourceType,
            'source_id' => $source->id,
        ]);

        return redirect('/panel/public-chat/' . $conversation->id);
    }

    public function reply(Request $request, $conversationId)
    {
        $user = auth()->user();

        $conversation = PublicChatConversation::find($conversationId);

        if (empty($conversation) || !$this->userCanAccess($user, $conversation)) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $message = $request->input('message');

        if (empty($message)) {
            return response()->json(['error' => 'Message is required'], 422);
        }

        PublicChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $message,
            'reply_to_id' => $request->input('reply_to_id') ?: null,
        ]);

        $conversation->update(['updated_at' => now()]);
        $conversation->markAsRead($user->id);

        return response()->json(['success' => true]);
    }

    /**
     * GET messages for a conversation (AJAX polling)
     */
    public function getMessages(Request $request, $conversationId)
    {
        $user = auth()->user();

        $conversation = PublicChatConversation::find($conversationId);

        if (empty($conversation) || !$this->userCanAccess($user, $conversation)) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $afterId = $request->get('after', 0);

        $messages = PublicChatMessage::where('conversation_id', $conversationId)
            ->when($afterId > 0, function ($q) use ($afterId) {
                $q->where('id', '>', $afterId);
            })
            ->with(['sender', 'replyTo', 'replyTo.sender'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                $data = [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender ? $msg->sender->full_name : '',
                    'sender_avatar' => $msg->sender ? $msg->sender->getAvatar(40) : '',
                    'message' => $msg->message,
                    'created_at' => dateTimeFormat(strtotime($msg->created_at), 'H:i'),
                    'reply_to' => null,
                ];

                if ($msg->replyTo) {
                    $data['reply_to'] = [
                        'id' => $msg->replyTo->id,
                        'sender_name' => $msg->replyTo->sender ? $msg->replyTo->sender->full_name : '',
                        'message' => \Illuminate\Support\Str::limit($msg->replyTo->message, 80),
                    ];
                }

                return $data;
            });

        $conversation->markAsRead($user->id);

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * AJAX endpoint: refresh conversation list sidebar
     */
    public function getConversationList(Request $request)
    {
        $user = auth()->user();
        $activeId = $request->get('active', 0);

        $conversations = $this->getConversationsForUser($user);

        $result = $conversations->map(function ($conv) use ($user, $activeId) {
            $otherParty = $this->getOtherParty($user, $conv);

            return [
                'id' => $conv->id,
                'other_name' => $otherParty ? $otherParty->full_name : '',
                'other_avatar' => $otherParty ? $otherParty->getAvatar(40) : '',
                'source_type' => ucfirst($conv->source_type),
                'source_title' => $conv->getSourceTitle(),
                'latest_message' => $conv->latestMessage ? \Illuminate\Support\Str::limit($conv->latestMessage->message, 40) : '',
                'latest_date' => $conv->latestMessage ? dateTimeFormat(strtotime($conv->latestMessage->created_at), 'j M Y | H:i') : '',
                'unread' => $conv->unreadCount($user->id),
                'is_active' => $conv->id == $activeId,
            ];
        });

        return response()->json(['conversations' => $result]);
    }

    private function resolveSource($slug, $sourceType)
    {
        switch ($sourceType) {
            case 'course':
                return Webinar::where('slug', $slug)->first();
            case 'event':
                return Event::where('slug', $slug)->first();
            case 'bundle':
                return Bundle::where('slug', $slug)->first();
            default:
                return null;
        }
    }

    private function getInstructor($source)
    {
        $instructorId = $source->teacher_id ?? $source->creator_id ?? null;
        return $instructorId ? User::find($instructorId) : null;
    }
}
