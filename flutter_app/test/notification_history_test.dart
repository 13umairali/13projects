import 'dart:convert';
import 'package:dahimail/core/api.dart';
import 'package:dahimail/core/paged.dart';
import 'package:dahimail/core/refresh.dart';
import 'package:dahimail/core/session.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  setUp(() async {
    SharedPreferences.setMockInitialValues({});
    await Api.wipeCache();
  });
  tearDown(Api.wipeCache);

  testWidgets('notification history appends every older page and refresh cannot discard scrolled rows', (tester) async {
    final session = Session()..token = ''..user = {'id': 1};
    final pages = <int>[];
    await http.runWithClient(() async {
      await tester.pumpWidget(ChangeNotifierProvider.value(value: session, child: MaterialApp(home: PagedList(
        endpoint: 'notifications', pauseRefreshWhileScrolling: true,
        itemBuilder: (_, row, __) => SizedBox(height: 72, child: Text('History ${row['id']}')),
      ))));
      await tester.pumpAndSettle();
      expect(pages, [1]);
      expect(find.text('History 1'), findsOneWidget);
      await tester.scrollUntilVisible(find.text('History 45'), 500, maxScrolls: 20);
      await tester.pumpAndSettle();
      expect(pages, [1, 2, 3]);
      expect(find.text('History 45'), findsOneWidget);
      final list = tester.widget<ListView>(find.byType(ListView));
      final position = list.controller!.position.pixels;
      expect(tester.state<PagedListState>(find.byType(PagedList)).isAtTop, isFalse);
      AppRefresh.bump();
      await tester.pumpAndSettle();
      expect(pages, [1, 2, 3]);
      expect(list.controller!.position.pixels, position);
      expect(find.text('History 45'), findsOneWidget);
      expect(find.byType(CircularProgressIndicator), findsNothing);
      list.controller!.jumpTo(0);
      AppRefresh.bump();
      await tester.pumpAndSettle();
      expect(pages, [1, 2, 3, 1]);
      expect(find.text('History 1'), findsOneWidget);
      expect(tester.takeException(), isNull);
      await tester.pumpWidget(const SizedBox.shrink());
    }, () => MockClient((request) async {
      expect(request.url.path, endsWith('/notifications'));
      final page = int.parse(request.url.queryParameters['page']!);
      pages.add(page);
      return http.Response(jsonEncode({
        'notifications': [for (var i = (page - 1) * 20 + 1; i <= page * 20 && i <= 45; i++) {'id': i, 'read': i > 20}],
        'meta': {'current_page': page, 'last_page': 3},
      }), 200);
    }));
    session.dispose();
  });
}
