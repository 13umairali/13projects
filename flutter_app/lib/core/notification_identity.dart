/// Delivery IDs plus the email event identity shared by push and REST polling.
/// Include the recipient and event so accounts and delivery updates stay separate.
class NotificationIdentity {
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
