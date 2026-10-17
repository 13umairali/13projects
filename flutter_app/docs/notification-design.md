# Notification reference design

The shared foreground banner and notification center card use a left brand/category
mark, sender and preview, a timestamp beside the right avatar, and View/Reply below.
The layout adapts to available width, text size and light/dark mode.

## External notifications

- Android: chat, email and social alerts use the native expanded-text template
  (`BigTextStyle`), taking the compact sender/preview/avatar structure from the
  Gmail/Snapchat examples and multiline expansion/inline Reply from WhatsApp.
  Both provide the sender photo or a circular initial immediately, a clear sender
  and preview, a system timestamp, and native View/inline Reply actions where
  applicable. The system owns avatar placement, compact spacing and expansion.
  There are no custom Android notification views or manufacturer-specific rules.
  The same native styles are used by the Flutter fallback when native posting
  is unavailable. Long/multiline content is retained for expansion; the OS can
  still shorten the visible preview to fit its viewport. Optional avatar
  downloads update the existing alert without alerting again or resurrecting a
  dismissed notification.
- iOS/iPadOS: collapsed banners use Apple's template. Expanding/long-pressing a
  notification uses the embedded `NotificationContent` extension for categories
  `MESSAGE_VIEW`, `MESSAGE_REPLY` and `CHAT_REPLY`. It displays the sender, preview and avatar, and leaves View/Reply to iOS.
  The OS header supplies the app logo; it is not repeated inside the card. Remote and local notifications use the
  same categories. Sender and preview use larger, scalable type; up to eight
  preview lines are visible when expanded, with a 40pt avatar. Avatar downloads
  are optional; initials appear immediately. Avatar fields support sender, caller
  and legacy from-avatar payloads.
- The OS owns the app identity header, icon, time, outer shape, action placement,
  privacy settings and collapsed truncation. These cannot be replaced by the
  supplied screenshot on every Android/iOS version. Long content can be ellipsized by the OS;
  View retains the existing full-message behavior. Incoming call screens use
  CallKit/Android call notifications and are separate from this card.

## Install and verify

Rebuild and install the app; replacing server files or hot-reloading Dart does not
update Android resources or embed an iOS extension. No backend changes are needed.

For iOS, open `ios/Runner.xcworkspace` on macOS after the repository's usual Flutter
setup. Select the same Apple development team for Runner and NotificationContent,
and provision `com.dahify.dahimail.NotificationContent` with that team. The target
is already a Runner dependency and is copied into `Runner.app/PlugIns` by the
Embed App Extensions phase. Its version/build come from Flutter's generated
configuration, matching Runner. It supports iPhone and iPad (iOS 14+).

Before release, build/archive on Xcode and test a new APNs alert and a local alert
for each category, in the foreground, background and after termination. Long-press
on iPhone/iPad to check the reference card. Check View, inline Reply, optional
avatar failure, long sender/body, accessibility text sizes, dark mode and hidden
lock-screen previews. Check Android collapsed, heads-up and expanded alerts on
multiple manufacturers as well. Existing delivered notifications do not change
when an app update is installed.

Linux validation covers Flutter widgets, Android framework rendering and action
contracts. It cannot establish iOS compilation/signing, actual manufacturer shade
rendering, or live FCM/APNs delivery; those device checks remain required.
