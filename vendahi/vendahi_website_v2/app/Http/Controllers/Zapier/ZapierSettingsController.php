<?php

namespace App\Http\Controllers\Zapier;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Models\ZapierSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Admin side of the Zapier integration (Settings-style page): enable/disable,
 * API key lifecycle, acting user, and a view over Zapier's subscriptions and
 * recent deliveries. Gated by the `zapier_settings` permission.
 */
class ZapierSettingsController extends Controller
{
    public function show(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', ZapierSetting::class);

        $settings = ZapierSetting::current();

        $hooks = Webhook::query()
            ->where('source', 'zapier')
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->get(['id', 'name', 'url', 'events', 'is_active', 'last_fired_at', 'created_at']);

        $deliveries = WebhookDelivery::query()
            ->whereIn('webhook_id', $hooks->pluck('id'))
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'webhook_id', 'event', 'status', 'attempt', 'response_code', 'created_at']);

        return response()->json([
            'enabled' => (bool) $settings->enabled,
            'has_key' => ! empty($settings->api_key_hash),
            'api_key_last4' => $settings->api_key_last4,
            'key_generated_at' => $settings->key_generated_at,
            'last_used_at' => $settings->last_used_at,
            'user_id' => $settings->user_id,
            'users' => User::whereNull('deleted_at')->where('statut', 1)
                ->get(['id', 'username', 'email']),
            'hooks' => $hooks,
            'deliveries' => $deliveries,
            'trigger_events' => ZapierController::TRIGGER_EVENTS,
        ]);
    }

    public function update(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'update', ZapierSetting::class);

        $data = $request->validate([
            'enabled' => 'required|boolean',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $settings = ZapierSetting::current();
        $settings->fill([
            'enabled' => (bool) $data['enabled'],
            'user_id' => $data['user_id'] ?? $settings->user_id,
        ])->save();

        return response()->json(['success' => true]);
    }

    /** Generate (or rotate) the API key. The plain key is returned ONCE. */
    public function generateKey(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'update', ZapierSetting::class);

        $plain = 'zap_'.Str::random(40);

        $settings = ZapierSetting::current();
        $settings->fill([
            'api_key_hash' => hash('sha256', $plain),
            'api_key_last4' => substr($plain, -4),
            'key_generated_at' => now(),
        ])->save();

        return response()->json(['api_key' => $plain]);
    }

    /** Revoke the key and drop every Zapier subscription. */
    public function revokeKey(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'update', ZapierSetting::class);

        $settings = ZapierSetting::current();
        $settings->fill([
            'api_key_hash' => null,
            'api_key_last4' => null,
            'enabled' => false,
        ])->save();

        Webhook::query()->where('source', 'zapier')->delete();

        return response()->json(['success' => true]);
    }
}
