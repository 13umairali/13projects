import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
const source = readFileSync(new URL('../../public/js/call-tones.js', import.meta.url), 'utf8');
const flush = () => new Promise(resolve => setImmediate(resolve));

/** Fake <audio>: records every file, whether it loops, and can refuse to play until a gesture "unlocks" autoplay. */
function harness({ blocked = false } = {}) {
  const listeners = new Map(), sounds = [];
  class Audio {
    constructor(src) { this.src = src; this.loop = false; this.paused = true; this.file = src.split('/').pop(); sounds.push(this); }
    play() { if (blocked) return Promise.reject(Error('gesture required')); this.paused = false; return Promise.resolve(); }
    pause() { this.paused = true; }
    removeAttribute() {}
  }
  const window = {};
  vm.runInNewContext(source, { window, Audio, Promise,
    document: { addEventListener(name, fn) { listeners.set(name, fn); }, removeEventListener(name) { listeners.delete(name); } } });
  const playing = () => sounds.filter(s => !s.paused).map(s => s.file);
  return { tones: new window.DahiCallTones(), sounds, listeners, playing, allow() { blocked = false; } };
}

test('the app\'s three sounds are used: dialing, then ringback, then silence, then one connected chime', async () => {
  const h = harness(); h.tones.dialing(); await flush();
  assert.deepEqual(h.playing(), ['dialing.wav']); assert.equal(h.sounds[0].loop, true);
  h.tones.ringing(); await flush();
  assert.deepEqual(h.playing(), ['ringback.wav']); assert.equal(h.sounds[1].loop, true);
  h.tones.accepted(); await flush();
  assert.deepEqual(h.playing(), []);
  h.tones.connected(); h.tones.connected(); await flush();
  assert.deepEqual(h.playing(), ['connected.wav']); assert.equal(h.sounds[2].loop, false);
  assert.equal(h.sounds.length, 3);
});

test('dialing never replaces ringback', async () => {
  const h = harness(); h.tones.ringing(); h.tones.dialing(); h.tones.dialing(); await flush();
  assert.deepEqual(h.sounds.map(s => s.file), ['ringback.wav']);
  assert.deepEqual(h.playing(), ['ringback.wav']);
});

test('after the call is accepted neither dialing nor ringback can restart', async () => {
  const h = harness(); h.tones.dialing(); h.tones.accepted();
  h.tones.dialing(); h.tones.ringing(); await flush();
  assert.deepEqual(h.playing(), []); assert.deepEqual(h.sounds.map(s => s.file), ['dialing.wav']);
});

test('the callee hears only the ringtone, which stops on accept and on decline', async () => {
  const h = harness(); h.tones.ringtone(); h.tones.ringtone(); await flush();
  assert.deepEqual(h.sounds.map(s => s.file), ['ringtone.wav']); assert.equal(h.sounds[0].loop, true);
  h.tones.stop(); await flush();
  assert.deepEqual(h.playing(), []);
});

test('hang-up stops everything, releases listeners and the same object can serve the next call', async () => {
  const h = harness(); h.tones.dialing(); await flush();
  h.tones.stop(); await flush();
  assert.deepEqual(h.playing(), []); assert.equal(h.listeners.size, 0);
  h.tones.dialing(); await flush();
  assert.deepEqual(h.playing(), ['dialing.wav']);
});

test('blocked autoplay waits for one gesture and cannot queue repeated dialing sounds', async () => {
  const h = harness({ blocked: true }); h.tones.dialing(); h.tones.dialing(); await flush();
  assert.equal(h.sounds.length, 1); assert.deepEqual(h.playing(), []);
  assert.ok(h.listeners.get('click'));
  h.allow(); h.listeners.get('click')(); await flush();
  assert.deepEqual(h.playing(), ['dialing.wav']); assert.equal(h.sounds.length, 1);
  assert.equal(h.listeners.size, 0);
});

test('a blocked connected chime plays on the next gesture', async () => {
  const h = harness({ blocked: true }); h.tones.connected(); await flush();
  assert.deepEqual(h.playing(), []);
  h.allow(); h.listeners.get('keydown')(); await flush();
  assert.deepEqual(h.playing(), ['connected.wav']);
});

test('hang-up while the browser is still deciding never produces a late sound', async () => {
  const h = harness(); h.tones.dialing(); h.tones.connected(); h.tones.stop(); await flush();
  assert.deepEqual(h.playing(), []); assert.equal(h.listeners.size, 0);
});
