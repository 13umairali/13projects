/* Call progress sounds for the website: the same three sounds and the same rules as the phone app
 * (flutter_app/lib/core/call_tones.dart, docs/call-meeting-tones.md).
 *
 *   caller:  dialing()  "Calling…"   soft double blip, repeats       (/sounds/dialing.wav)
 *            ringing()  "Ringing…"   classic ringback, 2 s on / 4 s off (/sounds/ringback.wav)
 *            accepted() they picked up: silence until the media is up
 *            connected() media is up: one short confirmation          (/sounds/connected.wav)
 *   callee:  ringtone() incoming call: the ringtone, repeats           (/sounds/ringtone.wav)
 *   both:    stop()     hang-up / decline / missed: everything stops
 *
 * Rules: dialing never replaces ringback; once accepted (or connected) neither can restart; after stop() the
 * object can be used for the next call. Browsers may block autoplay: the sound then starts on the next click,
 * key press or touch, and a call that has already ended never makes a late sound. */
(function () {
  var FILES = { dialing: 'dialing.wav', ringback: 'ringback.wav', ringtone: 'ringtone.wav', connected: 'connected.wav' };
  var GESTURES = ['click', 'keydown', 'touchstart'];

  function CallTones() {
    this.base = window.DahiSoundBase || '/sounds/';
    this.mode = '';            // '' | dialing | ringback | ringtone
    this.isAccepted = false;
    this.isConnected = false;
    this.loop = null;          // the looping <audio> (dialing / ringback / ringtone)
    this.chimeWanted = false;  // the connected sound is waiting for a gesture
    this.token = 0;            // bumped by stop(): late promises of an earlier call do nothing
    this.listening = false;
    this.unlock = this.resume.bind(this);
  }

  CallTones.prototype.listen = function () {
    if (this.listening) return;
    this.listening = true;
    GESTURES.forEach(function (name) { document.addEventListener(name, this.unlock, true); }, this);
  };
  CallTones.prototype.unlisten = function () {
    if (!this.listening) return;
    this.listening = false;
    GESTURES.forEach(function (name) { document.removeEventListener(name, this.unlock, true); }, this);
  };

  /** Plays one file. Resolves true when it is playing, false when the browser wants a gesture first. */
  CallTones.prototype.play = function (name, loop) {
    var token = this.token, audio = new Audio(this.base + FILES[name]);
    audio.loop = !!loop; audio.preload = 'auto';
    var attempt; try { attempt = audio.play(); } catch (e) { attempt = Promise.reject(e); }   // called synchronously: keeps the user gesture, and stop() right after still wins
    var done = Promise.resolve(attempt).then(function () { return true; }, function () { return false; });
    return { audio: audio, ready: done.then(function (ok) { return this.token === token ? ok : (this.release(audio), true); }.bind(this)) };
  };
  CallTones.prototype.release = function (audio) {
    try { audio.pause(); audio.removeAttribute && audio.removeAttribute('src'); audio.src = ''; } catch (e) {}
  };
  CallTones.prototype.clear = function () {
    var audio = this.loop; this.loop = null;
    if (audio) this.release(audio);
  };

  CallTones.prototype.start = function (mode) {
    this.clear(); this.mode = mode;
    var token = this.token, started = this.play(mode, true);
    this.loop = started.audio;
    started.ready.then(function (ok) { if (!ok && this.token === token && this.mode === mode) this.listen(); }.bind(this));
  };

  /** Other person not connected yet: "Calling…" dial tone. */
  CallTones.prototype.dialing = function () {
    if (this.isConnected || this.isAccepted || this.mode === 'ringback' || this.mode === 'dialing') return;
    this.start('dialing');
  };
  /** Other person's device is ringing: "Ringing…" ringback (switches over from the dial tone, never back). */
  CallTones.prototype.ringing = function () {
    if (this.isConnected || this.isAccepted || this.mode === 'ringback') return;
    this.start('ringback');
  };
  /** Incoming call on this device (website callee). The caller's tones are never played here. */
  CallTones.prototype.ringtone = function () {
    if (this.isConnected || this.isAccepted || this.mode === 'ringtone') return;
    this.start('ringtone');
  };
  /** They picked up: silence until the media connects. */
  CallTones.prototype.accepted = function () {
    if (this.isAccepted || this.isConnected) return;
    this.isAccepted = true; this.mode = ''; this.clear();
  };
  CallTones.prototype.connected = function () {
    if (this.isConnected) return;
    this.isConnected = true; this.mode = ''; this.clear();
    var token = this.token, chime = this.play('connected', false);
    chime.ready.then(function (ok) {
      if (this.token !== token) return;
      if (!ok) { this.chimeWanted = true; this.listen(); }
    }.bind(this));
  };
  /** A gesture arrived (or the page asks): retry whatever the browser blocked. */
  CallTones.prototype.resume = function () {
    var token = this.token;
    if (this.chimeWanted) {
      this.chimeWanted = false;
      this.play('connected', false).ready.then(function (ok) { if (this.token === token && !ok) this.chimeWanted = true; else this.unlistenIfIdle(); }.bind(this));
      return;
    }
    if (this.loop && this.mode && this.loop.paused) {
      var audio = this.loop;
      var attempt; try { attempt = audio.play(); } catch (e) { attempt = Promise.reject(e); }
      Promise.resolve(attempt).then(function () { if (this.token === token) this.unlistenIfIdle(); }.bind(this), function () {});
    } else this.unlistenIfIdle();
  };
  CallTones.prototype.unlistenIfIdle = function () {
    if (!this.chimeWanted && !(this.loop && this.mode && this.loop.paused)) this.unlisten();
  };
  /** Hang-up, decline, missed, error: everything stops, and the object is ready for the next call. */
  CallTones.prototype.stop = function () {
    this.token++; this.clear(); this.mode = ''; this.chimeWanted = false;
    this.isAccepted = false; this.isConnected = false; this.unlisten();
  };

  window.DahiCallTones = CallTones;
})();
