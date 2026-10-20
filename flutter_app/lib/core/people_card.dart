import 'package:flutter/material.dart';
import 'theme.dart';
import 'widgets.dart';

/// Shared Everyone-card presentation for all filters in the Friends screen.
class PeopleCard extends StatelessWidget {
  final Widget avatar;
  final String name, subtitle, status;
  final Color? statusColor;
  final List<Widget> badges, actions;
  final bool selected;
  final VoidCallback? onTap, onLongPress;
  const PeopleCard(
      {super.key,
      required this.avatar,
      required this.name,
      this.subtitle = '',
      this.status = '',
      this.statusColor,
      this.badges = const [],
      this.actions = const [],
      this.selected = false,
      this.onTap,
      this.onLongPress});

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final muted = AppColors.mutedOf(context);
    // Call / chat buttons share one look: 40 px tonal circles in the brand colour.
    final actionTheme = Theme.of(context).copyWith(
      iconButtonTheme: IconButtonThemeData(
        style: IconButton.styleFrom(
          backgroundColor: scheme.primary.withValues(alpha: .09),
          foregroundColor: scheme.primary,
          minimumSize: const Size(40, 40),
          fixedSize: const Size(40, 40),
          padding: EdgeInsets.zero,
          iconSize: 20,
          shape: const CircleBorder(),
        ),
      ),
    );
    return GestureDetector(
      onLongPress: onLongPress,
      child: AppCard(
        padding: const EdgeInsets.all(14),
        margin: const EdgeInsets.only(bottom: 8),
        color: selected ? scheme.primaryContainer : null,
        onTap: onTap,
        child: LayoutBuilder(builder: (context, constraints) {
          final stackActions = constraints.maxWidth < 300 || MediaQuery.textScalerOf(context).scale(1) > 1.3;
          final text = Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
            Row(children: [
              Flexible(
                child: Text(name,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(fontWeight: FontWeight.w600, fontSize: 15.5, height: 1.25, color: scheme.onSurface)),
              ),
              for (final b in badges) Padding(padding: const EdgeInsetsDirectional.only(start: 6), child: b),
            ]),
            if (subtitle.isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(top: 2),
                child: Text(subtitle, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 13, color: muted)),
              ),
            if (status.isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(top: 1),
                child: Text(status, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 12.5, color: statusColor ?? muted)),
              ),
          ]);
          Widget spaced(List<Widget> items) => Row(mainAxisSize: MainAxisSize.min, children: [
                for (var i = 0; i < items.length; i++) ...[if (i > 0) const SizedBox(width: 6), items[i]],
              ]);
          return Theme(
            data: actionTheme,
            child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
              Row(crossAxisAlignment: CrossAxisAlignment.center, children: [
                avatar,
                const SizedBox(width: 12),
                Expanded(child: text),
                if (!stackActions && actions.isNotEmpty) Padding(padding: const EdgeInsetsDirectional.only(start: 8), child: spaced(actions)),
              ]),
              if (stackActions && actions.isNotEmpty)
                Padding(
                  padding: const EdgeInsets.only(top: 10),
                  child: Wrap(alignment: WrapAlignment.end, spacing: 6, runSpacing: 6, children: actions),
                ),
            ]),
          );
        }),
      ),
    );
  }
}

class PeopleCardBadge extends StatelessWidget {
  final String label;
  final Color? color;
  const PeopleCardBadge(this.label, {super.key, this.color});
  @override
  Widget build(BuildContext context) {
    final c = color ?? AppColors.statusColor(label);
    final fill = Color.alphaBlend(
        c.withValues(alpha: .12), Theme.of(context).colorScheme.surface);
    return Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
        decoration:
            BoxDecoration(color: fill, borderRadius: BorderRadius.circular(999)),
        child: Text(label,
            style: TextStyle(
                fontSize: 10.5,
                fontWeight: FontWeight.w700,
                color: AppColors.readableAccent(c, fill))));
  }
}
