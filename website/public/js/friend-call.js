/* Friend audio calls (WebRTC). Loaded on every page of the signed-in website: it listens for incoming calls (poll every 4 s)
   and can start a call to a friend:  FriendCall.start(userId, name).  The same endpoints are used by the mobile app,
   so the website and the app can call each other. No external libraries. */
(function () {
  if (window.FriendCall) return;
  var tones = window.DahiCallTones ? new window.DahiCallTones() : null;
  var roomHost = null;
  try { if (window.parent !== window && window.parent.DahiRoom) roomHost = window.parent.DahiRoom; } catch (e) {}
  if (roomHost && roomHost.active()) {
    document.addEventListener('click', function (event) {
      var link = event.target.closest && event.target.closest('a[href]');
      if (link && /^\/meet\//.test(new URL(link.href, location.origin).pathname)) {
        event.preventDefault(); roomHost.restore();
      }
    }, true);
    window.FriendCall = { start: roomHost.restore, startVideo: roomHost.restore, startGroupAudio: roomHost.restore };
    return;
  }
  var BASE = '/friends/api';
  var S = { call: null, incoming: null, pc: null, stream: null, last: 0, sig: null, clock: null, secs: 0, queue: [], remoteSet: false,
            ring: null, muted: false, pulling: false, polling: false, starting: false, muteBtn: null, msgSeen: null, title: null };
  var readRevision = 0, callRevision = 0, pollAgain = false, accepting = false;
  var endedCalls = new Set();
  function rememberEnd(id) { if (!id) return; endedCalls.add(Number(id)); if (endedCalls.size > 256) endedCalls.delete(endedCalls.values().next().value); }
  var callerAvatar = null, box = null, nameEl = null, statusEl = null, btnRow = null, audio = null, enableAudio = null, actx = null, hideTimer = null;

  function csrf() { var m = document.querySelector('meta[name="csrf-token"]'); return m ? m.content : ''; }
  function api(method, path, body) {
    return fetch(BASE + path, {
      method: method, credentials: 'same-origin', cache: 'no-store', keepalive: method === 'POST' && /\/(end|decline)$/.test(path),
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
      body: body ? JSON.stringify(body) : undefined
    }).then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { j._status = r.status; return j; }); });
  }

  function terminate(path, attempt) {
    return new Promise(function (resolve, reject) {
      var timer = setTimeout(function () { reject(new Error('Hang-up acknowledgement timed out.')); }, 5000);
      api('POST', path).then(function (r) { clearTimeout(timer); resolve(r); }, function (e) { clearTimeout(timer); reject(e); });
    }).then(function (r) {
      if (r._status >= 400) { var e = new Error(r.message || 'Could not confirm hang-up.'); e.status = r._status; throw e; }
    }).catch(function (e) {
      if ((attempt || 0) >= 3 || (e.status && e.status < 500 && e.status !== 429)) throw e;
      return new Promise(function (resolve) { setTimeout(resolve, 300 * Math.pow(2, attempt || 0)); })
        .then(function () { return terminate(path, (attempt || 0) + 1); });
    });
  }
  function deliverEnd(path) { terminate(path).catch(function () { toast('Call disconnected locally', 'Could not confirm with the server. Check your connection.'); }); }

  // ───────── interface ─────────
  // Same look as the phone app's incoming-call screen (flutter_app/lib/core/incoming_call_view.dart) and the SaaS
  // call/meeting pages: dark navy gradient + blurred photo, name and "Incoming … Call…" on top, big photo with ripples,
  // round Decline (left, red) and Accept (right, green) buttons. While a call runs it is a compact card.
  var CSS = '' +
    '.dcl{position:fixed;z-index:2147483000;color:#fff;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;-webkit-font-smoothing:antialiased;display:none}' +
    '.dcl *{box-sizing:border-box}' +
    '.dcl-full{inset:0;background:linear-gradient(180deg,#0b141a,#13222b);overflow:hidden}' +
    '.dcl-bd{position:absolute;inset:-40px;background:center/cover no-repeat;filter:blur(28px);opacity:.32}' +
    '.dcl-scrim{position:absolute;inset:0;background:rgba(0,0,0,.35)}' +
    '.dcl-body{position:relative;height:100%;display:flex;flex-direction:column;align-items:center;text-align:center;padding:calc(env(safe-area-inset-top,0px) + 2.5rem) 1.5rem calc(env(safe-area-inset-bottom,0px) + 2rem)}' +
    '.dcl-name{font-size:1.75rem;font-weight:500;letter-spacing:-.01em;max-width:min(90vw,36rem);overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow-wrap:anywhere}' +
    '.dcl-status{font-size:1rem;color:rgba(255,255,255,.7);margin-top:.5rem}' +
    '.dcl-avwrap{position:relative;flex:1;width:100%;display:flex;align-items:center;justify-content:center;min-height:0}' +
    '.dcl-av{position:relative;width:10rem;height:10rem;border-radius:50%;background:#252532 center/cover;display:flex;align-items:center;justify-content:center;font-size:3.5rem;font-weight:600;box-shadow:0 18px 50px rgba(0,0,0,.45)}' +
    '.dcl-rp{position:absolute;left:50%;top:50%;width:10rem;height:10rem;margin:-5rem 0 0 -5rem;border-radius:50%;background:rgba(255,255,255,.1);opacity:0;animation:dcl-ripple 2.4s linear 30}' +
    '.dcl-rp:nth-child(2){animation-delay:.8s}.dcl-rp:nth-child(3){animation-delay:1.6s}' +
    '@keyframes dcl-ripple{0%{transform:scale(1);opacity:.1}100%{transform:scale(1.9);opacity:0}}' +
    '@media (prefers-reduced-motion:reduce){.dcl-rp{animation:none}}' +
    '.dcl-actions{display:flex;justify-content:space-evenly;width:min(24rem,100%);direction:ltr}' +
    '.dcl-card .dcl-actions{width:100%;justify-content:flex-end;gap:.6rem}' +
    '.dcl-col{display:flex;flex-direction:column;align-items:center;gap:.5rem;font-size:.875rem;color:rgba(255,255,255,.7)}' +
    '.dcl-b{width:4.4rem;height:4.4rem;border:0;border-radius:50%;color:#fff;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:transform .1s,filter .15s}' +
    '.dcl-b:hover{filter:brightness(1.08)}.dcl-b:active{transform:scale(.94)}.dcl-b:disabled{opacity:.5;cursor:default}' +
    '.dcl-b svg{width:30px;height:30px}' +
    '.dcl-b:focus-visible,.dcl-pill:focus-visible{outline:2px solid #60a5fa;outline-offset:3px}' +
    '.dcl-b.accept{background:#25d366}.dcl-b.decline{background:#f15c6d}.dcl-b.neutral{background:#2e3c45}' +
    '.dcl-card{right:1.25rem;bottom:1.25rem;width:20rem;max-width:calc(100vw - 2.5rem);background:rgba(17,28,36,.96);border:1px solid rgba(255,255,255,.1);border-radius:1.25rem;box-shadow:0 14px 40px rgba(0,0,0,.45);padding:1rem;-webkit-backdrop-filter:blur(14px);backdrop-filter:blur(14px)}' +
    '.dcl-card .dcl-body{height:auto;padding:0;align-items:stretch;text-align:left}' +
    '.dcl-card .dcl-bd,.dcl-card .dcl-scrim,.dcl-card .dcl-avwrap{display:none}' +
    '.dcl-card .dcl-name{font-size:1rem;font-weight:600}.dcl-card .dcl-status{font-size:.85rem;margin-top:.15rem;font-variant-numeric:tabular-nums}' +
    '.dcl-card .dcl-actions{margin-top:.85rem}' +
    '.dcl-t{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}' +
    '.dcl-card .dcl-b{width:auto;height:2.5rem;border-radius:999px;padding:0 1.1rem;font-weight:600;font-size:.875rem;flex:1}.dcl-card .dcl-b svg{display:none}' +
    '.dcl-card .dcl-t{position:static;width:auto;height:auto;overflow:visible;clip:auto}' +
    '.dcl-card .dcl-col{flex:1;gap:0}.dcl-card .dcl-col>span{display:none}' +
    '.dcl-pill{border:0;border-radius:999px;padding:.55rem 1rem;color:#fff;background:#2563eb;font:inherit;font-weight:600;cursor:pointer;margin-top:.6rem}' +
    '.dcl-toast{position:fixed;right:1.25rem;top:1.25rem;width:19rem;max-width:calc(100vw - 2.5rem);z-index:2147483000;background:rgba(17,28,36,.96);color:#fff;border:1px solid rgba(255,255,255,.1);border-radius:1rem;box-shadow:0 14px 40px rgba(0,0,0,.45);padding:.8rem 1rem;cursor:pointer;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}' +
    '.dcl-toast b{display:block;font-size:.95rem}.dcl-toast span{display:block;color:rgba(255,255,255,.7);font-size:.85rem;margin-top:.15rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}' +
    '.dcl-rooms{position:fixed;bottom:1.25rem;right:1.25rem;max-width:calc(100vw - 2.5rem);z-index:60;display:grid;gap:.4rem;padding:.6rem;border-radius:1rem;background:rgba(17,28,36,.96);border:1px solid rgba(255,255,255,.1);box-shadow:0 14px 40px rgba(0,0,0,.45);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}' +
    '.dcl-rooms a{display:block;padding:.55rem .9rem;border-radius:.75rem;background:#2563eb;color:#fff;text-decoration:none;font-weight:600;font-size:.9rem}.dcl-rooms a:hover{background:#1d4ed8}';
  var ICONS = {
    accept: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15.05 15.05 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25c1.1.37 2.3.57 3.6.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.6 21 3 13.4 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02z"/></svg>',
    video: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17 10.5V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3.5l4 4v-11z"/></svg>',
    decline: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 9c-1.6 0-3.15.25-4.6.72v3.1c0 .39-.23.74-.56.9-.98.49-1.87 1.12-2.66 1.85-.18.18-.43.28-.7.28-.28 0-.53-.11-.71-.29L.29 13.08A.96.96 0 0 1 0 12.37c0-.28.11-.53.29-.71C3.34 8.77 7.46 7 12 7s8.66 1.77 11.71 4.66c.18.18.29.43.29.71 0 .28-.11.53-.29.71l-2.48 2.48c-.18.18-.43.29-.71.29-.27 0-.52-.1-.7-.28a11.3 11.3 0 0 0-2.66-1.85.99.99 0 0 1-.56-.9v-3.1C15.15 9.25 13.6 9 12 9z"/></svg>'
  };
  function initials(name) { return String(name || '').trim().split(/\s+/).slice(0, 2).map(function (part) { return part.charAt(0).toUpperCase(); }).join(''); }
  function el(tag, cls) { var e = document.createElement(tag); if (cls) e.className = cls; return e; }
  function injectStyles() {
    if (injectStyles.done) return; injectStyles.done = true;
    var st = el('style'); st.textContent = CSS; (document.head || document.body).appendChild(st);
  }
  var bdEl = null, avEl = null, rippleEls = [];
  function build() {
    if (box) return;
    injectStyles();
    box = el('div', 'dcl dcl-card');
    bdEl = el('div', 'dcl-bd'); var scrim = el('div', 'dcl-scrim'), body = el('div', 'dcl-body');
    nameEl = el('div', 'dcl-name'); statusEl = el('div', 'dcl-status'); statusEl.setAttribute('role', 'status'); statusEl.setAttribute('aria-live', 'polite');
    var wrap = el('div', 'dcl-avwrap'); for (var i = 0; i < 3; i++) wrap.appendChild(el('span', 'dcl-rp'));
    callerAvatar = avEl = el('div', 'dcl-av'); avEl.setAttribute('aria-hidden', 'true'); wrap.appendChild(avEl);
    btnRow = el('div', 'dcl-actions');
    body.appendChild(nameEl); body.appendChild(statusEl); body.appendChild(wrap); body.appendChild(btnRow);
    box.appendChild(bdEl); box.appendChild(scrim); box.appendChild(body);
    document.body.appendChild(box);
    box.addEventListener('keydown', function (event) {
      if (!S.incoming || event.key !== 'Tab') return;
      var buttons = btnRow.querySelectorAll('button');
      if (!buttons.length) return;
      var first = buttons[0], last = buttons[buttons.length - 1];
      if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
      else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });
    enableAudio = el('button', 'dcl-pill'); enableAudio.type = 'button'; enableAudio.textContent = 'Enable call audio'; enableAudio.style.display = 'none';
    enableAudio.onclick = playCallAudio; body.appendChild(enableAudio);
    audio = document.createElement('audio'); audio.autoplay = true; audio.setAttribute('playsinline', ''); document.body.appendChild(audio);
  }
  /** buttons: [label, kind ('accept' | 'decline' | 'neutral'), handler, disabled?] */
  function button(label, kind, fn, disabled) {
    var col = el('div', 'dcl-col'), b = el('button', 'dcl-b ' + kind), cap = el('span');
    b.type = 'button'; b.onclick = fn; b.disabled = !!disabled; b.setAttribute('aria-label', label);
    var video = S.incoming && S.incoming.video && !S.incoming.audio_only && !S.incoming.group;
    b.innerHTML = ICONS[kind === 'accept' ? (video ? 'video' : 'accept') : kind === 'decline' ? 'decline' : ''] || '';
    var t = el('span', 'dcl-t'); t.textContent = label; b.appendChild(t);   // icon in the full screen, text in the compact card
    cap.textContent = label; col.appendChild(b); col.appendChild(cap);
    return { col: col, b: b };
  }
  function show(name, status, buttons, avatarUrl) {
    build(); clearTimeout(hideTimer);
    var peer = ((S.incoming || S.call) || {}).peer || {}, photo = avatarUrl || peer.avatar_url || null;
    nameEl.textContent = name; statusEl.textContent = status; btnRow.innerHTML = ''; S.muteBtn = null;
    var first = null;
    (buttons || []).forEach(function (spec) {
      var made = button(spec[0], spec[1], spec[2], spec[3]);
      if (spec[0] === 'Mute' || spec[0] === 'Unmute') S.muteBtn = made.b;
      if (!first && !spec[3]) first = made.b;
      btnRow.appendChild(made.col);
    });
    var incoming = !!S.incoming;
    box.className = 'dcl ' + (incoming ? 'dcl-full' : 'dcl-card');
    avEl.style.backgroundImage = photo ? 'url("' + String(photo).replace(/"/g, '%22') + '")' : ''; avEl.textContent = photo ? '' : initials(name);
    bdEl.style.backgroundImage = photo ? 'url("' + String(photo).replace(/"/g, '%22') + '")' : '';
    box.style.display = 'block';
    box.setAttribute('aria-modal', incoming ? 'true' : 'false');
    box.setAttribute('role', incoming ? 'dialog' : 'status'); box.setAttribute('aria-label', status);
    if (incoming && first && first.focus) first.focus();
  }
  function setStatus(t) { if (statusEl) statusEl.textContent = t; }
  function hide() { if (box) box.style.display = 'none'; }

  // ───────── alerts: message pop-up + sound, desktop notification, tab title ─────────
  function micMessage(e) {
    var n = e && e.name; try { console.error('Call error:', e); } catch (x) {}
    if (n === 'NotAllowedError' || n === 'SecurityError') return 'The microphone is blocked. Click the lock icon in the address bar, allow the microphone for this site, and try again.';
    if (n === 'NotFoundError' || n === 'OverconstrainedError') return 'No microphone was found on this device.';
    if (n === 'NotReadableError') return 'The microphone is being used by another program.';
    return 'Could not start the call (' + (n || 'error') + ').';
  }
  function desktopNotify(title, body, onclick) {
    try {
      if (!('Notification' in window) || Notification.permission !== 'granted' || !document.hidden) return;
      var n = new Notification(title, { body: body, tag: 'dahimail-live' });
      n.onclick = function () { window.focus(); if (onclick) onclick(); n.close(); };
    } catch (e) {}
  }
  // The permission question is NOT asked here any more (it used to pop up on the very first click anywhere).
  // js/dahi-push.js shows a friendly "Turn on notifications" bar on Friends / chat pages and asks only after the person clicks it.

  // Answer / Decline pressed on the browser notification (service worker opens /friends?callaction=answer&call=ID)
  var PENDING = null;
  try {
    var qs = new URLSearchParams(location.search);
    if (qs.get('callaction') && qs.get('call')) {
      PENDING = { action: qs.get('callaction'), id: parseInt(qs.get('call'), 10) };
      qs.delete('callaction'); qs.delete('call');
      history.replaceState(null, '', location.pathname + (qs.toString() ? '?' + qs.toString() : '') + location.hash);
    }
  } catch (e) {}
  function ding() {
    try {
      var C = window.AudioContext || window.webkitAudioContext; if (!C) return;
      actx = actx || new C(); var t = actx.currentTime;
      [660, 880].forEach(function (f, i) {
        var o = actx.createOscillator(), g = actx.createGain(); o.frequency.value = f; o.connect(g); g.connect(actx.destination);
        g.gain.setValueAtTime(0.0001, t + i * 0.14); g.gain.exponentialRampToValueAtTime(0.12, t + i * 0.14 + 0.02); g.gain.exponentialRampToValueAtTime(0.0001, t + i * 0.14 + 0.2);
        o.start(t + i * 0.14); o.stop(t + i * 0.14 + 0.22);
      });
    } catch (e) {}
  }
  function toast(title, text, url) {
    injectStyles();
    var t = el('div', 'dcl-toast'), head = el('b'), body = el('span');
    head.textContent = title; body.textContent = text; t.appendChild(head); t.appendChild(body);
    t.onclick = function () { if (url) window.location.href = url; };
    document.body.appendChild(t); setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 7000);
  }
  try {
    var roomNotice = JSON.parse(sessionStorage.getItem('dahi-room-notice') || 'null');
    sessionStorage.removeItem('dahi-room-notice');
    if (roomNotice && Date.now() - roomNotice.at < 15000) toast('Call / meeting', roomNotice.text);
  } catch (e) {}
  function resumableRooms(rooms) {
    var old = document.getElementById('dahi-resume-rooms'); if (old) old.remove();
    if (!rooms || !rooms.length || S.call || S.incoming) return;
    injectStyles();
    var bar = el('aside', 'dcl-rooms'); bar.id = 'dahi-resume-rooms'; bar.setAttribute('aria-label', 'Ongoing calls and meetings');
    rooms.forEach(function (room) {
      var a = el('a'); a.textContent = (room.rejoin === false ? 'Join · ' : 'Rejoin · ') + room.title;
      a.href = '/meet/' + encodeURIComponent(room.code) + '?auto=1' + (room.audio_only ? '&audio=1' : '') + '&return_to=' + encodeURIComponent(location.pathname + location.search);
      bar.appendChild(a);
    });
    document.body.appendChild(bar);
  }
  function titleBadge(n) {
    if (S.title === null) S.title = document.title.replace(/^\(\d+\)\s*/, '');
    document.title = (n > 0 ? '(' + n + ') ' : '') + S.title;
  }
  /** m = { unread, latest: {id, from_id, from_name, kind, preview} } from the 4-second poll */
  function messages(m) {
    if (!m) return;
    titleBadge(m.unread || 0);
    var l = m.latest;
    if (S.msgSeen === null) { S.msgSeen = l ? l.id : 0; return; }              // first look: remember, do not alert
    if (!l || l.id <= S.msgSeen) return;
    S.msgSeen = l.id;
    var open = document.querySelector('[data-friend-chat][data-user-id="' + l.from_id + '"]');
    if (open && !document.hidden) return;                                      // you are reading that chat right now
    var url = l.chat_url || '/friends/chat/' + l.from_id, text = l.kind === 'call' ? '\uD83D\uDCDE ' + l.preview : l.preview;
    ding(); if (l.kind === 'call' || !window.DahiMessages?.show({type:'chat', from_id:l.from_id, message_id:l.id, sender_name:l.from_name, body:text})) toast(l.from_name, text, url); desktopNotify(l.from_name, text, function () { window.location.href = url; });
  }

  // ───────── ring tone (a short beep every 2 s) ─────────
  function beep() {
    try {
      var C = window.AudioContext || window.webkitAudioContext; if (!C) return;
      actx = actx || new C(); var o = actx.createOscillator(), g = actx.createGain();
      o.frequency.value = 880; g.gain.value = 0.06; o.connect(g); g.connect(actx.destination); o.start(); setTimeout(function () { o.stop(); }, 350);
    } catch (e) {}
  }
  function startRing() { stopRing(); if (tones) tones.ringtone(); else { beep(); S.ring = setInterval(beep, 2000); } }
  function stopRing() { if (tones) tones.stop(); if (S.ring) { clearInterval(S.ring); S.ring = null; } }

  function playCallAudio() {
    if (!audio || !audio.srcObject) return Promise.resolve();
    var stream = audio.srcObject;
    return Promise.resolve().then(function () { return audio.play(); }).then(function () {
      if (audio.srcObject === stream) enableAudio.style.display = 'none';
    }).catch(function () {
      if (audio.srcObject === stream) enableAudio.style.display = '';
    });
  }

  // ───────── WebRTC ─────────
  function prepare(servers) {
    return navigator.mediaDevices.getUserMedia({ audio: { echoCancellation: true, noiseSuppression: true }, video: false }).then(function (stream) {
      S.stream = stream;
      var pc = new RTCPeerConnection({ iceServers: servers && servers.length ? servers : [{ urls: 'stun:stun.l.google.com:19302' }] });
      S.pc = pc;
      stream.getTracks().forEach(function (t) { pc.addTrack(t, stream); });
      pc.onicecandidate = function (e) { if (e.candidate && S.call) api('POST', '/calls/' + S.call.id + '/signal', { type: 'ice', payload: JSON.stringify(e.candidate) }); };
      pc.ontrack = function (e) {
        if (S.pc !== pc || !S.call) return;
        build();
        var remote = audio.srcObject || new MediaStream();
        if (!remote.getTracks().some(function (t) { return t.id === e.track.id; })) remote.addTrack(e.track);
        audio.srcObject = remote; playCallAudio();
      };
      pc.onconnectionstatechange = function () {
        if (S.pc !== pc || !S.call) return;
        if (pc.connectionState === 'connected') connected();
        else if ((pc.connectionState === 'failed') && S.call) { endCall('Connection lost'); }
      };
    });
  }
  function connected() {
    if (S.clock || !S.call) return; if (tones) tones.connected(); S.secs = 0; setStatus('00:00');
    S.clock = setInterval(function () { S.secs++; setStatus(('0' + Math.floor(S.secs / 60)).slice(-2) + ':' + ('0' + S.secs % 60).slice(-2)); }, 1000);
  }
  function flushQueue() { var q = S.queue; S.queue = []; q.forEach(function (c) { S.pc.addIceCandidate(c).catch(function () {}); }); }
  function handle(s) {
    if (!S.pc) return Promise.resolve();
    var data; try { data = JSON.parse(s.payload); } catch (e) { return Promise.resolve(); }
    if (s.type === 'offer') {
      return S.pc.setRemoteDescription(new RTCSessionDescription(data)).then(function () { S.remoteSet = true; flushQueue(); return S.pc.createAnswer(); })
        .then(function (a) { return S.pc.setLocalDescription(a).then(function () { return api('POST', '/calls/' + S.call.id + '/signal', { type: 'answer', payload: JSON.stringify(S.pc.localDescription) }); }); })
        .catch(function () {});
    }
    if (s.type === 'answer') {
      return S.pc.setRemoteDescription(new RTCSessionDescription(data)).then(function () { S.remoteSet = true; flushQueue(); }).catch(function () {});
    }
    if (s.type === 'ice') {
      var c = new RTCIceCandidate(data);
      if (S.remoteSet) S.pc.addIceCandidate(c).catch(function () {}); else S.queue.push(c);
    }
    return Promise.resolve();
  }
  function loop() {
    clearInterval(S.sig);
    S.sig = setInterval(pullSignals, 1000);
    pullSignals();
  }
  function pullSignals() {
      if (!S.call || S.pulling) return; S.pulling = true; var id = S.call.id;
      api('GET', '/calls/' + id + '/signals?after=' + S.last).then(function (j) {
        if (!S.call || S.call.id !== id) return;
        var d = j.data; if (!d) { finish('Call ended'); return; }
        return d.signals.reduce(function (p, s) { return p.then(function () { S.last = Math.max(S.last, s.id); return handle(s); }); }, Promise.resolve()).then(function () {
          if (['ended', 'declined', 'cancelled', 'missed'].indexOf(d.status) > -1) finish(d.status === 'declined' ? 'Call declined' : d.status === 'missed' ? 'No answer' : 'Call ended');
          else if (d.status === 'active' && !S.clock) { if (tones) tones.accepted(); setStatus('Connecting…'); }
        });
      }).catch(function () {}).then(function () { S.pulling = false; });
  }

  function finish(reason) {
    if (tones) tones.stop();
    if (audio) { audio.pause(); audio.srcObject = null; }
    if (enableAudio) enableAudio.style.display = 'none';
    clearInterval(S.sig); clearInterval(S.clock); stopRing(); S.clock = null;
    if (S.pc) { try { S.pc.close(); } catch (e) {} }
    if (S.stream) S.stream.getTracks().forEach(function (t) { t.stop(); });
    var name = S.call ? S.call.peer.name : (S.incoming ? S.incoming.peer.name : '');
    rememberEnd(S.call && S.call.id); rememberEnd(S.incoming && S.incoming.id); accepting = false;
    S.call = null; S.incoming = null; S.pc = null; S.stream = null; S.remoteSet = false; S.queue = []; S.last = 0; S.secs = 0; S.muted = false; S.starting = false;
    if (reason) { show(name, reason, []); hideTimer = setTimeout(hide, reason.length > 40 ? 7000 : 2500); } else hide();
  }
  function endCall(reason) { var id = S.call && S.call.id; if (id) deliverEnd('/calls/' + id + '/end'); finish(reason); }
  function toggleMute() {
    S.muted = !S.muted;
    if (S.stream) S.stream.getAudioTracks().forEach(function (t) { t.enabled = !S.muted; });
    if (S.muteBtn) S.muteBtn.textContent = S.muted ? 'Unmute' : 'Mute';
  }
  function inCallButtons() { return [['Mute', 'neutral', toggleMute], ['End', 'decline', function () { endCall('Call ended'); }]]; }

  // ───────── start / answer ─────────
  function start(userId, name) {
    if (S.call || S.incoming || S.starting) { alert('You are already in a call.'); return; }
    if (!window.RTCPeerConnection || !navigator.mediaDevices) { alert('Your browser cannot make calls. Use a current version of Chrome, Edge, Firefox or Safari over HTTPS.'); return; }
    S.starting = true; show(name || 'Friend', 'Calling…', []);
    api('POST', '/calls/start/' + userId).then(function (r) {
      if (!r.data) { S.starting = false; show(name || 'Friend', r.message || 'Could not start the call.', []); hideTimer = setTimeout(hide, 3000); return; }
      S.call = r.data; S.starting = false; S.last = 0;
      show(S.call.peer.name, 'Calling…', [['End', 'decline', function () { endCall('Call ended'); }]]);
      return prepare(S.call.ice_servers).then(function () { if (tones) tones.dialing(); return S.pc.createOffer(); })
        .then(function (o) { return S.pc.setLocalDescription(o); })
        .then(function () { return api('POST', '/calls/' + S.call.id + '/signal', { type: 'offer', payload: JSON.stringify(S.pc.localDescription) }); })
        .then(function () { show(S.call.peer.name, 'Ringing…', inCallButtons()); loop(); })
        .catch(function (e) { endCall(micMessage(e)); });
    }).catch(function () { S.starting = false; finish('Could not start the call. Check your connection.'); });
  }
  function showIncoming(c) {
    if (endedCalls.has(Number(c.id)) || S.call || S.starting || accepting || (S.incoming && S.incoming.id === c.id)) return;
    S.incoming = c; startRing();
    // same wording and order as the app: "Incoming Meeting…" / "Incoming Video Call…" / "Incoming Audio Call…", Decline left, Accept right
    var kind = c.group ? 'Meeting' : (c.video && !c.audio_only) ? 'Video Call' : 'Audio Call';
    desktopNotify('Incoming ' + kind, c.peer.name + ' is calling you', function () {});
    show(c.peer.name, 'Incoming ' + kind + '…', [['Decline', 'decline', decline], ['Accept', 'accept', accept]]);
    if (PENDING && PENDING.id === c.id) { var act = PENDING.action; PENDING = null; if (act === 'decline') decline(); else accept(); }
  }
  function accept() {
    var c = S.incoming; if (!c || accepting) return; accepting = true; stopRing();
    show(c.peer.name, 'Please wait…', [['Decline', 'decline', decline, true], ['Accept', 'accept', accept, true]]);   // busy: dimmed, like the app
    api('POST', '/calls/' + c.id + '/answer').then(function (r) {
      if (endedCalls.has(Number(c.id)) || !S.incoming || S.incoming.id !== c.id) return;
      accepting = false;
      if (r._status >= 400) { rememberEnd(c.id); S.incoming = null; show(c.peer.name, r.message || 'The call is no longer available.', []); hideTimer = setTimeout(hide, 2500); return; }
      if (c.video && c.meeting) { S.incoming = null; window.location.href = '/meet/' + c.meeting + '?auto=1&call=1' + (c.audio_only ? '&audio=1' : '') + '&return_to=' + encodeURIComponent(c.peer.chat_url ? new URL(c.peer.chat_url, location.origin).pathname : '/friends/chat/' + c.peer.id); return; } // a video call opens the meeting room
      S.incoming = null; S.call = c; S.last = 0;
      show(c.peer.name, 'Connecting…', inCallButtons());
      return prepare(c.ice_servers).then(loop).catch(function (e) { endCall(micMessage(e)); });
    }).catch(function () { accepting = false; if (S.incoming && S.incoming.id === c.id && !endedCalls.has(Number(c.id))) { S.incoming = null; showIncoming(c); } });
  }
  function decline() {
    var c = S.incoming; if (!c) return; stopRing(); S.incoming = null;
    rememberEnd(c.id); accepting = false; deliverEnd('/calls/' + c.id + '/decline'); hide();
  }

  // ───────── listen for incoming calls ─────────
  function pollCalls() {
    if (S.call || S.starting || accepting) return;
    if (S.polling) { pollAgain = true; return; } S.polling = true;
    var revision = readRevision, callVersion = callRevision;
    api('GET', '/calls/poll').then(function (j) {
      if (callVersion !== callRevision) { pollAgain = true; return; }
      var d = j.data; if (!d) return;
      if (revision === readRevision) messages(d.messages);
      resumableRooms(d.resumable);
      if (S.call || S.starting || accepting) return;
      if (d.incoming) showIncoming(d.incoming);
      else if (S.incoming) { stopRing(); var n = S.incoming.peer.name; S.incoming = null; show(n, 'Missed call', []); hideTimer = setTimeout(hide, 3000); }
    }).catch(function () {}).then(function () { S.polling = false; if (pollAgain) { pollAgain = false; pollCalls(); } });
  }
  setInterval(pollCalls, 1000);
  pollCalls();
  window.addEventListener('beforeunload', function () { if (S.call) { try { navigator.sendBeacon(BASE + '/calls/' + S.call.id + '/end', new Blob([JSON.stringify({ _token: csrf() })], { type: 'application/json' })); } catch (e) {} } });

  /** Video call: a private 2-person meeting. The friend's phone / browser rings and joins the same room. */
  function startVideo(userId, name, audioOnly) {
    if (S.call || S.incoming || S.starting) { alert('You are already in a call.'); return; }
    S.starting = true;
    api('POST', '/calls/video/' + userId + (audioOnly ? '?audio=1' : '')).then(function (r) {
      S.starting = false;
      if (r._status >= 400 || !r.data || !r.data.meeting) { alert(r.message || 'Could not start the video call.'); return; }
      window.location.href = '/meet/' + r.data.meeting + '?auto=1&call=1' + (audioOnly ? '&audio=1' : '') + '&return_to=' + encodeURIComponent(location.pathname + location.search + location.hash);
    }).catch(function () { S.starting = false; alert('Could not start the video call. Check your connection.'); });
  }

  window.addEventListener('dahi:chat-read', function (event) {
    readRevision++;
    if (event.detail && typeof event.detail.unread === 'number') titleBadge(event.detail.unread);
    pollCalls();
  });
  window.addEventListener('dahi:sync', function (event) {
    var d = event.detail || {};
    if (d.type === 'call' || d.type === 'call_cancel') callRevision++;
    if (d.type === 'call' && d.incoming && d.incoming.video && d.incoming.meeting) {
      var incoming = d.incoming;
      if (Number(incoming.sent_at) + Number(incoming.ttl) > Date.now() / 1000) showIncoming(incoming);
    }
    if (d.type === 'call_cancel') {
      if (d.reason === 'answered' && S.call && S.call.id === d.call_id && tones) tones.accepted();
      if (d.reason !== 'answered') rememberEnd(d.call_id);
      if (((S.call && S.call.id === d.call_id) || (S.incoming && S.incoming.id === d.call_id)) && (d.reason !== 'answered' || (S.incoming && !accepting))) finish(d.reason === 'answered' ? 'Answered on another device' : 'Call ended');
    }
    if (d.type === 'call_signal' && S.call && S.call.id === d.call_id) pullSignals();
    pollCalls();
  });
  window.addEventListener('online', pollCalls);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) pollCalls(); });
  window.FriendCall = { start: function (id, n) { startVideo(id, n, true); }, legacyStart: start, startVideo: startVideo, startGroupAudio: function (id, n) { startVideo(id, n, true); } };
})();
