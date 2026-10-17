# Chat, Email and Friends lists

The main conversation lists, Friends list, friend requests and friend search use
`PeopleListRow` and `PeopleAvatar`. These are list components independent of the
notification center and push notification design.

- 50 logical pixel circular photos with a soft, two-initial fallback.
- 16 px sender names, 14 px email subjects, 13 px secondary text, 11 px times.
- Flat surfaces, 16 px horizontal padding and subtle dividers inset beneath text.
- Unread emphasis/counts, online presence, receipts, pin/mute/channel and star
  indicators remain available. Email contacts without a photo use initials.
- Friends: tap the row for the profile, use Chat for messages, or the three-dot
  menu for Email and Audio call (when permitted). Requests keep explicit actions.
- Rows grow with text scaling; names/previews truncate and request actions wrap.
  Light and dark theme colors come from the current app theme.

## Install and verify

Apply the ZIP paths relative to the repository root, then rebuild/release the
Flutter app. No server, dependency or native configuration updates are required
for this redesign. Existing notification behavior stays at its prior version.

From `flutter_app`:

```sh
flutter analyze --no-pub lib/core/people_list_row.dart lib/screens/inbox_screens.dart lib/screens/friends_screens.dart lib/screens/friend_requests_page.dart lib/screens/add_friend_page.dart test/people_list_row_test.dart
flutter test --no-pub test/people_list_row_test.dart test/chat_list_restore_test.dart test/friend_tools_test.dart
```

Optional: set `PEOPLE_PREVIEW_DIR` to an absolute directory before running tests
to render the actual Chat, Email and Friends list screens with synthetic data.
