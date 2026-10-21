<?php
namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Support\ClientRealtime;
use Illuminate\Http\Request;

class RealtimeController extends Controller
{
    public function config(Request $request)
    {
        // pusher.com or a self-hosted Reverb server — both speak the Pusher protocol,
        // so the app only needs to know which host to open a websocket to.
        $c = ClientRealtime::client();
        return response()->json([
            'enabled' => $c['enabled'],
            'key' => $c['key'],
            'host' => $c['host'] ?: 'ws-'.config('broadcasting.connections.pusher.options.cluster', 'mt1').'.pusher.com',
            'port' => $c['port'],
            'tls' => $c['tls'],
            'channel' => 'private-user.'.$request->user()->id,
        ])->header('Cache-Control', 'no-store');
    }

    public function auth(Request $request)
    {
        $data = $request->validate(['socket_id' => ['required', 'string', 'max:100', 'regex:/^\d+\.\d+$/'], 'channel_name' => ['required', 'string', 'max:100']]);
        abort_unless($data['channel_name'] === 'private-user.'.$request->user()->id, 403);
        abort_unless(ClientRealtime::enabled() && ClientRealtime::secret() !== '', 503);
        return response()->json(['auth' => ClientRealtime::key().':'.hash_hmac('sha256', $data['socket_id'].':'.$data['channel_name'], ClientRealtime::secret())])->header('Cache-Control', 'no-store');
    }
}
