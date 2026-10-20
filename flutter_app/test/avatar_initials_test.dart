import 'package:flutter_test/flutter_test.dart';
import 'package:dahimail/core/widgets.dart';
import 'package:dahimail/core/notification_identity.dart';

void main() {
  test('To:/From: prefixes are not part of the initials', () {
    expect(Avatar.initials('To: John Doe'), 'JD');
    expect(Avatar.initials('from: john doe'), 'JD');
    expect(Avatar.initials('John Doe'), 'JD');
    expect(Avatar.initials('Dahimail'), 'D');
    expect(Avatar.initials('Mary Ann Smith'), 'MS');
    expect(Avatar.initials(''), '');
    expect(Avatar.cleanName('To: John Doe'), 'John Doe');
  });

  test('an e-mail reply in the same thread gets a different alert identity', () {
    final first = {'type': 'email_received', 'notification_id': '7', 'body': 'First message', 'created_at': '2026-10-19T10:00:00Z'};
    final reply = {'type': 'email_received', 'notification_id': '7', 'body': 'The latest reply', 'created_at': '2026-10-19T11:00:00Z'};
    expect(NotificationIdentity.emailVersion(first), isNot(NotificationIdentity.emailVersion(reply)));
    expect(NotificationIdentity.emailVersion(first), NotificationIdentity.emailVersion(Map.of(first)));
    expect(NotificationIdentity.emailVersion({'type': 'chat', 'body': 'x'}), '');
  });
}
