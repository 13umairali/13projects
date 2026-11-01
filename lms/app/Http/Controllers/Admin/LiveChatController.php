<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseChatMessage;
use App\Models\Role;
use App\Models\Support;
use App\Models\Webinar;
use App\Models\PublicChatConversation;
use App\Models\PublicChatMessage;
use App\Models\Setting;
use App\Models\Translation\SettingTranslation;
use App\User;
use Illuminate\Http\Request;

class LiveChatController extends Controller
{
    /**
     * Display a list of courses that have chat room enabled.
     */
    public function chatRooms(Request $request)
    {
        $this->authorize('admin_live_chat_chat_rooms');

        $query = Webinar::where('chat_room', true)
            ->where('status', 'active')
            ->with([
                'teacher' => function ($q) {
                    $q->select('id', 'full_name');
                }
            ])
            ->withCount('chatMessages');

        // Filters
        $search = $request->get('search');
        $teacher_ids = $request->get('teacher_ids');
        $status = $request->get('chat_status');

        if (!empty($search)) {
            $query->whereTranslationLike('title', "%$search%");
        }

        if (!empty($teacher_ids)) {
            $query->where(function ($q) use ($teacher_ids) {
                $q->whereIn('teacher_id', $teacher_ids)
                    ->orWhereIn('creator_id', $teacher_ids);
            });
        }

        if ($status === 'open') {
            $query->where(function ($q) {
                $q->whereNull('chat_room_closed')
                    ->orWhere('chat_room_closed', false);
            });
        } elseif ($status === 'closed') {
            $query->where('chat_room_closed', true);
        }

        $chatRooms = $query->orderBy('created_at', 'desc')->paginate(10);

        // Statistics
        $totalChatRooms = Webinar::where('chat_room', true)->where('status', 'active')->count();
        $openChatRooms = Webinar::where('chat_room', true)->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('chat_room_closed')->orWhere('chat_room_closed', false);
            })->count();
        $closedChatRooms = Webinar::where('chat_room', true)->where('status', 'active')
            ->where('chat_room_closed', true)->count();
        $totalMessages = CourseChatMessage::count();

        // For filter select2
        $teachers = null;
        if (!empty($teacher_ids)) {
            $teachers = User::select('id', 'full_name')->whereIn('id', $teacher_ids)->get();
        }

        $data = [
            'pageTitle' => trans('update.chat_rooms'),
            'chatRooms' => $chatRooms,
            'totalChatRooms' => $totalChatRooms,
            'openChatRooms' => $openChatRooms,
            'closedChatRooms' => $closedChatRooms,
            'totalMessages' => $totalMessages,
            'teachers' => $teachers,
        ];

        return view('admin.live_chat.chat_rooms', $data);
    }

    /**
     * Clear all messages in a specific chat room.
     */
    public function clearChatRoomMessages($id)
    {
        $this->authorize('admin_live_chat_chat_rooms_clear');

        $webinar = Webinar::where('id', $id)
            ->where('chat_room', true)
            ->first();

        if (empty($webinar)) {
            abort(404);
        }

        // Delete associated files
        $messages = CourseChatMessage::where('webinar_id', $webinar->id)
            ->whereNotNull('file_path')
            ->get();

        foreach ($messages as $message) {
            $filePath = public_path(ltrim($message->file_path, '/'));
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        // Delete all messages
        CourseChatMessage::where('webinar_id', $webinar->id)->delete();

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.messages_cleared_successfully'),
            'status' => 'success'
        ];

        return redirect(getAdminPanelUrl() . '/live-chat/chat-rooms')->with(['toast' => $toastData]);
    }

    /**
     * Display all public chat (conversations between students and instructors)
     */
    public function publicChat(Request $request)
    {
        $this->authorize('admin_live_chat_public_chat');

        $query = PublicChatConversation::with([
            'user' => function ($q) {
                $q->select('id', 'full_name', 'avatar');
            },
            'instructor' => function ($q) {
                $q->select('id', 'full_name', 'avatar');
            },
            'latestMessage'
        ]);

        $totalConversations = deepClone($query)->count();
        // For public chat, we don't have a strict 'status' like support tickets, 
        // but we can show latest update stats or similar if needed.

        // Handle filters
        $query = $this->handlePublicChatFilters($request, $query);

        $conversations = $query->orderBy('updated_at', 'desc')
            ->paginate(10);

        foreach ($conversations as $conversation) {
            $conversation->source_title = $conversation->getSourceTitle();
        }

        // For filter select2
        $teacher_ids = $request->get('teacher_ids');
        $student_ids = $request->get('student_ids');

        $data = [
            'pageTitle' => trans('update.public_chat'),
            'conversations' => $conversations,
            'totalConversations' => $totalConversations,
        ];

        if (!empty($teacher_ids)) {
            $data['teachers'] = User::select('id', 'full_name')
                ->whereIn('id', $teacher_ids)->get();
        }

        if (!empty($student_ids)) {
            $data['students'] = User::select('id', 'full_name')
                ->whereIn('id', $student_ids)->get();
        }

        return view('admin.live_chat.public_chat', $data);
    }

        /**
     * View a specific public chat conversation
     */
    public function publicChatView($id)
    {
        $this->authorize('admin_live_chat_public_chat');

        $conversation = PublicChatConversation::with([
            'user',
            'instructor',
            'messages' => function ($q) {
                $q->with(['sender', 'replyTo', 'replyTo.sender'])->orderBy('created_at', 'asc');
            }
        ])->find($id);

        if (empty($conversation)) {
            abort(404);
        }

        $conversation->source_title = $conversation->getSourceTitle();

        $data = [
            'pageTitle' => trans('update.public_chat') . ' - ' . $conversation->source_title,
            'conversation' => $conversation,
            'messages' => $conversation->messages,
        ];

        return view('admin.live_chat.conversation', $data);
    }

    /**
     * Delete a specific public chat conversation
     */
    public function deletePublicChat($id)
    {
        $this->authorize('admin_live_chat_public_chat');

        $conversation = PublicChatConversation::find($id);

        if (!empty($conversation)) {
            // Delete all messages associated with this conversation
            PublicChatMessage::where('conversation_id', $conversation->id)->delete();
            
            // Delete the conversation itself
            $conversation->delete();

            return redirect()->back()->with(['toast' => [
                'title' => trans('public.request_success'),
                'msg' => 'Conversation deleted successfully',
                'status' => 'success'
            ]]);
        }

        abort(404);
    }

    /**
     * Handle filters for public chat listing.
     */
    private function handlePublicChatFilters(Request $request, $query)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $teacher_ids = $request->get('teacher_ids');
        $student_ids = $request->get('student_ids');
        $source_title = $request->get('source_title');
        $type = $request->get('type');

        if (!empty($from) and !empty($to)) {
            $query->whereBetween('created_at', [date('Y-m-d H:i:s', strtotime($from)), date('Y-m-d H:i:s', strtotime($to))]);
        }

        if (!empty($teacher_ids)) {
            $query->whereIn('instructor_id', $teacher_ids);
        }

        if (!empty($student_ids)) {
            $query->whereIn('user_id', $student_ids);
        }

        if (!empty($type)) {
            $query->where('source_type', $type);
        }

        // Searching by source title is tricky because it's dynamic, 
        // but we can filter by source_id if we first find the sources.
        if (!empty($source_title)) {
            $webinarIds = Webinar::whereTranslationLike('title', "%$source_title%")->pluck('id')->toArray();
            $bundleIds = \App\Models\Bundle::whereTranslationLike('title', "%$source_title%")->pluck('id')->toArray();
            $eventIds = \App\Models\Event::whereTranslationLike('title', "%$source_title%")->pluck('id')->toArray();
            
            $query->where(function($q) use ($webinarIds, $bundleIds, $eventIds) {
                $q->where(function($sq) use ($webinarIds) {
                    $sq->whereIn('source_type', ['webinar', 'course'])
                       ->whereIn('source_id', $webinarIds);
                })->orWhere(function($sq) use ($bundleIds) {
                    $sq->where('source_type', 'bundle')
                       ->whereIn('source_id', $bundleIds);
                })->orWhere(function($sq) use ($eventIds) {
                    $sq->where('source_type', 'event')
                       ->whereIn('source_id', $eventIds);
                });
            });
        }

        return $query;
    }

    /**
     * Display live chat settings page.
     */
    public function settings()
    {
        $this->authorize('admin_live_chat_settings');

        $value = getFeaturesSettings();

        if (!empty($value) and !is_array($value)) {
            $value = json_decode($value, true);
        }

        $data = [
            'pageTitle' => trans('update.live_chat_settings'),
            'itemValue' => $value,
        ];

        return view('admin.live_chat.settings', $data);
    }

    /**
     * Store live chat settings.
     */
    public function storeSettings(Request $request)
    {
        $this->authorize('admin_live_chat_settings');

        $newValues = $request->get('value', []);
        $name = Setting::$featuresName; // 'features'
        $page = 'general';
        $locale = Setting::$defaultSettingsLocale; // 'en'

        $settings = Setting::where('name', $name)->first();
        $values = [];

        if (!empty($settings) and !empty($settings->value)) {
            $values = json_decode($settings->value, true);
        }

        // Merge new values into existing ones
        if (!empty($newValues)) {
            foreach ($newValues as $newKey => $newValue) {
                $values[$newKey] = $newValue;
            }
        }

        // Update or create Setting record
        $settings = Setting::updateOrCreate(
            ['name' => $name],
            [
                'page' => $page,
                'updated_at' => time(),
            ]
        );

        // Update or create SettingTranslation record (the actual values are stored here)
        SettingTranslation::updateOrCreate(
            [
                'setting_id' => $settings->id,
                'locale' => mb_strtolower($locale)
            ],
            [
                'value' => json_encode($values),
            ]
        );

        // Clear cache so changes take effect immediately
        cache()->forget('settings.' . $name);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.update_setting_success'),
            'status' => 'success'
        ];

        return back()->with(['toast' => $toastData]);
    }
}
