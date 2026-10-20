import 'cached_image.dart';
import 'package:flutter/material.dart';
import 'theme.dart';
import 'widgets.dart';

/// List-only portrait treatment. Notification avatars keep their own styling.
class PeopleAvatar extends StatelessWidget {
  final String name;
  final String? url;
  final bool online;
  final double radius;
  final VoidCallback? onTap;
  const PeopleAvatar(
      {super.key,
      required this.name,
      this.url,
      this.online = false,
      this.radius = 25,
      this.onTap});

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final color = Avatar.colorFor(name);
    final fill = Color.alphaBlend(color.withValues(alpha: .12), scheme.surface);
    final shown = Avatar.initials(name);
    final initials = shown.isEmpty || name == 'Unknown' ? null : shown;
    final photo = url?.trim();
    return Semantics(
      label: online ? '$name, online' : null,
      child: InkWell(
        onTap: onTap,
        customBorder: const CircleBorder(),
        child: Stack(children: [
          CircleAvatar(
              radius: radius,
              backgroundColor: fill,
              foregroundImage: avatarProvider(photo, width: (radius * 6).round()),
              onForegroundImageError:
                  photo == null || photo.isEmpty ? null : (_, __) {},
              child: initials == null
                  ? Icon(Icons.person_rounded,
                      color: AppColors.readableAccent(color, fill), size: radius * 1.04)
                  : Text(initials,
                      style: TextStyle(
                          fontSize: radius * (initials.length > 1 ? 0.68 : 0.76),
                          fontWeight: FontWeight.w600,
                          color: AppColors.readableAccent(color, fill)))),
          if (online)
            Positioned(
                bottom: 0,
                right: 0,
                child: Container(
                    width: radius * 0.52,
                    height: radius * 0.52,
                    decoration: BoxDecoration(
                        color: AppColors.success,
                        shape: BoxShape.circle,
                        border: Border.all(color: scheme.surface, width: 2)))),
        ]),
      ),
    );
  }
}

/// Standard edge-to-edge people/conversation row with an inset divider.
/// The body grows with text size; actions wrap instead of squeezing the name.
class PeopleListRow extends StatelessWidget {
  final Widget avatar;
  final String name, preview;
  final String? subject, time;
  final bool unread, selected;
  final int unreadCount;
  final Widget? previewLeading, footer, trailing;
  final List<Widget> indicators, actions;
  final VoidCallback? onTap, onLongPress;
  /// Modern SaaS card: inset, hairline border, rounded, avatar centred with the text, action buttons in an even row.
  /// Used for people directories (Friends, requests, Add friends); chat/mail lists keep the flat edge-to-edge row.
  final bool card;
  const PeopleListRow(
      {super.key,
      required this.avatar,
      required this.name,
      this.preview = '',
      this.subject,
      this.time,
      this.unread = false,
      this.selected = false,
      this.unreadCount = 0,
      this.previewLeading,
      this.footer,
      this.trailing,
      this.indicators = const [],
      this.actions = const [],
      this.onTap,
      this.onLongPress,
      this.card = false});

  Widget _cardBody(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final muted = AppColors.mutedOf(context);
    final active = unread || unreadCount > 0;
    final roomy = MediaQuery.textScalerOf(context).scale(1) <= 1.2 && actions.length <= 3;
    final decoration = cardDecoration(context);
    final hasSubject = subject?.trim().isNotEmpty == true;
    final showTime = time?.isNotEmpty == true;
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 14, vertical: 3),
      decoration: selected ? decoration.copyWith(color: scheme.primaryContainer) : decoration,
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(14),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onTap,
          onLongPress: onLongPress,
          child: Padding(
            padding: const EdgeInsets.fromLTRB(12, 9, 12, 9),
            child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
              Row(crossAxisAlignment: CrossAxisAlignment.center, children: [
                avatar,
                const SizedBox(width: 12),
                Expanded(
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
                    Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Expanded(
                        child: Text(name, maxLines: 1, overflow: TextOverflow.ellipsis,
                            style: TextStyle(fontSize: 15.5, height: 1.3, fontWeight: active ? FontWeight.w700 : FontWeight.w600, color: scheme.onSurface)),
                      ),
                      if (showTime) ...[
                        const SizedBox(width: 8),
                        ConstrainedBox(
                          constraints: const BoxConstraints(maxWidth: 76),
                          child: Text(time!, maxLines: 1, overflow: TextOverflow.ellipsis, textAlign: TextAlign.end,
                              style: TextStyle(fontSize: 11, height: 1.5, fontWeight: active ? FontWeight.w600 : FontWeight.w400, color: active ? scheme.primary : muted)),
                        ),
                      ],
                    ]),
                    if (hasSubject)
                      Padding(
                        padding: const EdgeInsets.only(top: 2),
                        child: Text(subject!, maxLines: 1, overflow: TextOverflow.ellipsis,
                            style: TextStyle(fontSize: 14, height: 1.35, fontWeight: active ? FontWeight.w600 : FontWeight.w500, color: scheme.onSurface)),
                      ),
                    if (preview.isNotEmpty || indicators.isNotEmpty || active || previewLeading != null)
                      Padding(
                        padding: const EdgeInsets.only(top: 2),
                        child: Row(crossAxisAlignment: CrossAxisAlignment.center, children: [
                          if (previewLeading != null) Padding(padding: const EdgeInsetsDirectional.only(end: 4), child: previewLeading),
                          Expanded(child: Text(preview, maxLines: hasSubject ? 1 : 2, overflow: TextOverflow.ellipsis,
                              style: TextStyle(fontSize: 13, height: 1.35, color: muted))),
                          if (trailing == null)
                            for (final indicator in indicators)
                              Padding(padding: const EdgeInsetsDirectional.only(start: 6), child: IconTheme(data: IconThemeData(size: 16, color: muted), child: indicator)),
                          if (active)
                            Padding(
                              padding: const EdgeInsetsDirectional.only(start: 8),
                              child: unreadCount > 0
                                  ? Container(
                                      constraints: const BoxConstraints(minWidth: 20),
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                                      decoration: BoxDecoration(color: scheme.primary, borderRadius: BorderRadius.circular(999)),
                                      child: Text(unreadCount > 99 ? '99+' : '$unreadCount', textAlign: TextAlign.center,
                                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: scheme.onPrimary)))
                                  : Semantics(label: 'Unread', child: Container(width: 8, height: 8, decoration: BoxDecoration(color: scheme.primary, shape: BoxShape.circle))),
                            ),
                        ]),
                      ),
                    if (footer != null) Padding(padding: const EdgeInsets.only(top: 6), child: footer),
                  ]),
                ),
                if (trailing != null) Padding(padding: const EdgeInsetsDirectional.only(start: 6), child: trailing),
              ]),
              if (actions.isNotEmpty) ...[
                const SizedBox(height: 8),
                if (roomy)
                  Row(children: [
                    for (var i = 0; i < actions.length; i++) ...[
                      if (i > 0) const SizedBox(width: 8),
                      Expanded(child: actions[i]),
                    ],
                  ])
                else
                  Wrap(spacing: 8, runSpacing: 8, alignment: WrapAlignment.end, children: actions),
              ],
            ]),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (card) return _cardBody(context);
    final scheme = Theme.of(context).colorScheme;
    final muted = AppColors.mutedOf(context);
    final active = unread || unreadCount > 0;
    return Material(
      color: selected ? scheme.primaryContainer : scheme.surface,
      child: InkWell(
        onTap: onTap,
        onLongPress: onLongPress,
        child: Column(children: [
          Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              child:
                  Row(crossAxisAlignment: CrossAxisAlignment.center, children: [
                // Avatar is vertically centred against the text block.
                avatar,
                const SizedBox(width: 12),
                Expanded(
                    child: ConstrainedBox(
                      constraints: const BoxConstraints(minHeight: 44),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                      Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Expanded(
                                child: Text(name,
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: TextStyle(
                                        fontSize: 15.5,
                                        height: 1.25,
                                        fontWeight: active
                                            ? FontWeight.w700
                                            : FontWeight.w600,
                                        color: scheme.onSurface))),
                            if (time?.isNotEmpty == true) ...[
                              const SizedBox(width: 8),
                              ConstrainedBox(
                                  constraints:
                                      const BoxConstraints(maxWidth: 76),
                                  child: Text(time!,
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                      textAlign: TextAlign.end,
                                      style: TextStyle(
                                          fontSize: 11,
                                          height: 1.5,
                                          fontWeight: active
                                              ? FontWeight.w600
                                              : FontWeight.w400,
                                          color: active
                                              ? scheme.primary
                                              : muted))),
                            ],
                          ]),
                      if (subject?.trim().isNotEmpty == true)
                        Padding(
                            padding: const EdgeInsets.only(top: 1),
                            child: Text(subject!,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                    fontSize: 13.5,
                                    height: 1.3,
                                    fontWeight: active
                                        ? FontWeight.w600
                                        : FontWeight.w500,
                                    color: scheme.onSurface))),
                      if (preview.isNotEmpty || indicators.isNotEmpty || active)
                        Padding(
                            padding: const EdgeInsets.only(top: 2),
                            child: Row(
                                crossAxisAlignment: CrossAxisAlignment.center,
                                children: [
                                  if (previewLeading != null)
                                    Padding(
                                        padding:
                                            const EdgeInsetsDirectional.only(
                                                end: 4),
                                        child: previewLeading),
                                  Expanded(
                                      child: Text(preview,
                                          maxLines:
                                              subject?.trim().isNotEmpty == true
                                                  ? 1
                                                  : 2,
                                          overflow: TextOverflow.ellipsis,
                                          style: TextStyle(
                                              fontSize: 13,
                                              height: 1.4,
                                              color: muted))),
                                  for (final indicator in indicators)
                                    Padding(
                                        padding:
                                            const EdgeInsetsDirectional.only(
                                                start: 6),
                                        child: IconTheme(
                                            data: IconThemeData(
                                                size: 15, color: muted),
                                            child: indicator)),
                                  if (active)
                                    Padding(
                                        padding: const EdgeInsetsDirectional.only(
                                            start: 8),
                                        child: unreadCount > 0
                                            ? Container(
                                                constraints:
                                                    const BoxConstraints(
                                                        minWidth: 21),
                                                padding:
                                                    const EdgeInsets.symmetric(
                                                        horizontal: 6,
                                                        vertical: 2),
                                                decoration: BoxDecoration(
                                                    color: scheme.primary,
                                                    borderRadius:
                                                        BorderRadius.circular(
                                                            12)),
                                                child: Text(unreadCount > 99 ? '99+' : '$unreadCount',
                                                    textAlign: TextAlign.center,
                                                    style: TextStyle(
                                                        fontSize: 11,
                                                        fontWeight:
                                                            FontWeight.w700,
                                                        color:
                                                            scheme.onPrimary)))
                                            : Semantics(
                                                label: 'Unread',
                                                child: Container(width: 8, height: 8, decoration: BoxDecoration(color: scheme.primary, shape: BoxShape.circle)))),
                                ])),
                      if (footer != null)
                        Padding(
                            padding: const EdgeInsets.only(top: 6),
                            child: footer),
                      if (actions.isNotEmpty)
                        Padding(
                            padding: const EdgeInsets.only(top: 10),
                            child: Wrap(
                                spacing: 8,
                                runSpacing: 6,
                                crossAxisAlignment: WrapCrossAlignment.center,
                                children: actions)),
                    ]))),
                if (trailing != null)
                  Padding(
                      padding: const EdgeInsetsDirectional.only(start: 8),
                      child: trailing),
              ])),
          Padding(
              padding: const EdgeInsetsDirectional.only(start: 78),
              child: Divider(
                  height: 1,
                  thickness: .5,
                  color: AppColors.borderOf(context))),
        ]),
      ),
    );
  }
}
