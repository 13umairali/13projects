<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Super-admin controls for friend chat, file sharing and audio calls (Admin → Phone & Friends). */
class FriendSettings
{
    private static function on(string $key, string $default = '1'): bool
    {
        return SystemSetting::get($key, $default) === '1';
    }

    public static function chatEnabled(): bool { return self::on('friends_chat_enabled'); }
    public static function filesEnabled(): bool { return self::chatEnabled() && self::on('friends_files_enabled'); }
    public static function callsEnabled(): bool { return self::on('friends_calls_enabled'); }

    /** super-admin switch (Admin → Phone & Friends). OFF by default: read the legal note in INSTALL_STEP1.md first. */
    public static function callRecordingEnabled(): bool { return self::callsEnabled() && \App\Services\Recording\RecordingPolicy::enabled(); }

    public static function callRecordingMaxMb(): int
    {
        return max(5, min(500, (int) SystemSetting::get('friends_call_recording_max_mb', '100')));
    }

    public static function fileMaxMb(): int
    {
        return max(1, min(50, (int) SystemSetting::get('friends_file_max_mb', '10')));
    }

    public static function features(): array
    {
        return ['chat' => self::chatEnabled(), 'files' => self::filesEnabled(), 'calls' => self::callsEnabled(), 'max_mb' => self::fileMaxMb(), 'call_recording' => self::callRecordingEnabled()];
    }

    /**
     * STUN finds a phone's public address; TURN relays the audio/video when a direct connection is impossible (strict
     * networks, many mobile carriers). Without a TURN server some calls cannot connect.
     *
     * Order of preference:
     *  1. Cloudflare TURN (short-lived credentials made by the server, see cloudflareIce()).
     *  2. If Cloudflare is not configured or unreachable: the Google STUN server, as before.
     *  3. A manually entered TURN server from the admin page is always added on top (optional).
     */
    public static function iceServers(): array
    {
        $servers = self::cloudflareIce();

        if (!$servers) {
            $stun = trim(SystemSetting::get('friends_stun_url', 'stun:stun.l.google.com:19302')) ?: 'stun:stun.l.google.com:19302';
            $servers = [['urls' => $stun]];
        }

        $turn = trim(SystemSetting::get('friends_turn_url'));
        if ($turn !== '') {
            $servers[] = [
                'urls' => array_values(array_filter(array_map('trim', explode(',', $turn)))),
                'username' => SystemSetting::get('friends_turn_user'),
                'credential' => self::turnPassword(),
            ];
        }
        return $servers;
    }

    /**
     * Short-lived Cloudflare TURN credentials. The TURN key itself cannot be used as a username/password, so the server
     * asks Cloudflare for credentials (valid 24 h) and caches them for 18 h, so a call almost never waits for
     * Cloudflare. Returns [] when Cloudflare is not configured or the request fails (callers then use the old STUN setup).
     *
     * Only three addresses are handed to the clients on purpose: every extra address that a network blocks has to time
     * out before ICE gives up on it, which made the connection slow.
     *   - stun:...:3478                  finds the public address (fast, UDP)
     *   - turn:...:3478?transport=udp    relay over UDP (fast)
     *   - turns:...:443?transport=tcp    relay over TLS on 443: passes strict firewalls and mobile carriers
     */
    private static function cloudflareIce(): array
    {
        $id = config('services.cloudflare_turn.key_id');
        $token = config('services.cloudflare_turn.api_token');
        if (!$id || !$token) return [];

        $cached = Cache::get('cf_turn_ice');
        if (is_array($cached) && $cached) return $cached;
        if (Cache::get('cf_turn_down')) return [];   // failed a moment ago: do not make every request wait for the 3 s timeout again

        try {
            $r = Http::withToken($token)->acceptJson()->timeout(3)
                ->post("https://rtc.live.cloudflare.com/v1/turn/keys/{$id}/credentials/generate", ['ttl' => 86400]);
            if (!$r->successful()) {
                Log::warning('Cloudflare TURN credentials failed', ['status' => $r->status()]);
                Cache::put('cf_turn_down', 1, 60);
                return [];
            }

            $ice = $r->json('iceServers');
            if (!is_array($ice) || !$ice) { Cache::put('cf_turn_down', 1, 60); return []; }
            if (isset($ice['urls'])) $ice = [$ice];   // the API returns one object; WebRTC wants a list

            // find the entry that carries the username / credential
            $username = null; $credential = null;
            foreach ($ice as $entry) {
                if (!empty($entry['username']) && !empty($entry['credential'])) {
                    $username = $entry['username'];
                    $credential = $entry['credential'];
                    break;
                }
            }
            if (!$username || !$credential) {
                Log::warning('Cloudflare TURN response had no username/credential');
                Cache::put('cf_turn_down', 1, 60);
                return [];
            }

            $servers = [
                ['urls' => ['stun:stun.cloudflare.com:3478']],
                [
                    'urls' => ['turn:turn.cloudflare.com:3478?transport=udp', 'turns:turn.cloudflare.com:443?transport=tcp'],
                    'username' => $username,
                    'credential' => $credential,
                ],
            ];

            Cache::put('cf_turn_ice', $servers, 64800);   // credentials live 24 h; 18 h in the cache always leaves 6 h of validity
            return $servers;
        } catch (\Throwable $e) {
            Log::warning('Cloudflare TURN credentials unavailable', ['error' => $e->getMessage()]);
            Cache::put('cf_turn_down', 1, 60);
            return [];
        }
    }

    public static function turnPassword(): string
    {
        $raw = SystemSetting::get('friends_turn_pass');
        if ($raw === '') return '';
        try { return Crypt::decryptString($raw); } catch (\Throwable $e) { return ''; }
    }

    public static function saveTurnPassword(string $plain): void
    {
        SystemSetting::set('friends_turn_pass', $plain === '' ? '' : Crypt::encryptString($plain), 'friends');
    }
}