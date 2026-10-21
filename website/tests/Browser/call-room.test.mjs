import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
const source = readFileSync(new URL('../../public/js/call-room.js', import.meta.url), 'utf8')
  .replace('  start();', '  window.testCallRoom = { makePeer, apply, attachLocal, unlockSound, closePeer, finish, onSignal, endedBecause, watchRing, statusText };');
const flush = () => new Promise(resolve => setImmediate(resolve));
function room() {
  let reply = null;
  const elements = new Map(), sounds = [], stages = [], requests = [];
  const element = id => {
    if (!elements.has(id)) {
      const classes = new Set();
      elements.set(id, { style: {}, classList: {
        toggle(n, on) { if (on) classes.add(n); else classes.delete(n); },
        add(n) { classes.add(n); }, remove(n) { classes.delete(n); }, contains(n) { return classes.has(n); },
      }, appendChild(c) { c.parentNode = this; }, removeChild(c) { c.parentNode = null; },
        setAttribute() {}, getAttribute(k) { return { 'data-code': 'room', 'data-call-id': '5', 'data-role': 'caller', 'data-peer': '{"name":"Friend"}', 'data-me': '{"name":"Me"}' }[k]; },
        playCalls: 0, play() { this.playCalls++; return this.reject ? Promise.reject(Error('autoplay')) : Promise.resolve(); }, pause() {},
      });
    }
    return elements.get(id);
  };
  class Stream {
    constructor() { this.tracks = []; }
    getTracks() { return this.tracks; } getAudioTracks() { return this.tracks.filter(t => t.kind === 'audio'); }
    addTrack(t) { this.tracks.push(t); }
  }
  class Peer {
    addTransceiver() { return { sender: {} }; }
    async addIceCandidate(c) { (this.candidates ||= []).push(c); }
    async setRemoteDescription() {} getTransceivers() { return []; }
    async createAnswer() { return {}; } async setLocalDescription() {} close() {}
  }
  const window = { addEventListener() {}, DahiCallTones: class { connected() { sounds.push('connected'); } stop() { sounds.push('stop'); } resume() {} accepted() { stages.push('accepted'); } dialing() { stages.push('dialing'); } ringing() { stages.push('ringing'); } } };
  vm.runInNewContext(source, { window, document: { getElementById: element, querySelector: () => null, createElement: tag => element('created-' + tag), body: element('body'), addEventListener() {} },
    location: { origin: 'https://mail.test', search: '', replace() {} }, navigator: {}, MediaStream: Stream, RTCPeerConnection: Peer,
    URL, URLSearchParams, Set, Promise, Date, setInterval() { return 1; }, clearInterval() {}, setTimeout() {}, clearTimeout() {},
    fetch: async (url) => { requests.push(url); return { status: 200, json: async () => (reply ? reply(url) : {}) }; },
  });
  return { api: window.testCallRoom, state: window.__call.S, elements, sounds, stages, requests, setReply(fn) { reply = fn; } };
}
test('call audio plays in a dedicated sink with a camera-off participant and unlocks after autoplay rejection', async () => {
  const r = room(); r.state.pid = 7;
  r.api.apply({ me: {}, participants: [{ pid: 8, name: 'Friend', video: false }] });
  const peer = r.api.makePeer(8, true);
  peer.audio.reject = true;
  peer.pc.ontrack({ track: { id: 'mic', kind: 'audio' } });
  await flush();
  assert.equal(peer.audio.srcObject, peer.stream);
  assert.equal(peer.audio.playCalls, 1);
  assert.equal(r.elements.get('cl-note').classList.contains('hidden'), false);
  assert.equal(r.state.tiles[8].video.muted, true);
  peer.audio.reject = false;
  await r.api.unlockSound();
  assert.equal(r.elements.get('cl-note').classList.contains('hidden'), true);
  r.api.closePeer(8);
  assert.equal(peer.audio.srcObject, null); assert.equal(peer.audio.parentNode, null);
});
test('participant presence cannot start the timer or connection tone before WebRTC is connected', () => {
  const r = room(); r.state.pid = 7;
  r.api.apply({ me: {}, participants: [{ pid: 8, name: 'Friend' }] });
  assert.equal(r.state.connectedAt, 0); assert.deepEqual(r.sounds, []);
  const peer = r.api.makePeer(8, true); peer.pc.connectionState = 'connected';
  peer.pc.onconnectionstatechange(); peer.pc.onconnectionstatechange();
  assert.ok(r.state.connectedAt > 0); assert.deepEqual(r.sounds, ['connected']);
});
test('ICE received before an offer survives replacement of the waiting connection', async () => {
  const r = room(); const peer = r.api.makePeer(8, true);
  peer.queue.push({ candidate: 'early' });
  await r.api.onSignal({ from: 8, type: 'offer', payload: '{}' });
  assert.equal(r.state.peers[8].pc.candidates[0].candidate, 'early');
});

test('caller hears Calling… until their device is online, then Ringing…, and never goes back', async () => {
  const r = room(); r.state.pid = 7; r.state.sigLast = 0;
  r.setReply(() => ({ data: { status: 'ringing', peer_online: false, signals: [] } }));
  r.api.watchRing(); await flush(); await flush();
  assert.equal(r.api.statusText(), 'Calling…'); assert.deepEqual(r.stages, []);
  r.setReply(() => ({ data: { status: 'ringing', peer_online: true, signals: [] } }));
  r.api.watchRing(); await flush(); await flush();
  assert.equal(r.state.ringing, true); assert.equal(r.api.statusText(), 'Ringing…');
  assert.deepEqual(r.stages, ['ringing']);
});

test('when the other person joins, both ring tones stop for good', () => {
  const r = room(); r.state.pid = 7;
  r.api.apply({ me: {}, participants: [{ pid: 8, name: 'Friend' }] });
  assert.deepEqual(r.stages, ['accepted']);
});

test('call ended reasons match the app: declined, no answer / missed call, otherwise call ended', () => {
  const finished = (status) => { const r = room(); r.api.endedBecause(status); return r.elements.get('cl-end-title').textContent; };
  assert.equal(finished('declined'), 'Call declined');
  assert.equal(finished('missed'), 'No answer');          // the test caller is the caller
  assert.equal(finished('cancelled'), 'Call ended');      // the caller hung up while it rang
  assert.equal(finished('ended'), 'Call ended');
});
