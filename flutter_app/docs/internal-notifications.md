# Internal notification center

The Notifications screen uses a compact card with one category badge on the
left, the existing sender/content in the middle, and the existing timestamp
and sender avatar on the right. View/Reply buttons are removed from this screen;
tap a card to open its conversation or event. Unread emphasis remains.

Regular text and a short preview fit about eight to nine cards in a 390×780
viewport. Longer content or accessibility text sizes increase the card height.
The category has a tooltip and screen-reader label. Both themes are supported.

The screen requests 20 notifications per page and loads older pages while
scrolling, including read entries. The server limits each request rather than
the total history. Timer/realtime refresh waits while the user is scrolled down;
pull to refresh or return to the top for the latest list. Tapping an older card
marks it read without discarding its loaded history before navigation.

## Install

Deploy `website/app/Http/Controllers/NotificationController.php` and rebuild the
Flutter app with the included new/updated Dart files. Clear cached Laravel routes
and configuration using the project's normal deployment process; no migration
or new package dependency is required.

The archive for this change contains only the internal-center changes and their
checks. It applies on top of the previous push-notification update. The existing
push notification renderer, actions and shared foreground banner are unchanged.
