import '../core/utility_notice.dart';
import 'dart:async';
import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_webrtc/flutter_webrtc.dart';
import '../core/api.dart';
import '../core/call_audio.dart';
import '../core/calls.dart';
import '../core/notify.dart';
import '../core/android_callkit.dart';
import '../core/incoming_call_view.dart';
import '../core/callkit.dart';
import '../core/meeting_engine.dart';
import '../core/room_host.dart';
import '../core/room_media_view.dart';
import '../core/theme.dart';
import '../core/util.dart';
import '../core/widgets.dart';

/// The meeting room: preview → (waiting room) → video grid with controls.
/// Used for meetings and for 1:1 video calls (autoJoin: no preview, you are in at once).
class MeetingRoomPage extends StatefulWidget {
  final String code;
  final int? answerCallId;
  final String? title;
  final bool autoJoin;
  final bool audioOnly; // audio call: the camera is not asked for; it can be switched on later
  const MeetingRoomPage({super.key, required this.code, this.answerCallId, this.title, this.autoJoin = false, this.audioOnly = false});
  @override
  State<MeetingRoomPage> createState() => _MeetingRoomPageState();
}

class _MeetingRoomPageState extends State<MeetingRoomPage> {
  late final MeetingEngine _e;
  bool _prejoinMic = true, _prejoinCam = true, _started = false;
  final _chatText = TextEditingController();
  final _chatScroll = ScrollController();
  Timer? _tick;
  bool _warned5 = false, _warned0 = false, _leaveDialogOpen = false, _minimizing = false, _closing = false;

  @override
  void initState() {
    super.initState();
    final host = RoomHost.I;
    _e = host.obtain(Api.of(context), widget.code, widget.title, widget.audioOnly);
    host.minimized = false;
    host.setPageOpen(true);
    host.addListener(_onPresentationChange);
    CallManager.I.inRoom = true;
    _e.addListener(_onChange);
    _tick = Timer.periodic(const Duration(seconds: 1), (_) {
      if (!mounted || _e.phase != 'in') return;
      _checkTime();
      setState(() {});
    });
    if (_e.phase != 'idle') {
      _started = true; // back from the small window: the call kept running, nothing to join again
    } else if (widget.autoJoin) {
      _started = true;
      _e.join(audio: true, video: !widget.audioOnly, answerCallId: widget.answerCallId);
    } else {
      _e.startPreview(wantVideo: true);
    }
  }

  void _onPresentationChange() { if (mounted) setState(() {}); }

  void _onChange() {
    if (!mounted) return;
    if (_e.phase == 'ended' && !_e.terminationPending && _leaveDialogOpen) {
      _leaveDialogOpen = false;
      Navigator.of(context, rootNavigator: true).pop();
    }
    setState(() {});
    if (_e.phase == 'ended' && !_e.terminationPending && !_closing) {
      _closing = true;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted || _minimizing) return;
        final noticeContext = Navigator.of(context, rootNavigator: true).context;
        final route = ModalRoute.of(context);
        Navigator.of(context).popUntil((candidate) => candidate == route || candidate.isFirst);
        Navigator.of(context).pop();
        if (_e.message != null) UtilityNotice.show(noticeContext, _e.message!);
      });
    }
    if (_e.chatOpen) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (_chatScroll.hasClients) _chatScroll.jumpTo(_chatScroll.position.maxScrollExtent);
      });
    }
  }

  @override
  void dispose() {
    _tick?.cancel();
    _e.removeListener(_onChange);
    RoomHost.I.removeListener(_onPresentationChange);
    RoomHost.I.setPageOpen(false);
    // Minimized: the call keeps running in the small window. Anything else: close it.
    if (!(RoomHost.I.minimized && (_e.phase == 'in' || _e.phase == 'waiting' || _e.phase == 'joining'))) RoomHost.I.closeRoom();
    _chatText.dispose();
    _chatScroll.dispose();
    super.dispose();
  }

  // ───────────── time left / elapsed ─────────────
  int? get _leftSeconds {
    if (_e.meeting['kind'] == 'call') return null;
    final ends = DateTime.tryParse('${_e.meeting['ends_at'] ?? ''}');
    return ends?.difference(DateTime.now()).inSeconds;
  }

  String _fmt(int s) {
    s = s.abs();
    final h = s ~/ 3600, m = (s % 3600) ~/ 60, x = s % 60;
    return h > 0 ? '$h:${m.toString().padLeft(2, '0')}:${x.toString().padLeft(2, '0')}' : '$m:${x.toString().padLeft(2, '0')}';
  }

  void _checkTime() {
    final l = _leftSeconds;
    if (l == null) return;
    if (l <= 300 && l > 0 && !_warned5) {
      _warned5 = true;
      toast(context, '5 minutes left in this meeting.');
    }
    if (l <= 0 && !_warned0) {
      _warned0 = true;
      toast(context, _e.isHost ? 'Scheduled time is over. Host menu → Extend to keep going.' : 'The scheduled time is over.');
    }
  }

  String get _inviteText => '${_e.title}\nJoin: ${_e.url}${'${_e.meeting['pretty'] ?? ''}'.isNotEmpty ? '\nCode: ${_e.meeting['pretty']}' : ''}';

  // ───────────── leave ─────────────
  Future<void> _leaveFlow() async {
    if (_e.phase == 'ended' || _e.phase == 'error' || _e.phase == 'idle') {
      if (mounted) Navigator.of(context).pop();
      return;
    }
    if (_leaveDialogOpen) return;
    if (_e.isHost && _e.meeting['kind'] == 'meeting' && _e.participants.length > 2 && _e.phase == 'in') {
      _leaveDialogOpen = true;
      final choice = await showDialog<String>(
        context: context,
        builder: (c) => SimpleDialog(title: const Text('Leave the meeting?'), children: [
          SimpleDialogOption(onPressed: () => Navigator.pop(c, 'leave'), child: const Text('Close meeting for me')),
          SimpleDialogOption(onPressed: () => Navigator.pop(c, 'end'), child: const Text('Close meeting for everyone', style: TextStyle(color: AppColors.danger, fontWeight: FontWeight.w700))),
          SimpleDialogOption(onPressed: () => Navigator.pop(c), child: const Text('Cancel')),
        ]),
      );
      _leaveDialogOpen = false;
      if (choice == null || !mounted || _e.phase == 'ended') return;
      if (choice == 'end') await _e.endForAll();
      if (choice == 'leave') await _e.leave();
    } else {
      await _e.leave();
    }
    // The engine's terminal notification returns to the previous page once.
  }

  // Back button / gesture: while you are in the call it shrinks to the small window (it does NOT hang up).
  void _backPressed() {
    if (_e.phase == 'in' || _e.phase == 'waiting' || _e.phase == 'joining') {
      _minimize();
    } else {
      _leaveFlow();
    }
  }

  void _minimize() {
    if (_minimizing || !mounted) return;
    RoomHost.I.minimize();
    setState(() => _minimizing = true);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) Navigator.of(context).pop();
    });
  }

  /// The red End button: always visible, always works.
  Future<void> _endPressed() async {
    await _leaveFlow();
  }

  // ───────────── build ─────────────
  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: _minimizing || _e.phase == 'ended' || _e.phase == 'error' || _e.phase == 'idle',
      onPopInvokedWithResult: (didPop, _) {
        if (!didPop) _backPressed();
      },
      child: Scaffold(
        backgroundColor: const Color(0xFF111118),
        body: LayoutBuilder(builder: (context, box) {
          // Android resizes before its PiP callback reaches Dart. Both paths must
          // render only media, with no full-size controls or self-view overlays.
          if (RoomHost.I.inPip || box.maxWidth < 300 || box.maxHeight < 280) return RoomMediaView(engine: _e);
          return SafeArea(child: _body());
        }),
      ),
    );
  }

  Widget _body() {
    switch (_e.phase) {
      case 'joining':
        return _room();
      case 'waiting':
        return _center(Icons.hourglass_top_rounded, 'Waiting for the host to let you in…', 'You will join automatically.', action: 'Leave', onAction: _leaveFlow);
      case 'in':
        return _room();
      case 'ended':
        return _room();
      case 'error':
        return _center(Icons.error_outline_rounded, _e.message ?? 'Could not join.', '', action: 'Close', onAction: () => Navigator.of(context).pop());
      default:
        return _prejoin();
    }
  }

  Widget _center(IconData icon, String title, String sub, {required String action, required VoidCallback onAction}) => Center(
        child: Padding(
          padding: const EdgeInsets.all(28),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            Icon(icon, color: Colors.white70, size: 54),
            const SizedBox(height: 16),
            Text(title, textAlign: TextAlign.center, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w700)),
            if (sub.isNotEmpty) ...[const SizedBox(height: 8), Text(sub, textAlign: TextAlign.center, style: const TextStyle(color: Colors.white60))],
            const SizedBox(height: 22),
            FilledButton(onPressed: onAction, child: Text(action)),
          ]),
        ),
      );

  // ───────────── before joining ─────────────
  Widget _prejoin() {
    final hasPreview = _e.hasVideoTrack && _prejoinCam;
    return Column(children: [
      Align(alignment: Alignment.centerLeft, child: IconButton(icon: const Icon(Icons.arrow_back_rounded, color: Colors.white), onPressed: _leaveFlow)),
      Text(widget.title ?? 'Join meeting', style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w700)),
      const SizedBox(height: 14),
      Expanded(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 6),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(18),
            child: Container(
              color: const Color(0xFF22222C),
              child: hasPreview
                  ? RTCVideoView(_e.localRenderer, mirror: true, objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover)
                  : const Center(child: Icon(Icons.videocam_off_rounded, color: Colors.white38, size: 56)),
            ),
          ),
        ),
      ),
      if (_e.message != null) Padding(padding: const EdgeInsets.fromLTRB(22, 8, 22, 0), child: Text(_e.message!, textAlign: TextAlign.center, style: const TextStyle(color: Colors.amber, fontSize: 13))),
      const SizedBox(height: 12),
      Row(mainAxisAlignment: MainAxisAlignment.center, children: [
        _round(_prejoinMic ? Icons.mic_rounded : Icons.mic_off_rounded, () => setState(() => _prejoinMic = !_prejoinMic), off: !_prejoinMic, label: 'Mic'),
        const SizedBox(width: 18),
        _round(_prejoinCam ? Icons.videocam_rounded : Icons.videocam_off_rounded, () => setState(() => _prejoinCam = !_prejoinCam), off: !_prejoinCam, label: 'Camera'),
      ]),
      Padding(
        padding: const EdgeInsets.fromLTRB(22, 18, 22, 22),
        child: SizedBox(
          width: double.infinity,
          child: FilledButton.icon(
            icon: const Icon(Icons.login_rounded),
            label: const Padding(padding: EdgeInsets.symmetric(vertical: 12), child: Text('Join now', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700))),
            onPressed: _started && _e.phase != 'idle' ? null : () {
              _started = true;
              _e.join(audio: _prejoinMic, video: _prejoinCam);
            },
          ),
        ),
      ),
    ]);
  }

  // ───────────── in the room ─────────────
  Widget _room() {
    final list = _e.participants;
    final modBanner = _e.isMod && _e.waiting.isNotEmpty;
    return Stack(children: [
      Positioned.fill(child: Padding(padding: EdgeInsets.fromLTRB(6, 56, 6, 96), child: _grid(list))),
      // recording sign: shown whenever the server says a recording runs (the admin cannot hide it completely)
      if (_e.recBanner != null)
        Positioned(
          top: 52, left: 12, right: 12,
          child: Center(child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(color: const Color(0xFFB91C1C), borderRadius: BorderRadius.circular(20)),
            child: Text(_e.recBanner!, textAlign: TextAlign.center, style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600, decoration: TextDecoration.none)),
          )),
        ),
      // top bar
      Positioned(
        left: 0,
        right: 0,
        top: 0,
        child: Container(
          height: 52,
          padding: const EdgeInsets.symmetric(horizontal: 8),
          child: Row(children: [
            IconButton(tooltip: 'Minimize', icon: const Icon(Icons.keyboard_arrow_down_rounded, color: Colors.white, size: 30), onPressed: _minimize),
            Expanded(child: Column(mainAxisAlignment: MainAxisAlignment.center, crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text((_e.meeting['kind'] == 'call' && widget.title != null && widget.title!.isNotEmpty) ? widget.title! : _e.title, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 15)),
              Row(children: [
                if (_e.recRunning && _e.recUi['icon'] == true)
                  Container(
                    margin: const EdgeInsets.only(right: 8),
                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 1),
                    decoration: BoxDecoration(color: const Color(0xFFDC2626), borderRadius: BorderRadius.circular(10)),
                    child: Text('● REC ${_fmt(_e.recElapsed)}', style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w800)),
                  ),
                Text(_e.isCall && _e.phase == 'in' ? _e.callProgress : _e.phase == 'joining' ? 'Connecting…' : _e.phase == 'ended' ? 'Disconnecting…' : '${list.length} in the ${_e.roomLabel}${_e.locked ? '  ·  🔒' : ''}', style: const TextStyle(color: Colors.white60, fontSize: 12)),
                if (_leftSeconds != null) Text('   ${_leftSeconds! > 0 ? '⏳ ${_fmt(_leftSeconds!)} left' : 'Over time +${_fmt(_leftSeconds!)}'}', style: TextStyle(color: _leftSeconds! <= 0 ? Colors.redAccent : (_leftSeconds! <= 300 ? Colors.amber : Colors.white60), fontSize: 12, fontWeight: FontWeight.w600)),
              ]),
            ])),
            if (_e.canInvite) IconButton(tooltip: 'Add people', icon: const Icon(Icons.person_add_alt_1_rounded, color: Colors.white), onPressed: _addPeopleSheet),
            if (_e.url.isNotEmpty)
              IconButton(
                tooltip: 'Share invitation',
                icon: const Icon(Icons.ios_share_rounded, color: Colors.white),
                onPressed: () {
                  Clipboard.setData(ClipboardData(text: _inviteText));
                  toast(context, 'Invitation copied – paste it in any app to share');
                },
              ),
          ]),
        ),
      ),
      if (modBanner)
        Positioned(
          left: 12,
          right: 12,
          top: 56,
          child: Material(
            color: AppColors.primary,
            borderRadius: BorderRadius.circular(12),
            child: InkWell(
              borderRadius: BorderRadius.circular(12),
              onTap: _participantsSheet,
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                child: Row(children: [
                  const Icon(Icons.person_add_alt_1_rounded, color: Colors.white, size: 20),
                  const SizedBox(width: 10),
                  Expanded(child: Text(_e.waiting.length == 1 ? '${_e.waiting.first['name']} wants to join' : '${_e.waiting.length} people want to join', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600))),
                  const Text('Review', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                ]),
              ),
            ),
          ),
        ),
      Positioned(left: 0, right: 0, bottom: 0, child: _controls()),
    ]);
  }

  Widget _grid(List<MJson> list) {
    if (list.isEmpty) return const SizedBox();
    if (list.length == 1) {
      return Stack(children: [
        Positioned.fill(child: _tile(list.first)),
        Positioned(left: 0, right: 0, bottom: 14, child: Center(child: Text(_e.isCall ? _e.callProgress : 'Waiting for others to join…  Share the invite link', style: TextStyle(color: Colors.white70, fontSize: 13)))),
      ]);
    }
    if (list.length == 2) {
      final other = list.firstWhere((p) => p['is_me'] != true, orElse: () => list.last);
      final me = list.firstWhere((p) => p['is_me'] == true, orElse: () => list.first);
      return Stack(children: [
        Positioned.fill(child: _tile(other)),
        Positioned(right: 8, bottom: 8, width: 104, height: 148, child: _tile(me, small: true)),
      ]);
    }
    final cols = list.length <= 4 ? 2 : 3;
    final rows = (list.length / cols).ceil();
    return LayoutBuilder(builder: (c, box) {
      final w = (box.maxWidth - (cols - 1) * 6) / cols;
      final h = math.max(90.0, (box.maxHeight - (rows - 1) * 6) / rows);
      return SingleChildScrollView(
        child: Wrap(spacing: 6, runSpacing: 6, children: [for (final p in list) SizedBox(width: w, height: h, child: _tile(p))]),
      );
    });
  }

  Widget _tile(MJson p, {bool small = false}) {
    final me = p['is_me'] == true;
    final pid = (p['pid'] as num).toInt();
    final peer = _e.peers[pid];
    final videoOn = me ? (_e.camOn && _e.hasVideoTrack) : (p['video'] == true && (peer?.hasVideo ?? false));
    final name = '${p['name']}${me ? ' (You)' : ''}';
    final audioOn = me ? _e.micOn : p['audio'] == true;
    final renderer = me ? _e.localRenderer : peer?.renderer;
    return ClipRRect(
      borderRadius: BorderRadius.circular(small ? 12 : 14),
      child: Container(
        color: const Color(0xFF22222C),
        child: Stack(fit: StackFit.expand, children: [
          if (videoOn && renderer != null && (me ? _e.rendererReady : peer!.rendererReady)) RTCVideoView(renderer, mirror: me && _e.frontCamera, objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover) else Center(child: Avatar('${p['name']}', url: p['avatar_url'] as String?, radius: small ? 24 : 40)),
          Positioned(
            left: 6,
            bottom: 6,
            right: 6,
            child: Row(children: [
              Flexible(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(color: Colors.black54, borderRadius: BorderRadius.circular(8)),
                  child: Row(mainAxisSize: MainAxisSize.min, children: [
                    if (!audioOn) const Padding(padding: EdgeInsets.only(right: 4), child: Icon(Icons.mic_off_rounded, color: Colors.redAccent, size: 14)),
                    Flexible(child: Text(small ? 'You' : name, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(color: Colors.white, fontSize: 12))),
                  ]),
                ),
              ),
            ]),
          ),
          if (p['hand'] == true) const Positioned(top: 6, right: 6, child: Text('✋', style: TextStyle(fontSize: 22))),
          if (p['sharing'] == true && !small) const Positioned(top: 6, left: 6, child: Icon(Icons.screen_share_rounded, color: Colors.white, size: 18)),
          if ((p['role'] == 'host' || p['role'] == 'cohost') && !small) Positioned(top: 6, left: p['sharing'] == true ? 30 : 6, child: Icon(Icons.shield_rounded, color: Colors.amber.shade300, size: 16)),
        ]),
      ),
    );
  }

  Widget _round(IconData icon, VoidCallback onTap, {bool off = false, bool danger = false, String? label, int badge = 0, bool active = false}) {
    final bg = danger ? AppColors.danger : (off ? Colors.white : (active ? AppColors.primary : const Color(0xFF2E2E3A)));
    final fg = danger ? Colors.white : (off ? Colors.black87 : Colors.white);
    return Column(mainAxisSize: MainAxisSize.min, children: [
      Stack(clipBehavior: Clip.none, children: [
        Material(
          color: bg,
          shape: const CircleBorder(),
          child: InkWell(customBorder: const CircleBorder(), onTap: onTap, child: Padding(padding: const EdgeInsets.all(13), child: Icon(icon, color: fg, size: 24))),
        ),
        if (badge > 0)
          Positioned(
            right: -2,
            top: -2,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
              decoration: BoxDecoration(color: AppColors.danger, borderRadius: BorderRadius.circular(10)),
              child: Text('$badge', style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700)),
            ),
          ),
      ]),
      if (label != null) Padding(padding: const EdgeInsets.only(top: 4), child: Text(label, style: const TextStyle(color: Colors.white70, fontSize: 11))),
    ]);
  }

  Widget _controls() {
    final isCall = _e.meeting['kind'] == 'call';

    void showMoreMenu() {
      showModalBottomSheet(
        context: context,
        showDragHandle: true,
        builder: (c) => SafeArea(
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const Padding(padding: EdgeInsets.all(16), child: Text('More options', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold))),
            if (_e.hasVideoTrack)
              ListTile(leading: const Icon(Icons.cameraswitch_rounded), title: const Text('Flip camera'), onTap: () { Navigator.pop(c); _e.switchCamera(); }),
            ListTile(
              leading: const Icon(Icons.people_alt_rounded),
              title: const Text('People'),
              trailing: _e.isMod && _e.waiting.isNotEmpty ? Chip(label: Text('${_e.waiting.length}'), backgroundColor: AppColors.danger, labelStyle: const TextStyle(color: Colors.white)) : null,
              onTap: () { Navigator.pop(c); _participantsSheet(); },
            ),
            ListTile(
              leading: const Icon(Icons.chat_bubble_rounded),
              title: const Text('Chat'),
              trailing: _e.unreadChat > 0 ? Chip(label: Text('${_e.unreadChat}'), backgroundColor: AppColors.primary, labelStyle: const TextStyle(color: Colors.white)) : null,
              onTap: () { Navigator.pop(c); _chatSheet(); },
            ),
            ListTile(
              leading: const Icon(Icons.back_hand_rounded),
              title: Text(_e.hand ? 'Lower hand' : 'Raise hand'),
              onTap: () { Navigator.pop(c); _e.toggleHand(); },
            ),
            if (_e.recCanStart || (_e.recSession != null && _e.recCanStop))
              ListTile(
                leading: Icon(Icons.fiber_manual_record_rounded, color: _e.recSession != null ? AppColors.danger : null),
                title: Text(_e.recSession == null ? (_e.recMode == 3 ? 'Ask to record' : _e.recMode == 5 ? 'Record (private)' : 'Record') : (_e.recSession!['status'] == 'pending' ? 'Cancel recording' : 'Stop recording')),
                onTap: () { Navigator.pop(c); _e.recToggle(); },
              ),
            if (_e.isMod)
              ListTile(leading: const Icon(Icons.more_horiz_rounded), title: const Text('Host controls'), onTap: () { Navigator.pop(c); _hostSheet(); }),
            ListTile(leading: const Icon(Icons.picture_in_picture_alt_rounded), title: const Text('Minimize'), onTap: () { Navigator.pop(c); _minimize(); }),
            const SizedBox(height: 16),
          ]),
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
      decoration: const BoxDecoration(gradient: LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter, colors: [Colors.transparent, Color(0xEE111118)])),
      child: Row(mainAxisAlignment: MainAxisAlignment.spaceEvenly, children: [
        _round(_e.micOn ? Icons.mic_rounded : Icons.mic_off_rounded, _e.toggleMic, off: !_e.micOn, label: _e.micOn ? 'Mute' : 'Unmute'),
        _round(_e.camOn ? Icons.videocam_rounded : Icons.videocam_off_rounded, _e.toggleCam, off: !_e.camOn, label: _e.camOn ? 'Video' : 'Video off'),
        _round(_e.audio.icon, () => showCallAudioPicker(context, _e.audio, _e.selectAudioOutput), active: _e.speaker, label: 'Audio'),
        _round(Icons.more_horiz_rounded, showMoreMenu, label: 'More'),
        _round(Icons.call_end_rounded, _endPressed, danger: true, label: isCall ? 'End' : 'Leave'),
      ]),
    );
  }

  // ───────────── sheets ─────────────
  void _sheet(Widget Function(BuildContext) build) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      showDragHandle: true,
      backgroundColor: AppColors.surfaceDark,
      builder: (c) => Theme(
        data: Theme.of(context).copyWith(
          brightness: Brightness.dark,
          colorScheme: ColorScheme.dark(
            surface: AppColors.surfaceDark,
            onSurface: Colors.white,
            primary: AppColors.primary,
          ),
        ),
        child: AnimatedBuilder(animation: _e, builder: (c2, _) => build(c2)),
      ),
    );
  }

  void _participantsSheet() => _sheet((c) {
        final h = MediaQuery.sizeOf(c).height * 0.7;
        return SizedBox(
          height: h,
          child: ListView(padding: const EdgeInsets.fromLTRB(8, 0, 8, 20), children: [
            if (_e.isMod && _e.waiting.isNotEmpty) ...[
              Padding(
                padding: const EdgeInsets.fromLTRB(12, 0, 6, 4),
                child: Row(children: [
                  Expanded(child: Text('Waiting to join (${_e.waiting.length})', style: const TextStyle(fontWeight: FontWeight.w700))),
                  if (_e.waiting.length > 1) TextButton(onPressed: () => _host('admit_all'), child: const Text('Admit all')),
                ]),
              ),
              for (final w in _e.waiting)
                ListTile(
                  leading: Avatar('${w['name']}', radius: 18),
                  title: Text('${w['name']}${w['guest'] == true ? '  (guest)' : ''}'),
                  trailing: Row(mainAxisSize: MainAxisSize.min, children: [
                    TextButton(onPressed: () => _host('deny', (w['pid'] as num).toInt()), child: const Text('Deny', style: TextStyle(color: AppColors.danger))),
                    FilledButton(style: FilledButton.styleFrom(minimumSize: const Size(72, 40), padding: const EdgeInsets.symmetric(horizontal: 18), tapTargetSize: MaterialTapTargetSize.shrinkWrap), onPressed: () => _host('admit', (w['pid'] as num).toInt()), child: const Text('Admit')),
                  ]),
                ),
              const Divider(),
            ],
            Padding(padding: const EdgeInsets.fromLTRB(12, 4, 6, 4), child: Text('In the meeting (${_e.participants.length})', style: const TextStyle(fontWeight: FontWeight.w700))),
            for (final p in _e.participants) _personRow(p),
          ]),
        );
      });

  Widget _personRow(MJson p) {
    final me = p['is_me'] == true;
    final pid = (p['pid'] as num).toInt();
    final role = '${p['role']}';
    return ListTile(
      leading: Avatar('${p['name']}', url: p['avatar_url'] as String?, radius: 18),
      title: Text('${p['name']}${me ? ' (You)' : ''}'),
      subtitle: Text(role == 'host' ? 'Host' : (role == 'cohost' ? 'Co-host' : 'Participant')),
      trailing: Row(mainAxisSize: MainAxisSize.min, children: [
        if (p['hand'] == true) const Text('✋ '),
        Icon((me ? _e.micOn : p['audio'] == true) ? Icons.mic_rounded : Icons.mic_off_rounded, size: 19, color: AppColors.muted),
        Icon((me ? _e.camOn : p['video'] == true) ? Icons.videocam_rounded : Icons.videocam_off_rounded, size: 19, color: AppColors.muted),
        if (_e.isMod && !me && role != 'host')
          PopupMenuButton<String>(
            onSelected: (v) => _host(v, pid),
            itemBuilder: (_) => [
              const PopupMenuItem(value: 'mute', child: Text('Mute')),
              if (p['hand'] == true) const PopupMenuItem(value: 'lower_hand', child: Text('Lower hand')),
              if (_e.isHost) PopupMenuItem(value: role == 'cohost' ? 'uncohost' : 'cohost', child: Text(role == 'cohost' ? 'Remove co-host' : 'Make co-host')),
              const PopupMenuItem(value: 'remove', child: Text('Remove from meeting', style: TextStyle(color: AppColors.danger))),
            ],
          ),
      ]),
    );
  }

  Future<void> _host(String action, [int? target]) async {
    if (action == 'remove') {
      final ok = await confirmDialog(context, 'Remove this person?', 'They are taken out of the meeting and cannot come back unless you let them in again.', action: 'Remove', danger: true);
      if (!ok) return;
    }
    final msg = await _e.hostAction(action, target);
    if (msg != null && mounted && action != 'lower_hand') toast(context, msg);
  }

  void _hostSheet() => _sheet((c) => SafeArea(
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          if (_e.meeting['kind'] != 'call') ListTile(leading: const Icon(Icons.more_time_rounded), title: const Text('Extend by 30 minutes'), onTap: () async {
            Navigator.pop(c);
            final msg = await _e.extend(30);
            _warned5 = false;
            _warned0 = false;
            if (msg != null && mounted) toast(context, msg);
          }),
          ListTile(leading: const Icon(Icons.mic_off_rounded), title: const Text('Mute everyone'), onTap: () { Navigator.pop(c); _host('mute_all'); }),
          ListTile(leading: Icon(_e.locked ? Icons.lock_open_rounded : Icons.lock_rounded), title: Text(_e.locked ? 'Unlock meeting' : 'Lock meeting (nobody new can join)'), onTap: () { Navigator.pop(c); _host(_e.locked ? 'unlock' : 'lock'); }),
          if (_e.isHost && _e.meeting['kind'] == 'meeting' && _e.participants.length > 2) ListTile(leading: const Icon(Icons.call_end_rounded, color: AppColors.danger), title: const Text('Close meeting for everyone', style: TextStyle(color: AppColors.danger)), onTap: () async {
            Navigator.pop(c);
            if (_leaveDialogOpen || _e.phase != 'in') return;
            _leaveDialogOpen = true;
            final ok = await confirmDialog(context, 'End the meeting?', 'Everybody is disconnected.', action: 'End for all', danger: true);
            _leaveDialogOpen = false;
            if (ok && mounted && _e.phase == 'in') {
              await _e.endForAll();
              // The engine's terminal notification returns to the previous page once.
            }
          }),
          const SizedBox(height: 8),
        ]),
      ));

  /// Friends and teammates, online ones first. "Add" rings them; Accept brings them into this room.
  void _addPeopleSheet() {
    _e.loadInvitable();
    _sheet((c) {
      final list = _e.invitable;
      final h = MediaQuery.sizeOf(c).height * 0.55;
      return SizedBox(
        height: h,
        child: Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.paddingOf(c).bottom + 12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Padding(
                padding: EdgeInsets.fromLTRB(16, 4, 16, 4),
                child: Text('Add people to this call', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 18, color: Colors.white)),
              ),
              const Padding(
                padding: EdgeInsets.fromLTRB(16, 0, 16, 12),
                child: Text('They get a ringing call. Online friends are listed first.', style: TextStyle(color: Colors.white70, fontSize: 13)),
              ),
              Expanded(
                child: list == null
                    ? const Center(child: CircularProgressIndicator(color: Colors.white))
                    : list.isEmpty
                        ? const Center(child: Padding(padding: EdgeInsets.all(24), child: Text('No friends or teammates to add yet.', textAlign: TextAlign.center, style: TextStyle(color: Colors.white70, fontSize: 15))))
                        : ListView.builder(
                            padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
                            itemCount: list.length,
                            itemBuilder: (_, i) {
                              final f = list[i];
                              final rawId = f['id'] ?? f['user_id'];
                              final id = rawId is num ? rawId.toInt() : int.tryParse('$rawId');
                              final name = '${f['name'] ?? f['username'] ?? f['email'] ?? 'User'}';
                              final avatarUrl = f['avatar_url'] is String ? f['avatar_url'] as String : null;
                              final status = '${f['status'] ?? ''}';
                              final online = f['online'] == true;
                              final inCall = f['in_call'] == true;
                              final ringing = f['ringing'] == true;

                              return ListTile(
                                contentPadding: EdgeInsets.zero,
                                leading: Stack(children: [
                                  Avatar(name, url: avatarUrl, radius: 22),
                                  if (online)
                                    Positioned(
                                      right: 0,
                                      bottom: 0,
                                      child: Container(
                                        width: 12,
                                        height: 12,
                                        decoration: BoxDecoration(
                                          color: AppColors.success,
                                          shape: BoxShape.circle,
                                          border: Border.all(color: AppColors.surfaceDark, width: 2),
                                        ),
                                      ),
                                    ),
                                ]),
                                title: Text(name, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
                                subtitle: status.isEmpty
                                    ? null
                                    : Text(
                                        status,
                                        style: TextStyle(
                                          color: online ? AppColors.success : Colors.white60,
                                          fontSize: 12.5,
                                        ),
                                      ),
                                trailing: inCall
                                    ? const Text('In call', style: TextStyle(color: Colors.white54))
                                    : ringing
                                        ? const Text('Calling…', style: TextStyle(color: Colors.white54))
                                        : FilledButton(
                                            style: FilledButton.styleFrom(
                                              minimumSize: const Size(72, 40),
                                              padding: const EdgeInsets.symmetric(horizontal: 18),
                                              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                            ),
                                            onPressed: id == null
                                                ? null
                                                : () async {
                                                    final msg = await _e.invite(id);
                                                    if (mounted && msg != null) toast(context, msg);
                                                  },
                                            child: const Text('Add'),
                                          ),
                              );
                            },
                          ),
              ),
            ],
          ),
        ),
      );
    });
  }

  void _chatSheet() {
    _e.openChat(true);
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      showDragHandle: true,
      backgroundColor: AppColors.surfaceDark,
      builder: (c) => Theme(
        data: Theme.of(context).copyWith(
          brightness: Brightness.dark,
          colorScheme: ColorScheme.dark(
            surface: AppColors.surfaceDark,
            onSurface: Colors.white,
            primary: AppColors.primary,
          ),
        ),
        child: Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.of(c).viewInsets.bottom),
          child: AnimatedBuilder(
            animation: _e,
            builder: (c2, _) => SizedBox(
              height: MediaQuery.sizeOf(c2).height * 0.6,
              child: Column(children: [
                Expanded(
                  child: _e.chat.isEmpty
                      ? const Center(child: Padding(padding: EdgeInsets.all(24), child: Text('No messages yet. Messages here are only for this meeting.', textAlign: TextAlign.center, style: TextStyle(color: Colors.white60))))
                      : ListView.builder(
                          controller: _chatScroll,
                          padding: const EdgeInsets.symmetric(horizontal: 14),
                          itemCount: _e.chat.length,
                          itemBuilder: (_, i) {
                            final m = _e.chat[i];
                            final mine = m['pid'] == _e.pid;
                            return Align(
                              alignment: mine ? Alignment.centerRight : Alignment.centerLeft,
                              child: Container(
                                margin: const EdgeInsets.symmetric(vertical: 3),
                                padding: const EdgeInsets.fromLTRB(12, 7, 12, 6),
                                constraints: BoxConstraints(maxWidth: MediaQuery.sizeOf(c2).width * 0.78),
                                decoration: BoxDecoration(color: mine ? AppColors.primary : AppColors.elevatedDark, borderRadius: BorderRadius.circular(14)),
                                child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
                                  if (!mine) Text('${m['name']}', style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: AppColors.primaryOnDark)),
                                  Text('${m['body']}', style: const TextStyle(color: Colors.white)),
                                  Text('${m['time']}', style: const TextStyle(fontSize: 10.5, color: Colors.white60)),
                                ]),
                              ),
                            );
                          },
                        ),
                ),
                Padding(
                  padding: const EdgeInsets.fromLTRB(12, 6, 8, 10),
                  child: Row(children: [
                    Expanded(child: TextField(controller: _chatText, textCapitalization: TextCapitalization.sentences, decoration: const InputDecoration(hintText: 'Message everyone', contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 10)), onSubmitted: (_) => _sendChat())),
                    IconButton.filled(icon: const Icon(Icons.send_rounded), onPressed: _sendChat),
                  ]),
                ),
              ]),
            ),
          ),
        ),
      ),
    ).whenComplete(() => _e.openChat(false));
  }

  Future<void> _sendChat() async {
    final t = _chatText.text;
    if (t.trim().isEmpty) return;
    _chatText.clear();
    final err = await _e.sendChat(t);
    if (err != null && mounted) toast(context, err, error: true);
  }
}

/// Full-screen ringing for an incoming VIDEO call (phone or browser caller). Accept opens the room.
class IncomingVideoCallPage extends StatefulWidget {
  final Map<String, dynamic> call;
  final String? autoAction; // 'call_accept' / 'call_decline' when the user pressed a button on the notification
  const IncomingVideoCallPage({super.key, required this.call, this.autoAction});
  @override
  State<IncomingVideoCallPage> createState() => _IncomingVideoCallPageState();
}

class _IncomingVideoCallPageState extends State<IncomingVideoCallPage> {
  bool _busy = false;
  late final int _gone;

  Map get _peer => (widget.call['peer'] as Map?) ?? const {};

  @override
  void initState() {
    super.initState();
    _gone = CallManager.I.videoGone.value;
    CallManager.I.videoGone.addListener(_check);
    CallManager.I.incomingAction.addListener(_action);
    if (widget.autoAction == 'call_accept') WidgetsBinding.instance.addPostFrameCallback((_) => _accept());
    if (widget.autoAction == 'call_decline') WidgetsBinding.instance.addPostFrameCallback((_) => _decline());
  }

  void _action() {
    final n = NotifyService.I;
    if (n.pendingCallId != widget.call['id'] || !mounted || _busy) return;
    final action = n.pendingCallAction;
    n.pendingCallAction = null;
    if (action == 'call_accept') _accept();
    if (action == 'call_decline') _decline();
  }

  void _check() {
    if (CallManager.I.videoGone.value != _gone && mounted && !_busy) Navigator.of(context).pop();
  }

  @override
  void dispose() {
    CallManager.I.videoGone.removeListener(_check);
    CallManager.I.incomingAction.removeListener(_action);
    super.dispose();
  }

  Future<void> _accept() async {
    if (_busy) return;
    setState(() => _busy = true);
    CallManager.I.ringStop();
    try {
      await AndroidCallKit.answerInApp((widget.call['id'] as num).toInt());
      await Api.of(context).post('calls/${widget.call['id']}/answer');
      if (!mounted) return;
      Navigator.of(context).pushReplacement(MaterialPageRoute(fullscreenDialog: true, builder: (_) => MeetingRoomPage(code: '${widget.call['meeting']}', title: '${widget.call['audio_only'] == true ? 'Audio' : 'Video'} call with ${_peer['name'] ?? ''}', autoJoin: true, audioOnly: widget.call['audio_only'] == true)));
    } catch (e) {
      await AndroidCallKit.endAll().catchError((_) {});
      await CallKitBridge.endAll();
      if (mounted) {
        toast(context, e is ApiException ? e.message : 'Could not answer the call.', error: true);
        Navigator.of(context).pop();
      }
    }
  }

  Future<void> _decline() async {
    if (_busy) return;
    setState(() => _busy = true);
    CallManager.I.ringStop();
    CallManager.I.dismissIncoming((widget.call['id'] as num).toInt());
    unawaited(Api.of(context).post('calls/${widget.call['id']}/decline').catchError((_) {}));
    unawaited(AndroidCallKit.endAll().catchError((_) {}));
    unawaited(CallKitBridge.endAll());
    if (mounted) Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) => PopScope(
        canPop: false,
        onPopInvokedWithResult: (didPop, result) {
          if (!didPop && !_busy) {
            CallManager.I.minimizeIncoming(widget.call);
            Navigator.of(context).pop();
          }
        },
        child: Scaffold(
        backgroundColor: IncomingCallPresentation.background,
        body: IncomingCallView(
          call: IncomingCallPresentation.from(widget.call),
          busy: _busy,
          onAccept: _accept,
          onDecline: _decline,
        ),
      ));
}
