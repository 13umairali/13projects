import 'cached_image.dart';
import 'package:flutter/material.dart';
import 'theme.dart';
import 'widgets.dart';

/// Friends screen only: modern SaaS tile avatar (soft gradient squircle). Other lists keep [PeopleAvatar].
class FriendAvatar extends StatelessWidget {
  final String name;
  final String? url;
  final bool online;
  final double radius;
  final VoidCallback? onTap;
  const FriendAvatar({super.key, required this.name, this.url, this.online = false, this.radius = 25, this.onTap});

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final dark = Theme.of(context).brightness == Brightness.dark;
    final color = Avatar.colorFor(name);
    final size = radius * 2;
    final corner = BorderRadius.circular(size * 0.32); // soft "squircle" tile, the modern SaaS look
    final base = Color.alphaBlend(color.withValues(alpha: dark ? .22 : .10), scheme.surface);
    final top = Color.alphaBlend(color.withValues(alpha: dark ? .34 : .18), scheme.surface);
    final ink = AppColors.readableAccent(color, base);
    final shown = Avatar.initials(name);
    final initials = shown.isEmpty || name == 'Unknown' ? null : shown;
    final photo = url?.trim();
    final hasPhoto = photo != null && photo.startsWith('http');
    final fallback = Center(
        child: initials == null
            ? Icon(Icons.person_rounded, color: ink, size: radius * 1.05)
            : Text(initials,
                maxLines: 1,
                style: TextStyle(
                    fontSize: radius * (initials.length > 1 ? 0.70 : 0.80),
                    height: 1,
                    letterSpacing: .4,
                    fontWeight: FontWeight.w700,
                    color: ink)));
    final dot = radius * 0.56;
    return Semantics(
      label: online ? '$name, online' : null,
      child: InkWell(
        onTap: onTap,
        borderRadius: corner,
        child: SizedBox(
          width: size,
          height: size,
          child: Stack(clipBehavior: Clip.none, children: [
            Positioned.fill(
                child: Container(
                    decoration: BoxDecoration(
                        borderRadius: corner,
                        gradient: LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [top, base]),
                        border: Border.all(color: color.withValues(alpha: dark ? .38 : .22), width: 1)),
                    child: ClipRRect(
                        borderRadius: BorderRadius.circular(size * 0.32 - 1),
                        child: hasPhoto
                            ? CachedNetImage(photo, fit: BoxFit.cover, width: size, height: size, cacheWidth: (size * 3).round(), placeholder: (_) => fallback, error: (_) => fallback)
                            : fallback))),
            if (online)
              Positioned(
                  bottom: -2,
                  right: -2,
                  child: Container(
                      width: dot,
                      height: dot,
                      decoration: BoxDecoration(
                          color: AppColors.success,
                          shape: BoxShape.circle,
                          border: Border.all(color: scheme.surface, width: 2.2)))),
          ]),
        ),
      ),
    );
  }
}
