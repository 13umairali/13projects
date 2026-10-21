<?php

namespace App\Support;

/**
 * The websocket configuration browsers and the mobile app need so call / meeting signals (offer, answer,
 * ICE candidates) arrive the instant they are sent instead of on the next poll.
 *
 * Works with both Pusher-compatible transports:
 *   - pusher.com          (BROADCAST_CONNECTION=pusher + PUSHER_APP_KEY / SECRET)
 *   - self-hosted Reverb  (BROADCAST_CONNECTION=reverb + REVERB_APP_KEY / SECRET)
 *
 * The client-facing host / port / scheme come from services.realtime (PUSHER_WS_HOST / PUSHER_WS_PORT /
 * PUSHER_WS_SCHEME) so the same values serve both transports.
 */
class ClientRealtime
{
    public static function connection(): ?string
    {
        $c = (string) config('broadcasting.default');
        return in_array($c, ['pusher', 'reverb'], true) ? $c : null;
    }

    public static function key(): string
    {
        $c = self::connection();
        return $c ? trim((string) config('broadcasting.connections.' . $c . '.key')) : '';
    }

    public static function secret(): string
    {
        $c = self::connection();
        return $c ? trim((string) config('broadcasting.connections.' . $c . '.secret')) : '';
    }

    public static function enabled(): bool
    {
        return self::connection() !== null && self::key() !== '';
    }

    /**
     * The <meta name="dahi-realtime"> payload consumed by resources/js/realtime.js, and the same
     * shape the mobile app expects from realtime/config.
     *
     * @return array{enabled:bool,key:string,cluster:string,host:?string,port:int,tls:bool,auth:string}
     */
    public static function client(): array
    {
        return [
            'enabled' => self::enabled(),
            'key' => self::key(),
            'cluster' => (string) config('broadcasting.connections.pusher.options.cluster', 'mt1'),
            'host' => config('services.realtime.ws_host'),
            'port' => (int) config('services.realtime.ws_port', 443),
            'tls' => config('services.realtime.ws_scheme', 'https') === 'https',
            'auth' => url('/broadcasting/auth'),
        ];
    }

    /** The meta tag payload for a signed-in person (user + workspace channels + client()). */
    public static function meta(?int $userId, ?int $workspaceId = null): array
    {
        return ['user' => $userId, 'workspace' => $workspaceId] + self::client();
    }
}
