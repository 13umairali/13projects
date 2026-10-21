import 'dart:async';
import 'api.dart';

/// Sends WebRTC signals (offer / answer / ICE candidates) to the server.
///
/// Before: one HTTP request per ICE candidate (10–20 per side), and a failed request was silently lost, which left
/// the call stuck or failed. Now:
///  • candidates are collected for [window] and sent in ONE request;
///  • offer / answer go out at once (and take any waiting candidates with them, in order);
///  • a failed request is retried with a short back-off;
///  • an older server without the batch route (404 / 405) is served with one request per signal.
class SignalSender {
  SignalSender({required this.batch, required this.single, this.window = const Duration(milliseconds: 60)});

  /// POST many signals at once.
  final Future<void> Function(List<Map<String, dynamic>> items) batch;
  /// POST one signal (fallback for servers that do not know the batch route yet).
  final Future<void> Function(Map<String, dynamic> item) single;
  final Duration window;

  final _pending = <Map<String, dynamic>>[];
  Timer? _timer;
  Future<void> _tail = Future.value();
  bool _closed = false, _legacy = false;

  /// [item] is {to, type, payload}. Offers and answers pass `immediate: true`.
  void add(Map<String, dynamic> item, {bool immediate = false}) {
    if (_closed) return;
    _pending.add(item);
    if (immediate) {
      _timer?.cancel();
      _timer = null;
      _flush();
    } else {
      _timer ??= Timer(window, () { _timer = null; _flush(); });
    }
  }

  void _flush() {
    if (_pending.isEmpty) return;
    final items = List<Map<String, dynamic>>.of(_pending);
    _pending.clear();
    // One delivery at a time keeps the order: an offer is never overtaken by its candidates.
    _tail = _tail.catchError((_) {}).then((_) => _deliver(items));
  }

  Future<void> _deliver(List<Map<String, dynamic>> items) async {
    for (var attempt = 0; attempt < 4 && !_closed; attempt++) {
      try {
        if (_legacy) {
          for (final i in items) { await single(i); }
        } else {
          await batch(items);
        }
        return;
      } on ApiException catch (e) {
        if (!_legacy && (e.status == 404 || e.status == 405)) { _legacy = true; attempt--; continue; }
        if (e.status >= 400 && e.status < 500 && e.status != 408 && e.status != 429) return; // refused: retrying cannot help
      } catch (_) {}
      await Future<void>.delayed(Duration(milliseconds: 250 * (1 << attempt)));
    }
  }

  /// Send what is waiting right now (used before the room closes).
  Future<void> flush() {
    _timer?.cancel();
    _timer = null;
    _flush();
    return _tail.catchError((_) {});
  }

  void close() {
    _closed = true;
    _timer?.cancel();
    _pending.clear();
  }
}
