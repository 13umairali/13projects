<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="dahi-realtime" content="{{ json_encode(['user' => auth()->id(), 'workspace' => auth()->user()->active_workspace_id] + \App\Support\ClientRealtime::client()) }}">
    @php
        // Polling stays available when the optional realtime bundle is missing: the call screen must always load.
        $realtimeAssets = '';
        try { $realtimeAssets = app(\Illuminate\Foundation\Vite::class)('resources/js/realtime.js'); }
        catch (\Illuminate\Foundation\ViteException $error) { \Illuminate\Support\Facades\Log::warning('Call realtime assets unavailable; polling stays on.', ['exception' => get_class($error)]); }
    @endphp
    {!! $realtimeAssets !!}
    <title>{{ $audioOnly ? __('Audio call') : __('Video call') }} · {{ $peer['name'] }}</title>
    <meta name="theme-color" content="#0b141a">
    <link rel="stylesheet" href="{{ asset('css/call-room.css') }}?v=20261021">
</head>
<body>
<div id="cl" class="{{ $audioOnly ? 'audio' : '' }}"
     data-code="{{ $code }}" data-call-id="{{ $callId }}" data-audio="{{ $audioOnly ? 1 : 0 }}" data-role="{{ $role }}" data-return="{{ $return }}"
     data-me="{{ json_encode($me) }}" data-peer="{{ json_encode($peer) }}">

    <div id="cl-bd" aria-hidden="true"></div>
    <div id="cl-scrim" aria-hidden="true"></div>
    <div id="cl-stage"></div>

    <header id="cl-top">
        <span class="kind">
            @if($audioOnly)
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.7a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/></svg>{{ __('Audio call') }}
            @else
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>{{ __('Video call') }}
            @endif
        </span>
        <div id="cl-head"><div class="n" id="cl-head-n"></div><div class="s" id="cl-head-s" aria-hidden="true"></div></div>
    </header>

    <div id="cl-center">
        <div id="cl-name"></div>
        <div id="cl-status" role="status" aria-live="polite">{{ __('Connecting…') }}</div>
        <div id="cl-av-wrap"><span class="rp" aria-hidden="true"></span><span class="rp" aria-hidden="true"></span><span class="rp" aria-hidden="true"></span><div id="cl-av" aria-hidden="true"></div></div>
    </div>

    <div id="cl-self" class="hidden" title="{{ __('Drag to move') }}"><video id="cl-self-v" autoplay muted playsinline></video></div>

    <div id="cl-note" class="hidden" role="status">{{ __('Tap anywhere to turn the sound on') }}</div>

    <div id="cl-bar" role="toolbar" aria-label="{{ __('Call controls') }}">
        <div class="ctl"><button id="cl-mic" class="cb" type="button" aria-label="{{ __('Mute') }}" aria-pressed="false" title="{{ __('Mute') }} (M)"></button><span id="cl-mic-l">{{ __('Mute') }}</span></div>
        <div class="ctl {{ $audioOnly ? 'hidden' : '' }}" id="cl-cam-wrap"><button id="cl-cam" class="cb" type="button" aria-label="{{ __('Camera') }}" aria-pressed="false" title="{{ __('Camera') }} (V)"></button><span id="cl-cam-l">{{ __('Camera') }}</span></div>
        <div class="ctl"><button id="cl-end" class="cb end" type="button" aria-label="{{ __('End call') }}" title="{{ __('End call') }}"></button><span>{{ __('End') }}</span></div>
    </div>

    <div id="cl-end-msg" role="alertdialog" aria-labelledby="cl-end-title">
        <div class="end-card">
            <div class="end-ico" aria-hidden="true"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.7 13.3a16 16 0 0 0 2.6 2.1l1.7-1.7a2 2 0 0 1 2.1-.4c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.4 2.1L8 10"/><path d="M23 1L1 23"/></svg></div>
            <h1 id="cl-end-title"></h1><p id="cl-end-sub"></p>
            <button id="cl-back" type="button">{{ __('Back') }}</button>
        </div>
    </div>
</div>
<script src="{{ asset('js/call-tones.js') }}?v=20261021-sounds"></script>
<script src="{{ asset('js/call-room.js') }}?v=20261021-saas"></script>
</body>
</html>