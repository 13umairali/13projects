<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="dahi-realtime" content="<?php echo e(json_encode([
        'user' => auth()->id(), 'workspace' => auth()->user()->active_workspace_id,
        'key' => config('broadcasting.default') === 'pusher' ? config('broadcasting.connections.pusher.key') : null,
        'cluster' => config('broadcasting.connections.pusher.options.cluster', 'mt1'),
        'host' => config('services.realtime.ws_host'), 'port' => config('services.realtime.ws_port', 443),
        'tls' => config('services.realtime.ws_scheme', 'https') === 'https', 'auth' => url('/broadcasting/auth'),
    ])); ?>">
    <?php
        // Polling stays available when the optional realtime bundle is missing: the call screen must always load.
        $realtimeAssets = '';
        try { $realtimeAssets = app(\Illuminate\Foundation\Vite::class)('resources/js/realtime.js'); }
        catch (\Illuminate\Foundation\ViteException $error) { \Illuminate\Support\Facades\Log::warning('Call realtime assets unavailable; polling stays on.', ['exception' => get_class($error)]); }
    ?>
    <?php echo $realtimeAssets; ?>

    <title><?php echo e($audioOnly ? __('Audio call') : __('Video call')); ?> · <?php echo e($peer['name']); ?></title>
    <meta name="theme-color" content="#0b141a">
    <link rel="stylesheet" href="<?php echo e(asset('css/call-room.css')); ?>?v=20261021">
</head>
<body>
<div id="cl" class="<?php echo e($audioOnly ? 'audio' : ''); ?>"
     data-code="<?php echo e($code); ?>" data-call-id="<?php echo e($callId); ?>" data-audio="<?php echo e($audioOnly ? 1 : 0); ?>" data-role="<?php echo e($role); ?>" data-return="<?php echo e($return); ?>"
     data-me="<?php echo e(json_encode($me)); ?>" data-peer="<?php echo e(json_encode($peer)); ?>">

    <div id="cl-bd" aria-hidden="true"></div>
    <div id="cl-scrim" aria-hidden="true"></div>
    <div id="cl-stage"></div>

    <header id="cl-top">
        <span class="kind">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($audioOnly): ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.7a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/></svg><?php echo e(__('Audio call')); ?>

            <?php else: ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg><?php echo e(__('Video call')); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </span>
        <div id="cl-head"><div class="n" id="cl-head-n"></div><div class="s" id="cl-head-s" aria-hidden="true"></div></div>
    </header>

    <div id="cl-center">
        <div id="cl-name"></div>
        <div id="cl-status" role="status" aria-live="polite"><?php echo e(__('Connecting…')); ?></div>
        <div id="cl-av-wrap"><span class="rp" aria-hidden="true"></span><span class="rp" aria-hidden="true"></span><span class="rp" aria-hidden="true"></span><div id="cl-av" aria-hidden="true"></div></div>
    </div>

    <div id="cl-self" class="hidden" title="<?php echo e(__('Drag to move')); ?>"><video id="cl-self-v" autoplay muted playsinline></video></div>

    <div id="cl-note" class="hidden" role="status"><?php echo e(__('Tap anywhere to turn the sound on')); ?></div>

    <div id="cl-bar" role="toolbar" aria-label="<?php echo e(__('Call controls')); ?>">
        <div class="ctl"><button id="cl-mic" class="cb" type="button" aria-label="<?php echo e(__('Mute')); ?>" aria-pressed="false" title="<?php echo e(__('Mute')); ?> (M)"></button><span id="cl-mic-l"><?php echo e(__('Mute')); ?></span></div>
        <div class="ctl <?php echo e($audioOnly ? 'hidden' : ''); ?>" id="cl-cam-wrap"><button id="cl-cam" class="cb" type="button" aria-label="<?php echo e(__('Camera')); ?>" aria-pressed="false" title="<?php echo e(__('Camera')); ?> (V)"></button><span id="cl-cam-l"><?php echo e(__('Camera')); ?></span></div>
        <div class="ctl"><button id="cl-end" class="cb end" type="button" aria-label="<?php echo e(__('End call')); ?>" title="<?php echo e(__('End call')); ?>"></button><span><?php echo e(__('End')); ?></span></div>
    </div>

    <div id="cl-end-msg" role="alertdialog" aria-labelledby="cl-end-title">
        <div class="end-card">
            <div class="end-ico" aria-hidden="true"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.7 13.3a16 16 0 0 0 2.6 2.1l1.7-1.7a2 2 0 0 1 2.1-.4c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.4 2.1L8 10"/><path d="M23 1L1 23"/></svg></div>
            <h1 id="cl-end-title"></h1><p id="cl-end-sub"></p>
            <button id="cl-back" type="button"><?php echo e(__('Back')); ?></button>
        </div>
    </div>
</div>
<script src="<?php echo e(asset('js/call-tones.js')); ?>?v=20261021-sounds"></script>
<script src="<?php echo e(asset('js/call-room.js')); ?>?v=20261021-saas"></script>
</body>
</html><?php /**PATH /home/dahimail.com/public_html/resources/views/calls/room.blade.php ENDPATH**/ ?>