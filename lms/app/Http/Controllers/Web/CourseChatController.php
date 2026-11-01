<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CourseChatMessage;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;

class CourseChatController extends Controller
{
    public function index($slug)
    {
        $user = auth()->user();

        if (empty($user)) {
            return redirect('/login');
        }

        $course = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (empty($course)) {
            abort(404);
        }

        // Check if chat room is enabled
        if (!$course->hasChatRoom()) {
            abort(404);
        }

        // Check if user has access (bought or is teacher/creator)
        if (!$course->checkUserHasBought($user)) {
            return redirect('/course/' . $slug);
        }

        $isRtl = !empty(session()->get('locale')) && in_array(session()->get('locale'), ['ar', 'fa', 'he']);

        $data = [
            'pageTitle' => trans('update.chat_room') . ' - ' . $course->title,
            'course' => $course,
            'authUser' => $user,
            'isRtl' => $isRtl,
        ];

        return view('design_1.web.courses.chat_room.index', $data);
    }

    public function getMessages(Request $request, $slug)
    {
        $user = auth()->user();

        if (empty($user)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $course = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (empty($course) || !$course->hasChatRoom()) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if (!$course->checkUserHasBought($user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $afterId = $request->get('after', 0);

        // Check if messages were cleared (client has ID higher than max in DB)
        $messagesClearedFlag = false;
        if ($afterId > 0) {
            $maxId = CourseChatMessage::where('webinar_id', $course->id)->max('id');
            if ($maxId === null || $maxId < $afterId) {
                // Messages were cleared or client's lastId is outdated
                $messagesClearedFlag = true;
                $afterId = 0; // Reset to get all messages
            }
        }

        $messages = CourseChatMessage::where('webinar_id', $course->id)
            ->when($afterId > 0, function ($query) use ($afterId) {
                $query->where('id', '>', $afterId);
            })
            ->with(['sender', 'replyTo.sender'])
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();

        $formattedMessages = [];

        foreach ($messages as $message) {
            $formattedMessages[] = $this->formatMessage($message, $course);
        }

        // Get online users (users who sent messages in the last 30 seconds)
        $onlineUsers = $this->getOnlineUsers($course);

        // Get typing users
        $typingCacheKey = 'chat_typing_' . $course->id;
        $typingData = cache()->get($typingCacheKey, []);
        $typingCutoff = now()->timestamp - 3;
        $typingUserIds = [];
        foreach ($typingData as $uid => $ts) {
            if ($ts > $typingCutoff && $uid != $user->id) {
                $typingUserIds[] = (int) $uid;
            }
        }

        return response()->json([
            'success' => true,
            'messages' => $formattedMessages,
            'online_users' => $onlineUsers,
            'typing_users' => $typingUserIds,
            'current_user_id' => $user->id,
            'is_closed' => $course->chat_room_closed ?? false,
            'messages_cleared' => $messagesClearedFlag,
        ]);
    }

    public function sendMessage(Request $request, $slug)
    {
        $user = auth()->user();

        if (empty($user)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $course = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (empty($course) || !$course->hasChatRoom()) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if (!$course->checkUserHasBought($user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Check if chat is closed
        if ($course->chat_room_closed) {
            return response()->json(['error' => 'Chat room is closed'], 403);
        }

        $type = $request->get('type', 'text');
        $messageData = [
            'webinar_id' => $course->id,
            'sender_id' => $user->id,
            'reply_to_id' => $request->get('reply_to_id'),
            'type' => $type,
        ];

        // Handle different message types
        if ($type === 'text') {
            $request->validate([
                'message' => 'required|string|max:2000',
            ]);
            $messageData['message'] = $request->get('message');
        } elseif ($type === 'voice') {
            $request->validate([
                'file' => 'required|file|mimes:webm,mp3,ogg,wav|max:5120', // 5MB
                'duration' => 'nullable|integer',
            ]);

            $file = $request->file('file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $storagePath = 'store/' . $user->id . '/chat-room';
            $fullPath = public_path($storagePath);

            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            $file->move($fullPath, $fileName);

            $messageData['message'] = trans('update.voice_message');
            $messageData['file_path'] = '/' . $storagePath . '/' . $fileName;
            $messageData['file_name'] = $fileName;
            $messageData['file_size'] = filesize($fullPath . '/' . $fileName);
            $messageData['voice_duration'] = $request->get('duration', 0);
        } elseif ($type === 'file') {
            $request->validate([
                'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,txt|max:10240', // 10MB
            ]);

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $storagePath = 'store/' . $user->id . '/chat-room';
            $fullPath = public_path($storagePath);

            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            $file->move($fullPath, $fileName);

            $messageData['message'] = $originalName;
            $messageData['file_path'] = '/' . $storagePath . '/' . $fileName;
            $messageData['file_name'] = $originalName;
            $messageData['file_size'] = filesize($fullPath . '/' . $fileName);
        } elseif ($type === 'image') {
            $request->validate([
                'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:5120', // 5MB
            ]);

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $storagePath = 'store/' . $user->id . '/chat-room';
            $fullPath = public_path($storagePath);

            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            $file->move($fullPath, $fileName);

            $messageData['message'] = $originalName;
            $messageData['file_path'] = '/' . $storagePath . '/' . $fileName;
            $messageData['file_name'] = $originalName;
            $messageData['file_size'] = filesize($fullPath . '/' . $fileName);
        } elseif ($type === 'video') {
            $request->validate([
                'file' => 'required|file|mimes:mp4,mov,avi,webm|max:20480', // 20MB
            ]);

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $storagePath = 'store/' . $user->id . '/chat-room';
            $fullPath = public_path($storagePath);

            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            $file->move($fullPath, $fileName);

            $messageData['message'] = $originalName;
            $messageData['file_path'] = '/' . $storagePath . '/' . $fileName;
            $messageData['file_name'] = $originalName;
            $messageData['file_size'] = filesize($fullPath . '/' . $fileName);
        } else {
            return response()->json(['error' => 'Invalid message type'], 400);
        }

        $message = CourseChatMessage::create($messageData);
        $message->load(['sender', 'replyTo.sender']);

        return response()->json([
            'success' => true,
            'message' => $this->formatMessage($message, $course),
        ]);
    }

    private function formatMessage($message, $course)
    {
        $currentUser = auth()->user();
        $isInstructor = $currentUser && ($currentUser->id == $course->teacher_id || $currentUser->id == $course->creator_id || $currentUser->isAdmin());

        return [
            'id' => $message->id,
            'message' => $message->message,
            'type' => $message->type ?? 'text',
            'file_path' => $message->file_path,
            'file_name' => $message->file_name,
            'file_size' => $message->file_size,
            'voice_duration' => $message->voice_duration,
            'sender_id' => $message->sender_id,
            'is_system' => $message->is_system ?? false,
            'edited_at' => $message->edited_at ? $message->edited_at->toISOString() : null,
            'can_edit' => $currentUser && $message->sender_id == $currentUser->id && ($message->type ?? 'text') === 'text' && getFeaturesSettings('allow_chat_edit_messages'),
            'can_delete' => $isInstructor && getFeaturesSettings('allow_chat_delete_messages'),
            'created_at' => dateTimeFormat($message->created_at->timestamp, 'H:i'),
            'sender' => $message->sender ? [
                'id' => $message->sender->id,
                'full_name' => $message->sender->full_name,
                'avatar' => $message->sender->getAvatar(48),
                'is_teacher' => ($message->sender->id == $course->teacher_id || $message->sender->id == $course->creator_id),
            ] : null,
            'reply_to' => $message->replyTo ? [
                'id' => $message->replyTo->id,
                'message' => $message->replyTo->message,
                'type' => $message->replyTo->type ?? 'text',
                'file_path' => $message->replyTo->file_path,
                'sender' => [
                    'full_name' => $message->replyTo->sender->full_name,
                ],
            ] : null,
        ];
    }

    public function editMessage(Request $request, $slug, $messageId)
    {
        $user = auth()->user();

        if (empty($user)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $course = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (empty($course) || !$course->hasChatRoom()) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $message = CourseChatMessage::where('id', $messageId)
            ->where('webinar_id', $course->id)
            ->first();

        if (empty($message)) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        // Only sender can edit their own text messages
        if ($message->sender_id != $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        if (($message->type ?? 'text') !== 'text') {
            return response()->json(['error' => 'Only text messages can be edited'], 400);
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $message->update([
            'message' => $request->get('message'),
            'edited_at' => now(),
        ]);

        $message->load(['sender', 'replyTo.sender']);

        return response()->json([
            'success' => true,
            'message' => $this->formatMessage($message, $course),
        ]);
    }

    public function deleteMessage(Request $request, $slug, $messageId)
    {
        $user = auth()->user();

        if (empty($user)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $course = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (empty($course) || !$course->hasChatRoom()) {
            return response()->json(['error' => 'Not found'], 404);
        }

        // Only instructor/creator/admin can delete messages
        if ($user->id != $course->teacher_id && $user->id != $course->creator_id && !$user->isAdmin()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $message = CourseChatMessage::where('id', $messageId)
            ->where('webinar_id', $course->id)
            ->first();

        if (empty($message)) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        // Delete associated file if exists
        if ($message->file_path) {
            $filePath = public_path(ltrim($message->file_path, '/'));
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        $message->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function clearMessages(Request $request, $slug)
    {
        $user = auth()->user();

        if (empty($user)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $course = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (empty($course) || !$course->hasChatRoom()) {
            return response()->json(['error' => 'Not found'], 404);
        }

        // Only instructor/creator/admin can clear messages
        if ($user->id != $course->teacher_id && $user->id != $course->creator_id && !$user->isAdmin()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Delete files from store folder
        $messages = CourseChatMessage::where('webinar_id', $course->id)
            ->whereNotNull('file_path')
            ->get();

        foreach ($messages as $message) {
            $filePath = public_path(ltrim($message->file_path, '/'));
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        // Delete all messages for this course
        CourseChatMessage::where('webinar_id', $course->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Messages cleared successfully',
        ]);
    }

    public function toggleChat(Request $request, $slug)
    {
        $user = auth()->user();

        if (empty($user)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $course = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (empty($course) || !$course->hasChatRoom()) {
            return response()->json(['error' => 'Not found'], 404);
        }

        // Only instructor/creator/admin can toggle chat
        if ($user->id != $course->teacher_id && $user->id != $course->creator_id && !$user->isAdmin()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Toggle the chat room closed state
        $isClosed = !$course->chat_room_closed;
        $course->update(['chat_room_closed' => $isClosed]);

        // Create a system message to notify users
        $systemMessage = $isClosed
            ? trans('update.chat_room_closed_by_instructor')
            : trans('update.chat_room_opened_by_instructor');

        CourseChatMessage::create([
            'webinar_id' => $course->id,
            'sender_id' => $user->id,
            'message' => $systemMessage,
            'is_system' => true,
        ]);

        return response()->json([
            'success' => true,
            'is_closed' => $isClosed,
        ]);
    }

    private function getOnlineUsers(Webinar $course)
    {
        $currentUserId = auth()->id();
        $cacheKey = 'chat_presence_' . $course->id;

        // Update current user's presence
        if ($currentUserId) {
            $presence = cache()->get($cacheKey, []);
            $presence[$currentUserId] = now()->timestamp;

            // Clean up old entries (older than 30 seconds)
            $cutoff = now()->timestamp - 30;
            $presence = array_filter($presence, function ($timestamp) use ($cutoff) {
                return $timestamp > $cutoff;
            });

            cache()->put($cacheKey, $presence, 60); // Store for 60 seconds
        }

        // Get all online user IDs
        $presence = cache()->get($cacheKey, []);
        $cutoff = now()->timestamp - 30;
        $onlineUserIds = array_keys(array_filter($presence, function ($timestamp) use ($cutoff) {
            return $timestamp > $cutoff;
        }));

        if (empty($onlineUserIds)) {
            return [];
        }

        $users = User::whereIn('id', $onlineUserIds)->get();

        $onlineUsers = [];
        foreach ($users as $usr) {
            // Determine role label
            if ($usr->isAdmin()) {
                $roleLabel = trans('admin/main.admin');
            } elseif ($usr->id == $course->teacher_id || $usr->id == $course->creator_id) {
                $roleLabel = trans('update.role_teacher');
            } else {
                $roleLabel = trans('update.role_user');
            }

            $onlineUsers[] = [
                'id' => $usr->id,
                'full_name' => $usr->full_name,
                'avatar' => $usr->getAvatar(48),
                'role' => $roleLabel,
            ];
        }

        return $onlineUsers;
    }

    /**
     * Check if the instructor is currently online in the chat room
     */
    public function checkInstructorOnline($slug)
    {
        $course = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (empty($course) || !$course->hasChatRoom()) {
            return response()->json(['online' => false]);
        }

        $cacheKey = 'chat_presence_' . $course->id;
        $presence = cache()->get($cacheKey, []);
        $cutoff = now()->timestamp - 30;

        // Check if teacher or creator is online
        $instructorOnline = false;

        if (!empty($presence[$course->teacher_id]) && $presence[$course->teacher_id] > $cutoff) {
            $instructorOnline = true;
        }

        if (!$instructorOnline && $course->creator_id && !empty($presence[$course->creator_id]) && $presence[$course->creator_id] > $cutoff) {
            $instructorOnline = true;
        }

        return response()->json(['online' => $instructorOnline]);
    }

    /**
     * Report that the current user is typing
     */
    public function reportTyping(Request $request, $slug)
    {
        $user = auth()->user();
        if (empty($user)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $course = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (empty($course) || !$course->hasChatRoom()) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $cacheKey = 'chat_typing_' . $course->id;
        $typingData = cache()->get($cacheKey, []);
        $typingData[$user->id] = now()->timestamp;

        // Clean up old entries (older than 5 seconds)
        $cutoff = now()->timestamp - 5;
        $typingData = array_filter($typingData, function ($ts) use ($cutoff) {
            return $ts > $cutoff;
        });

        cache()->put($cacheKey, $typingData, 10);

        return response()->json(['success' => true]);
    }
}
