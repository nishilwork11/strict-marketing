<?php

namespace Tests\Feature;

use App\Enums\EnquiryStatus;
use App\Mail\NewEnquiry;
use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function enquiry(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ann Lee',
            'email' => 'ann@example.co.uk',
            'phone' => '07700 900123',
            'company' => 'Lee & Co',
            'services' => ['SEO', 'Printing'],
            'budget' => '£5k–15k',
            'message' => 'We need a new website and some flyers by spring.',
        ], $overrides);
    }

    public function test_a_valid_enquiry_is_saved_and_thanked_by_first_name(): void
    {
        $this->postJson('/contact', $this->enquiry())
            ->assertOk()
            ->assertExactJson(['status' => 'success', 'message' => "Thanks, Ann. We'll reply within one working day."]);

        $enquiry = Enquiry::sole();
        $this->assertSame('Ann Lee', $enquiry->name);
        $this->assertSame('Lee & Co', $enquiry->company);
        $this->assertSame(['SEO', 'Printing'], $enquiry->services);
        $this->assertSame('£5k–15k', $enquiry->budget);
        $this->assertSame(EnquiryStatus::New, $enquiry->status);
        $this->assertNotNull($enquiry->received_at);
    }

    public function test_invalid_fields_get_their_messages(): void
    {
        $this->postJson('/contact', ['name' => ' A ', 'email' => 'nope', 'phone' => '12', 'message' => 'Too short'])
            ->assertUnprocessable()
            ->assertExactJson([
                'status' => 'error',
                'message' => 'Check the highlighted fields and send again.',
                'errors' => [
                    'name' => 'Enter your name.',
                    'email' => 'Enter an email address, like name@company.co.uk.',
                    'phone' => 'Enter a phone number, like 07700 900123.',
                    'message' => 'Tell us a bit more about the project (at least 20 characters).',
                ],
            ]);

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_only_offered_services_and_budgets_are_kept(): void
    {
        $this->postJson('/contact', $this->enquiry(['services' => ['SEO', 'Hacking'], 'budget' => '£1m']))->assertOk();

        $enquiry = Enquiry::sole();
        $this->assertSame(['SEO'], $enquiry->services);
        $this->assertSame('', $enquiry->budget);
    }

    public function test_long_answers_are_cut_to_length(): void
    {
        $this->postJson('/contact', $this->enquiry(['name' => str_repeat('a', 150), 'company' => str_repeat('b', 200)]))->assertOk();

        $enquiry = Enquiry::sole();
        $this->assertSame(100, mb_strlen($enquiry->name));
        $this->assertSame(120, mb_strlen($enquiry->company));
    }

    public function test_the_honeypot_pretends_to_send_but_saves_nothing(): void
    {
        $this->postJson('/contact', $this->enquiry(['website' => 'http://spam.example']))
            ->assertOk()
            ->assertExactJson(['status' => 'success', 'message' => "Thanks. We'll be in touch soon."]);

        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_without_javascript_the_form_comes_back_with_its_errors_and_input(): void
    {
        $this->post('/contact', $this->enquiry(['email' => 'nope', 'services' => ['CRM', 'Hacking']]))
            ->assertRedirect(url('/').'#contact')
            ->assertSessionHasErrors(['email' => 'Enter an email address, like name@company.co.uk.'])
            ->assertSessionHasInput('name', 'Ann Lee')
            ->assertSessionHasInput('services', ['CRM']);

        $this->followingRedirects()
            ->post('/contact', $this->enquiry(['email' => 'nope']))
            ->assertSee('Enter an email address, like name@company.co.uk.')
            ->assertSee('Check the highlighted fields and send again.')
            ->assertSee('value="Ann Lee"', false);
    }

    public function test_without_javascript_a_sent_enquiry_shows_the_thank_you(): void
    {
        $this->followingRedirects()
            ->post('/contact', $this->enquiry())
            ->assertSee('Enquiry sent.')
            ->assertSee("Thanks, Ann. We'll reply within one working day.");

        $this->assertDatabaseCount('enquiries', 1);
    }

    public function test_text_that_is_not_utf8_does_not_lose_the_form(): void
    {
        $this->post('/contact', $this->enquiry(['name' => "Ann \xA3", 'email' => 'nope']))
            ->assertSessionHasErrors('email')
            ->assertSessionHasInput('name', 'Ann ?');
    }

    public function test_each_enquiry_is_emailed_to_the_team_with_replies_going_to_the_visitor(): void
    {
        Mail::fake();

        $this->postJson('/contact', $this->enquiry())->assertOk();

        $enquiry = Enquiry::sole();
        Mail::assertSent(NewEnquiry::class, function (NewEnquiry $mail) use ($enquiry) {
            return $mail->hasTo('hello@strictmedia.co.uk')
                && $mail->hasReplyTo('ann@example.co.uk', 'Ann Lee')
                && $mail->hasSubject('New enquiry from Ann Lee')
                && $mail->adminUrl === route('admin.enquiries.show', $enquiry);
        });
    }

    public function test_the_email_shows_everything_that_was_sent(): void
    {
        $mail = new NewEnquiry($this->enquiry(['message' => "Line one *not bold*\n\nLine <b>two</b>"]), 'https://example.test/admin/enquiries/1');

        $mail->assertSeeInHtml('Lee &amp; Co', false)
            ->assertSeeInHtml('mailto:ann@example.co.uk', false)
            ->assertSeeInHtml('SEO, Printing')
            ->assertSeeInHtml('£5k–15k')
            ->assertSeeInHtml('Line one *not bold*<br><br>Line &lt;b&gt;two&lt;/b&gt;', false)
            ->assertSeeInHtml('https://example.test/admin/enquiries/1', false)
            ->assertSeeInText('New enquiry from Ann Lee');
    }

    public function test_an_enquiry_that_cannot_be_saved_is_still_emailed(): void
    {
        Mail::fake();
        Schema::drop('enquiries');

        $this->postJson('/contact', $this->enquiry())->assertOk()->assertJson(['status' => 'success']);

        Mail::assertSent(NewEnquiry::class, fn (NewEnquiry $mail) => $mail->adminUrl === null);
    }

    public function test_an_enquiry_that_cannot_be_emailed_is_still_saved(): void
    {
        Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP is down'));

        $this->postJson('/contact', $this->enquiry())->assertOk()->assertJson(['status' => 'success']);

        $this->assertDatabaseCount('enquiries', 1);
    }

    public function test_an_enquiry_that_can_be_neither_saved_nor_emailed_says_so(): void
    {
        Log::spy();
        Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP is down'));
        Schema::drop('enquiries');

        $this->postJson('/contact', $this->enquiry())
            ->assertServerError()
            ->assertJson([
                'status' => 'error',
                'message' => 'Your enquiry could not be sent. Try again, or email us at hello@strictmedia.co.uk.',
            ]);

        Log::shouldHaveReceived('error')->times(3);
    }
}
