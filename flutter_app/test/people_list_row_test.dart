import 'dart:convert';
import 'dart:io';
import 'dart:ui' as ui;
import 'package:dahimail/core/api.dart';
import 'package:dahimail/core/people_list_row.dart';
import 'package:dahimail/core/session.dart';
import 'package:dahimail/core/theme.dart';
import 'package:dahimail/screens/friends_screens.dart';
import 'package:dahimail/screens/inbox_screens.dart';
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  setUp(() {
    SharedPreferences.setMockInitialValues({});
    Api.clearCache();
  });

  testWidgets(
      'people rows adapt to narrow screens, large text and dark theme; actions stay separate',
      (tester) async {
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);
    var opened = 0, selected = 0, accepted = 0;
    for (final width in [280.0, 390.0, 768.0]) {
      for (final scale in [1.0, 1.8]) {
        for (final dark in [false, true]) {
          tester.view.physicalSize = Size(width, 900);
          tester.view.devicePixelRatio = 1;
          await tester.pumpWidget(MaterialApp(
              theme: dark ? AppTheme.dark() : AppTheme.light(),
              builder: (context, child) => MediaQuery(
                  data: MediaQuery.of(context)
                      .copyWith(textScaler: TextScaler.linear(scale)),
                  child: child!),
              home: Scaffold(
                  body: ListView(children: [
                PeopleListRow(
                    avatar:
                        const PeopleAvatar(name: 'Alex Morgan', online: true),
                    name: 'Alex Morgan with a long display name',
                    preview:
                        'A long preview that wraps naturally without clipping the row.',
                    time: 'Yesterday',
                    unreadCount: 126,
                    previewLeading:
                        const Icon(Icons.done_all_rounded, size: 16),
                    indicators: const [
                      Icon(Icons.push_pin_rounded),
                      Icon(Icons.notifications_off_outlined)
                    ],
                    onTap: () => opened++,
                    onLongPress: () => selected++),
                PeopleListRow(
                    avatar: const PeopleAvatar(name: 'Project Team'),
                    name: 'Project Team',
                    subject: 'Schedule for the upcoming product release',
                    time: '2h ago',
                    unread: true,
                    preview:
                        'Please review the latest update before the meeting.',
                    footer: const Text('Product'),
                    onTap: () => opened++),
                PeopleListRow(
                    avatar: const PeopleAvatar(name: 'Sam Rivera'),
                    name: 'Sam Rivera',
                    preview: 'Wants to be your friend',
                    onTap: () => opened++,
                    actions: [
                      FilledButton(
                          onPressed: () => accepted++,
                          child: const Text('Accept')),
                      OutlinedButton(
                          onPressed: () {}, child: const Text('Reject')),
                      TextButton(onPressed: () {}, child: const Text('Block')),
                    ]),
                PeopleListRow(
                    avatar: const PeopleAvatar(name: 'Jamie Wilson'),
                    name: 'Jamie Wilson',
                    preview: 'jamie.wilson@example.test',
                    unreadCount: 3,
                    trailing: Row(mainAxisSize: MainAxisSize.min, children: [
                      IconButton.filledTonal(
                          onPressed: () {},
                          icon: const Icon(Icons.chat_bubble_outline_rounded)),
                      PopupMenuButton<String>(
                          itemBuilder: (_) => const [
                                PopupMenuItem(
                                    value: 'email', child: Text('Email'))
                              ]),
                    ])),
              ]))));
          await tester.pumpAndSettle();
          expect(tester.takeException(), isNull,
              reason: '$width / $scale / dark=$dark');
          expect(find.text('99+'), findsOneWidget);
          expect(find.text('AM'), findsOneWidget);
          final avatar = find.byType(PeopleAvatar).first;
          expect(tester.getSize(avatar), const Size(50, 50));
          final name = find.text('Alex Morgan with a long display name');
          expect(tester.getTopLeft(avatar).dx,
              lessThan(tester.getTopLeft(name).dx));
          if (scale == 1) {
            expect(tester.getSize(find.byType(PeopleListRow).first).height,
                lessThan(105));
          }
          await tester.tap(name);
          await tester.longPress(name);
          await tester.ensureVisible(find.text('Accept'));
          final before = opened;
          await tester.tap(find.text('Accept'));
          await tester.pumpAndSettle();
          expect(opened, before,
              reason: 'The request action must not also open the row');
        }
      }
    }
    expect(opened, 12);
    expect(selected, 12);
    expect(accepted, 12);
    await tester.pumpWidget(const SizedBox.shrink());
  });

  for (final screen in ['Chats', 'Email', 'Friends']) {
    testWidgets(
        '$screen uses the new people rows and retains list selection/actions',
        (tester) async {
      await (FontLoader('Inter')
            ..addFont(rootBundle.load('assets/fonts/Inter-Variable.ttf')))
          .load();
      await (FontLoader('MaterialIcons')
            ..addFont(rootBundle.load('fonts/MaterialIcons-Regular.otf')))
          .load();
      tester.view.physicalSize = const Size(390, 780);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);
      final session = Session()
        ..token = ''
        ..user = {'id': 1};
      final boundary = GlobalKey();
      const names = [
        'Alex Morgan',
        'Sam Rivera',
        'Taylor Kim',
        'Jordan Lee',
        'Casey Chen',
        'Jamie Wilson'
      ];
      final people = [
        for (var i = 0; i < names.length; i++)
          {
            'id': i + 2,
            'kind': 'friend',
            'name': names[i],
            'email': '${names[i].split(' ').first.toLowerCase()}@example.test',
            'preview': [
              'Are we still meeting this afternoon?',
              'Thanks! See you tomorrow.',
              'The photos look great.'
            ][i % 3],
            'unread': i == 0 ? 3 : 0,
            'time': ['10:42', '09:18', 'Yesterday'][i % 3],
            'online': i == 0,
            'status': 'read',
            'since': '2025',
            'at': '2026-10-16T10:${50 - i}:00Z',
          }
      ];
      await http.runWithClient(() async {
        await tester.pumpWidget(ChangeNotifierProvider.value(
            value: session,
            child: MaterialApp(
                theme: AppTheme.light(),
                home: RepaintBoundary(
                    key: boundary,
                    child: Scaffold(
                        appBar: AppBar(title: Text(screen)),
                        body: screen == 'Friends'
                            ? const FriendsPage()
                            : ConversationsPage(
                                socialOnly: screen == 'Chats',
                                channel:
                                    screen == 'Email' ? 'email' : 'all'))))));
        await tester.pumpAndSettle();
        expect(tester.takeException(), isNull);
        expect(find.text('Alex Morgan'), findsOneWidget);
        expect(find.byType(PeopleListRow), findsWidgets);
        if (screen == 'Friends') {
          expect(find.byTooltip('Chat'), findsWidgets);
          expect(find.text('3'), findsOneWidget);
          await tester.tap(find.byTooltip('More actions for Alex Morgan'));
          await tester.pumpAndSettle();
          expect(find.text('Email'), findsWidgets);
          expect(find.text('Audio call'), findsOneWidget);
          await tester.tapAt(const Offset(12, 160));
          await tester.pumpAndSettle();
        } else {
          final firstRow = find.byType(PeopleListRow).first;
          expect(tester.widget<PeopleListRow>(firstRow).selected, false);
          await tester.longPress(find.text('Alex Morgan'));
          await tester.pumpAndSettle();
          expect(
              tester
                  .widget<PeopleListRow>(find.byType(PeopleListRow).first)
                  .selected,
              true);
          await tester.tap(find.text('Alex Morgan'));
          await tester.pumpAndSettle();
          expect(
              tester
                  .widget<PeopleListRow>(find.byType(PeopleListRow).first)
                  .selected,
              false);
        }
        final dir = Platform.environment['PEOPLE_PREVIEW_DIR'];
        if (dir != null) {
          await tester.runAsync(() async {
            final render = boundary.currentContext!.findRenderObject()!
                as RenderRepaintBoundary;
            final image = await render.toImage(pixelRatio: 2);
            final bytes =
                await image.toByteData(format: ui.ImageByteFormat.png);
            await Directory(dir).create(recursive: true);
            await File('$dir/${screen.toLowerCase()}.png')
                .writeAsBytes(bytes!.buffer.asUint8List());
            image.dispose();
          });
        }
        await tester.pumpWidget(const SizedBox.shrink());
        await tester.pumpAndSettle();
      },
          () => MockClient((request) async {
                final path = request.url.path;
                if (path.endsWith('friends/chats')) {
                  return http.Response(
                      jsonEncode({
                        'data': people,
                        'features': {'chat': true}
                      }),
                      200);
                }
                if (path.endsWith('/friends')) {
                  return http.Response(
                      jsonEncode({
                        'data': {
                          'friends': people,
                          'features': {'chat': true, 'calls': true}
                        }
                      }),
                      200);
                }
                if (path.endsWith('inbox/conversations')) {
                  return http.Response(
                      jsonEncode({
                        'data': screen == 'Chats'
                            ? []
                            : [
                                for (var i = 0; i < people.length; i++)
                                  {
                                    'id': i + 20,
                                    'contact_name': names[i],
                                    'channel': 'email',
                                    'subject': [
                                      'Project update',
                                      'Weekend plans',
                                      'Design review'
                                    ][i % 3],
                                    'preview':
                                        'Here is the latest update. Let me know what you think.',
                                    'is_unread': i == 0,
                                    'time': people[i]['time'],
                                    'is_starred': i == 1
                                  },
                              ],
                        'has_more': false
                      }),
                      200);
                }
                return http.Response('{"data":[]}', 200);
              }));
      session.dispose();
    });
  }
}
