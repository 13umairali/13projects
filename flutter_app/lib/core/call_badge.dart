import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'api.dart';

/// Missed-call badge for the Calls icon in the header.
///
/// Standard "unseen" logic (WhatsApp / Phone app style):
///  * The badge counts only MISSED INCOMING calls that are newer than the last call the user has SEEN.
///  * "Seen" is a single high-water mark (the highest call id) saved per account, not a list of every call.
///  * First run on a phone (fresh install / reinstall / first sign-in) seeds the mark with the newest existing call,
///    so old history never shows up as "new" again.
///  * Opening the Calls screen moves the mark to the newest call and clears the badge.
class CallBadge {
  CallBadge._();

  /// Number of unseen missed calls (drives the badge).
  static final ValueNotifier<int> unseen = ValueNotifier<int>(0);

  static String _key(int? userId) => 'calls_seen_id_${userId ?? 0}';

  static int? _id(dynamic v) => v is num ? v.toInt() : int.tryParse('$v');

  /// A call counts as missed when somebody called this user and it was not answered or declined by this user.
  static bool isMissed(Map<String, dynamic> c) {
    if (c['outgoing'] == true) return false;
    final o = '${c['outcome']}'.toLowerCase();
    return o != 'answered' && o != 'declined' && o != 'rejected';
  }

  static int _maxId(List<Map<String, dynamic>> calls) {
    var m = 0;
    for (final c in calls) {
      final i = _id(c['id']);
      if (i != null && i > m) m = i;
    }
    return m;
  }

  /// Recount from the server (cheap). Safe to call often: on start, on resume, every minute, after a call ends.
  static Future<void> refresh(Api api, int? userId) async {
    try {
      final calls = Api.list(await api.getNoCache('calls/history'));
      await apply(calls, userId);
    } catch (_) {/* keep the last count while offline */}
  }

  /// Applies an already-downloaded history list.
  static Future<void> apply(List<Map<String, dynamic>> calls, int? userId) async {
    final prefs = await SharedPreferences.getInstance();
    final key = _key(userId);
    final newest = _maxId(calls);
    final seen = prefs.getInt(key);
    if (seen == null) {
      // Fresh install / new account on this phone: existing history is NOT new.
      await prefs.setInt(key, newest);
      unseen.value = 0;
      return;
    }
    // History was cleared on the server: bring the mark down so future calls count again.
    if (newest < seen) await prefs.setInt(key, newest);
    final mark = newest < seen ? newest : seen;
    final n = calls.where((c) => isMissed(c) && (_id(c['id']) ?? 0) > mark).length;
    if (unseen.value != n) unseen.value = n;
  }

  /// The user looked at the Calls screen: everything up to the newest call is now seen.
  static Future<void> markSeen(List<Map<String, dynamic>> calls, int? userId) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(_key(userId), _maxId(calls));
    unseen.value = 0;
  }

  /// Sign-out / account switch: never carry one account's badge into another.
  static void reset() => unseen.value = 0;
}
