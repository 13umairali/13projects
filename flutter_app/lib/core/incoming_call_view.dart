import 'cached_image.dart';
import 'dart:ui' show ImageFilter;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'notification_presentation.dart';

/// Shared identity/type contract for room calls, legacy audio and Android CallKit.
class IncomingCallPresentation {
  final String name;
  final String? avatar;
  final bool video, meeting;
  const IncomingCallPresentation({required this.name, this.avatar, this.video = false, this.meeting = false});

  factory IncomingCallPresentation.from(Map data) {
    final peer = data['peer'] is Map ? data['peer'] as Map : const {};
    final avatar = '${data['caller_avatar'] ?? peer['avatar_url'] ?? ''}';
    return IncomingCallPresentation(
      name: '${data['caller_name'] ?? peer['name'] ?? ''}'.trim().isEmpty ? 'Someone' : '${data['caller_name'] ?? peer['name']}',
      avatar: avatar.isEmpty ? null : avatar,
      video: NotificationPresentation.flag(data['video']) && !NotificationPresentation.flag(data['audio_only']),
      meeting: NotificationPresentation.flag(data['group']) || NotificationPresentation.flag(data['group_invite']),
    );
  }

  String get status => meeting ? 'Incoming Meeting…' : video ? 'Incoming Video Call…' : 'Incoming Audio Call…';
  static const background = Color(0xFF0B141A);
  // Keep in sync with Android's incoming_call_style.xml/activity_incoming_call.xml.
  static const avatarBackground = Color(0xFF252532);
  static const acceptColor = Color(0xFF25D366), declineColor = Color(0xFFF15C6D);
  static const secondary = Color(0xB3FFFFFF);
  static const avatarSize = 160.0, actionSize = 70.0;
  static const textStyle = TextStyle(inherit: false, fontFamily: 'Roboto', height: 1.2, color: secondary, fontSize: 14);

  String get initials => name.trim().split(RegExp(r'\s+')).where((s) => s.isNotEmpty).take(2).map((s) => s.characters.first).join().toUpperCase();
}

/// Incoming call screen, messaging-app style: dark blurred backdrop, name and call type at the top, large profile picture
/// with ripples, and two big round Decline / Accept buttons at the bottom.
class IncomingCallView extends StatefulWidget {
  final IncomingCallPresentation call;
  final VoidCallback onAccept, onDecline;
  final bool busy;
  const IncomingCallView({super.key, required this.call, required this.onAccept, required this.onDecline, this.busy = false});

  @override
  State<IncomingCallView> createState() => _IncomingCallViewState();
}

class _IncomingCallViewState extends State<IncomingCallView> with SingleTickerProviderStateMixin {
  // Ripples run for about a minute (longer than any ring) and then stop by themselves.
  late final AnimationController _pulse = AnimationController(vsync: this, duration: const Duration(milliseconds: 2400))..repeat(count: 30);

  IncomingCallPresentation get call => widget.call;
  bool get busy => widget.busy;

  @override
  void dispose() {
    _pulse.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => AnnotatedRegion<SystemUiOverlayStyle>(
    value: const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent, statusBarIconBrightness: Brightness.light,
      statusBarBrightness: Brightness.dark, systemNavigationBarColor: IncomingCallPresentation.background,
      systemNavigationBarIconBrightness: Brightness.light, systemNavigationBarContrastEnforced: false,
      systemStatusBarContrastEnforced: false,
    ),
    child: Stack(fit: StackFit.expand, children: [
      _backdrop(),
      SafeArea(child: LayoutBuilder(builder: (context, constraints) => SingleChildScrollView(
        child: ConstrainedBox(constraints: BoxConstraints(minHeight: constraints.maxHeight), child: IntrinsicHeight(
          child: Padding(padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24), child: Column(children: [
            const SizedBox(height: 28),
            Text(call.name, textAlign: TextAlign.center, maxLines: 2, overflow: TextOverflow.ellipsis,
              style: IncomingCallPresentation.textStyle.copyWith(color: Colors.white, fontSize: 28, fontWeight: FontWeight.w500)),
            const SizedBox(height: 8),
            Text(busy ? 'Please wait…' : call.status, textAlign: TextAlign.center, style: IncomingCallPresentation.textStyle.copyWith(fontSize: 16)),
            const Spacer(flex: 2),
            _avatar(),
            const Spacer(flex: 3),
            Directionality(textDirection: TextDirection.ltr, child: Row(mainAxisAlignment: MainAxisAlignment.spaceEvenly, children: [
              _action('Decline', Icons.call_end, IncomingCallPresentation.declineColor, widget.onDecline),
              _action('Accept', call.video ? Icons.videocam : Icons.call, IncomingCallPresentation.acceptColor, widget.onAccept),
            ])),
            const SizedBox(height: 16),
          ])),
        )),
      ))),
    ]),
  );

  Widget _backdrop() => Stack(fit: StackFit.expand, children: [
    const DecoratedBox(decoration: BoxDecoration(gradient: LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter,
      colors: [Color(0xFF0B141A), Color(0xFF13222B)]))),
    if (call.avatar != null)
      ImageFiltered(imageFilter: ImageFilter.blur(sigmaX: 28, sigmaY: 28),
        child: Opacity(opacity: .32, child: CachedNetImage(call.avatar!, fit: BoxFit.cover))),
    ColoredBox(color: Colors.black.withValues(alpha: .35)),
  ]);

  Widget _avatar() {
    final placeholder = Center(child: Text(call.initials,
      style: IncomingCallPresentation.textStyle.copyWith(color: Colors.white, fontSize: 56, fontWeight: FontWeight.w600)));
    const size = IncomingCallPresentation.avatarSize;
    final photo = ExcludeSemantics(child: ClipOval(child: SizedBox.square(dimension: size,
      child: ColoredBox(color: IncomingCallPresentation.avatarBackground, child: call.avatar == null
        ? placeholder : CachedNetImage(call.avatar!, fit: BoxFit.cover, placeholder: (_) => placeholder, error: (_) => placeholder)),
    )));
    return SizedBox.square(dimension: size * 1.9, child: AnimatedBuilder(animation: _pulse, builder: (_, __) => Stack(alignment: Alignment.center, children: [
      for (var i = 0; i < 3; i++)
        () {
          final t = (_pulse.value + i / 3) % 1.0;
          final d = size * (1 + .9 * t);
          return Container(width: d, height: d, decoration: BoxDecoration(shape: BoxShape.circle, color: Colors.white.withValues(alpha: .10 * (1 - t))));
        }(),
      photo,
    ])));
  }

  Widget _action(String label, IconData icon, Color color, VoidCallback action) => Column(mainAxisSize: MainAxisSize.min, children: [
    IconButton.filled(onPressed: busy ? null : action, tooltip: label,
      style: IconButton.styleFrom(backgroundColor: color, foregroundColor: Colors.white,
        disabledForegroundColor: Colors.white.withValues(alpha: .5), disabledBackgroundColor: color.withValues(alpha: .5),
        fixedSize: const Size.square(IncomingCallPresentation.actionSize), shape: const CircleBorder(),
        visualDensity: VisualDensity.standard, padding: const EdgeInsets.all(20)),
      icon: Icon(icon, size: 30)),
    const SizedBox(height: 8),
    Text(label, style: IncomingCallPresentation.textStyle),
  ]);
}
