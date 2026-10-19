import 'profile_avatar.dart';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../core/api.dart';
import '../core/room_entry.dart';
import '../core/calls.dart';
import '../core/paged.dart';
import '../core/theme.dart';
import '../core/widgets.dart';

/// WhatsApp-style Call Screen: Search, All/Missed tabs, detailed call logs with audio/video buttons, and New Call FAB.
class CallHistoryPage extends StatefulWidget {
  final int? userId;
  const CallHistoryPage({super.key, this.userId});
  @override
  State<CallHistoryPage> createState() => _CallHistoryPageState();
}

class _CallHistoryPageState extends State<CallHistoryPage> {
  int _v = 0;
  String _filterTab = 'all'; // 'all' or 'missed'
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _markViewed();
  }

  Future<void> _markViewed() async {
    try {
      final list = Api.list(await Api.of(context).getNoCache('calls/history'));
      final prefs = await SharedPreferences.getInstance();
      final viewed = prefs.getStringList('viewed_calls') ?? <String>[];
      for (final c in list) {
        if (c['id'] != null && !viewed.contains('${c['id']}')) {
          viewed.add('${c['id']}');
        }
      }
      await prefs.setStringList('viewed_calls', viewed);
    } catch (_) {}
  }

  Future<void> _startNewCall() async {
    List<Map<String, dynamic>> people = [];
    try {
      people = Api.list(await Api.of(context).get('friends/forward-targets'));
    } catch (_) {}
    if (!mounted) return;
    String q = '';
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
              height: MediaQuery.sizeOf(c).height * 0.7,
              child: Column(children: [
                const Padding(
                  padding: EdgeInsets.fromLTRB(16, 0, 16, 8),
                  child: Align(alignment: Alignment.centerLeft, child: Text('New call', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 18))),
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
                      : ListView(children: [
                          for (final p in list)
                            ListTile(
                              leading: ProfileAvatar(p, radius: 22),
                              title: Text('${p['name']}', style: const TextStyle(fontWeight: FontWeight.w600)),
                              trailing: Row(mainAxisSize: MainAxisSize.min, children: [
                                IconButton(
                                  tooltip: 'Audio call',
                                  icon: const Icon(Icons.call_rounded, color: AppColors.primary),
                                  onPressed: () => Navigator.pop(c, {'peer': p, 'video': false}),
                                ),
                                IconButton(
                                  tooltip: 'Video call',
                                  icon: const Icon(Icons.videocam_rounded, color: AppColors.primary),
                                  onPressed: () => Navigator.pop(c, {'peer': p, 'video': true}),
                                ),
                              ]),
                              onTap: () => Navigator.pop(c, {'peer': p, 'video': false}),
                            ),
                        ]),
                ),
              ]),
            ),
          );
        },
      ),
    );
    if (chosen == null || !mounted) return;
    final peer = chosen['peer'] as Map<String, dynamic>;
    final video = chosen['video'] == true;
    final peerId = (peer['id'] as num).toInt();
    if (video) {
      CallManager.I.startVideoCall(context, peerId, '${peer['name']}');
    } else {
      CallManager.I.startCall(context, peerId);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(
          title: const Text('Calls'),
          actions: [
            IconButton(
              tooltip: 'Search calls',
              icon: const Icon(Icons.search_rounded),
              onPressed: () async {
                final q = await showSearch(context: context, delegate: CallSearchDelegate());
                if (q != null) setState(() => _searchQuery = q);
              },
            ),
            PopupMenuButton<String>(
              onSelected: (v) async {
                if (v == 'clear') {
                  final ok = await confirmDialog(context, 'Clear call history?', 'All calls will be removed from this list.', action: 'Clear', danger: true);
                  if (ok && mounted && await run(context, (a) => a.delete('calls/history'), ok: '*')) {
                    final prefs = await SharedPreferences.getInstance();
                    await prefs.remove('viewed_calls');
                    setState(() => _v++);
                  }
                }
                if (v == 'refresh') setState(() => _v++);
              },
              itemBuilder: (_) => [
                const PopupMenuItem(value: 'refresh', child: Text('Refresh')),
                if (widget.userId == null) const PopupMenuItem(value: 'clear', child: Text('Clear call history')),
              ],
            ),
          ],
        ),
        floatingActionButton: FloatingActionButton(
          tooltip: 'Start call',
          onPressed: _startNewCall,
          child: const Icon(Icons.add_call),
        ),
        body: Column(children: [
          const OngoingRooms(),
          // WhatsApp style filter tabs (All / Missed)
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 4),
            child: Row(children: [
              ChoiceChip(
                label: const Text('All'),
                selected: _filterTab == 'all',
                onSelected: (_) => setState(() => _filterTab = 'all'),
              ),
              const SizedBox(width: 8),
              ChoiceChip(
                label: const Text('Missed'),
                selected: _filterTab == 'missed',
                onSelected: (_) => setState(() => _filterTab = 'missed'),
              ),
              if (_searchQuery.isNotEmpty) ...[
                const Spacer(),
                Chip(
                  label: Text('Search: $_searchQuery'),
                  onDeleted: () => setState(() => _searchQuery = ''),
                ),
              ],
            ]),
          ),
          const Divider(height: 1),
          Expanded(
            child: AsyncView<List<Item>>(
              key: ValueKey('$_v:$_filterTab:$_searchQuery'),
              load: (api) async {
                var calls = Api.list(await api.getNoCache('calls/history'));
                if (widget.userId != null) {
                  calls = calls.where((c) => c['peer']?['id'] == widget.userId).toList();
                }
                if (_filterTab == 'missed') {
                  calls = calls.where((c) => c['outcome'] != 'answered' && c['outgoing'] != true).toList();
                }
                if (_searchQuery.isNotEmpty) {
                  final q = _searchQuery.toLowerCase();
                  calls = calls.where((c) {
                    final name = '${c['peer']?['name'] ?? ''}'.toLowerCase();
                    return name.contains(q);
                  }).toList();
                }
                return calls;
              },
              builder: (c, calls, reload) => calls.isEmpty
                  ? const EmptyState(icon: Icons.phone_missed_outlined, text: 'No call history.')
                  : RefreshIndicator(
                      onRefresh: reload,
                      child: ListView.separated(
                        itemCount: calls.length,
                        separatorBuilder: (_, __) => const Divider(height: 1),
                        itemBuilder: (_, i) {
                          final x = calls[i];
                          final peer = Map<String, dynamic>.from(x['peer'] as Map? ?? {});
                          final out = x['outgoing'] == true;
                          final outcome = '${x['outcome']}';
                          final video = x['video'] == true;
                          final missed = outcome != 'answered' && !out;
                          
                          // WhatsApp style subtitle & icon representation
                          final statusText = outcome == 'answered'
                              ? ('${x['duration_text'] ?? ''}'.isEmpty ? 'Answered' : '${x['duration_text']}')
                              : (outcome == 'declined' ? 'Declined' : (out ? 'No answer' : 'Missed'));
                          
                          return ListTile(
                            leading: ProfileAvatar(peer, radius: 24),
                            title: Text(
                              '${peer['name'] ?? 'Unknown'}',
                              style: TextStyle(fontWeight: FontWeight.w700, color: missed ? AppColors.danger : null),
                            ),
                            subtitle: Row(children: [
                              Icon(
                                out ? Icons.call_made_rounded : (missed ? Icons.call_missed_rounded : Icons.call_received_rounded),
                                size: 16,
                                color: missed ? AppColors.danger : AppColors.primary,
                              ),
                              const SizedBox(width: 4),
                              Icon(
                                video ? Icons.videocam_rounded : Icons.call_rounded,
                                size: 14,
                                color: AppColors.muted,
                              ),
                              const SizedBox(width: 4),
                              Expanded(
                                child: Text(
                                  '$statusText · ${x['when'] ?? ''}',
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(fontSize: 12.5),
                                ),
                              ),
                            ]),
                            trailing: Row(mainAxisSize: MainAxisSize.min, children: [
                              IconButton(
                                tooltip: 'Audio call',
                                icon: const Icon(Icons.call_rounded, color: AppColors.primary),
                                onPressed: () => CallManager.I.startCall(c, (peer['id'] as num).toInt()),
                              ),
                              IconButton(
                                tooltip: 'Video call',
                                icon: const Icon(Icons.videocam_rounded, color: AppColors.primary),
                                onPressed: () => CallManager.I.startVideoCall(c, (peer['id'] as num).toInt(), '${peer['name']}'),
                              ),
                            ]),
                          );
                        },
                      ),
                    ),
            ),
          ),
        ]),
      );
}

class CallSearchDelegate extends SearchDelegate<String?> {
  @override
  List<Widget>? buildActions(BuildContext context) => [
        if (query.isNotEmpty)
          IconButton(icon: const Icon(Icons.clear), onPressed: () => query = ''),
      ];

  @override
  Widget? buildLeading(BuildContext context) => IconButton(
        icon: const Icon(Icons.arrow_back),
        onPressed: () => close(context, null),
      );

  @override
  Widget buildResults(BuildContext context) {
    close(context, query);
    return Container();
  }

  @override
  Widget buildSuggestions(BuildContext context) {
    return Center(child: Text('Search calls for "$query"', style: const TextStyle(color: AppColors.muted)));
  }
}
