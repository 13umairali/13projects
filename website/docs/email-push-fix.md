# Email push while the app is closed

## Update: sync runs but `sender_type` is rejected

`SQLSTATE ... Data truncated for column 'sender_type'` on an outbound import is
a concrete persistence failure. The message schema permits `contact`, `agent`,
`ai` and `system`. All four import paths previously wrote `user` for sent mail,
so a sent-folder import could repeatedly fail the account sync before completion.
This patch uses `agent`, matching the existing SMTP/mobile compose paths. It also
fixes the two conversation API writers (`agent` and `type=message`) and aligns the
API agent-message count with that canonical value. No enum migration is needed;
do not disable MySQL strict mode to suppress the error.

The scheduler STARTED/FINISHED lines show that scheduled commands were invoked;
FINISHED alone does not mean every account imported successfully. Private
`state.changed` broadcasts update an open app and do not prove Firebase or phone
receipt. The fixes below retain the same Firebase transport and native receiver
used by chat.

### Diagnose the separate “Email push was not accepted” warning

This warning means no registered device accepted that attempt. It does not by
itself establish why: missing credentials, missing registrations, authentication
and provider rejection can all return false. Updated logs include:

- `FCM notification unavailable`: recipient user, event type, safe reason code.
- `FCM email device result`: user, message, device ID, platform, acceptance and
  provider code. A `web` acceptance does not prove an Android/iOS attempt succeeded.
- `Email push accepted by Firebase` / `Email push was not accepted`: account,
  workspace, recipient user, message and notification IDs. Firebase acceptance
  confirms the provider request succeeded; actual phone receipt is a separate check.

These entries do not include device tokens, message content or credentials.
For **the recipient user ID**, run on the server:

```sh
php artisan push:status --user=USER_ID
php artisan push:status --user=USER_ID --probe
```

Use the email account owner's user ID for incoming email, and the sending user's
ID for sent/status alerts. Do not substitute an account ID or workspace ID.
Check that an `android` or `ios` registration exists for that user. If none does,
sign into that user in the existing app and retry registration. If the provider
rejects it, use the reported code to repair that registration/configuration.

For a visible diagnostic alert while the recipient's app is closed:

```sh
php artisan push:status --user=USER_ID --test
```

This sends a real alert to all registered devices belonging to that user. Then
send a **new real email** and inspect the email-specific device result. Previous
failed push attempts are not automatically replayed by this patch, and historical
backfill remains intentionally silent. The phone's Device notifications screen
also shows Android's last native delivery/posting result.

## If running `php artisan schedule:run` delivers pending notifications

This result confirms email discovery and push delivery execute when the scheduler
runs. The server must repeat that command automatically. A single manual run
does not keep the scheduler alive. Check for a missing, disabled or failing cron
entry before changing Firebase or rebuilding the phone app.

The old setup command claimed that automation middleware was already registered
and no cron was needed. This project has no such middleware. The updated command
corrects that guidance and prints a cron entry using the current server's PHP,
Laravel directory and log path. `AUTOMATION_MODE` alone does not start anything.

## Why chat worked while email appeared only after opening the app

Chat follows the sender request -> saved message -> direct FCM -> native phone
notification path. Email uses the same `FcmPush::sendToUser` and native receiver,
but first needs a server process to discover new mail. The previous scheduled
command only enqueued imports; without a working `email-sync` worker, nothing
was imported or pushed until an app request performed the sync. Its scheduler
mutex could also block discovery for 24 hours after a killed PHP process.

This update:

- Runs the existing email import job directly from the scheduler with
  `mailtrixy:sync-emails --inline`. Import, OAuth refresh, automation and message
  persistence retain their existing logic; Firebase is called after persistence
  through the same sender and high-priority Android path as chat.
- Uses five-minute expiring polling/scheduler leases so interrupted processes
  recover automatically. The command starts no more accounts after its 40-second
  budget; it rotates the starting account to avoid starving later mailboxes.
  A single account/import can exceed that budget; the host's process limit still
  applies. Imported messages/cursors allow the next tick to resume.
- Searches for arrivals even when the initial IMAP inbox was empty (no last UID).
  Previously that inbox could remain marked backfilled and never import mail.
- Retains the earlier fix isolating internal notification subscriber/storage
  errors from Firebase delivery, plus existing notification IDs, mute controls,
  preferences and initial-history suppression.

No Android/iOS changes, database migration or dependency update are needed.
The ZIP includes every updated/new file from both fixes with original paths.

## Deploy

1. Back up and upload these runtime files from the ZIP:

   - `website/app/Console/Commands/SyncEmailsCommand.php`
   - `website/app/Console/Commands/SetupAutomationCommand.php`
   - `website/app/Http/Controllers/Api/ConversationController.php`
   - `website/app/Http/Controllers/Api/AnalyticsController.php`
   - `website/app/Services/Email/EmailSyncService.php`
   - `website/app/Services/Email/MessageAlerts.php`
   - `website/app/Services/FcmPush.php`
   - `website/config/automation.php`
   - `website/routes/console.php`

   The other files are tests and these instructions.

2. In the deployed Laravel directory, run:

   ```sh
   php artisan optimize:clear
   php artisan queue:restart
   ```

   Run `php artisan mailtrixy:sync-emails --inline --force` once to confirm the
   sent-folder import no longer produces the `sender_type` error. On a busy
   server, a held polling lease reports that the next tick will retry; wait for
   it to finish/expire. Verify a subsequent scheduled run completes successfully
   for the affected account.

3. On the **production server**, inside the deployed Laravel directory, run:

   ```sh
   php artisan mailtrixy:setup-automation --mode=scheduler
   ```

   Copy its generated cron entry into `crontab -e` for the application user, or
   your hosting control panel's Cron Jobs screen. It prints instructions; it does
   **not** install cron. Set the frequency to every minute (`* * * * *`). In a
   hosting panel with separate minute/hour/day fields, put only the command
   portion after `* * * * *` in the Command field. Use a PHP CLI binary supported
   by the app (PHP 8.2+) if your host requires a different path.

   The generated entry has this form (replace the example paths):

   ```cron
   * * * * * cd /absolute/path/to/website && /absolute/path/to/php artisan schedule:run >> /absolute/path/to/website/storage/logs/scheduler.log 2>&1
   ```

   The updated scheduler imports mail inline every 30 seconds while running.
   Other existing queue workers can continue handling their normal jobs.
   The cron user must be able to read the app and write `storage/` and
   `bootstrap/cache/`. Keep one scheduler cron entry; edit an existing one instead
   of adding duplicates. Confirm the entry is enabled in your hosting panel or
   listed by `crontab -l`. Do not discard output until automatic execution works.

   If shared hosting does not support Laravel background scheduler processes,
   use a dedicated cron entry for email instead:

   ```cron
   * * * * * cd /absolute/path/to/website && /absolute/path/to/php artisan mailtrixy:sync-emails --inline >> /absolute/path/to/website/storage/logs/email-sync.log 2>&1
   ```

   Choose one way to trigger email polling. Keep any existing scheduler used by
   other features. Background email discovery requires server cron; opening or
   reinstalling the phone app cannot replace it. The old command without
   `--inline` still uses the queue for compatibility.

## Verify on deployment

1. Open/sign into the existing phone app once to register the device, then close
   or background it. Send a real email from another address to the recipient's
   connected mailbox.
2. Wait for one or two cron ticks **without running Artisan manually**. Check
   `storage/logs/scheduler.log` for scheduled command output/errors; the main
   Laravel log also records `[Scheduler]` start/finish entries and a heartbeat
   every five minutes. If nothing runs automatically, fix the server cron entry
   or ask the hosting provider to enable it. `schedule:list` only lists tasks;
   it does not prove cron is running.
   Verify one email entry and one phone system notification.
   Repeat with the screen locked. Do not force-stop the app in Android Settings.
3. To diagnose immediately, run this on the server without opening the app:

   ```sh
   php artisan mailtrixy:sync-emails --inline --force
   ```

   To select one mailbox, append `--account=ACCOUNT_ID` using its numeric
   `email_accounts.id`. A healthy import reports `Synced email account ... inline`;
   this confirms import execution, not phone receipt. A busy poll reports that
   the next tick will retry. Failed accounts return a failure exit code and
   continue to other accounts. Inspect `storage/logs/laravel*.log` for sync and
   Firebase errors. If this command produces a notification while ordinary cron
   does not, correct the cron/PHP path or hosting scheduler configuration.

A Firebase test only checks provider delivery; use a **real email** for this
verification. Email and chat both use the New messages device category. OS
permissions, channel settings, Focus/DND and background restrictions still apply.
Initial historical mailbox imports deliberately do not alert for old emails.

## Tests

From `website/`, run:

```sh
php artisan test tests/Unit/BackgroundEmailPushTest.php tests/Unit/ImapSyncCursorTest.php tests/Unit/EmailSyncIdentityTest.php tests/Unit/GmailSyncPaginationTest.php tests/Unit/EmailRefreshTest.php tests/Unit/Realtime/PushDeliveryTest.php tests/Unit/Realtime/StateUpdatesTest.php
```

`BackgroundEmailPushTest` also has an opt-in socket regression using a disposable
loopback IMAP fixture at port 1143 (override `IMAP_TEST_PORT`), synthetic account
`sync@example.test`, and a private password file. It appends synthetic email;
never point it at a real mailbox:

```sh
IMAP_TEST_PASSWORD_FILE=/private/path/fixture-password php artisan test tests/Unit/BackgroundEmailPushTest.php
```

The socket regression verifies the real mailbox -> inline cron import -> saved
message/internal notification -> outgoing FCM request with no authenticated app
and no queue worker, plus no duplicate alert on a repeat poll. Firebase HTTP is
faked with synthetic credentials. Physical phone receipt and the production
cron configuration must still be verified on deployment.

It also imports a historical outbound message from `INBOX.Sent` with the actual
schema constraints, then verifies a subsequent inbound push. Provider fixtures
cover both directions for IMAP overview, full IMAP, Gmail and Outlook; API create
and reply cases enforce the same schema values. Push regressions distinguish
browser acceptance from Android rejection and check safe failure reporting.
