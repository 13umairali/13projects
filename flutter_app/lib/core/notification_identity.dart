/// Delivery IDs plus the email event identity shared by push and REST polling.
/// Include the recipient and event so accounts and delivery updates stay separate.
class NotificationIdentity {
  /// A reply in the same thread can reuse the thread's notification/message id. Include the newest content and
  /// time for e-mail alerts so the reply is shown (and replaces the old alert) instead of being dropped as a duplicate.
  static String emailVersion(Map data) {
    final type = '${data['type'] ?? ''}';
    if (type != 'email_received' && type != 'new_email') return '';
    final time = [data['created_at_iso'], data['created_at'], data['sent_at']]
        .map((v) => '${v ?? ''}'.trim()).firstWhere((v) => v.isNotEmpty, orElse: () => '');
    var h = 0x811c9dc5; // FNV-1a: stable across runs, unlike String.hashCode
    for (final c in '${data['body'] ?? ''}|$time'.codeUnits) {
      h = ((h ^ c) * 0x01000193) & 0x7fffffff;
    }
    return ':v${h.toRadixString(36)}';
  }

  static Set<String> keys(Map data, {String? messageId}) {
    final keys = <String>{};
    for (final value in [data['notification_id'], data['id'], messageId]) {
      final key = '${value ?? ''}'.trim();
      if (key.isNotEmpty) keys.add(key);
    }
    final type = '${data['type'] ?? ''}';
    final event = type == 'new_email' ? 'email_received' : type;
    final user = int.tryParse('${data['recipient_user_id']}');
    final message = int.tryParse('${data['message_id']}');
    if (const ['email_received', 'email_sent', 'email_delivered', 'email_failed', 'email_bounced'].contains(event)
        && user != null && user > 0 && message != null && message > 0) {
      keys.add('email:$user:$message:$event');
    }
    return keys;
  }
}
