<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b141a">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
    <meta name="dahi-realtime" content="<?php echo e(json_encode([
        'user' => auth()->id(), 'workspace' => auth()->user()->active_workspace_id,
        'key' => config('broadcasting.default') === 'pusher' ? config('broadcasting.connections.pusher.key') : null,
        'cluster' => config('broadcasting.connections.pusher.options.cluster', 'mt1'),
        'host' => config('services.realtime.ws_host'), 'port' => config('services.realtime.ws_port', 443),
        'tls' => config('services.realtime.ws_scheme', 'https') === 'https', 'auth' => url('/broadcasting/auth'),
    ])); ?>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php
        // Polling in meeting.js remains available during an incomplete asset deployment.
        // A missing optional websocket bundle must not make the call room return HTTP 500.
        $realtimeAssets = '';
        try {
            $realtimeAssets = app(\Illuminate\Foundation\Vite::class)('resources/js/realtime.js');
        } catch (\Illuminate\Foundation\ViteException $error) {
            \Illuminate\Support\Facades\Log::warning('Meeting realtime assets unavailable; rebuild website assets with npm ci && npm run build.', ['exception' => get_class($error)]);
        }
        $isCall = $meeting['kind'] === 'call';
        $kindLabel = $isCall ? (str_starts_with((string) $meeting['title'], 'Audio') ? __('Audio call') : __('Video call')) : __('Meeting');
    ?>
    <?php echo $realtimeAssets; ?>

    <title><?php echo e($meeting['title']); ?> · <?php echo e(config('app.name')); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('css/meeting-room.css')); ?>?v=20261021">
</head>
<body>
<div id="mt"
     data-code="<?php echo e($meeting['code']); ?>" data-pretty="<?php echo e($meeting['pretty']); ?>" data-title="<?php echo e($meeting['title']); ?>" data-url="<?php echo e($meeting['url']); ?>" data-kind="<?php echo e($meeting['kind']); ?>"
     data-user="<?php echo e(json_encode($user)); ?>" data-ended="<?php echo e($ended ? 1 : 0); ?>" data-guests="<?php echo e($allowGuests ? 1 : 0); ?>" data-signin="<?php echo e(url('/login')); ?>">

    
    <section id="mt-pre" class="mt-screen" aria-label="<?php echo e(__('Get ready to join')); ?>">
        <div class="card">
            <div>
                <div class="pv">
                    <video id="mt-preview" autoplay muted playsinline></video>
                    <div id="mt-prev-off" class="off">
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 1l22 22"/><path d="M16 16v1a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h2m5.7 0H14a2 2 0 0 1 2 2v3.3l7-5v10"/></svg>
                        <?php echo e(__('Camera is off')); ?>

                    </div>
                    <div class="tg"><button id="mt-pre-mic" class="rb" type="button" aria-label="<?php echo e(__('Microphone')); ?>"></button><button id="mt-pre-cam" class="rb" type="button" aria-label="<?php echo e(__('Camera')); ?>"></button></div>
                </div>
                <div id="mt-devs" class="devs hidden">
                    <label class="dev" id="mt-mic-wrap"><span><?php echo e(__('Microphone')); ?></span><select id="mt-sel-mic"></select></label>
                    <label class="dev" id="mt-cam-wrap"><span><?php echo e(__('Camera')); ?></span><select id="mt-sel-cam"></select></label>
                    <label class="dev" id="mt-spk-wrap"><span><?php echo e(__('Speaker')); ?></span><select id="mt-sel-spk"></select></label>
                </div>
            </div>
            <div class="side-col">
                <span class="kicker"><?php echo e($kindLabel); ?></span>
                <h1 id="mt-pre-title"><?php echo e($meeting['title']); ?></h1>
                <div class="chips">
                    <span class="chip" id="mt-pre-code-chip"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/></svg><span id="mt-pre-code"></span></span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($meeting['host_name']): ?><span class="chip"><?php echo e(__('Host: :n', ['n' => $meeting['host_name']])); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div id="mt-name-wrap"><label class="sub" for="mt-name"><?php echo e(__('Your name')); ?></label><input id="mt-name" type="text" maxlength="60" placeholder="<?php echo e(__('Type your name')); ?>" autocomplete="name"></div>
                <div id="mt-as" class="sub"></div>
                <button id="mt-join" class="primary" type="button"><?php echo e(__('Join now')); ?></button>
                <div id="mt-signin" class="status-line hidden"><a href="<?php echo e(url('/login')); ?>"><?php echo e(__('Sign in')); ?></a> <?php echo e(__('to join with your account.')); ?></div>
                <div id="mt-pre-msg" role="status" aria-live="polite"></div>
                <button id="mt-pre-end" class="ghost" type="button"><?php echo e(__('Cancel / go back')); ?></button>
            </div>
        </div>
    </section>

    
    <section id="mt-wait" class="mt-screen hidden"><div class="center"><div class="spin" aria-hidden="true"></div><h1 id="mt-wait-title"></h1><div class="sub" role="status"><?php echo e(__('Waiting for the host to let you in…')); ?></div><button id="mt-wait-end" class="ghost" type="button"><?php echo e(__('Leave meeting')); ?></button></div></section>

    
    <section id="mt-end" class="mt-screen hidden"><div class="center"><div class="state-ico" aria-hidden="true"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.7 13.3a16 16 0 0 0 2.6 2.1l1.7-1.7a2 2 0 0 1 2.1-.4c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.4 2.1L8 10"/><path d="M23 1L1 23"/></svg></div><h1 id="mt-end-msg" role="status"></h1><button id="mt-rejoin" class="primary hidden" type="button"><?php echo e(__('Rejoin')); ?></button><a class="ghost" href="<?php echo e($user ? url('/meetings') : url('/')); ?>"><?php echo e(__('Back')); ?></a></div></section>

    
    <section id="mt-room" class="hidden">
        <header id="mt-top">
            <button id="mt-b-minimize" class="icon-btn" type="button" title="<?php echo e(__('Minimize')); ?>" aria-label="<?php echo e(__('Minimize')); ?>"></button>
            <div class="t" id="mt-title"></div>
            <span id="mt-lock" class="hidden" title="<?php echo e(__('Locked')); ?>" aria-label="<?php echo e(__('Locked')); ?>"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg></span>
            <button id="mt-enable-audio" class="lnk hidden" type="button"><?php echo e(__('Enable call audio')); ?></button>
            <div class="i">
                <span id="mt-count"></span><span id="mt-left"></span><span id="mt-clock" title="<?php echo e(__('Time in meeting')); ?>">0:00</span>
                <button id="mt-add" class="lnk hidden" type="button" title="<?php echo e(__('Add people')); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg><span><?php echo e(__('Add people')); ?></span></button>
                <button id="mt-share" class="lnk" type="button" title="<?php echo e(__('Share')); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7M16 6l-4-4-4 4M12 2v13"/></svg><span><?php echo e(__('Share')); ?></span></button>
                <button id="mt-copy" class="lnk" type="button" title="<?php echo e(__('Copy link')); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg><span><?php echo e(__('Copy link')); ?></span></button>
            </div>
        </header>
        <div id="mt-main">
            <div id="mt-stage" class="grid" aria-label="<?php echo e(__('Participants')); ?>"></div>
            <div id="mt-alone"><div id="mt-alone-text"></div><code id="mt-alone-link"></code></div>
            <aside id="mt-side" class="hidden" aria-label="<?php echo e(__('Meeting panel')); ?>">
                <div class="tabs"><button id="mt-tab-people" class="on" type="button"><?php echo e(__('People')); ?></button><button id="mt-tab-chat" type="button"><?php echo e(__('Chat')); ?></button><button id="mt-side-x" class="x" type="button" aria-label="<?php echo e(__('Close')); ?>">&times;</button></div>
                <div id="mt-peoplepane"><div id="mt-people"></div></div>
                <div id="mt-chatpane" class="hidden"><div id="mt-msgs" aria-live="polite"></div><form id="mt-chatform"><input type="text" id="mt-chatin" maxlength="1000" placeholder="<?php echo e(__('Message everyone…')); ?>" autocomplete="off" aria-label="<?php echo e(__('Message')); ?>"><button type="submit"><?php echo e(__('Send')); ?></button></form></div>
            </aside>
        </div>
        <footer id="mt-ctl" role="toolbar" aria-label="<?php echo e(__('Meeting controls')); ?>">
            <button id="mt-b-mic" type="button"></button><button id="mt-b-cam" type="button"></button><span class="sep" aria-hidden="true"></span><button id="mt-b-share" type="button"></button><button id="mt-b-hand" type="button"></button><span class="sep" aria-hidden="true"></span>
            <span class="bw"><button id="mt-b-chat" type="button"></button><span id="mt-chat-dot" class="dot hidden"></span></span>
            <span class="bw"><button id="mt-b-people" type="button"></button><span id="mt-people-dot" class="dot hidden"></span></span>
            <button id="mt-b-more" type="button" class="hidden"></button><span class="sep" aria-hidden="true"></span><button id="mt-b-leave" type="button"></button>
        </footer>
    </section>
    <div id="mt-banner" class="hidden" role="status"><span class="spin" aria-hidden="true"></span><span><?php echo e(__('Connection lost. Reconnecting…')); ?></span></div>
    <div id="mt-toasts" role="status" aria-live="polite"></div>
    <div id="mt-return-bar" class="hidden"><span id="mt-return-label"></span><button id="mt-return" type="button"><?php echo e(__('Return to call')); ?></button><button id="mt-return-end" type="button"><?php echo e(__('End / leave')); ?></button></div>
</div>
<script src="<?php echo e(asset('js/call-recorder.js')); ?>?v=20261007"></script>
<script src="<?php echo e(asset('js/call-tones.js')); ?>?v=20261021-sounds"></script>
<script src="<?php echo e(asset('js/meeting.js')); ?>?v=20261021-rules"></script>
</body>
</html>
<?php /**PATH /home/dahimail.com/public_html/resources/views/meetings/room.blade.php ENDPATH**/ ?>