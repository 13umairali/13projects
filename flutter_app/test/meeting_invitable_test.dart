import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:provider/provider.dart';
import 'package:dahimail/core/api.dart';
import 'package:dahimail/core/meeting_engine.dart';
import 'package:dahimail/core/session.dart';
import 'package:dahimail/screens/meeting_room_page.dart';

class MockSession extends Session {
  final http.Client _client;
  MockSession(this._client);
  @override
  http.Client get client => _client;
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  test('canInvite is true when phase is joining or in', () {
    final client = MockClient((req) async => http.Response('{}', 200));
    final session = MockSession(client);
    final api = Api(session);
    final engine = MeetingEngine(api, 'room123');

    expect(engine.phase, 'idle');
    expect(engine.canInvite, false);

    engine.phase = 'joining';
    expect(engine.canInvite, true);

    engine.phase = 'in';
    expect(engine.canInvite, true);

    engine.phase = 'ended';
    expect(engine.canInvite, false);
  });

  test('loadInvitable falls back to people endpoint if invitable is empty or fails', () async {
    final client = MockClient((req) async {
      if (req.url.path.contains('/invitable')) {
        return http.Response('{"invitable": []}', 200);
      }
      if (req.url.path.contains('/people')) {
        return http.Response(
          '{"data": {"friends": [{"id": 10, "name": "Alice", "online": true}], "team": [{"id": 11, "name": "Bob", "online": false}]}}',
          200,
        );
      }
      return http.Response('{}', 200);
    });

    final session = MockSession(client);
    final api = Api(session);
    final engine = MeetingEngine(api, 'room123');

    expect(engine.invitable, null);

    await engine.loadInvitable();

    expect(engine.invitable, isNotNull);
    expect(engine.invitable!.length, 2);
    expect(engine.invitable![0]['name'], 'Alice');
    expect(engine.invitable![1]['name'], 'Bob');
  });

  testWidgets('MeetingRoomPage add people sheet displays invitable friends without crash', (tester) async {
    final client = MockClient((req) async {
      if (req.url.path.contains('/invitable')) {
        return http.Response(
          '{"invitable": [{"id": 10, "name": "Alice", "online": true, "avatar_url": "https://example.com/a.png"}]}',
          200,
        );
      }
      if (req.url.path.contains('/join')) {
        return http.Response('{"data": {"pid": 1, "role": "host", "status": "in", "meeting": {"kind": "call", "title": "Test Call"}}}', 200);
      }
      return http.Response('{"data": {}}', 200);
    });

    final session = MockSession(client)..ready = true..token = 'test'..user = {'id': 1};

    await tester.pumpWidget(
      MaterialApp(
        home: ChangeNotifierProvider<Session>.value(
          value: session,
          child: const MeetingRoomPage(code: 'testroom', autoJoin: true),
        ),
      ),
    );

    await tester.pump();
    await tester.pumpAndSettle();

    final addPeopleBtn = find.byTooltip('Add people');
    expect(addPeopleBtn, findsOneWidget);

    await tester.tap(addPeopleBtn);
    await tester.pump();

    expect(find.text('Add people to this call'), findsOneWidget);

    await tester.pumpAndSettle();

    expect(find.text('Alice'), findsOneWidget);
    expect(find.text('Add'), findsOneWidget);
  });
}
