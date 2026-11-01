<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\PublicChatConversation;
use App\Models\PublicChatMessage;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;

class PublicChatPageController extends Controller
{
    /**
     * Show the public chat page for a specific source
     */
    public function show(Request $request, $slug, $sourceType)
    {
        $user = auth()->user();

        if (empty($user)) {
            return redirect('/login');
        }

        // Resolve the source
        $source = $this->resolveSource($slug, $sourceType);
        if (empty($source)) {
            abort(404);
        }

        // Get the instructor
        $instructor = $this->getInstructor($source, $sourceType);
        if (empty($instructor) || !$instructor->enable_public_chat || !getFeaturesSettings('enable_public_chat_questions')) {
            abort(404);
        }

        // Find or create conversation
        $conversation = PublicChatConversation::firstOrCreate([
            'user_id' => $user->id,
            'instructor_id' => $instructor->id,
            'source_type' => $sourceType,
            'source_id' => $source->id,
        ]);

        $conversation->markAsRead($user->id);

        $messages = PublicChatMessage::where('conversation_id', $conversation->id)
            ->with(['sender'])
            ->orderBy('created_at', 'asc')
            ->get();

        $data = [
            'pageTitle' => trans('update.chat_with_instructor'),
            'conversation' => $conversation,
            'messages' => $messages,
            'source' => $source,
            'sourceType' => $sourceType,
            'instructor' => $instructor,
        ];

        return view('design_1.web.public_chat.show', $data);
    }

    /**
     * Send a message
     */
    public function send(Request $request, $slug, $sourceType)
    {
        $user = auth()->user();
        if (empty($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $source = $this->resolveSource($slug, $sourceType);
        if (empty($source)) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $instructor = $this->getInstructor($source, $sourceType);
        if (empty($instructor) || !$instructor->enable_public_chat) {
            return response()->json(['error' => 'Public chat disabled'], 403);
        }

        $message = $request->input('message');
        if (empty($message)) {
            return response()->json(['error' => 'Message is required'], 422);
        }

        $conversation = PublicChatConversation::firstOrCreate([
            'user_id' => $user->id,
            'instructor_id' => $instructor->id,
            'source_type' => $sourceType,
            'source_id' => $source->id,
        ]);

        PublicChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $message,
        ]);

        $conversation->update(['updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Get messages (AJAX polling)
     */
    public function getMessages(Request $request, $slug, $sourceType)
    {
        $user = auth()->user();
        if (empty($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $source = $this->resolveSource($slug, $sourceType);
        if (empty($source)) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $instructor = $this->getInstructor($source, $sourceType);
        if (empty($instructor)) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $conversation = PublicChatConversation::where('user_id', $user->id)
            ->where('instructor_id', $instructor->id)
            ->where('source_type', $sourceType)
            ->where('source_id', $source->id)
            ->first();

        if (empty($conversation)) {
            return response()->json(['messages' => []]);
        }

        $afterId = $request->get('after', 0);

        $messages = PublicChatMessage::where('conversation_id', $conversation->id)
            ->when($afterId > 0, function ($q) use ($afterId) {
                $q->where('id', '>', $afterId);
            })
            ->with(['sender'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender ? $msg->sender->full_name : '',
                    'sender_avatar' => $msg->sender ? $msg->sender->getAvatar(40) : '',
                    'message' => $msg->message,
                    'created_at' => $msg->created_at->toDateTimeString(),
                ];
            });

        $conversation->markAsRead($user->id);

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    private function resolveSource($slug, $sourceType)
    {
        switch ($sourceType) {
            case 'course':
            case 'event':
                return Webinar::where('slug', $slug)->first();
            case 'bundle':
                return Bundle::where('slug', $slug)->first();
            default:
                return null;
        }
    }

    private function getInstructor($source, $sourceType)
    {
        if (empty($source))
            return null;

        $instructorId = null;
        if ($sourceType === 'bundle') {
            $instructorId = $source->teacher_id ?? $source->creator_id ?? null;
        } else {
            $instructorId = $source->teacher_id ?? $source->creator_id ?? null;
        }

        return $instructorId ? User::find($instructorId) : null;
    }
}
