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
  Widget build(BuildContext context) => GestureDetector(
      onLongPress: onLongPress,
      child: AppCard(
        padding: const EdgeInsets.fromLTRB(12, 10, 4, 10),
        color: selected ? Theme.of(context).colorScheme.primaryContainer : null,
        onTap: onTap,
        child: LayoutBuilder(builder: (context, constraints) {
          final stackActions = constraints.maxWidth < 280 ||
              MediaQuery.textScalerOf(context).scale(1) > 1.3;
          final text =
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(name,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style:
                    const TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
            if (badges.isNotEmpty)
              Padding(
                  padding: const EdgeInsets.only(top: 4, bottom: 2),
                  child: Wrap(spacing: 6, runSpacing: 4, children: badges)),
            if (subtitle.isNotEmpty)
              Text(subtitle,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                      fontSize: 12.5, color: AppColors.mutedOf(context))),
            if (status.isNotEmpty)
              Text(status,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                      fontSize: 12,
                      color: statusColor ?? AppColors.mutedOf(context))),
          ]);
          return Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(children: [
                  avatar,
                  const SizedBox(width: 12),
                  Expanded(child: text),
                  if (!stackActions) ...actions,
                ]),
                if (stackActions && actions.isNotEmpty)
                  Padding(
                      padding: const EdgeInsets.only(top: 6, left: 4),
                      child: Wrap(
                          alignment: WrapAlignment.end,
                          spacing: 4,
                          runSpacing: 4,
                          children: actions)),
              ]);
        }),
      ));
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
        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 1),
        decoration:
            BoxDecoration(color: fill, borderRadius: BorderRadius.circular(8)),
        child: Text(label,
            style: TextStyle(
                fontSize: 10.5,
                fontWeight: FontWeight.w700,
                color: AppColors.readableAccent(c, fill))));
  }
}
