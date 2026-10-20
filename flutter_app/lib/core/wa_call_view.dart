import 'cached_image.dart';
import 'dart:ui' show FontFeature, ImageFilter;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

/// One round button of the bottom call bar (speaker, video, mute, flip, end …).
class WaCallButton {
  final IconData icon;
  final String tooltip;
  final VoidCallback? onTap;
  final bool active; // white disc with a dark icon (e.g. muted / speaker on)
  final bool danger; // the red End button
  const WaCallButton({required this.icon, required this.tooltip, this.onTap, this.active = false, this.danger = false});
}

/// Blurred profile-photo backdrop shared by every call and meeting screen.
class WaCallBackdrop extends StatelessWidget {
  final String? avatarUrl;
  const WaCallBackdrop({super.key, this.avatarUrl});

  @override
  Widget build(BuildContext context) {
    final url = avatarUrl;
    return Stack(fit: StackFit.expand, children: [
      const DecoratedBox(
        decoration: BoxDecoration(gradient: LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter, colors: [Color(0xFF0B141A), Color(0xFF13222B)])),
      ),
      if (url != null && url.startsWith('http'))
        ImageFiltered(
          imageFilter: ImageFilter.blur(sigmaX: 28, sigmaY: 28),
          child: Opacity(opacity: .35, child: CachedNetImage(url, fit: BoxFit.cover)),
        ),
      ColoredBox(color: Colors.black.withValues(alpha: .35)),
    ]);
  }
}

/// One round button of the call bar.
class WaRoundButton extends StatelessWidget {
  final WaCallButton button;
  final double size;
  const WaRoundButton(this.button, {super.key, this.size = 58});

  @override
  Widget build(BuildContext context) {
    final b = button;
    final bg = b.danger ? WaCallView.endRed : (b.active ? Colors.white : WaCallView.buttonFill);
    final fg = b.active && !b.danger ? const Color(0xFF111B21) : Colors.white;
    return Semantics(
      button: true,
      label: b.tooltip,
      child: Tooltip(
        message: b.tooltip,
        child: Material(
          color: bg,
          shape: const CircleBorder(),
          child: InkWell(
            customBorder: const CircleBorder(),
            onTap: b.onTap == null
                ? null
                : () {
                    HapticFeedback.selectionClick();
                    b.onTap!();
                  },
            child: SizedBox.square(dimension: size, child: Icon(b.icon, color: fg, size: 26)),
          ),
        ),
      ),
    );
  }
}

/// The rounded control panel at the bottom of every call and meeting screen.
class WaCallPanel extends StatelessWidget {
  final List<WaCallButton> buttons;
  final bool translucent;
  final Widget? primary; // optional wide action above the round buttons (e.g. "Join now")
  const WaCallPanel({super.key, required this.buttons, this.translucent = false, this.primary});

  /// Height to keep free for the panel (without the bottom safe-area inset).
  static const height = 128.0;
  static const primaryHeight = 72.0;

  @override
  Widget build(BuildContext context) => Container(
        padding: EdgeInsets.fromLTRB(20, 18, 20, 18 + MediaQuery.paddingOf(context).bottom * .6),
        decoration: BoxDecoration(
          color: translucent ? const Color(0xCC1F2C34) : WaCallView.panel,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(30)),
        ),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          if (primary != null) ...[primary!, const SizedBox(height: 16)],
          Row(mainAxisAlignment: MainAxisAlignment.spaceEvenly, children: [for (final b in buttons) WaRoundButton(b)]),
        ]),
      );
}

/// Messaging-app style one-to-one call screen, shared by audio and video, dialing / ringing / connected:
///
///  - Audio: dark background (blurred profile photo), name + status ("Calling…", "Ringing…", "00:12") at the top,
///    large profile picture with ripples while the call is not connected, rounded control panel at the bottom.
///  - Video, not connected yet: your own camera full screen, dimmed, with the name and status on top.
///  - Video, connected: the other person full screen, your picture as a small rounded window that can be dragged
///    to either side, name and timer at the top left, translucent control panel at the bottom.
class WaCallView extends StatefulWidget {
  final String name;
  final String? avatarUrl;
  final String status;
  final bool video, connected;
  final Widget? remote; // other person's video (when it is on)
  final Widget? local; // own camera
  final List<WaCallButton> buttons;
  final VoidCallback? onBack, onAddPerson, onMore;
  final Widget? banner; // e.g. the recording notice
  final IconData? icon; // shown instead of the initials when there is no photo (meetings, errors)
  final int statusMaxLines; // longer messages (waiting room, errors) may wrap
  final bool pulse; // ripples around the picture while not connected
  final Widget? primary; // wide action above the round buttons (e.g. "Join now")
  final IconData backIcon;
  final String backTooltip;
  const WaCallView({
    super.key,
    required this.name,
    required this.status,
    required this.buttons,
    this.avatarUrl,
    this.video = false,
    this.connected = false,
    this.remote,
    this.local,
    this.onBack,
    this.onAddPerson,
    this.onMore,
    this.banner,
    this.icon,
    this.statusMaxLines = 1,
    this.pulse = true,
    this.primary,
    this.backIcon = Icons.keyboard_arrow_down_rounded,
    this.backTooltip = 'Minimize',
  });

  static const bg = Color(0xFF0B141A);
  static const panel = Color(0xFF1F2C34);
  static const buttonFill = Color(0xFF2E3C45);
  static const endRed = Color(0xFFF15C6D);

  static String initialsOf(String name) => name
      .trim()
      .split(RegExp(r'\s+'))
      .where((s) => s.isNotEmpty)
      .take(2)
      .map((s) => s.characters.first)
      .join()
      .toUpperCase();

  @override
  State<WaCallView> createState() => _WaCallViewState();
}

class _WaCallViewState extends State<WaCallView> with SingleTickerProviderStateMixin {
  late final AnimationController _pulse = AnimationController(vsync: this, duration: const Duration(milliseconds: 2400));
  Offset? _selfPos; // top-left of the small self view; null = default corner
  static const _selfW = 96.0, _selfH = 136.0, _edge = 14.0;
  bool get _pulsing => !widget.connected && widget.pulse;

  @override
  void initState() {
    super.initState();
    if (_pulsing) _pulse.repeat(count: 40);
  }

  @override
  void didUpdateWidget(WaCallView old) {
    super.didUpdateWidget(old);
    if (!_pulsing) {
      if (_pulse.isAnimating) _pulse.stop();
    } else if (!_pulse.isAnimating) {
      _pulse.repeat(count: 40);
    }
  }

  @override
  void dispose() {
    _pulse.dispose();
    super.dispose();
  }

  Widget _photo(double size) {
    final initials = WaCallView.initialsOf(widget.name);
    final placeholder = Container(
      width: size,
      height: size,
      alignment: Alignment.center,
      color: const Color(0xFF2A3942),
      child: widget.icon != null
          ? Icon(widget.icon, color: Colors.white70, size: size * .46)
          : initials.isEmpty
              ? Icon(Icons.person_rounded, color: Colors.white54, size: size * .5)
              : Text(initials, style: TextStyle(color: Colors.white, fontSize: size * .34, fontWeight: FontWeight.w600)),
    );
    final url = widget.avatarUrl;
    final child = url == null || !url.startsWith('http')
        ? placeholder
        : CachedNetImage(url, width: size, height: size, fit: BoxFit.cover, error: (_) => placeholder);
    return ExcludeSemantics(child: ClipOval(child: SizedBox.square(dimension: size, child: child)));
  }

  Widget _ripples(double size) => SizedBox(
        width: size * 1.9,
        height: size * 1.9,
        child: AnimatedBuilder(
          animation: _pulse,
          builder: (_, __) => Stack(alignment: Alignment.center, children: [
            if (_pulsing)
              for (var i = 0; i < 3; i++)
                () {
                  final t = (_pulse.value + i / 3) % 1.0;
                  final d = size * (1 + .9 * t);
                  return Container(
                    width: d,
                    height: d,
                    decoration: BoxDecoration(shape: BoxShape.circle, color: Colors.white.withValues(alpha: .10 * (1 - t))),
                  );
                }(),
            _photo(size),
          ]),
        ),
      );

  Widget _nameBlock({required bool centered}) => Column(
        crossAxisAlignment: centered ? CrossAxisAlignment.center : CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(widget.name,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: centered ? TextAlign.center : TextAlign.start,
              style: TextStyle(color: Colors.white, fontSize: centered ? 28 : 19, fontWeight: centered ? FontWeight.w500 : FontWeight.w600, height: 1.2)),
          SizedBox(height: centered ? 6 : 2),
          Text(widget.status,
              maxLines: centered ? widget.statusMaxLines : 1,
              textAlign: centered ? TextAlign.center : TextAlign.start,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(color: Colors.white.withValues(alpha: .78), fontSize: centered ? 17 : 14, height: 1.2, fontFeatures: const [FontFeature.tabularFigures()])),
        ],
      );

  @override
  Widget build(BuildContext context) {
    final video = widget.video;
    final showRemote = video && widget.connected && widget.remote != null;
    final showLocalBackground = video && !widget.connected && widget.local != null;
    final showSelf = video && widget.connected && widget.local != null;
    final overVideo = showRemote || showLocalBackground;
    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: SystemUiOverlayStyle.light.copyWith(statusBarColor: Colors.transparent, systemNavigationBarColor: overVideo ? Colors.black : WaCallView.panel),
      child: LayoutBuilder(builder: (context, box) {
        final topInset = MediaQuery.paddingOf(context).top;
        final bottomReserve = WaCallPanel.height + (widget.primary != null ? WaCallPanel.primaryHeight : 0) + MediaQuery.paddingOf(context).bottom * .6;
        final defaultPos = Offset(box.maxWidth - _selfW - _edge, topInset + 72);
        final pos = _selfPos ?? defaultPos;
        return Stack(fit: StackFit.expand, children: [
          Positioned.fill(child: showRemote ? widget.remote! : (showLocalBackground ? widget.local! : WaCallBackdrop(avatarUrl: widget.avatarUrl))),
          if (overVideo)
            Positioned.fill(
              child: DecoratedBox(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [Colors.black.withValues(alpha: showLocalBackground ? .55 : .45), Colors.transparent, Colors.transparent, Colors.black.withValues(alpha: .45)],
                    stops: const [0, .25, .7, 1],
                  ),
                ),
              ),
            ),
          // top bar
          Positioned(
            left: 0,
            right: 0,
            top: 0,
            child: Padding(
              padding: EdgeInsets.fromLTRB(4, topInset + 6, 4, 0),
              child: Row(children: [
                if (widget.onBack != null) IconButton(tooltip: widget.backTooltip, onPressed: widget.onBack, icon: Icon(widget.backIcon, color: Colors.white, size: 32)),
                if (overVideo) Expanded(child: _nameBlock(centered: false)) else const Spacer(),
                if (widget.onMore != null) IconButton(tooltip: 'More', onPressed: widget.onMore, icon: const Icon(Icons.more_vert_rounded, color: Colors.white, size: 24)),
                if (widget.onAddPerson != null) IconButton(tooltip: 'Add people', onPressed: widget.onAddPerson, icon: const Icon(Icons.person_add_alt_1_rounded, color: Colors.white, size: 24)),
              ]),
            ),
          ),
          // audio layout (also video while the other person's camera is off)
          if (!overVideo)
            Positioned.fill(
              child: Padding(
                padding: EdgeInsets.only(top: topInset + 66, bottom: bottomReserve + 8),
                child: Column(children: [
                  Padding(padding: const EdgeInsets.symmetric(horizontal: 28), child: _nameBlock(centered: true)),
                  const Spacer(),
                  _ripples(showSelf ? 150 : 168),
                  const Spacer(),
                ]),
              ),
            ),
          if (widget.banner != null) Positioned(top: topInset + 58, left: 12, right: 12, child: Center(child: widget.banner!)),
          // own picture (video, connected): small rounded window, draggable
          if (showSelf)
            Positioned(
              left: pos.dx,
              top: pos.dy,
              width: _selfW,
              height: _selfH,
              child: GestureDetector(
                onPanUpdate: (d) => setState(() {
                  final p = (_selfPos ?? defaultPos) + d.delta;
                  _selfPos = Offset(p.dx.clamp(_edge, box.maxWidth - _selfW - _edge), p.dy.clamp(topInset + 60, box.maxHeight - bottomReserve - _selfH - _edge));
                }),
                onPanEnd: (_) => setState(() {
                  final p = _selfPos ?? defaultPos;
                  final left = p.dx + _selfW / 2 < box.maxWidth / 2;
                  _selfPos = Offset(left ? _edge : box.maxWidth - _selfW - _edge, p.dy);
                }),
                child: DecoratedBox(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: Colors.white24, width: 1),
                    boxShadow: const [BoxShadow(color: Colors.black38, blurRadius: 10, offset: Offset(0, 3))],
                  ),
                  child: ClipRRect(borderRadius: BorderRadius.circular(15), child: widget.local!),
                ),
              ),
            ),
          // bottom control panel
          Positioned(left: 0, right: 0, bottom: 0, child: WaCallPanel(buttons: widget.buttons, translucent: overVideo, primary: widget.primary)),
        ]);
      }),
    );
  }
}
