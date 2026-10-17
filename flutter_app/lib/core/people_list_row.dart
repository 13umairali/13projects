import 'package:flutter/material.dart';
import 'theme.dart';
import 'widgets.dart';

/// List-only portrait treatment. Notification avatars keep their own styling.
class PeopleAvatar extends StatelessWidget {
  final String name;
  final String? url;
  final bool online;
  final VoidCallback? onTap;
  const PeopleAvatar(
      {super.key,
      required this.name,
      this.url,
      this.online = false,
      this.onTap});

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final color = Avatar.colorFor(name);
    final fill = Color.alphaBlend(color.withValues(alpha: .12), scheme.surface);
    final words =
        name.trim().split(RegExp(r'\s+')).where((s) => s.isNotEmpty).toList();
    final initials = words.isEmpty || name == 'Unknown'
        ? null
        : '${words.first.characters.first}${words.length > 1 ? words.last.characters.first : ''}'
            .toUpperCase();
    final photo = url?.trim();
    return Semantics(
      label: online ? '$name, online' : null,
      child: InkWell(
        onTap: onTap,
        customBorder: const CircleBorder(),
        child: Stack(children: [
          CircleAvatar(
              radius: 25,
              backgroundColor: fill,
              foregroundImage:
                  photo == null || photo.isEmpty ? null : NetworkImage(photo),
              onForegroundImageError:
                  photo == null || photo.isEmpty ? null : (_, __) {},
              child: initials == null
                  ? Icon(Icons.person_rounded,
                      color: AppColors.readableAccent(color, fill), size: 26)
                  : Text(initials,
                      style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                          color: AppColors.readableAccent(color, fill)))),
          if (online)
            Positioned(
                bottom: 0,
                right: 0,
                child: Container(
                    width: 13,
                    height: 13,
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
      this.onLongPress});

  @override
  Widget build(BuildContext context) {
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
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              child:
                  Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Padding(padding: const EdgeInsets.only(top: 1), child: avatar),
                const SizedBox(width: 14),
                Expanded(
                    child: Column(
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
                                        fontSize: 16,
                                        height: 1.3,
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
                            padding: const EdgeInsets.only(top: 3),
                            child: Text(subject!,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                    fontSize: 14,
                                    height: 1.35,
                                    fontWeight: active
                                        ? FontWeight.w600
                                        : FontWeight.w500,
                                    color: scheme.onSurface))),
                      if (preview.isNotEmpty || indicators.isNotEmpty || active)
                        Padding(
                            padding: const EdgeInsets.only(top: 4),
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
                            padding: const EdgeInsets.only(top: 8),
                            child: footer),
                      if (actions.isNotEmpty)
                        Padding(
                            padding: const EdgeInsets.only(top: 10),
                            child: Wrap(
                                spacing: 8,
                                runSpacing: 6,
                                crossAxisAlignment: WrapCrossAlignment.center,
                                children: actions)),
                    ])),
                if (trailing != null)
                  Padding(
                      padding: const EdgeInsetsDirectional.only(start: 8),
                      child: trailing),
              ])),
          Padding(
              padding: const EdgeInsetsDirectional.only(start: 80),
              child: Divider(
                  height: 1,
                  thickness: .5,
                  color: AppColors.borderOf(context))),
        ]),
      ),
    );
  }
}
