<?php

use App\Jobs\SyncEmailAccountJob;
use App\Models\EmailAccount;
use App\Models\Message;
use App\Services\Email\EmailSyncService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Webklex\PHPIMAP\ClientManager;

// Only the mailbox boundary is synthetic. Exercise the real importer,
// persistence observer, internal notification, FCM sender and synchronous job.
class BackgroundMailboxFixture extends EmailSyncService
{
    public array $polled = [];
    public array $fail = [];
    public bool $slow = false;

    protected function syncImapMailbox(EmailAccount $account, bool $foreground = false): int
    {
        $this->polled[] = $account->id;
        if (in_array($account->id, $this->fail)) throw new RuntimeException('Synthetic mailbox failure');
        $created = $this->processImapOverview((object) [
            'uid' => 11, 'uidvalidity' => 9,
            'from' => 'Alice Example <alice@example.test>', 'to' => $account->email,
            'message_id' => '<background-'.$account->id.'@example.test>',
            'subject' => 'Background email', 'date' => now()->toIso8601String(),
        ], $account);
        if ($this->slow) Carbon::setTestNow(now()->addSeconds(41));
        return (int) $created;
    }
}

class ProviderImportFixture extends EmailSyncService
{
    public function import(string $provider, EmailAccount $account, string $from): bool
    {
        $id = '<'.$provider.'@example.test>';
        $headers = ['From' => $from, 'To' => 'alice@example.test', 'Subject' => 'Provider import',
            'Date' => now()->toRfc2822String(), 'Message-ID' => $id];
        if ($provider === 'imap-overview') {
            return $this->processImapOverview((object) ['uid' => 11, 'from' => $from,
                'to' => 'alice@example.test', 'subject' => 'Provider import', 'date' => $headers['Date'],
                'message_id' => $id], $account, 'INBOX.Sent');
        }
        if ($provider === 'imap-full') {
            $message = Mockery::mock(Webklex\PHPIMAP\Message::class);
            $message->shouldReceive('getMessageId')->andReturn(new Webklex\PHPIMAP\Attribute('message_id', $id));
            $message->shouldReceive('getFolderPath')->andReturn('INBOX.Sent');
            $message->shouldReceive('getUid')->andReturn(11);
            $message->shouldReceive('getFrom')->andReturn([$from]);
            $message->shouldReceive('getTo')->andReturn(['alice@example.test']);
            $message->shouldReceive('getCc', 'getBcc')->andReturn([]);
            $message->shouldReceive('getAttachments')->andReturn(new Webklex\PHPIMAP\Support\AttachmentCollection());
            $message->shouldReceive('getInReplyTo', 'getReferences')->andReturn(null);
            $message->shouldReceive('getSubject')->andReturn(new Webklex\PHPIMAP\Attribute('subject', 'Provider import'));
            $message->shouldReceive('getDate')->andReturn(new Webklex\PHPIMAP\Attribute('date', now()));
            $message->shouldReceive('getHTMLBody')->andReturn('');
            $message->shouldReceive('getTextBody')->andReturn('Provider body');
            return $this->processImapMessage($message, $account);
        }
        if ($provider === 'gmail') {
            $message = new Google\Service\Gmail\Message(['id' => 'gmail-synthetic', 'payload' => [
                'mimeType' => 'text/plain', 'body' => ['data' => rtrim(strtr(base64_encode('Provider body'), '+/', '-_'), '=')],
                'headers' => collect($headers)->map(fn ($value, $name) => ['name' => $name, 'value' => $value])->values()->all(),
            ]]);
            return $this->processGmailMessage($message, $account);
        }
        return $this->processOutlookMessage(['id' => 'outlook-synthetic', 'internetMessageId' => $id,
            'from' => ['emailAddress' => ['address' => $from]], 'subject' => 'Provider import',
            'toRecipients' => [['emailAddress' => ['address' => 'alice@example.test']]],
            'receivedDateTime' => now()->toIso8601String(), 'bodyPreview' => 'Provider body'],
            $account, new GuzzleHttp\Client(), 'synthetic-token');
    }
}

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
        'activitylog.enabled' => false, 'queue.default' => 'database', 'cache.default' => 'array']);
    DB::purge('sqlite');
    Cache::flush();
    DB::statement('PRAGMA foreign_keys = OFF');
    DB::statement('CREATE TABLE users (id INTEGER PRIMARY KEY, uuid TEXT, name TEXT, email TEXT, avatar_path TEXT, active_workspace_id INTEGER, notification_preferences TEXT)');
    DB::statement('CREATE TABLE workspaces (id INTEGER PRIMARY KEY, settings TEXT, deleted_at TEXT)');
    DB::statement('CREATE TABLE workspace_members (workspace_id INTEGER, user_id INTEGER, role TEXT)');
    foreach (['0001_01_01_000005_create_email_accounts_table.php', '0001_01_01_000006_create_contacts_and_crm_tables.php',
        '0001_01_01_000007_create_conversations_and_messages_tables.php'] as $migration) {
        (require database_path('migrations/'.$migration))->up();
    }
    DB::statement('ALTER TABLE email_accounts ADD COLUMN deleted_at TEXT');
    DB::statement('CREATE TABLE jobs (id INTEGER PRIMARY KEY, queue TEXT, payload TEXT, attempts INTEGER, reserved_at INTEGER, available_at INTEGER, created_at INTEGER)');
    DB::statement('CREATE TABLE notifications (id TEXT PRIMARY KEY, type TEXT, notifiable_type TEXT, notifiable_id INTEGER, data TEXT, read_at TEXT, created_at TEXT, updated_at TEXT)');
    DB::statement('CREATE TABLE device_tokens (id INTEGER PRIMARY KEY, user_id INTEGER, token TEXT, platform TEXT, app_version TEXT)');
    DB::table('users')->insert(['id' => 1, 'uuid' => 'owner', 'name' => 'Owner', 'email' => 'sync@example.test', 'notification_preferences' => '{}']);
    DB::table('workspaces')->insert(['id' => 7, 'settings' => '{"contact_auto_create":false}']);
    DB::table('workspace_members')->insert(['workspace_id' => 7, 'user_id' => 1, 'role' => 'owner']);
    DB::table('device_tokens')->insert(['user_id' => 1, 'token' => 'synthetic-phone', 'platform' => 'android', 'app_version' => '1.0.22+11']);
    $this->account = EmailAccount::create(['workspace_id' => 7, 'user_id' => 1, 'email' => 'sync@example.test', 'display_name' => 'Owner',
        'provider' => 'imap', 'status' => 'connected', 'last_synced_at' => now()->subHour(), 'sync_folders' => ['INBOX']]);
    $this->mailbox = new BackgroundMailboxFixture();
    app()->instance(EmailSyncService::class, $this->mailbox);
    Event::fake([App\Events\StateChanged::class, App\Events\ContactCreated::class, App\Events\MessageReceived::class]);
    Storage::fake('local');
    $key = openssl_pkey_new(['private_key_bits' => 2048]);
    openssl_pkey_export($key, $pem);
    Storage::disk('local')->put('fcm-test.json', json_encode(['project_id' => 'synthetic-project', 'client_email' => 'test@example.test', 'private_key' => $pem]));
    config(['services.fcm.credentials' => Storage::disk('local')->path('fcm-test.json')]);
    Cache::put('fcm_access_token', 'synthetic-access', 3600);
    Http::preventStrayRequests();
    Http::fake(['fcm.googleapis.com/*' => Http::response(['name' => 'accepted'])]);
});

afterEach(fn () => Carbon::setTestNow());

test('provider imports persist schema-valid senders and push only new inbound email', function (string $provider, bool $outbound) {
    $service = new ProviderImportFixture();
    $from = $outbound ? $this->account->email : 'alice@example.test';
    expect($service->import($provider, $this->account, $from))->toBeTrue();
    $message = Message::sole();
    expect($message->direction)->toBe($outbound ? 'outbound' : 'inbound')
        ->and($message->sender_type)->toBe($outbound ? 'agent' : 'contact')
        ->and($message->sender_id)->toBe($outbound ? 1 : null);
    expect($service->import($provider, $this->account, $from))->toBeFalse();
    expect(Message::count())->toBe(1);
    Http::assertSentCount($outbound ? 0 : 1);
})->with(['imap-overview', 'imap-full', 'gmail', 'outlook'])->with([true, false]);

test('conversation API creates and replies with schema-valid message values', function () {
    $user = App\Models\User::findOrFail(1);
    $user->active_workspace_id = 7;
    $this->actingAs($user);
    $contact = App\Models\Contact::create(['workspace_id' => 7, 'email' => 'alice@example.test', 'first_name' => 'Alice']);
    $request = Illuminate\Http\Request::create('/', 'POST', ['contact_id' => $contact->id, 'channel' => 'email', 'message' => 'First message']);
    $request->setUserResolver(fn () => $user);
    $controller = app(App\Http\Controllers\Api\ConversationController::class);
    expect($controller->store($request)->getStatusCode())->toBe(201);
    $conversation = App\Models\Conversation::sole();
    $reply = Illuminate\Http\Request::create('/', 'POST', ['body_text' => 'Reply message']);
    $reply->setUserResolver(fn () => $user);
    expect($controller->reply($reply, $conversation->id)->getStatusCode())->toBe(201);
    expect(Message::pluck('sender_type')->all())->toBe(['agent', 'agent']);
    expect(Message::pluck('type')->all())->toBe(['message', 'message']);
    expect($conversation->fresh()->messages_count)->toBe(2);
});

test('inline polling imports and pushes without an app request even with a stalled unique queued job', function () {
    expect(auth()->guest())->toBeTrue();
    $this->artisan('mailtrixy:sync-emails', ['--force' => true])->assertExitCode(0);
    expect(DB::table('jobs')->count())->toBe(1);
    Http::assertNothingSent();
    expect(Message::count())->toBe(0);

    $this->artisan('mailtrixy:sync-emails', ['--inline' => true, '--force' => true])->assertExitCode(0);
    expect(DB::table('jobs')->count())->toBe(1); // No queue worker ran.
    expect(Cache::lock(Illuminate\Bus\UniqueLock::getKey(new SyncEmailAccountJob($this->account)))->get())->toBeFalse();
    expect(Message::count())->toBe(1);
    expect(DB::table('notifications')->count())->toBe(1);
    Http::assertSentCount(1);
    Http::assertSent(fn ($request) => $request['message']['token'] === 'synthetic-phone'
        && $request['message']['data']['type'] === 'email_received'
        && $request['message']['data']['sender_name'] === 'Alice Example'
        && $request['message']['data']['recipient_user_id'] === '1'
        && $request['message']['data']['message_id'] === (string) Message::value('id')
        && $request['message']['data']['notification_id'] === DB::table('notifications')->value('id')
        && $request['message']['data']['reply_kind'] === 'conversation'
        && $request['message']['android']['priority'] === 'HIGH' && !isset($request['message']['notification']));
    Event::assertDispatched(App\Events\MessageReceived::class);

    $this->artisan('mailtrixy:sync-emails', ['--inline' => true, '--force' => true])->assertExitCode(0);
    expect(Message::count())->toBe(1);
    expect(DB::table('notifications')->count())->toBe(1);
    Http::assertSentCount(1);
});

test('inline polling respects disconnected deleted recent and explicitly selected accounts', function () {
    $this->account->update(['status' => 'disconnected']);
    $this->artisan('mailtrixy:sync-emails', ['--inline' => true])->assertExitCode(0);
    $this->account->update(['status' => 'connected', 'last_synced_at' => now()]);
    $this->artisan('mailtrixy:sync-emails', ['--inline' => true])->assertExitCode(0);
    $this->artisan('mailtrixy:sync-emails', ['--inline' => true, '--workspace' => 8, '--force' => true])->assertExitCode(0);
    $this->artisan('mailtrixy:sync-emails', ['--inline' => true, '--account' => 999, '--force' => true])->assertExitCode(0);
    $this->account->delete();
    $this->artisan('mailtrixy:sync-emails', ['--inline' => true, '--force' => true])->assertExitCode(0);
    expect($this->mailbox->polled)->toBe([]);
    Http::assertNothingSent();
});

test('inline polling continues with healthy accounts after a mailbox failure', function () {
    $other = $this->account->replicate();
    $other->uuid = Illuminate\Support\Str::uuid();
    $other->email = 'other@example.test';
    $other->save();
    $this->mailbox->fail = [$this->account->id];
    $this->artisan('mailtrixy:sync-emails', ['--inline' => true, '--force' => true])->assertExitCode(1);
    expect($this->mailbox->polled)->toBe([1, 2]);
    Http::assertSentCount(1);
});

test('an interrupted poll lease expires and the next tick recovers without an app opening', function () {
    Carbon::setTestNow(now());
    Cache::lock('email-sync:inline', 300)->get(); // Simulate a killed PHP process.
    $this->artisan('mailtrixy:sync-emails', ['--inline' => true, '--force' => true])->assertExitCode(0);
    Http::assertNothingSent();
    Carbon::setTestNow(now()->addMinutes(6));
    $this->artisan('mailtrixy:sync-emails', ['--inline' => true, '--force' => true])->assertExitCode(0);
    Http::assertSentCount(1);
});

test('slow accounts do not starve later accounts on subsequent cron ticks', function () {
    Carbon::setTestNow(now());
    $other = $this->account->replicate();
    $other->uuid = Illuminate\Support\Str::uuid();
    $other->email = 'other@example.test';
    $other->save();
    $this->mailbox->slow = true;
    $this->artisan('mailtrixy:sync-emails', ['--inline' => true, '--force' => true])->assertExitCode(0);
    expect($this->mailbox->polled)->toBe([1]);
    $this->artisan('mailtrixy:sync-emails', ['--inline' => true, '--force' => true])->assertExitCode(0);
    expect($this->mailbox->polled)->toBe([1, 2]);
    Http::assertSentCount(2);
});

test('scheduled email discovery uses inline import and recovers its own interrupted mutex', function () {
    Carbon::setTestNow(now());
    $event = collect(app(Schedule::class)->events())->first(fn ($event) => str_contains($event->command ?? '', 'mailtrixy:sync-emails'));
    expect($event)->not->toBeNull();
    expect($event->command)->toContain('--inline');
    expect($event->shouldSkipDueToOverlapping())->toBeFalse();
    expect($event->shouldSkipDueToOverlapping())->toBeTrue();
    Carbon::setTestNow(now()->addMinutes(6));
    expect($event->shouldSkipDueToOverlapping())->toBeFalse();
    $event->mutex->forget($event);
});

test('socket mailbox arrival reaches Firebase from cron with no authenticated app or queue worker', function () {
    if (!getenv('IMAP_TEST_PASSWORD_FILE')) $this->markTestSkipped('Requires the disposable loopback fixture documented in email-push-fix.md.');
    expect(auth()->guest())->toBeTrue();
    app()->instance(EmailSyncService::class, new EmailSyncService());
    $password = file_get_contents(getenv('IMAP_TEST_PASSWORD_FILE'));
    $this->account->update(['imap_host' => '127.0.0.1', 'imap_port' => (int) (getenv('IMAP_TEST_PORT') ?: 1143),
        'imap_username' => 'sync@example.test', 'imap_password' => $password, 'imap_encryption' => 'none',
        'sync_folders' => ['INBOX', 'Sent'], 'last_synced_at' => null]);
    $client = (new ClientManager())->make(['host' => '127.0.0.1', 'port' => $this->account->imap_port, 'encryption' => false,
        'username' => 'sync@example.test', 'password' => $password, 'protocol' => 'imap', 'authentication' => null]);
    $client->connect();
    try {
        if (!$client->getFolderByPath('INBOX.Sent')) $client->createFolder('INBOX.Sent', false);
        $sentTag = bin2hex(random_bytes(8));
        $client->getConnection()->appendMessage('INBOX.Sent', "From: sync@example.test\r\nTo: alice@example.test\r\nSubject: Sent history $sentTag\r\nDate: ".date(DATE_RFC2822, time() - 86400)."\r\nMessage-ID: <$sentTag@example.test>\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\nHistorical sent email.\r\n")->validatedData();
        $this->artisan('mailtrixy:sync-emails', ['--inline' => true, '--force' => true])->assertExitCode(0);
        Http::assertNothingSent(); // Initial history establishes the baseline.
        $sent = Message::where('message_id_header', '<'.$sentTag.'@example.test>')->firstOrFail();
        expect($sent->sender_type)->toBe('agent')->and($sent->direction)->toBe('outbound');
        expect($this->account->fresh()->error_message)->toBeNull();
        $tag = bin2hex(random_bytes(8));
        $client->getConnection()->appendMessage('INBOX', "From: Alice Example <alice@example.test>\r\nTo: sync@example.test\r\nSubject: Cron $tag\r\nDate: ".date(DATE_RFC2822)."\r\nMessage-ID: <$tag@example.test>\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\nCron email preview.\r\n")->validatedData();
        SyncEmailAccountJob::dispatch($this->account);
        expect(DB::table('jobs')->count())->toBe(1);
        $this->artisan('mailtrixy:sync-emails', ['--inline' => true, '--force' => true])->assertExitCode(0);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['message']['data']['type'] === 'email_received'
            && $request['message']['data']['notification_layout'] === 'v2'
            && $request['message']['android']['priority'] === 'HIGH');
        expect(Message::where('message_id_header', '<'.$tag.'@example.test>')->count())->toBe(1);
        expect(DB::table('jobs')->count())->toBe(1);
        $this->artisan('mailtrixy:sync-emails', ['--inline' => true, '--force' => true])->assertExitCode(0);
        Http::assertSentCount(1);
    } finally {
        try { $client->deleteFolder('INBOX.Sent', false); }
        finally { $client->disconnect(); }
    }
});


test('mobile notification Reply sends to the inbound sender when contacts are disabled', function () {
    DB::statement('ALTER TABLE messages ADD COLUMN schedule_status TEXT');
    (new ProviderImportFixture())->import('gmail', $this->account, 'alice@example.test');
    $incoming = Message::where('message_id_header', '<gmail@example.test>')->firstOrFail();
    expect($incoming->conversation->contact_id)->toBeNull();
    $user = App\Models\User::findOrFail(1);
    $user->active_workspace_id = 7;
    $this->actingAs($user);
    $request = Illuminate\Http\Request::create('/api/v1/inbox/conversations/'.$incoming->conversation_id.'/send', 'POST', ['body' => 'Reply from notification']);
    $request->setUserResolver(fn () => $user);
    $sender = Mockery::mock(App\Services\Email\EmailSendService::class);
    $sentMessage = null;
    $sender->shouldReceive('sendReply')->once()->andReturnUsing(function (Message $message, EmailAccount $account) use (&$sentMessage) {
        if (!$message->to_emails) throw new RuntimeException('Recipient required.');
        $sentMessage = $message;
        expect($account->id)->toBe($this->account->id);
        return true;
    });
    app()->instance(App\Services\Email\EmailSendService::class, $sender);
    $response = app(App\Http\Controllers\Api\Mobile\SendController::class)->reply($request, $incoming->conversation_id);
    expect($response->getStatusCode())->toBe(201, $response->getData(true)['message'] ?? '');
    expect($response->getData(true)['delivery_status'])->toBe('sent');
    expect($sentMessage->to_emails)->toBe(['alice@example.test']);
    expect($sentMessage->in_reply_to)->toBe($incoming->message_id_header);
    expect($sentMessage->body_text)->toBe('Reply from notification');
});
