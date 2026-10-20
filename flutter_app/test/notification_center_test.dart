import 'dart:io';
import 'dart:ui' as ui;
import 'package:dahimail/core/notification_center_tile.dart';
import 'package:dahimail/core/theme.dart';
import 'package:dahimail/core/widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('compact internal cards retain sender/content and right-side time/avatar without brand or actions', (tester) async {
    await (FontLoader('Inter')..addFont(rootBundle.load('assets/fonts/Inter-Variable.ttf'))).load();
    await (FontLoader('MaterialIcons')..addFont(rootBundle.load('fonts/MaterialIcons-Regular.otf'))).load();
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);
    final boundary = GlobalKey();
    for (final width in [280.0, 390.0]) {
      for (final scale in [1.0, 1.7]) {
        for (final dark in [false, true]) {
          tester.view.physicalSize = Size(width, 780);
          tester.view.devicePixelRatio = 1;
          await tester.pumpWidget(MaterialApp(theme: dark ? AppTheme.dark() : AppTheme.light(), home:
            MediaQuery(data: MediaQueryData(size: Size(width, 780), textScaler: TextScaler.linear(scale)), child:
              RepaintBoundary(key: boundary, child: Scaffold(
                appBar: AppBar(title: const Text('Notifications'), actions: [TextButton(onPressed: () {}, child: const Text('Mark all read'))]),
                body: ListView(padding: const EdgeInsets.fromLTRB(6, 4, 6, 24), children: [
                  for (var i = 0; i < 12; i++) AppCard(padding: EdgeInsets.zero, margin: const EdgeInsets.only(bottom: 6),
                    child: NotificationCenterTile(notification: {
                      'type': ['email_received', 'chat', 'friend_request', 'meeting_updated'][i % 4],
                      'sender_name': 'Ali', 'body': 'Re: Project update', 'created_at': '9 hours ago', 'read': i > 2,
                    }, onTap: () {})),
                ]),
              )),
            ),
          ));
          await tester.pumpAndSettle();
          expect(tester.takeException(), isNull, reason: '$width / $scale / dark=$dark');
          expect(find.byType(Image), findsNothing);
          expect(find.text('VIEW'), findsNothing);
          expect(find.text('REPLY'), findsNothing);
          expect(find.byIcon(Icons.mail_rounded), findsWidgets);
          final tile = find.byType(NotificationCenterTile).first;
          final sender = find.descendant(of: tile, matching: find.text('Ali'));
          final avatar = find.descendant(of: tile, matching: find.byType(Avatar));
          final time = find.descendant(of: tile, matching: find.text('9h ago'));
          // Balanced: time is on the sender's line just left of the photo, and the icon, text block and photo
          // all share the card's vertical centre.
          expect(tester.getTopRight(time).dx, lessThan(tester.getTopLeft(avatar).dx));
          expect((tester.getCenter(time).dy - tester.getCenter(sender).dy).abs(), lessThan(8));
          expect((tester.getCenter(avatar).dy - tester.getCenter(tile).dy).abs(), lessThan(2));
          expect(tester.getCenter(avatar).dx, greaterThan(tester.getCenter(sender).dx));
          if (width == 390 && scale == 1) {
            expect(tester.getSize(tile).height, lessThan(90));
            final visible = find.byType(NotificationCenterTile).evaluate().where((e) {
              final box = e.renderObject! as RenderBox;
              return box.localToGlobal(Offset.zero).dy + box.size.height <= 780;
            }).length;
            expect(visible, greaterThanOrEqualTo(8));
            final output = Platform.environment['NOTIFICATION_PREVIEW_DIR'];
            if (output != null && !dark) {
              await tester.runAsync(() async {
                final render = boundary.currentContext!.findRenderObject()! as RenderRepaintBoundary;
                final image = await render.toImage(pixelRatio: 2);
                final bytes = await image.toByteData(format: ui.ImageByteFormat.png);
                await Directory(output).create(recursive: true);
                await File('$output/internal-notifications.png').writeAsBytes(bytes!.buffer.asUint8List());
                image.dispose();
              });
            }
          }
        }
      }
    }
    await tester.pumpWidget(const SizedBox.shrink());
  });

  testWidgets('internal card preserves content and opens on a body tap', (tester) async {
    var opened = false;
    await tester.pumpWidget(MaterialApp(home: Scaffold(body: NotificationCenterTile(
      notification: const {'type': 'email_received', 'sender_name': 'Alexandra Morgan',
        'sender_avatar': '', 'body': 'Full original content preview', 'read': false}, onTap: () => opened = true,
    ))));
    expect(find.text('Alexandra Morgan'), findsOneWidget);
    expect(find.text('Full original content preview'), findsOneWidget);
    expect(tester.widget<Avatar>(find.byType(Avatar)).radius, 20);
    await tester.tap(find.text('Full original content preview'));
    expect(opened, isTrue);
  });
}
