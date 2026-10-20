import 'package:flutter/services.dart';

/// Native playback shares the communication route without requesting media
/// audio focus or changing the iOS recording session used by WebRTC.
class CallTones {
  static const _channel = MethodChannel('com.dahify.dahimail/call_tones');
  Future<void> _work = Future.value();
  bool _connected = false, _accepted = false, _closed = false;
  String _mode = ''; // '' | dialing | ringback

  void _enqueue(String method) {
    _work = _work.catchError((_) {}).then((_) async {
      if ((_closed && method != 'stop') || (_accepted && (method == 'dialing' || method == 'ringback'))) return;
      try {
        await _channel.invokeMethod<void>(method);
      } catch (_) {}
    });
  }

  /// Other person is offline / not connected: "calling" dial tone.
  void dialing() {
    if (!_closed && !_connected && !_accepted && _mode != 'ringback') {
      _mode = 'dialing';
      _enqueue('dialing');
    }
  }

  /// Other person is online and their device is ringing: ringback tone (switches over from the dial tone).
  void ringing() {
    if (!_closed && !_connected && !_accepted && _mode != 'ringback') {
      _mode = 'ringback';
      _enqueue('ringback');
    }
  }

  void accepted() {
    if (_closed || _accepted || _connected) return;
    _accepted = true;
    _enqueue('stop');
  }

  void connected() {
    if (_closed || _connected) return;
    _connected = true;
    _enqueue('connected');
  }

  Future<void> stop() {
    if (!_closed) {
      _closed = true;
      _enqueue('stop');
    }
    return _work;
  }
}
