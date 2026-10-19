<?php

namespace Tests\Feature;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Support\AdminSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    const PASSWORD = 'correct horse battery staple';

    protected function setUp(): void
    {
        parent::setUp();

        config(['admin.password' => self::PASSWORD]);
        Sleep::fake();
    }

    private function signedIn(): static
    {
        return $this->withCookie(config('admin.cookie'), AdminSession::createToken());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function enquiry(array $attributes = []): Enquiry
    {
        return Enquiry::create(array_merge([
            'name' => 'Ann Lee',
            'email' => 'ann@example.co.uk',
            'phone' => '+44 7700 900123',
            'company' => 'Lee & Co',
            'services' => ['SEO'],
            'budget' => '£5k–15k',
            'message' => 'We need a new website and some flyers by spring.',
        ], $attributes));
    }

    public function test_signed_out_visitors_are_sent_to_the_login_page(): void
    {
        $enquiry = $this->enquiry();

        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get("/admin/enquiries/{$enquiry->id}")->assertRedirect('/admin/login');
        $this->patch("/admin/enquiries/{$enquiry->id}", ['status' => 'won'])->assertRedirect('/admin/login');
        $this->patchJson("/admin/enquiries/{$enquiry->id}", ['status' => 'won'])->assertUnauthorized();
        $this->delete("/admin/enquiries/{$enquiry->id}")->assertRedirect('/admin/login');

        $this->assertSame(EnquiryStatus::New, $enquiry->fresh()->status);
    }

    public function test_a_wrong_password_is_refused_after_a_pause(): void
    {
        $this->from('/admin/login')
            ->post('/admin/login', ['password' => 'nope'])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors(['password' => "That password isn't right."])
            ->assertCookieMissing(config('admin.cookie'));

        Sleep::assertSleptTimes(1);
    }

    public function test_the_right_password_signs_in_for_a_week(): void
    {
        $response = $this->post('/admin/login', ['password' => self::PASSWORD])
            ->assertRedirect('/admin');

        $cookie = $response->getCookie(config('admin.cookie'));
        $this->assertSame('/admin', $cookie->getPath());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
        $this->assertEqualsWithDelta(time() + 60 * 60 * 24 * 7, $cookie->getExpiresTime(), 5);
        Sleep::assertNeverSlept();
    }

    public function test_signed_in_admins_skip_the_login_page(): void
    {
        $this->signedIn()->get('/admin/login')->assertRedirect('/admin');
    }

    public function test_the_panel_stays_locked_without_a_password(): void
    {
        config(['admin.password' => '']);

        $this->from('/admin/login')
            ->post('/admin/login', ['password' => ''])
            ->assertSessionHasErrors([
                'password' => 'The admin panel is switched off. Set ADMIN_PASSWORD on the server to switch it on.',
            ]);
        $this->signedIn()->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_changing_the_password_signs_everyone_out(): void
    {
        $token = AdminSession::createToken();
        config(['admin.password' => 'a brand new password']);

        $this->withCookie(config('admin.cookie'), $token)->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_forged_and_expired_sessions_are_refused(): void
    {
        $this->withCookie(config('admin.cookie'), (time() + 3600).'.forged')->get('/admin')->assertRedirect('/admin/login');
        $this->withCookie(config('admin.cookie'), 'nonsense')->get('/admin')->assertRedirect('/admin/login');

        $token = AdminSession::createToken();
        $this->travel(8)->days();
        $this->withCookie(config('admin.cookie'), $token)->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_the_inbox_lists_enquiries_newest_first_with_the_counts(): void
    {
        $this->travelTo('2026-10-19 10:00:00');
        $this->enquiry(['name' => 'Old Client', 'received_at' => now()->subDays(3), 'status' => EnquiryStatus::Won]);
        $this->enquiry(['name' => 'New Client', 'received_at' => now()->subHour()]);

        $this->signedIn()->get('/admin')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSeeInOrder(['Waiting for a reply', 'Oldest arrived 1 hour ago', 'Last 7 days', '2 more than the week before'])
            ->assertSee('100% of closed enquiries')
            ->assertSee('Since 16 October 2026')
            ->assertSeeInOrder(['New Client', 'Old Client'])
            ->assertSee('2 enquiries shown');
    }

    public function test_the_inbox_opens_on_the_view_in_the_address_bar(): void
    {
        $this->enquiry(['name' => 'Old Client', 'status' => EnquiryStatus::Won]);
        $this->enquiry(['name' => 'New Client']);

        $this->signedIn()->get('/admin?status=won')->assertSee('1 enquiry shown');
        $this->signedIn()->get('/admin?q=nobody')->assertSee('0 enquiries shown')->assertSee('Nothing matches “nobody”.');
    }

    public function test_an_enquiry_page_shows_everything_that_was_sent(): void
    {
        $enquiry = $this->enquiry();

        $this->signedIn()->get("/admin/enquiries/{$enquiry->id}")
            ->assertOk()
            ->assertSee(['Ann Lee', 'Lee &amp; Co', 'ann@example.co.uk', '£5k–15k', 'We need a new website'], false)
            ->assertSee('mailto:ann@example.co.uk?subject=Re%3A%20your%20enquiry%20to%20STRICTMEDIA', false)
            ->assertSee('href="tel:+447700900123"', false);
    }

    public function test_status_changes_are_saved(): void
    {
        $enquiry = $this->enquiry();

        // As the status picker sends it, with fetch and the session cookie
        $this->signedIn()->withCredentials()->patchJson("/admin/enquiries/{$enquiry->id}", ['status' => 'contacted'])
            ->assertExactJson(['status' => 'contacted']);
        $this->assertSame(EnquiryStatus::Contacted, $enquiry->fresh()->status);

        $this->signedIn()->patch("/admin/enquiries/{$enquiry->id}", ['status' => 'bogus'])
            ->assertRedirect("/admin/enquiries/{$enquiry->id}");
        $this->assertSame(EnquiryStatus::Contacted, $enquiry->fresh()->status);
    }

    public function test_deleting_an_enquiry_goes_back_to_the_inbox(): void
    {
        $enquiry = $this->enquiry();

        $this->signedIn()->delete("/admin/enquiries/{$enquiry->id}")->assertRedirect('/admin');
        $this->assertModelMissing($enquiry);

        $this->signedIn()->get("/admin/enquiries/{$enquiry->id}")
            ->assertNotFound()
            ->assertSee("This enquiry isn't here", false);
    }

    public function test_signing_out_forgets_the_session(): void
    {
        $response = $this->signedIn()->post('/admin/logout')->assertRedirect('/admin/login');

        $this->assertLessThan(time(), $response->getCookie(config('admin.cookie'), false)->getExpiresTime());
    }
}
