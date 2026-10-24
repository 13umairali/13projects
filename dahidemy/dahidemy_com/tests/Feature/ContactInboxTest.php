<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The contact inbox: messages from the public site's contact form, listed with
 * backend-side search, sorting and paging.
 */
class ContactInboxTest extends TestCase
{
    protected function asAdmin(): static
    {
        return $this->withSession([
            config('saas.auth.token_key') => 'test-token',
            config('saas.auth.user_key') => ['name' => 'Test Admin', 'email' => 'admin@example.com'],
        ]);
    }

    private function fakeContacts(): void
    {
        Http::fake([
            '*/api/saas-contacts*' => Http::response(['status' => true, 'data' => [
                'current_page' => 1, 'per_page' => 10, 'total' => 2, 'last_page' => 1,
                'data' => [
                    [
                        'id' => 2, 'name' => 'Mona Guerrero', 'email' => 'sitis@mailinator.com',
                        'phone' => '+1 (575) 398-6754', 'subject' => 'Excepteur error in q',
                        'message' => 'Quo expedita veniam', 'isSeen' => 0,
                        'created_at' => '2026-07-14T09:28:03.000000Z',
                    ],
                    [
                        'id' => 1, 'name' => 'Jayme Reed', 'email' => 'movu@mailinator.com',
                        'phone' => '+1 (816) 263-8761', 'subject' => 'Doloremque illum qu',
                        'message' => 'Quo voluptatibus sit', 'isSeen' => 1,
                        'created_at' => '2026-07-14T09:27:56.000000Z',
                    ],
                ],
            ]]),
            '*' => Http::response(['status' => true, 'data' => []]),
        ]);
    }

    public function test_inbox_lists_messages_from_the_backend(): void
    {
        $this->fakeContacts();

        $response = $this->asAdmin()->get('/admin/contacts');

        $response->assertOk();
        $response->assertSee('Mona Guerrero');
        $response->assertSee('sitis@mailinator.com');
        $response->assertSee('+1 (575) 398-6754');
        $response->assertSee('Excepteur error in q');
        $response->assertSee('Quo expedita veniam');   // body ships with the list
        $response->assertSee('Jayme Reed');
        $response->assertSee('Showing 1–2 of 2 messages');

        // An unread message is flagged; a read one is not.
        $response->assertSee('New');
    }

    public function test_inbox_is_reachable_from_the_sidebar(): void
    {
        $this->fakeContacts();

        $this->asAdmin()->get('/admin/contacts')
            ->assertOk()
            ->assertSee('Contact messages')
            ->assertSee(route('admin.contacts.index'), false);
    }

    public function test_search_and_paging_are_passed_to_the_backend(): void
    {
        $this->fakeContacts();

        $this->asAdmin()->get('/admin/contacts?search=mona&per_page=25&page=2')->assertOk();

        Http::assertSent(fn ($r) => str_contains($r->url(), '/api/saas-contacts')
            && str_contains($r->url(), 'search=mona')
            && str_contains($r->url(), 'per_page=25')
            && str_contains($r->url(), 'page=2'));
    }

    public function test_sorting_is_passed_to_the_backend(): void
    {
        $this->fakeContacts();

        $this->asAdmin()->get('/admin/contacts?sort_by=name&sort_order=asc')->assertOk();

        Http::assertSent(fn ($r) => str_contains($r->url(), 'sort_by=name')
            && str_contains($r->url(), 'sort_order=asc'));
    }

    public function test_an_unsortable_column_falls_back_instead_of_being_forwarded(): void
    {
        $this->fakeContacts();

        // The backend only sorts by id/name/email/phone/subject/created_at; anything
        // else must not reach it (it would be silently ignored there, or worse).
        $this->asAdmin()->get('/admin/contacts?sort_by=password&sort_order=sideways')->assertOk();

        Http::assertSent(fn ($r) => str_contains($r->url(), 'sort_by=created_at')
            && str_contains($r->url(), 'sort_order=desc')
            && ! str_contains($r->url(), 'password'));
    }

    public function test_an_unreachable_backend_shows_a_warning_not_a_blank_page(): void
    {
        Http::fake(['*' => Http::response(['status' => false, 'message' => 'Down'], 500)]);

        $this->asAdmin()->get('/admin/contacts')
            ->assertOk()
            ->assertSee('Live data unavailable.')
            ->assertSee('No messages');
    }

    public function test_guests_cannot_read_the_inbox(): void
    {
        Http::fake();

        $this->get('/admin/contacts')->assertRedirect(route('login'));

        Http::assertNothingSent();
    }
}
