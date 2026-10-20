import 'package:flutter/material.dart';
import '../core/brand.dart';
import '../core/call_audio.dart';
import '../core/incoming_call_view.dart';
import '../core/wa_call_view.dart';
import '../core/calls.dart';
import '../core/theme.dart';
import '../core/widgets.dart';

/// Full-screen audio call: incoming (Accept / Decline), calling, connected (timer, Mute, Speaker, End).
class CallScreen extends StatefulWidget {
  final CallSession session;
  const CallScreen({super.key, required this.session});
  @override
  State<CallScreen> createState() => _CallScreenState();
}

class _CallScreenState extends State<CallScreen> {
  CallSession get s => widget.session;
  bool _closing = false;

  @override
  void initState() {
    super.initState();
    s.addListener(_changed);
  }

  @override
  void dispose() {
    s.removeListener(_changed);
    super.dispose();
  }

  void _changed() {
    if (!mounted) return;
    setState(() {});
    if (s.state == 'ended' && !_closing) {
      _closing = true;
      Future.delayed(Duration(milliseconds: (s.endReason ?? '').length > 40 ? 6000 : 1500), () {
        if (mounted) Navigator.of(context).maybePop();
      });
    }
  }

  String get _status {
    switch (s.state) {
      case 'calling':
        return 'Calling…';
      case 'ringing':
        return 'Incoming audio call';
      case 'connecting':
        return 'Connecting…';
      case 'connected':
        return '${(s.seconds ~/ 60).toString().padLeft(2, '0')}:${(s.seconds % 60).toString().padLeft(2, '0')}';
      default:
        return s.endReason ?? 'Call ended';
    }
  }

  Widget _round(IconData icon, String label, Color bg, VoidCallback? onTap, {bool on = false}) => Column(mainAxisSize: MainAxisSize.min, children: [
        Material(
          color: on ? Colors.white : bg,
          shape: const CircleBorder(),
          child: InkWell(customBorder: const CircleBorder(), onTap: onTap, child: Padding(padding: const EdgeInsets.all(18), child: Icon(icon, size: 28, color: on ? Colors.black87 : Colors.white))),
        ),
        const SizedBox(height: 8),
        Text(label, style: const TextStyle(color: Colors.white70, fontSize: 12.5)),
      ]);

  @override
  Widget build(BuildContext context) {
    final p = s.peer;
    final incoming = s.state == 'ringing';
    final over = s.state == 'ended';
    return PopScope(
      canPop: over,
      onPopInvokedWithResult: (didPop, result) {
        if (!didPop) { CallManager.I.minimizeLegacy(); Navigator.of(context).pop(); }
      },
      child: Scaffold(
        backgroundColor: const Color(0xFF101820),
        body: incoming ? IncomingCallView(
          call: IncomingCallPresentation(name: s.peerName, avatar: p['avatar_url'] as String?),
          onAccept: () { CallManager.I.ringStop(); s.accept(); },
          onDecline: () { CallManager.I.ringStop(); s.decline(); },
        ) : WaCallView(
          name: s.peerName,
          avatarUrl: p['avatar_url'] as String?,
          status: _status,
          connected: s.state == 'connected',
          onBack: () { CallManager.I.minimizeLegacy(); Navigator.of(context).pop(); },
          buttons: over ? const [] : [
            WaCallButton(icon: s.audio.icon, tooltip: 'Audio output', active: s.speaker, onTap: () => showCallAudioPicker(context, s.audio, s.selectAudioOutput)),
            WaCallButton(icon: s.muted ? Icons.mic_off_rounded : Icons.mic_rounded, tooltip: s.muted ? 'Unmute' : 'Mute', active: s.muted, onTap: s.toggleMute),
            WaCallButton(icon: Icons.call_end_rounded, tooltip: 'End call', danger: true, onTap: s.hangUp),
          ],
        ),
      ),
    );
  }
}
