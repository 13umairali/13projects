import 'package:flutter/material.dart';
import 'theme.dart';

/// "Last seen yesterday at 3:45 PM" -> "Yesterday", "Last seen Oct 18 at 9:10 AM" -> "Oct 18", "Last seen today at 3:45 PM" -> "3:45 PM",
/// "Last seen Sunday" -> "Oct 18" (date of that weekday), "Online" stays. Same short wording the Chats / Mail rows show.
String shortSeen(String raw) {
  var t = raw.trim();
  if (t.isEmpty) return '';
  if (RegExp(r'^online\b', caseSensitive: false).hasMatch(t) || RegExp(r'\bonline now\b', caseSensitive: false).hasMatch(t)) return 'Online';
  t = t.replaceFirst(RegExp(r'^(last\s+)?(seen|active)\s*', caseSensitive: false), '').trim();
  final low = t.toLowerCase();
  final at = RegExp(r'\s+at\s+(.+)$', caseSensitive: false).firstMatch(t);
  if (low.startsWith('today') && at != null) return at.group(1)!.trim();
  if (low.startsWith('yesterday')) return 'Yesterday';
  // Weekday names ("Sunday", "Last seen Sun at 8 PM") -> the calendar date, like Chats ("Oct 18").
  const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  for (var i = 0; i < 7; i++) {
    if (low.startsWith(days[i]) || low.startsWith(days[i].substring(0, 3))) {
      final now = DateTime.now();
      var back = (now.weekday - 1 - i) % 7;
      if (back == 0) back = 7; // same weekday = a week ago (today / yesterday are handled above)
      final d = now.subtract(Duration(days: back));
      return '${months[d.month - 1]} ${d.day}';
    }
  }
  if (at != null) t = t.substring(0, at.start).trim(); // "Oct 18 at 9:10 AM" -> "Oct 18"
  t = t.replaceFirst(RegExp(r',\s*\d{4}$'), ''); // drop the year
  if (t.isEmpty) return '';
  return t[0].toUpperCase() + t.substring(1);
}

/// Friends screen (bottom bar) rows for every tab: All · Friends · Team · Customers.
///
/// Uses the same template as the Chats / Mail rows: rounded card (same as the Chats / Mail cards), avatar on the left, 16 px name on the first line
/// with the timestamp at the top right (identical style to Chats / Mail), grey 13 px detail line below,
/// 11 px timestamp style.
///
///  - Line 1: full name (one line; cut with "…" only when it would touch the timestamp) · timestamp.
///  - Line 2: email / role · chat / call buttons on the right, tightly spaced. Labels (Friend / Team …) sit under the email.
class PeopleTabCard extends StatelessWidget {
  final Widget avatar;
  final String name, subtitle, status;
  final Color? statusColor;
  final List<Widget> badges, actions;
  final bool selected;
  final VoidCallback? onTap, onLongPress;
  const PeopleTabCard(
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
    final actionTheme = Theme.of(context).copyWith(
      iconButtonTheme: IconButtonThemeData(
        style: IconButton.styleFrom(
          backgroundColor: scheme.primary.withValues(alpha: .09),
          foregroundColor: scheme.primary,
          minimumSize: const Size(32, 32),
          fixedSize: const Size(32, 32),
          padding: EdgeInsets.zero,
          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
          visualDensity: VisualDensity.compact,
          iconSize: 18,
          shape: const CircleBorder(),
        ),
      ),
    );
    final big = MediaQuery.textScalerOf(context).scale(1) > 1.3;
    final detail = Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
      if (subtitle.isNotEmpty) Text(subtitle, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 13, height: 1.4, color: muted)),
      // Labels (Friend / Team …) directly under the email.
      if (badges.isNotEmpty)
        Padding(
          padding: EdgeInsets.only(top: subtitle.isNotEmpty ? 3 : 0),
          child: Wrap(spacing: 4, runSpacing: 2, children: badges),
        ),
    ]);
    final active = statusColor != null; // online
    final seen = shortSeen(status);
    final buttons = Row(mainAxisSize: MainAxisSize.min, children: [
      for (var i = 0; i < actions.length; i++) ...[if (i > 0) const SizedBox(width: 2), actions[i]],
    ]);
    final decoration = cardDecoration(context);
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      decoration: selected ? decoration.copyWith(color: scheme.primaryContainer) : decoration,
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(14),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
        onTap: onTap,
        onLongPress: onLongPress,
        child: Column(children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 12),
            child: Theme(
              data: actionTheme,
              child: Row(crossAxisAlignment: CrossAxisAlignment.center, children: [
                avatar,
                const SizedBox(width: 12),
                Expanded(
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
                    Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Expanded(
                        child: Text(name,
                            maxLines: 1,
                            softWrap: false,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(fontSize: 15.5, height: 1.3, fontWeight: FontWeight.w600, color: scheme.onSurface)),
                      ),
                      if (seen.isNotEmpty) ...[
                        const SizedBox(width: 8),
                        // Exactly the time style of the Chats / Mail rows.
                        ConstrainedBox(
                          constraints: const BoxConstraints(maxWidth: 76),
                          child: Text(seen,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              textAlign: TextAlign.end,
                              style: TextStyle(fontSize: 11, height: 1.5, fontWeight: active ? FontWeight.w600 : FontWeight.w400, color: active ? scheme.primary : muted)),
                        ),
                      ],
                    ]),
                    Padding(
                      padding: const EdgeInsets.only(top: 1),
                      child: Row(crossAxisAlignment: CrossAxisAlignment.center, children: [
                        Expanded(child: detail),
                        if (actions.isNotEmpty && !big) Padding(padding: const EdgeInsetsDirectional.only(start: 8), child: buttons),
                      ]),
                    ),
                    if (actions.isNotEmpty && big)
                      Padding(padding: const EdgeInsets.only(top: 6), child: Wrap(spacing: 2, runSpacing: 6, children: actions)),
                  ]),
                ),
              ]),
            ),
          ),
        ]),
      ),
      ),
    );
  }
}
