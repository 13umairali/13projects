import 'dart:convert';
import 'dart:io';
import 'dart:ui' as ui;
import 'package:dahimail/core/api.dart';
import 'package:dahimail/core/brand.dart';
import 'package:dahimail/core/config.dart';
import 'package:dahimail/core/people_card.dart';
import 'package:dahimail/core/session.dart';
import 'package:dahimail/core/theme.dart';
import 'package:dahimail/screens/contacts_screens.dart';
import 'package:dahimail/screens/login_screen.dart';
import 'package:dahimail/screens/people_screens.dart';
import 'package:dahimail/screens/person_profile_page.dart';
import 'package:dahimail/screens/register_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

Future<void> preview(WidgetTester tester, GlobalKey key, String name) async {
  final directory = Platform.environment['BRANDING_PREVIEW_DIR'];
  if (directory == null) return;
  await tester.runAsync(() async {
    final render =
        key.currentContext!.findRenderObject()! as RenderRepaintBoundary;
    final picture = await render.toImage(pixelRatio: 2);
    final bytes = await picture.toByteData(format: ui.ImageByteFormat.png);
    await Directory(directory).create(recursive: true);
    await File('$directory/$name.png')
        .writeAsBytes(bytes!.buffer.asUint8List());
    picture.dispose();
  });
}

void main() {
  setUp(() {
    SharedPreferences.setMockInitialValues({});
    Api.clearCache();
    PeoplePage.filter.value = 'all';
  });
  setUpAll(() async {
    await (FontLoader('Inter')
          ..addFont(rootBundle.load('assets/fonts/Inter-Variable.ttf')))
        .load();
    await (FontLoader('MaterialIcons')
          ..addFont(rootBundle.load('fonts/MaterialIcons-Regular.otf')))
        .load();
  });
  for (final registration in [false, true]) {
    testWidgets(
        '${registration ? 'Registration' : 'Login'} fits narrow/tablet, large text, keyboard and both themes',
        (tester) async {
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);
      final session = Session();
      final boundary = GlobalKey();
      await http.runWithClient(() async {
        for (final width in [320.0, 390.0, 800.0]) {
          for (final scale in [1.0, 1.7]) {
            for (final dark in [false, true]) {
              for (final keyboard in [0.0, 260.0]) {
                tester.view.physicalSize = Size(width, 780);
                tester.view.devicePixelRatio = 1;
                await tester.pumpWidget(ChangeNotifierProvider.value(
                    value: session,
                    child: MaterialApp(
                        theme: dark ? AppTheme.dark() : AppTheme.light(),
                        builder: (context, child) => MediaQuery(
                            data: MediaQuery.of(context).copyWith(
                                textScaler: TextScaler.linear(scale),
                                viewInsets: EdgeInsets.only(bottom: keyboard)),
                            child: child!),
                        home: RepaintBoundary(
                            key: boundary,
                            child: registration
                                ? const RegisterScreen()
                                : const LoginScreen()))));
                await tester.pumpAndSettle();
                await tester.runAsync(() => precacheImage(
                    const AssetImage(Brand.logoAsset),
                    tester.element(find.byType(BrandLogo))));
                await tester.pumpAndSettle();
                expect(
                    tester
                        .widget<RawImage>(find.descendant(
                            of: find.byType(BrandLogo),
                            matching: find.byType(RawImage)))
                        .image,
                    isNotNull);
                expect(find.byType(BrandLogo), findsOneWidget);
                final logo = tester.widget<Image>(find.descendant(
                    of: find.byType(BrandLogo), matching: find.byType(Image)));
                expect((logo.image as AssetImage).assetName, Brand.logoAsset);
                expect(logo.fit, BoxFit.contain);
                if (width == 390 && scale == 1 && !dark && keyboard == 0) {
                  await preview(tester, boundary,
                      registration ? 'registration' : 'login');
                }
                final submit = find.widgetWithText(
                    FilledButton, registration ? 'Create account' : 'Sign in');
                await tester.ensureVisible(submit);
                await tester.pumpAndSettle();
                expect(tester.getBottomRight(submit).dy,
                    lessThanOrEqualTo(780 - keyboard));
                expect(tester.takeException(), isNull,
                    reason:
                        '$width / $scale / dark=$dark / keyboard=$keyboard');
                await tester.pumpWidget(const SizedBox.shrink());
              }
            }
          }
        }
      },
          () => MockClient((_) async =>
              http.Response('{"data":{"registration":"optional"}}', 200)));
      session.dispose();
    });
  }
  testWidgets(
      'login preserves domain, password visibility and two-factor submission',
      (tester) async {
    final session = Session();
    final requests = <Map>[];
    await http.runWithClient(() async {
      await tester.pumpWidget(ChangeNotifierProvider.value(
          value: session,
          child:
              MaterialApp(theme: AppTheme.light(), home: const LoginScreen())));
      await tester.pumpAndSettle();
      await tester.enterText(find.byType(TextField).at(0), 'Alex');
      await tester.enterText(find.byType(TextField).at(1), 'test-password');
      await tester.tap(find.byTooltip('Show or hide password'));
      await tester.pump();
      expect(tester.widget<TextField>(find.byType(TextField).at(1)).obscureText,
          false);
      await tester.ensureVisible(find.text('Sign in'));
      await tester.tap(find.text('Sign in'));
      await tester.pumpAndSettle();
      expect(find.byType(TextField), findsNWidgets(3));
      await tester.ensureVisible(find.byType(TextField).at(2));
      await tester.enterText(find.byType(TextField).at(2), '123456');
      await tester.ensureVisible(find.text('Sign in'));
      await tester.tap(find.text('Sign in'));
      await tester.pumpAndSettle();
      expect(requests.first['email'], 'alex@${AppConfig.mailDomain}');
      expect(requests.last['password'], 'test-password');
      expect(requests.last['two_factor_code'], '123456');
      expect(tester.takeException(), isNull);
      await tester.pumpWidget(const SizedBox.shrink());
    },
        () => MockClient((r) async {
              requests.add(jsonDecode(r.body) as Map);
              return http.Response('{"two_factor_required":true}', 422);
            }));
    session.dispose();
  });
  testWidgets(
      'four people filters share Everyone cards and preserve profile/contact routing and selection',
      (tester) async {
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);
    addTearDown(() => PeoplePage.filter.value = 'all');
    final session = Session()
      ..token = ''
      ..user = {'id': 1};
    final boundary = GlobalKey();
    final data = {
      'data': {
        'friends': [
          {
            'id': 2,
            'name': 'Alex Morgan',
            'email': 'alex@example.test',
            'unread': 2,
            'online': true
          },
          {'id': 3, 'name': 'Sam Rivera', 'email': 'sam@example.test'}
        ],
        'team': [
          {
            'id': 2,
            'name': 'Alex Morgan',
            'email': 'alex@example.test',
            'role': 'Admin',
            'is_friend': true
          },
          {
            'id': 4,
            'name': 'Taylor Kim',
            'role': 'Agent',
            'last_active': 'today'
          }
        ],
        'incoming': [],
        'features': {'chat': true, 'calls': true}
      }
    };
    await http.runWithClient(() async {
      for (final width in [320.0, 390.0]) {
        for (final scale in [1.0, 1.7]) {
          tester.view.physicalSize = Size(width, 780);
          tester.view.devicePixelRatio = 1;
          await tester.pumpWidget(ChangeNotifierProvider.value(
              value: session,
              child: MaterialApp(
                  theme: scale == 1 ? AppTheme.light() : AppTheme.dark(),
                  builder: (c, child) => MediaQuery(
                      data: MediaQuery.of(c)
                          .copyWith(textScaler: TextScaler.linear(scale)),
                      child: child!),
                  home: RepaintBoundary(
                      key: boundary,
                      child: const Scaffold(body: PeoplePage())))));
          await tester.pumpAndSettle();
          for (final filter in ['all', 'friends', 'team', 'customers']) {
            PeoplePage.filter.value = filter;
            await tester.pumpAndSettle();
            expect(find.byType(PeopleCard), findsWidgets);
            if (filter != 'customers') {
              expect(find.text('Alex Morgan'),
                  findsOneWidget); // Deduplicated friend + teammate.
              for (final label in ['Chat', 'Audio call', 'Video call']) {
                expect(find.byTooltip(label), findsWidgets);
              }
            } else {
              expect(find.text('Acme Customer'), findsOneWidget);
              await tester.longPress(find.text('Acme Customer'));
              await tester.pumpAndSettle();
              expect(find.text('1 selected'), findsOneWidget);
              await tester.tap(find.text('Acme Customer'));
              await tester.pumpAndSettle();
              expect(find.text('1 selected'), findsNothing);
            }
            if (width == 390 && scale == 1) {
              await preview(tester, boundary, filter);
            }
            expect(tester.takeException(), isNull,
                reason: '$filter / $width / $scale');
          }
          await tester.pumpWidget(const SizedBox.shrink());
        }
      }
      PeoplePage.filter.value = 'friends';
      await tester.pumpWidget(ChangeNotifierProvider.value(
          value: session,
          child: MaterialApp(
              theme: AppTheme.light(),
              home: const Scaffold(body: PeoplePage()))));
      await tester.pumpAndSettle();
      await tester.tap(find.text('Alex Morgan'));
      await tester.pumpAndSettle();
      expect(find.byType(PersonProfilePage), findsOneWidget);
      await tester.pageBack();
      await tester.pumpAndSettle();
      PeoplePage.filter.value = 'customers';
      await tester.pumpAndSettle();
      await tester.tap(find.text('Acme Customer'));
      await tester.pumpAndSettle();
      expect(find.byType(ContactDetailPage), findsOneWidget);
      await tester.pumpWidget(const SizedBox.shrink());
    },
        () => MockClient((r) async {
              final path = r.url.path;
              if (path.endsWith('/people')) {
                return http.Response(jsonEncode(data), 200);
              }
              if (path.endsWith('/contacts')) {
                return http.Response(
                    '{"data":[{"id":61,"full_name":"Acme Customer","email":"hello@example.test","company":"Acme","status":"customer"}],"has_more":false}',
                    200);
              }
              if (path.endsWith('/contacts/61')) {
                return http.Response(
                    '{"data":{"id":61,"full_name":"Acme Customer","email":"hello@example.test"}}',
                    200);
              }
              if (path.endsWith('/people/2')) {
                return http.Response(
                    '{"data":{"id":2,"name":"Alex Morgan","is_friend":true}}',
                    200);
              }
              return http.Response('{"data":[]}', 200);
            }));
    session.dispose();
  });
}
