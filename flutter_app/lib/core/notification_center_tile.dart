import 'package:flutter/material.dart';
import 'notification_presentation.dart';
import 'widgets.dart';

/// Compact history row. Tapping the card opens its original conversation/event.
class NotificationCenterTile extends StatelessWidget {
  final Map notification;
  final VoidCallback onTap;
  const NotificationCenterTile({super.key, required this.notification, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final n = notification;
    final category = NotificationPresentation.from(n);
    final scheme = Theme.of(context).colorScheme;
    final unread = n['read'] != true;
    final name = NotificationPresentation.sender(n);
    final body = NotificationPresentation.body(n);
    return Semantics(label: unread ? 'Unread' : null, child: InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Tooltip(message: category.label, child: Semantics(label: category.label,
            child: Container(
              width: 38, height: 38,
              decoration: BoxDecoration(
                color: category.color.withValues(alpha: scheme.brightness == Brightness.dark ? .22 : .10),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: category.color.withValues(alpha: .20)),
              ),
              child: Icon(category.icon, size: 21, color: scheme.brightness == Brightness.dark
                ? Color.lerp(category.color, Colors.white, .4) : category.color),
            ),
          )),
          const SizedBox(width: 10),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
            Text(name, maxLines: 2, overflow: TextOverflow.ellipsis,
              style: TextStyle(fontSize: 14, height: 1.25,
                fontWeight: unread ? FontWeight.w600 : FontWeight.w500, color: scheme.onSurface)),
            const SizedBox(height: 5),
            Text(body.isEmpty ? category.action(n) : body, maxLines: 3,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(fontSize: 13, height: 1.35, color: scheme.onSurfaceVariant)),
          ])),
          const SizedBox(width: 8),
          ConstrainedBox(constraints: const BoxConstraints(maxWidth: 58), child:
            Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.end, children: [
              Text(NotificationPresentation.relativeTime(n), maxLines: 1, overflow: TextOverflow.ellipsis,
                style: TextStyle(fontSize: 10, height: 1.2, color: scheme.onSurfaceVariant)),
              const SizedBox(height: 4),
              Avatar(name, url: NotificationPresentation.avatar(n), radius: 18),
            ])),
        ]),
      ),
    ));
  }
}
