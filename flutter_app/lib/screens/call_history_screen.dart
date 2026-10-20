import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/api.dart';
import '../core/call_badge.dart';
import '../core/calls.dart';
import '../core/paged.dart';
import '../core/room_entry.dart';
import '../core/session.dart';
import '../core/theme.dart';
import '../core/widgets.dart';
import 'profile_avatar.dart';

/// Calls: All / Missed filters, one card per call, a single call-back action on the right
/// and a "New call" button at the bottom-right (standard FAB position).
class CallHistoryPage extends StatefulWidget {
  final int? userId;
  const CallHistoryPage({super.key, this.userId});
  @override
  State<CallHistoryPage> createState() => _CallHistoryPageState();
}

class _CallHistoryPageState extends State<CallHistoryPage> {
  String _filter = 'all'; // all | missed
  String _query = '';
  bool _searching = false;
  final _search = TextEditingController();
  int _v = 0;
  int _markedUpTo = -1;

  @override
  void dispose() {
    _search.dispose();
    super.dispose();
  }

  /// The user is looking at the list: everything up to the newest call is now "seen".
  void _markSeen(List<Item> all) {
    if (widget.userId != null) return; // a single person's history is only a filtered view
    var newest = 0;
    for (final c in all) {
      final i = c['id'] is num ? (c['id'] as num).toInt() : int.tryParse('${c['id']}') ?? 0;
      if (i > newest) newest = i;
    }
    if (newest == _markedUpTo && CallBadge.unseen.value == 0) return;
    _markedUpTo = newest;
    final uid = context.read<Session>().userId;
    WidgetsBinding.instance.addPostFrameCallback((_) => CallBadge.markSeen(all, uid));
  }

  Future<void> _startNewCall() async {
    final api = Api.of(context);
    // Saved list first (instant); refreshed in the background.
    List<Item> people = Api.list(Api.peek('friends/forward-targets'));
    if (people.isEmpty) {
      try {
        people = Api.list(await api.get('friends/forward-targets'));
      } catch (_) {}
    } else {
      api.get('friends/forward-targets').then<void>((_) {}, onError: (_) {});
    }
    if (!mounted) return;
    var q = '';
    final chosen = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      showDragHandle: true,
      builder: (c) => StatefulBuilder(
        builder: (c, setS) {
          final list = q.isEmpty ? people : people.where((p) => '${p['name']}'.toLowerCase().contains(q.toLowerCase())).toList();
          return Padding(
            padding: EdgeInsets.only(bottom: MediaQuery.of(c).viewInsets.bottom),
            child: SizedBox(
              height: MediaQuery.sizeOf(c).height * 0.72,
              child: Column(children: [
                const Padding(
                  padding: EdgeInsets.fromLTRB(20, 0, 20, 10),
                  child: Align(alignment: Alignment.centerLeft, child: Text('New call', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 18))),
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: TextField(
                    decoration: const InputDecoration(hintText: 'Search friends and teammates', prefixIcon: Icon(Icons.search_rounded)),
                    onChanged: (v) => setS(() => q = v.trim()),
                  ),
                ),
                const SizedBox(height: 8),
                Expanded(
                  child: list.isEmpty
                      ? const Center(child: Text('No contacts found.', style: TextStyle(color: AppColors.muted)))
                      : ListView.builder(
                          padding: const EdgeInsets.fromLTRB(8, 0, 8, 16),
                          itemCount: list.length,
                          itemBuilder: (_, i) {
                            final p = list[i];
                            return ListTile(
                              leading: ProfileAvatar(p, radius: 22),
                              title: Text('${p['name']}', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w600)),
                              trailing: Row(mainAxisSize: MainAxisSize.min, children: [
                                _RoundAction(icon: Icons.call_outlined, tooltip: 'Audio call', onTap: () => Navigator.pop(c, {'peer': p, 'video': false})),
                                const SizedBox(width: 8),
                                _RoundAction(icon: Icons.videocam_outlined, tooltip: 'Video call', onTap: () => Navigator.pop(c, {'peer': p, 'video': true})),
                              ]),
                              onTap: () => Navigator.pop(c, {'peer': p, 'video': false}),
                            );
                          },
                        ),
                ),
              ]),
            ),
          );
        },
      ),
    );
    if (chosen == null || !mounted) return;
    final peer = chosen['peer'] as Map<String, dynamic>;
    final peerId = (peer['id'] as num).toInt();
    if (chosen['video'] == true) {
      CallManager.I.startVideoCall(context, peerId, '${peer['name']}', avatar: peer['avatar_url'] as String?);
    } else {
      CallManager.I.startCall(context, peerId, name: '${peer['name']}', avatar: peer['avatar_url'] as String?);
    }
  }

  List<Item> _visible(List<Item> all) {
    var calls = all;
    if (widget.userId != null) calls = calls.where((c) => (c['peer'] is Map ? c['peer']['id'] : null) == widget.userId).toList();
    if (_filter == 'missed') calls = calls.where(CallBadge.isMissed).toList();
    if (_query.isNotEmpty) {
      final q = _query.toLowerCase();
      calls = calls.where((c) => '${c['peer'] is Map ? c['peer']['name'] : ''}'.toLowerCase().contains(q)).toList();
    }
    return calls;
  }

  PreferredSizeWidget _appBar() => AppBar(
        title: _searching
            ? TextField(
                controller: _search,
                autofocus: true,
                textInputAction: TextInputAction.search,
                decoration: const InputDecoration(hintText: 'Search calls', border: InputBorder.none, enabledBorder: InputBorder.none, focusedBorder: InputBorder.none, filled: false, contentPadding: EdgeInsets.zero),
                onChanged: (v) => setState(() => _query = v.trim()),
              )
            : const Text('Calls'),
        actions: [
          IconButton(
            tooltip: _searching ? 'Close search' : 'Search calls',
            icon: Icon(_searching ? Icons.close_rounded : Icons.search_rounded),
            onPressed: () => setState(() {
              _searching = !_searching;
              if (!_searching) {
                _query = '';
                _search.clear();
              }
            }),
          ),
          PopupMenuButton<String>(
            tooltip: 'More',
            icon: const Icon(Icons.more_vert_rounded),
            onSelected: (v) async {
              if (v == 'refresh') setState(() => _v++);
              if (v == 'clear') {
                final ok = await confirmDialog(context, 'Clear call history?', 'All calls will be removed from this list.', action: 'Clear', danger: true);
                if (ok && mounted && await run(context, (a) => a.delete('calls/history'), ok: '*')) {
                  CallBadge.unseen.value = 0;
                  setState(() => _v++);
                }
              }
            },
            itemBuilder: (_) => [
              const PopupMenuItem(value: 'refresh', child: Text('Refresh')),
              if (widget.userId == null) const PopupMenuItem(value: 'clear', child: Text('Clear call history')),
            ],
          ),
        ],
      );

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: _appBar(),
        floatingActionButton: FloatingActionButton.extended(
          heroTag: null,
          tooltip: 'New call',
          onPressed: _startNewCall,
          icon: const Icon(Icons.add_call),
          label: const Text('New call'),
        ),
        body: Column(children: [
          const OngoingRooms(),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: Row(children: [
              _FilterChip(label: 'All', selected: _filter == 'all', onTap: () => setState(() => _filter = 'all')),
              const SizedBox(width: 8),
              _FilterChip(label: 'Missed', selected: _filter == 'missed', onTap: () => setState(() => _filter = 'missed')),
            ]),
          ),
          Expanded(
            // The raw history is saved on the phone: coming back paints it instantly and refreshes quietly.
            child: AsyncView<List<Item>>(
              key: ValueKey('calls:$_v'),
              cacheKey: 'calls/history',
              load: (api) async => Api.list(await api.get('calls/history')),
              builder: (c, all, reload) {
                _markSeen(all);
                final calls = _visible(all);
                if (calls.isEmpty) {
                  return EmptyState(
                    icon: _filter == 'missed' ? Icons.phone_missed_outlined : Icons.call_outlined,
                    text: _query.isNotEmpty ? 'No calls match "$_query".' : (_filter == 'missed' ? 'No missed calls.' : 'No calls yet.\nTap New call to get started.'),
                  );
                }
                return RefreshIndicator(
                  onRefresh: reload,
                  child: ListView.separated(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(16, 4, 16, 96),
                    itemCount: calls.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                    itemBuilder: (_, i) => _CallCard(call: calls[i]),
                  ),
                );
              },
            ),
          ),
        ]),
      );
}

class _FilterChip extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;
  const _FilterChip({required this.label, required this.selected, required this.onTap});
  @override
  Widget build(BuildContext context) => ChoiceChip(
        label: Text(label),
        selected: selected,
        showCheckmark: false,
        onSelected: (_) => onTap(),
        visualDensity: VisualDensity.compact,
      );
}

/// 40 px circular tonal icon button used for call actions.
class _RoundAction extends StatelessWidget {
  final IconData icon;
  final String tooltip;
  final VoidCallback onTap;
  const _RoundAction({required this.icon, required this.tooltip, required this.onTap});
  @override
  Widget build(BuildContext context) => Tooltip(
        message: tooltip,
        child: Material(
          color: Theme.of(context).colorScheme.primary.withValues(alpha: 0.10),
          shape: const CircleBorder(),
          child: InkWell(
            customBorder: const CircleBorder(),
            onTap: onTap,
            child: SizedBox(width: 40, height: 40, child: Icon(icon, size: 20, color: Theme.of(context).colorScheme.primary)),
          ),
        ),
      );
}

class _CallCard extends StatelessWidget {
  final Item call;
  const _CallCard({required this.call});

  @override
  Widget build(BuildContext context) {
    final peer = Map<String, dynamic>.from(call['peer'] as Map? ?? {});
    final out = call['outgoing'] == true;
    final outcome = '${call['outcome']}'.toLowerCase();
    final video = call['video'] == true;
    final missed = CallBadge.isMissed(call);
    final peerId = peer['id'] is num ? (peer['id'] as num).toInt() : null;

    final status = outcome == 'answered'
        ? ('${call['duration_text'] ?? ''}'.isEmpty ? 'Answered' : '${call['duration_text']}')
        : (outcome == 'declined' ? 'Declined' : (out ? 'No answer' : 'Missed'));
    final dirIcon = out ? Icons.call_made_rounded : (missed ? Icons.call_missed_rounded : Icons.call_received_rounded);
    final dirColor = missed ? AppColors.danger : (outcome == 'answered' ? AppColors.successText : AppColors.muted);
    final when = '${call['when'] ?? ''}';

    void callBack() {
      if (peerId == null) return;
      if (video) {
        CallManager.I.startVideoCall(context, peerId, '${peer['name']}', avatar: peer['avatar_url'] as String?);
      } else {
        CallManager.I.startCall(context, peerId, name: '${peer['name']}', avatar: peer['avatar_url'] as String?);
      }
    }

    return Container(
      decoration: cardDecoration(context),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(Rad.l),
        child: InkWell(
          borderRadius: BorderRadius.circular(Rad.l),
          onTap: callBack,
          child: Padding(
            padding: const EdgeInsets.fromLTRB(12, 10, 12, 10),
            child: Row(children: [
              ProfileAvatar(peer, radius: 22),
              const SizedBox(width: 12),
              Expanded(
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(
                    '${peer['name'] ?? 'Unknown'}',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(fontSize: 15.5, fontWeight: FontWeight.w600, color: missed ? AppColors.danger : null),
                  ),
                  const SizedBox(height: 3),
                  Row(children: [
                    Icon(dirIcon, size: 15, color: dirColor),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        when.isEmpty ? status : '$status · $when',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 12.5, color: AppColors.muted),
                      ),
                    ),
                  ]),
                ]),
              ),
              const SizedBox(width: 8),
              // One action, always on the right: calls back the same way (audio or video).
              _RoundAction(
                icon: video ? Icons.videocam_outlined : Icons.call_outlined,
                tooltip: video ? 'Video call' : 'Audio call',
                onTap: callBack,
              ),
            ]),
          ),
        ),
      ),
    );
  }
}
