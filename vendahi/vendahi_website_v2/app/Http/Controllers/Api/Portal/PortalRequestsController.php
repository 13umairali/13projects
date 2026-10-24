<?php

namespace App\Http\Controllers\Api\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ProtocolDesk;
use App\Models\ProtocolRequest;
use App\Models\ProtocolRequestAttachment;
use App\Models\ProtocolRequestEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Client-portal side of Protocol & Requests: a customer submits complaints /
 * requests / enquiries with attachments, follows the desk-to-desk progress
 * (customer-visible events only) and replies on the thread.
 */
class PortalRequestsController extends Controller
{
    private function clientId(): int
    {
        $portalClient = Auth::guard('portal')->user();
        if (! $portalClient || (int) $portalClient->status !== 1) {
            abort(403, 'Portal access is disabled');
        }

        return (int) $portalClient->client_id;
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
            'current_desk' => $r->currentDesk?->name,
            'created_at' => $r->created_at?->toDateTimeString(),
            'last_activity_at' => $r->last_activity_at?->toDateTimeString(),
            'closed_at' => $r->closed_at?->toDateTimeString(),
        ];
        if ($withHistory) {
            $events = $r->events()->where('is_internal', false)->with(['user', 'client', 'toDesk', 'attachments'])->get();
            $row['events'] = $events->map(fn ($e) => [
                'id' => $e->id,
                'action' => $e->action,
                'actor' => $e->client_id ? ($e->client?->name ?: 'You') : ($e->user_id ? 'Staff' : 'System'),
                'by_customer' => (bool) $e->client_id,
                'to_desk' => $e->toDesk?->name,
                'note' => $e->note,
                'created_at' => $e->created_at?->toDateTimeString(),
                'attachments' => $e->attachments->map(fn ($a) => [
                    'id' => $a->id, 'file_name' => $a->file_name, 'file_size' => $a->file_size, 'url' => $a->url(),
                ])->values(),
            ])->values();
        }

        return $row;
    }

    private function storeFiles(Request $request, ProtocolRequest $req, int $clientId, ?int $eventId): void
    {
        $files = $request->file('attachments');
        if (! $files) {
            return;
        }
        $files = is_array($files) ? $files : [$files];
        $dir = upload_public_path('protocol');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
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
                'uploaded_by_client_id' => $clientId,
            ]);
        }
    }

    /** GET /api/portal/requests */
    public function index(Request $request)
    {
        $clientId = $this->clientId();
        $perPage = max(1, min(100, (int) $request->input('limit', 10)));
        $page = max(1, (int) $request->input('page', 1));

        $q = ProtocolRequest::whereNull('deleted_at')->where('client_id', $clientId)
            ->when($request->filled('status'), fn ($x) => $x->where('status', $request->status))
            ->when($request->filled('search'), fn ($x) => $x->where(fn ($w) => $w->where('Ref', 'LIKE', '%'.$request->search.'%')->orWhere('subject', 'LIKE', '%'.$request->search.'%')));
        $total = (clone $q)->count();
        $rows = $q->with('currentDesk')->orderByDesc('last_activity_at')->orderByDesc('id')
            ->offset(($page - 1) * $perPage)->limit($perPage)->get()->map(fn ($r) => $this->serialize($r));

        return response()->json([
            'requests' => $rows,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => max(1, (int) ceil($total / $perPage)),
            'types' => ProtocolRequest::TYPES,
        ]);
    }

    /** GET /api/portal/requests/{id} */
    public function show(Request $request, $id)
    {
        $clientId = $this->clientId();
        $req = ProtocolRequest::whereNull('deleted_at')->where('client_id', $clientId)->with('currentDesk')->findOrFail($id);

        return response()->json(['request' => $this->serialize($req, true)]);
    }

    /** POST /api/portal/requests */
    public function store(Request $request)
    {
        $clientId = $this->clientId();
        $client = Client::findOrFail($clientId);
        $data = $request->validate([
            'type' => ['required', Rule::in(ProtocolRequest::TYPES)],
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
            'attachments' => 'nullable',
            'attachments.*' => 'file|max:10240',
        ]);
        $desk = ProtocolDesk::entryDesk();
        if (! $desk) {
            return response()->json(['message' => 'Requests are not being accepted at the moment.'], 422);
        }

        $req = DB::transaction(function () use ($request, $data, $desk, $client) {
            $req = ProtocolRequest::create([
                'type' => $data['type'],
                'subject' => $data['subject'],
                'body' => $data['body'],
                'priority' => 'normal',
                'status' => 'open',
                'client_id' => $client->id,
                'submitter_name' => $client->name,
                'submitter_email' => $client->email,
                'channel' => 'portal',
                'current_desk_id' => $desk->id,
                'last_activity_at' => Carbon::now(),
            ]);
            $req->Ref = 'PR-'.str_pad((string) $req->id, 5, '0', STR_PAD_LEFT);
            $req->save();
            $ev = ProtocolRequestEvent::create([
                'request_id' => $req->id, 'action' => 'submitted', 'client_id' => $client->id,
                'to_desk_id' => $desk->id, 'is_internal' => false,
            ]);
            $this->storeFiles($request, $req, $client->id, $ev->id);

            return $req;
        });

        return response()->json(['success' => true, 'id' => $req->id, 'Ref' => $req->Ref], 201);
    }

    /** POST /api/portal/requests/{id}/reply — a customer message on the thread. */
    public function reply(Request $request, $id)
    {
        $clientId = $this->clientId();
        $req = ProtocolRequest::whereNull('deleted_at')->where('client_id', $clientId)->findOrFail($id);
        if ($req->status === 'closed') {
            return response()->json(['message' => 'This request is closed.'], 422);
        }
        $data = $request->validate([
            'note' => 'required|string|max:10000',
            'attachments' => 'nullable',
            'attachments.*' => 'file|max:10240',
        ]);
        $ev = DB::transaction(function () use ($request, $req, $data, $clientId) {
            $ev = ProtocolRequestEvent::create([
                'request_id' => $req->id, 'action' => 'reply', 'client_id' => $clientId,
                'from_desk_id' => $req->current_desk_id, 'note' => $data['note'], 'is_internal' => false,
            ]);
            $this->storeFiles($request, $req, $clientId, $ev->id);
            $req->last_activity_at = Carbon::now();
            $req->save();

            return $ev;
        });

        return response()->json(['success' => true, 'event_id' => $ev->id]);
    }
}
