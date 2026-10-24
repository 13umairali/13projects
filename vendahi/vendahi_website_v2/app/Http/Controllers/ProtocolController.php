<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ProtocolDesk;
use App\Models\ProtocolRequest;
use App\Models\ProtocolRequestAttachment;
use App\Models\ProtocolRequestEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Protocol & Requests — internal messaging / administrative protocol /
 * ticketing. Requests move desk to desk; every action is an event on the
 * request's history.
 */
class ProtocolController extends BaseController
{
    // ------------------------------------------------------------ helpers

    /** The acting user's desk ids, or null when they may act anywhere. */
    private function scope(Request $request): array
    {
        $user = $request->user('api') ?: Auth::user();
        $manage = $user ? app(\App\Policies\ProtocolRequestPolicy::class)->manage($user) : false;

        return [
            'user' => $user,
            'manage' => (bool) $manage,
            'deskIds' => $user ? ProtocolDesk::idsForUser((int) $user->id) : [],
        ];
    }

    /** Requests the user may see: everything (manage) or own / desk / assigned. */
    private function visibleQuery(Request $request)
    {
        $s = $this->scope($request);
        $q = ProtocolRequest::query()->whereNull('deleted_at');
        if ($s['manage']) {
            return $q;
        }
        $userId = (int) $s['user']->id;
        $deskIds = $s['deskIds'];

        return $q->where(function ($w) use ($userId, $deskIds) {
            $w->where('submitted_by_user_id', $userId)
                ->orWhere('assigned_to_user_id', $userId)
                ->when(! empty($deskIds), fn ($x) => $x->orWhereIn('current_desk_id', $deskIds))
                ->orWhereIn('id', function ($sub) use ($deskIds, $userId) {
                    // …and requests that passed through one of the user's desks.
                    $sub->select('request_id')->from('protocol_request_events')
                        ->where(function ($y) use ($deskIds, $userId) {
                            $y->where('user_id', $userId);
                            if (! empty($deskIds)) {
                                $y->orWhereIn('from_desk_id', $deskIds)->orWhereIn('to_desk_id', $deskIds);
                            }
                        });
                });
        });
    }

    /** May the user act on this request at its current desk? */
    private function assertCanAct(Request $request, ProtocolRequest $req): array
    {
        $this->authorizeForUser($request->user('api'), 'act', ProtocolRequest::class);
        $s = $this->scope($request);
        if ($s['manage']) {
            return $s;
        }
        $userId = (int) $s['user']->id;
        $atDesk = $req->current_desk_id && in_array((int) $req->current_desk_id, $s['deskIds'], true);
        if (! $atDesk && (int) $req->assigned_to_user_id !== $userId) {
            abort(403, 'This request is not at one of your desks.');
        }

        return $s;
    }

    private function event(ProtocolRequest $req, string $action, array $data = []): ProtocolRequestEvent
    {
        $ev = ProtocolRequestEvent::create(array_merge([
            'request_id' => $req->id,
            'action' => $action,
            'user_id' => Auth::id(),
            'is_internal' => false,
        ], $data));
        $req->last_activity_at = Carbon::now();
        $req->save();

        return $ev;
    }

    private function storeFiles(Request $request, ProtocolRequest $req, ?int $eventId, string $field = 'attachments'): int
    {
        $files = $request->file($field);
        if (! $files) {
            return 0;
        }
        $files = is_array($files) ? $files : [$files];
        $dir = upload_public_path('protocol');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $n = 0;
        foreach ($files as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $name = Str::random(24).'.'.$ext;
            // Capture metadata BEFORE move() — the temp file is gone afterwards.
            $originalName = $file->getClientOriginalName();
            $size = $file->getSize();
            $file->move($dir, $name);
            ProtocolRequestAttachment::create([
                'request_id' => $req->id,
                'event_id' => $eventId,
                'file_name' => $originalName,
                'file_path' => upload_path('protocol').'/'.$name,
                'file_type' => $ext,
                'file_size' => $size ?: null,
                'uploaded_by_user_id' => Auth::id(),
            ]);
            $n++;
        }

        return $n;
    }

    private function serialize(ProtocolRequest $r, bool $withHistory = false): array
    {
        $row = [
            'id' => $r->id,
            'Ref' => $r->Ref,
            'type' => $r->type,
            'subject' => $r->subject,
            'body' => $r->body,
            'priority' => $r->priority,
            'status' => $r->status,
            'channel' => $r->channel,
            'submitter' => $r->submitterLabel(),
            'submitter_email' => $r->submitter_email,
            'submitted_by_user_id' => $r->submitted_by_user_id,
            'client_id' => $r->client_id,
            'client_name' => $r->client?->name,
            'current_desk_id' => $r->current_desk_id,
            'current_desk' => $r->currentDesk?->name,
            'previous_desk_id' => $r->previous_desk_id,
            'previous_desk' => $r->previousDesk?->name,
            'assigned_to_user_id' => $r->assigned_to_user_id,
            'assigned_to' => $r->assignee?->username,
            'due_date' => $r->due_date?->format('Y-m-d'),
            'overdue' => $r->due_date && $r->isActive() && $r->due_date->lt(Carbon::today()),
            'received_at' => $r->received_at?->toDateTimeString(),
            'acknowledged_at' => $r->acknowledged_at?->toDateTimeString(),
            'decided_at' => $r->decided_at?->toDateTimeString(),
            'closed_at' => $r->closed_at?->toDateTimeString(),
            'closed_by' => $r->closedBy?->username,
            'last_activity_at' => $r->last_activity_at?->toDateTimeString(),
            'created_at' => $r->created_at?->toDateTimeString(),
            'attachments_count' => $r->attachments()->count(),
        ];
        if ($withHistory) {
            $row['events'] = $r->events()->with(['user', 'client', 'fromDesk', 'toDesk', 'assignee', 'attachments'])->get()->map(fn ($e) => [
                'id' => $e->id,
                'action' => $e->action,
                'actor' => $e->user?->username ?: ($e->client?->name ?: '---'),
                'actor_type' => $e->user_id ? 'staff' : ($e->client_id ? 'customer' : 'system'),
                'from_desk' => $e->fromDesk?->name,
                'to_desk' => $e->toDesk?->name,
                'assigned_to' => $e->assignee?->username,
                'note' => $e->note,
                'is_internal' => (bool) $e->is_internal,
                'created_at' => $e->created_at?->toDateTimeString(),
                'attachments' => $e->attachments->map(fn ($a) => $this->serializeAttachment($a))->values(),
            ])->values();
            $row['attachments'] = $r->attachments->map(fn ($a) => $this->serializeAttachment($a))->values();
        }

        return $row;
    }

    private function serializeAttachment(ProtocolRequestAttachment $a): array
    {
        return [
            'id' => $a->id,
            'event_id' => $a->event_id,
            'file_name' => $a->file_name,
            'file_type' => $a->file_type,
            'file_size' => $a->file_size,
            'url' => $a->url(),
            'created_at' => $a->created_at?->toDateTimeString(),
        ];
    }

    // ------------------------------------------------------------ meta

    /** Lists for the pages: desks, users, types, statuses + the user's scope. */
    public function meta(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', ProtocolRequest::class);
        $s = $this->scope($request);

        $desks = ProtocolDesk::whereNull('deleted_at')->with('users:id,username')->orderBy('sort_order')->orderBy('id')->get()
            ->map(fn ($d) => [
                'id' => $d->id, 'name' => $d->name, 'description' => $d->description,
                'sort_order' => $d->sort_order, 'is_active' => (bool) $d->is_active,
                'user_ids' => $d->users->pluck('id')->values(),
                'users' => $d->users->pluck('username')->values(),
            ]);

        return response()->json([
            'desks' => $desks,
            'users' => User::whereNull('deleted_at')->where('statut', 1)->orderBy('username')->get(['id', 'username']),
            'types' => ProtocolRequest::TYPES,
            'priorities' => ProtocolRequest::PRIORITIES,
            'statuses' => ProtocolRequest::STATUSES,
            'me' => [
                'id' => $s['user']?->id,
                'manage' => $s['manage'],
                'desk_ids' => $s['deskIds'],
            ],
        ]);
    }

    /** KPI counts for the list page. */
    public function dashboard(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', ProtocolRequest::class);
        $s = $this->scope($request);
        $visible = fn () => $this->visibleQuery($request);

        $inbox = $s['manage']
            ? (clone $visible())->whereIn('status', ProtocolRequest::ACTIVE_STATUSES)->count()
            : (clone $visible())->whereIn('status', ProtocolRequest::ACTIVE_STATUSES)
                ->where(function ($w) use ($s) {
                    $w->whereIn('current_desk_id', $s['deskIds'] ?: [0])->orWhere('assigned_to_user_id', $s['user']->id);
                })->count();

        return response()->json([
            'inbox' => $inbox,
            'awaiting_receipt' => (clone $visible())->where('status', 'open')->count(),
            'open' => (clone $visible())->whereIn('status', ProtocolRequest::ACTIVE_STATUSES)->count(),
            'overdue' => (clone $visible())->whereIn('status', ProtocolRequest::ACTIVE_STATUSES)->whereDate('due_date', '<', Carbon::today())->count(),
            'closed_this_month' => (clone $visible())->where('status', 'closed')->where('closed_at', '>=', Carbon::now()->startOfMonth())->count(),
            'by_status' => (clone $visible())->select('status', DB::raw('count(*) as n'))->groupBy('status')->pluck('n', 'status'),
            'by_type' => (clone $visible())->select('type', DB::raw('count(*) as n'))->groupBy('type')->pluck('n', 'type'),
        ]);
    }

    // ------------------------------------------------------------ requests

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', ProtocolRequest::class);
        $s = $this->scope($request);

        $perPage = (int) $request->input('limit', 10);
        $page = max(1, (int) $request->input('page', 1));
        $order = in_array($request->SortField, ['id', 'Ref', 'subject', 'type', 'priority', 'status', 'due_date', 'created_at', 'last_activity_at'], true)
            ? $request->SortField : 'last_activity_at';
        $dir = strtolower((string) $request->SortType) === 'asc' ? 'asc' : 'desc';

        $q = $this->visibleQuery($request)
            // 'active' is a virtual status: everything still moving.
            ->when($request->filled('status') && $request->status !== 'active', fn ($x) => $x->where('status', $request->status))
            ->when($request->status === 'active', fn ($x) => $x->whereIn('status', ProtocolRequest::ACTIVE_STATUSES))
            ->when($request->filled('type'), fn ($x) => $x->where('type', $request->type))
            ->when($request->filled('priority'), fn ($x) => $x->where('priority', $request->priority))
            ->when($request->filled('desk_id'), fn ($x) => $x->where('current_desk_id', $request->desk_id))
            ->when($request->filled('client_id'), fn ($x) => $x->where('client_id', $request->client_id))
            ->when($request->scope === 'inbox' && $s['user'], function ($x) use ($s) {
                $x->whereIn('status', ProtocolRequest::ACTIVE_STATUSES)->where(function ($w) use ($s) {
                    $w->whereIn('current_desk_id', $s['deskIds'] ?: [0])->orWhere('assigned_to_user_id', $s['user']->id);
                });
            })
            ->when($request->scope === 'mine' && $s['user'], fn ($x) => $x->where('submitted_by_user_id', $s['user']->id))
            ->when($request->filled('search'), function ($x) use ($request) {
                $needle = '%'.$request->search.'%';
                $x->where(function ($w) use ($needle) {
                    $w->where('Ref', 'LIKE', $needle)->orWhere('subject', 'LIKE', $needle)->orWhere('body', 'LIKE', $needle)
                        ->orWhere('submitter_name', 'LIKE', $needle)
                        ->orWhereHas('client', fn ($c) => $c->where('name', 'LIKE', $needle));
                });
            });

        $totalRows = (clone $q)->count();
        if ($perPage <= 0) {
            $perPage = $totalRows ?: 10;
        }
        $rows = $q->with(['client', 'currentDesk', 'previousDesk', 'assignee', 'submitter', 'closedBy'])
            ->orderBy($order, $dir)->orderBy('id', 'desc')
            ->offset(($page - 1) * $perPage)->limit($perPage)->get()
            ->map(fn ($r) => $this->serialize($r));

        return response()->json(['requests' => $rows, 'totalRows' => $totalRows]);
    }

    public function show(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'view', ProtocolRequest::class);
        $req = $this->visibleQuery($request)->with(['client', 'currentDesk', 'previousDesk', 'assignee', 'submitter', 'closedBy'])->findOrFail($id);
        $s = $this->scope($request);
        $canAct = $s['manage'] || ($req->current_desk_id && in_array((int) $req->current_desk_id, $s['deskIds'], true)) || (int) $req->assigned_to_user_id === (int) $s['user']->id;
        $data = $this->serialize($req, true);
        $data['can_act'] = $canAct && app(\App\Policies\ProtocolRequestPolicy::class)->act($s['user']);
        $data['can_manage'] = $s['manage'];

        return response()->json(['request' => $data]);
    }

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', ProtocolRequest::class);

        $data = $request->validate([
            'type' => ['required', Rule::in(ProtocolRequest::TYPES)],
            'subject' => 'required|string|max:255',
            'body' => 'nullable|string',
            'priority' => ['nullable', Rule::in(ProtocolRequest::PRIORITIES)],
            'desk_id' => 'nullable|integer|exists:protocol_desks,id',
            'client_id' => 'nullable|integer|exists:clients,id',
            'submitter_name' => 'nullable|string|max:150',
            'submitter_email' => 'nullable|email|max:150',
            'due_date' => 'nullable|date',
            'attachments' => 'nullable',
            'attachments.*' => 'file|max:10240',
        ]);

        $desk = ! empty($data['desk_id']) ? ProtocolDesk::find($data['desk_id']) : ProtocolDesk::entryDesk();
        if (! $desk) {
            return response()->json(['message' => 'Create at least one desk (Protocol → Desks) before submitting requests.'], 422);
        }

        $req = DB::transaction(function () use ($request, $data, $desk) {
            $req = ProtocolRequest::create([
                'type' => $data['type'],
                'subject' => $data['subject'],
                'body' => $data['body'] ?? null,
                'priority' => $data['priority'] ?? 'normal',
                'status' => 'open',
                'submitted_by_user_id' => Auth::id(),
                'client_id' => $data['client_id'] ?? null,
                'submitter_name' => $data['submitter_name'] ?? null,
                'submitter_email' => $data['submitter_email'] ?? null,
                'channel' => 'staff',
                'current_desk_id' => $desk->id,
                'due_date' => $data['due_date'] ?? null,
                'last_activity_at' => Carbon::now(),
            ]);
            $req->Ref = 'PR-'.str_pad((string) $req->id, 5, '0', STR_PAD_LEFT);
            $req->save();

            $ev = $this->event($req, 'submitted', ['to_desk_id' => $desk->id, 'note' => null]);
            $this->storeFiles($request, $req, $ev->id);

            return $req;
        });

        return response()->json(['success' => true, 'id' => $req->id, 'Ref' => $req->Ref]);
    }

    /** Edit the request's own fields (subject, body, type, priority, due date). */
    public function update(Request $request, $id)
    {
        $req = $this->visibleQuery($request)->findOrFail($id);
        $s = $this->scope($request);
        if (! $s['manage'] && (int) $req->submitted_by_user_id !== (int) $s['user']->id) {
            $this->assertCanAct($request, $req);
        }
        $data = $request->validate([
            'type' => ['nullable', Rule::in(ProtocolRequest::TYPES)],
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'priority' => ['nullable', Rule::in(ProtocolRequest::PRIORITIES)],
            'due_date' => 'nullable|date',
        ]);
        $req->fill(array_filter($data, fn ($v) => $v !== null));
        if ($request->has('due_date') && ! $request->filled('due_date')) {
            $req->due_date = null;
        }
        $req->save();
        $this->event($req, 'updated', ['is_internal' => true, 'note' => 'Request details edited']);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'manage', ProtocolRequest::class);
        $req = ProtocolRequest::findOrFail($id);
        foreach ($req->attachments as $a) {
            $full = public_path($a->file_path);
            if ($a->file_path && is_file($full)) {
                @unlink($full);
            }
        }
        $req->delete();

        return response()->json(['success' => true]);
    }

    // ------------------------------------------------------------ actions

    /**
     * One endpoint for every desk action: POST protocol/requests/{id}/action
     * {action, note, to_desk_id, assigned_to_user_id, is_internal, attachments[]}.
     */
    public function action(Request $request, $id)
    {
        $req = ProtocolRequest::whereNull('deleted_at')->findOrFail($id);
        $s = $this->assertCanAct($request, $req);
        $data = $request->validate([
            'action' => ['required', Rule::in(['receive', 'acknowledge', 'note', 'respond', 'forward', 'reassign', 'return', 'approve', 'reject', 'close', 'reopen', 'attach'])],
            'note' => 'nullable|string',
            'to_desk_id' => 'nullable|integer|exists:protocol_desks,id',
            'assigned_to_user_id' => 'nullable|integer|exists:users,id',
            'is_internal' => 'nullable',
            'attachments' => 'nullable',
            'attachments.*' => 'file|max:10240',
        ]);
        $action = $data['action'];
        $note = isset($data['note']) ? trim((string) $data['note']) : null;
        $now = Carbon::now();

        if ($req->status === 'closed' && $action !== 'reopen') {
            return response()->json(['message' => 'This request is closed. Reopen it first.'], 422);
        }
        if (in_array($action, ['note', 'respond', 'forward', 'return', 'approve', 'reject'], true) && $note === '' ) {
            $note = null;
        }
        if (in_array($action, ['note', 'respond'], true) && $note === null) {
            return response()->json(['message' => 'A note is required.'], 422);
        }

        $ev = DB::transaction(function () use ($req, $s, $action, $note, $data, $now, $request) {
            $fromDesk = $req->current_desk_id;
            switch ($action) {
                case 'receive':
                    $req->status = in_array($req->status, ['open'], true) ? 'received' : $req->status;
                    $req->received_at = $now;
                    $ev = $this->event($req, 'received', ['from_desk_id' => $fromDesk, 'note' => $note, 'is_internal' => true]);
                    break;

                case 'acknowledge':
                    if (in_array($req->status, ['open', 'received'], true)) {
                        $req->status = 'acknowledged';
                    }
                    $req->acknowledged_at = $req->acknowledged_at ?: $now;
                    $req->received_at = $req->received_at ?: $now;
                    $ev = $this->event($req, 'acknowledged', ['from_desk_id' => $fromDesk, 'note' => $note]);
                    break;

                case 'note':
                    if (in_array($req->status, ['open', 'received', 'acknowledged'], true)) {
                        $req->status = 'in_progress';
                    }
                    $ev = $this->event($req, 'note', ['from_desk_id' => $fromDesk, 'note' => $note, 'is_internal' => true]);
                    break;

                case 'respond':
                    if (in_array($req->status, ['open', 'received', 'acknowledged'], true)) {
                        $req->status = 'in_progress';
                    }
                    $ev = $this->event($req, 'response', ['from_desk_id' => $fromDesk, 'note' => $note, 'is_internal' => false]);
                    break;

                case 'forward':
                    $to = ! empty($data['to_desk_id']) ? (int) $data['to_desk_id'] : null;
                    if (! $to) {
                        // Default: the next desk in chain order.
                        $cur = ProtocolDesk::find($fromDesk);
                        $next = ProtocolDesk::where('is_active', true)
                            ->when($cur, fn ($q) => $q->where(fn ($w) => $w->where('sort_order', '>', $cur->sort_order)->orWhere(fn ($z) => $z->where('sort_order', $cur->sort_order)->where('id', '>', $cur->id))))
                            ->orderBy('sort_order')->orderBy('id')->first();
                        if (! $next) {
                            abort(422, 'No next desk in the chain — choose a desk to forward to.');
                        }
                        $to = $next->id;
                    }
                    if ($to === (int) $fromDesk) {
                        abort(422, 'The request is already at that desk.');
                    }
                    $req->previous_desk_id = $fromDesk;
                    $req->current_desk_id = $to;
                    $req->assigned_to_user_id = null;
                    $req->status = 'open';
                    $req->received_at = null;
                    $ev = $this->event($req, 'forwarded', ['from_desk_id' => $fromDesk, 'to_desk_id' => $to, 'note' => $note, 'is_internal' => true]);
                    break;

                case 'reassign':
                    $uid = ! empty($data['assigned_to_user_id']) ? (int) $data['assigned_to_user_id'] : null;
                    if (! $uid) {
                        abort(422, 'Choose the officer to assign.');
                    }
                    $req->assigned_to_user_id = $uid;
                    $ev = $this->event($req, 'reassigned', ['from_desk_id' => $fromDesk, 'assigned_to_user_id' => $uid, 'note' => $note, 'is_internal' => true]);
                    break;

                case 'return':
                    $prev = $req->previous_desk_id;
                    if (! $prev) {
                        // Fall back to the desk of the last forward event.
                        $last = $req->events()->where('action', 'forwarded')->orderByDesc('id')->first();
                        $prev = $last?->from_desk_id;
                    }
                    if (! $prev) {
                        abort(422, 'There is no previous desk to return this request to.');
                    }
                    $req->previous_desk_id = $fromDesk;
                    $req->current_desk_id = $prev;
                    $req->assigned_to_user_id = null;
                    $req->status = 'open';
                    $req->received_at = null;
                    $ev = $this->event($req, 'returned', ['from_desk_id' => $fromDesk, 'to_desk_id' => $prev, 'note' => $note, 'is_internal' => true]);
                    break;

                case 'approve':
                case 'reject':
                    $req->status = $action === 'approve' ? 'approved' : 'rejected';
                    $req->decided_at = $now;
                    $ev = $this->event($req, $action === 'approve' ? 'approved' : 'rejected', ['from_desk_id' => $fromDesk, 'note' => $note, 'is_internal' => false]);
                    break;

                case 'close':
                    $req->status = 'closed';
                    $req->closed_at = $now;
                    $req->closed_by_user_id = Auth::id();
                    $ev = $this->event($req, 'closed', ['from_desk_id' => $fromDesk, 'note' => $note, 'is_internal' => false]);
                    break;

                case 'reopen':
                    $req->status = 'open';
                    $req->closed_at = null;
                    $req->closed_by_user_id = null;
                    $req->decided_at = null;
                    $ev = $this->event($req, 'reopened', ['from_desk_id' => $fromDesk, 'note' => $note, 'is_internal' => false]);
                    break;

                case 'attach':
                default:
                    $ev = $this->event($req, 'attachment', ['from_desk_id' => $fromDesk, 'note' => $note, 'is_internal' => (bool) filter_var($data['is_internal'] ?? false, FILTER_VALIDATE_BOOLEAN)]);
                    break;
            }
            $req->save();
            $n = $this->storeFiles($request, $req, $ev->id);
            if ($n === 0 && $action === 'attach') {
                abort(422, 'Choose at least one file.');
            }

            return $ev;
        });

        return response()->json(['success' => true, 'event_id' => $ev->id, 'status' => $req->fresh()->status]);
    }

    public function attachmentDestroy(Request $request, $id)
    {
        $a = ProtocolRequestAttachment::findOrFail($id);
        $req = ProtocolRequest::findOrFail($a->request_id);
        $s = $this->scope($request);
        if (! $s['manage'] && (int) $a->uploaded_by_user_id !== (int) $s['user']->id) {
            $this->assertCanAct($request, $req);
        }
        $full = public_path($a->file_path);
        if ($a->file_path && is_file($full)) {
            @unlink($full);
        }
        $a->delete();

        return response()->json(['success' => true]);
    }

    // ------------------------------------------------------------ desks

    public function desksIndex(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', ProtocolRequest::class);
        $desks = ProtocolDesk::whereNull('deleted_at')->with('users:id,username')->withCount([
            'requests as active_count' => fn ($q) => $q->whereIn('status', ProtocolRequest::ACTIVE_STATUSES),
        ])->orderBy('sort_order')->orderBy('id')->get()->map(fn ($d) => [
            'id' => $d->id, 'name' => $d->name, 'description' => $d->description, 'sort_order' => $d->sort_order,
            'is_active' => (bool) $d->is_active, 'active_count' => (int) $d->active_count,
            'user_ids' => $d->users->pluck('id')->values(), 'users' => $d->users->pluck('username')->values(),
        ]);

        return response()->json(['desks' => $desks, 'totalRows' => $desks->count()]);
    }

    public function desksStore(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'manage', ProtocolRequest::class);
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);
        $desk = ProtocolDesk::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? ((int) ProtocolDesk::max('sort_order') + 1),
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);
        $desk->users()->sync($data['user_ids'] ?? []);

        return response()->json(['success' => true, 'id' => $desk->id]);
    }

    public function desksUpdate(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'manage', ProtocolRequest::class);
        $desk = ProtocolDesk::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);
        $desk->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? $desk->sort_order,
            'is_active' => filter_var($data['is_active'] ?? $desk->is_active, FILTER_VALIDATE_BOOLEAN),
        ]);
        if ($request->has('user_ids')) {
            $desk->users()->sync($data['user_ids'] ?? []);
        }

        return response()->json(['success' => true]);
    }

    public function desksDestroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'manage', ProtocolRequest::class);
        $desk = ProtocolDesk::findOrFail($id);
        $active = ProtocolRequest::where('current_desk_id', $desk->id)->whereIn('status', ProtocolRequest::ACTIVE_STATUSES)->count();
        if ($active > 0) {
            return response()->json(['message' => "This desk still holds {$active} active request(s). Forward them first."], 422);
        }
        $desk->users()->detach();
        $desk->delete();

        return response()->json(['success' => true]);
    }

    /** Customers for the "on behalf of" picker (small search). */
    public function clients(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', ProtocolRequest::class);
        $rows = Client::whereNull('deleted_at')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'LIKE', '%'.$request->search.'%'))
            ->orderBy('name')->limit(30)->get(['id', 'name', 'email']);

        return response()->json(['clients' => $rows]);
    }
}
