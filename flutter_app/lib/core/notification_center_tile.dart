import 'package:flutter/material.dart';
import 'notification_presentation.dart';
import 'widgets.dart';

/// Compact history row. Tapping the card opens its original conversation/event.
///
/// Balanced three-part row, everything centred on the same horizontal axis:
///
///   (icon)   Sender name              9h ago   (photo)
///   40 px    Message preview, 2 lines          40 px
///
/// Both ends are 40 px circles, so the card is symmetrical; the time sits on the name line, just left of the photo.
class NotificationCenterTile extends StatelessWidget {
  final Map notification;
  final VoidCallback onTap;
  const NotificationCenterTile({super.key, required this.notification, required this.onTap});

  static const double _circle = 40;

  @override
  Widget build(BuildContext context) {
    final n = notification;
    final category = NotificationPresentation.from(n);
    final scheme = Theme.of(context).colorScheme;
    final dark = scheme.brightness == Brightness.dark;
    final unread = n['read'] != true;
    final name = NotificationPresentation.sender(n);
    final body = NotificationPresentation.body(n);
    final tint = dark ? Color.lerp(category.color, Colors.white, .4)! : category.color;
    return Semantics(label: unread ? 'Unread' : null, child: InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        child: Row(crossAxisAlignment: CrossAxisAlignment.center, children: [
          Tooltip(message: category.label, child: Semantics(label: category.label,
            child: Container(
              width: _circle, height: _circle,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: category.color.withValues(alpha: dark ? .22 : .10),
              ),
              child: Icon(category.icon, size: 20, color: tint),
            ),
          )),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
            Row(crossAxisAlignment: CrossAxisAlignment.center, children: [
              Expanded(child: Text(name, maxLines: 1, overflow: TextOverflow.ellipsis,
                style: TextStyle(fontSize: 15, height: 1.25,
                  fontWeight: unread ? FontWeight.w700 : FontWeight.w600, color: scheme.onSurface))),
              const SizedBox(width: 8),
              if (unread) Container(width: 7, height: 7, margin: const EdgeInsets.only(right: 6),
                decoration: BoxDecoration(color: scheme.primary, shape: BoxShape.circle)),
              Text(NotificationPresentation.relativeTime(n), maxLines: 1,
                style: TextStyle(fontSize: 11.5, height: 1.25, color: scheme.onSurfaceVariant)),
            ]),
            const SizedBox(height: 3),
            Text(body.isEmpty ? category.action(n) : body, maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(fontSize: 13.5, height: 1.35, color: scheme.onSurfaceVariant)),
          ])),
          const SizedBox(width: 12),
          Avatar(name, url: NotificationPresentation.avatar(n), radius: _circle / 2),
        ]),
      ),
    ));
  }
}
