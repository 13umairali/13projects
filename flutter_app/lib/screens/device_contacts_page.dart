import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../core/api.dart';
import '../core/device_contacts_sync.dart';
import '../core/config.dart';
import '../core/people_list_row.dart';
import '../core/theme.dart';
import '../core/util.dart';
import '../core/widgets.dart';
import 'person_profile_page.dart';
import 'friends_screens.dart';

class DeviceContactsPage extends StatefulWidget {
  const DeviceContactsPage({super.key});
  @override
  State<DeviceContactsPage> createState() => _DeviceContactsPageState();
}

class _DeviceContactsPageState extends State<DeviceContactsPage> {
  final _syncer = DeviceContactsSync.I;
  final _sending = <int>{};
  final _search = TextEditingController();
  String _q = '';
  List<Map<String, dynamic>> get _contacts => _syncer.contacts;
  List<Map<String, dynamic>> get _matches => _syncer.matches;

  @override
  void initState() {
    super.initState();
    _syncer.addListener(_changed);
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      await _syncer.restore(Api.of(context).session);
      if (!mounted) return;
      _syncer.watch();
      if (_syncer.paused) return;
      // Saved contacts are already on screen; this only refreshes them when they are stale.
      await _syncer.enable(force: false);
    });
  }

  void _changed() { if (mounted) setState(() {}); }
  Future<void> _sync() => _syncer.sync(force: true);

  @override
  void dispose() {
    _syncer.removeListener(_changed);
    _search.dispose();
    super.dispose();
  }

  Future<void> _invite() async {
    try {
      await const MethodChannel('com.dahify.dahimail/share').invokeMethod('shareText', {'text': 'Join me on Dahimail to chat, email and call. Get started: ${AppConfig.baseUrl}/register'});
    } catch (_) { if (mounted) toast(context, 'Could not open sharing apps. Please try again.', error: true); }
  }

  Future<void> _add(Map<String, dynamic> user) async {
    final id = user['id'] as int;
    setState(() => _sending.add(id));
    final ok = await run(context, (api) => api.post('friends/requests', {'user_id': id}), ok: 'Friend request sent');
    if (mounted) setState(() { _sending.remove(id); if (ok) user['relation'] = 'pending'; });
  }

  void _showNumbers(Map<String, dynamic> contact) => showDialog<void>(
        context: context,
        builder: (c) => AlertDialog(
          title: Text('${contact['name']}'),
          content: Text((contact['phones'] as List).join('\n')),
          actions: [TextButton(onPressed: () => Navigator.pop(c), child: const Text('Close'))],
        ),
      );

  bool _hit(String q, Iterable<String> fields) => fields.any((f) => f.toLowerCase().contains(q));

  @override
  Widget build(BuildContext context) {
    final q = _q.trim().toLowerCase();
    final matchedKeys = _matches.map((u) => u['phone_key']).toSet();
    final invitations = _contacts.where((c) => !(c['keys'] as List).any(matchedKeys.contains)).toList();
    final matches = q.isEmpty ? _matches : _matches.where((u) => _hit(q, ['${u['name']}'])).toList();
    final invites = q.isEmpty ? invitations : invitations.where((c) => _hit(q, ['${c['name']}', ...(c['phones'] as List).cast<String>()])).toList();
    final needsAccess = !_syncer.allowed || _syncer.paused;

    return Scaffold(
      appBar: AppBar(title: const Text('Contacts'), actions: [
        IconButton(
          onPressed: () => pushPage(context, const AppPage(title: 'Who can find me', body: PhoneDiscoveryPage())),
          tooltip: 'Who can find me',
          icon: const Icon(Icons.tune_rounded),
        ),
      ]),
      body: RefreshIndicator(
        onRefresh: _sync,
        child: CustomScrollView(physics: const AlwaysScrollableScrollPhysics(), slivers: [
          SliverToBoxAdapter(child: _busyBar()),
          SliverPadding(
            padding: const EdgeInsets.fromLTRB(16, 4, 16, 0),
            sliver: SliverList(delegate: SliverChildListDelegate([
              if (_syncer.error != null) _notice(context, icon: Icons.sync_problem_rounded, title: _syncer.error!, action: 'Retry', onAction: _sync, tone: AppColors.warn),
              if (needsAccess)
                _notice(
                  context,
                  icon: Icons.contacts_rounded,
                  title: _syncer.paused ? 'Contact syncing is paused' : 'Find friends from your contacts',
                  subtitle: _syncer.paused ? 'Resume to see who is already on Dahimail.' : 'Allow access to see which of your contacts are already on Dahimail.',
                  action: _syncer.paused ? 'Resume' : 'Allow access',
                  onAction: _syncer.enable,
                ),
              _SearchField(controller: _search, onChanged: (v) => setState(() => _q = v), onClear: () => setState(() { _search.clear(); _q = ''; })),
              const SizedBox(height: 12),
              _StatRow(onApp: _matches.length, toInvite: invitations.length),
            ])),
          ),
          ..._group(
            context,
            title: 'On Dahimail',
            count: matches.length,
            empty: q.isNotEmpty ? 'No matching contacts on Dahimail.' : 'None of your contacts are on Dahimail yet.',
            emptyIcon: Icons.people_outline_rounded,
            builder: (i) {
              final user = matches[i];
              final id = user['id'] as int;
              return _ContactTile(
                avatar: PeopleAvatar(name: '${user['name']}', url: user['avatar_url'] as String?, radius: 25),
                name: '${user['name']}',
                onTap: () => pushPage(context, PersonProfilePage(userId: id)),
                trailing: _relationAction(context, user, id),
              );
            },
            length: matches.length,
          ),
          ..._group(
            context,
            title: 'Invite to Dahimail',
            count: invites.length,
            empty: q.isNotEmpty ? 'No matching contacts to invite.' : 'No contacts to invite.',
            emptyIcon: Icons.mail_outline_rounded,
            builder: (i) {
              final c = invites[i];
              final phones = (c['phones'] as List).cast<String>();
              return _ContactTile(
                avatar: PeopleAvatar(name: '${c['name']}', radius: 25),
                name: '${c['name']}',
                subtitle: phones.isEmpty ? '' : (phones.length > 1 ? '${phones.first}  +${phones.length - 1}' : phones.first),
                onTap: () => _showNumbers(c),
                trailing: OutlinedButton(
                  style: _pill(context, filled: false),
                  onPressed: _invite,
                  child: const Text('Invite'),
                ),
              );
            },
            length: invites.length,
          ),
          const SliverToBoxAdapter(child: SizedBox(height: 32)),
        ]),
      ),
    );
  }

  Widget _busyBar() => SizedBox(height: 2, child: _syncer.busy ? const LinearProgressIndicator(minHeight: 2) : null);

  /// Friends / Pending are quiet status pills; everybody else gets one clear "Add" button.
  Widget _relationAction(BuildContext context, Map<String, dynamic> user, int id) {
    switch ('${user['relation']}') {
      case 'accepted':
      case 'friend':
        return const _StatusPill('Friends', Icons.check_rounded, AppColors.success);
      case 'pending':
      case 'sent':
        return _StatusPill('Pending', Icons.schedule_rounded, AppColors.mutedOf(context));
      case 'received':
        return const _StatusPill('Wants to connect', Icons.mark_email_unread_outlined, AppColors.primary);
      default:
        final busy = _sending.contains(id);
        return FilledButton(
          style: _pill(context, filled: true),
          onPressed: busy ? null : () => _add(user),
          child: busy
              ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
              : const Row(mainAxisSize: MainAxisSize.min, children: [Icon(Icons.person_add_alt_1_rounded, size: 16), SizedBox(width: 6), Text('Add')]),
        );
    }
  }

  static ButtonStyle _pill(BuildContext context, {required bool filled}) => ButtonStyle(
        minimumSize: WidgetStateProperty.all(const Size(0, 36)),
        padding: WidgetStateProperty.all(const EdgeInsets.symmetric(horizontal: 14)),
        shape: WidgetStateProperty.all(const StadiumBorder()),
        textStyle: WidgetStateProperty.all(const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600)),
        side: filled ? null : WidgetStateProperty.all(BorderSide(color: AppColors.borderOf(context))),
        elevation: WidgetStateProperty.all(0),
      );

  /// One titled section rendered as a single grouped card: first row rounds the top, last row the bottom,
  /// hairline dividers between rows. Rows are built lazily, so thousands of contacts stay smooth.
  List<Widget> _group(
    BuildContext context, {
    required String title,
    required int count,
    required String empty,
    required IconData emptyIcon,
    required Widget Function(int index) builder,
    required int length,
  }) {
    return [
      SliverPadding(
        padding: const EdgeInsets.fromLTRB(20, 22, 20, 8),
        sliver: SliverToBoxAdapter(
          child: Row(children: [
            Expanded(child: Semantics(header: true, child: Text(title, style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700, color: AppColors.mutedOf(context))))),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(color: AppColors.softOf(context), borderRadius: BorderRadius.circular(999)),
              child: Text('$count', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: AppColors.mutedOf(context))),
            ),
          ]),
        ),
      ),
      if (length == 0)
        SliverPadding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          sliver: SliverToBoxAdapter(
            child: Container(
              padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
              decoration: cardDecoration(context),
              child: Column(children: [
                Icon(emptyIcon, size: 28, color: AppColors.mutedOf(context)),
                const SizedBox(height: 8),
                Text(empty, textAlign: TextAlign.center, style: TextStyle(fontSize: 13.5, color: AppColors.mutedOf(context))),
              ]),
            ),
          ),
        )
      else
        SliverPadding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          sliver: SliverList(
            delegate: SliverChildBuilderDelegate(
              (c, i) => _GroupCell(first: i == 0, last: i == length - 1, child: builder(i)),
              childCount: length,
            ),
          ),
        ),
    ];
  }

  Widget _notice(BuildContext context, {required IconData icon, required String title, String? subtitle, required String action, required VoidCallback onAction, Color tone = AppColors.primary}) {
    final scheme = Theme.of(context).colorScheme;
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: cardDecoration(context),
      child: Row(children: [
        Container(
          width: 42,
          height: 42,
          decoration: BoxDecoration(color: tone.withValues(alpha: .12), borderRadius: BorderRadius.circular(12)),
          child: Icon(icon, color: tone, size: 22),
        ),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(title, style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.w600, color: scheme.onSurface)),
          if (subtitle != null) Padding(padding: const EdgeInsets.only(top: 2), child: Text(subtitle, style: TextStyle(fontSize: 12.5, height: 1.35, color: AppColors.mutedOf(context)))),
        ])),
        const SizedBox(width: 10),
        FilledButton(style: _pill(context, filled: true), onPressed: onAction, child: Text(action)),
      ]),
    );
  }
}

class _GroupCell extends StatelessWidget {
  final bool first, last;
  final Widget child;
  const _GroupCell({required this.first, required this.last, required this.child});
  @override
  Widget build(BuildContext context) {
    final dark = AppColors.isDark(context);
    final line = dark ? AppColors.borderDark : AppColors.border.withValues(alpha: .8);
    const r = Radius.circular(16);
    return Container(
      decoration: BoxDecoration(
        color: dark ? AppColors.surfaceDark : Colors.white,
        borderRadius: BorderRadius.vertical(top: first ? r : Radius.zero, bottom: last ? r : Radius.zero),
        border: Border(
          left: BorderSide(color: line),
          right: BorderSide(color: line),
          top: first ? BorderSide(color: line) : BorderSide.none,
          bottom: BorderSide(color: line, width: last ? 1 : .6),
        ),
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.vertical(top: first ? r : Radius.zero, bottom: last ? r : Radius.zero),
        child: child,
      ),
    );
  }
}

class _ContactTile extends StatelessWidget {
  final Widget avatar, trailing;
  final String name, subtitle;
  final VoidCallback? onTap;
  const _ContactTile({required this.avatar, required this.trailing, required this.name, this.subtitle = '', this.onTap});
  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(14, 10, 14, 10),
          child: Row(children: [
            avatar,
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
              Text(name, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 15.5, height: 1.25, fontWeight: FontWeight.w600, color: scheme.onSurface)),
              if (subtitle.isNotEmpty) Padding(padding: const EdgeInsets.only(top: 2), child: Text(subtitle, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 13, color: AppColors.mutedOf(context)))),
            ])),
            const SizedBox(width: 10),
            trailing,
          ]),
        ),
      ),
    );
  }
}

class _StatusPill extends StatelessWidget {
  final String label;
  final IconData icon;
  final Color color;
  const _StatusPill(this.label, this.icon, this.color);
  @override
  Widget build(BuildContext context) {
    final dark = AppColors.isDark(context);
    final ink = color == AppColors.success && !dark ? AppColors.successText : color;
    return Container(
      padding: const EdgeInsets.fromLTRB(9, 6, 11, 6),
      decoration: BoxDecoration(color: color.withValues(alpha: dark ? .18 : .10), borderRadius: BorderRadius.circular(999)),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(icon, size: 14, color: ink),
        const SizedBox(width: 4),
        Text(label, style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: ink)),
      ]),
    );
  }
}

class _SearchField extends StatelessWidget {
  final TextEditingController controller;
  final ValueChanged<String> onChanged;
  final VoidCallback onClear;
  const _SearchField({required this.controller, required this.onChanged, required this.onClear});
  @override
  Widget build(BuildContext context) {
    final dark = AppColors.isDark(context);
    OutlineInputBorder border(Color c) => OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide(color: c));
    return TextField(
      controller: controller,
      onChanged: onChanged,
      textInputAction: TextInputAction.search,
      decoration: InputDecoration(
        hintText: 'Search contacts',
        isDense: true,
        filled: true,
        fillColor: dark ? AppColors.surfaceDark : Colors.white,
        contentPadding: const EdgeInsets.symmetric(vertical: 13),
        prefixIcon: const Icon(Icons.search_rounded, size: 21),
        suffixIcon: controller.text.isEmpty ? null : IconButton(tooltip: 'Clear', icon: const Icon(Icons.close_rounded, size: 19), onPressed: onClear),
        enabledBorder: border(AppColors.borderOf(context)),
        focusedBorder: border(Theme.of(context).colorScheme.primary),
        border: border(AppColors.borderOf(context)),
      ),
    );
  }
}

class _StatRow extends StatelessWidget {
  final int onApp, toInvite;
  const _StatRow({required this.onApp, required this.toInvite});
  @override
  Widget build(BuildContext context) => Row(children: [
        Expanded(child: _Stat(value: onApp, label: 'On Dahimail', icon: Icons.verified_user_outlined, color: AppColors.success)),
        const SizedBox(width: 10),
        Expanded(child: _Stat(value: toInvite, label: 'To invite', icon: Icons.person_add_alt_rounded, color: AppColors.primary)),
      ]);
}

class _Stat extends StatelessWidget {
  final int value;
  final String label;
  final IconData icon;
  final Color color;
  const _Stat({required this.value, required this.label, required this.icon, required this.color});
  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: cardDecoration(context),
        child: Row(children: [
          Container(
            width: 34,
            height: 34,
            decoration: BoxDecoration(color: color.withValues(alpha: .12), borderRadius: BorderRadius.circular(10)),
            child: Icon(icon, size: 18, color: color),
          ),
          const SizedBox(width: 10),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
            Text('$value', style: const TextStyle(fontSize: 17, height: 1.1, fontWeight: FontWeight.w700)),
            Text(label, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 12, color: AppColors.mutedOf(context))),
          ])),
        ]),
      );
}
