<?php

namespace Tests\Feature;

use App\Mail\TestEmail;
use App\Support\AdminSession;
use App\Support\MailCheck;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage as SymfonySentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\RawMessage;
use Tests\TestCase;

class AdminEmailTest extends TestCase
{
    const PASSWORD = 'mailbox password #1';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'admin.password' => 'correct horse battery staple',
            'mail.default' => 'smtp',
            'mail.mailers.smtp.scheme' => 'smtps',
            'mail.mailers.smtp.host' => 'smtp.example.net',
            'mail.mailers.smtp.port' => 465,
            'mail.mailers.smtp.username' => 'hello@strictmedia.co.uk',
            'mail.mailers.smtp.password' => self::PASSWORD,
            'mail.from.address' => 'hello@strictmedia.co.uk',
        ]);

        $this->usingEnv([]);
    }

    private function signedIn(): static
    {
        return $this->withCookie(config('admin.cookie'), AdminSession::createToken());
    }

    /**
     * What the page reads from .env, and whether the config is cached.
     *
     * @param  array<string, string|null>  $dotenv
     */
    private function usingEnv(array $dotenv, bool $cached = false, array $newerThanCache = []): void
    {
        $this->app->instance(MailCheck::class, new MailCheck($cached, $dotenv, $newerThanCache));
    }

    public function test_signed_out_visitors_are_sent_to_the_login_page(): void
    {
        Mail::fake();

        $this->get('/admin/email')->assertRedirect('/admin/login');
        $this->post('/admin/email', ['to' => 'ann@example.co.uk'])->assertRedirect('/admin/login');

        Mail::assertNothingSent();
    }

    public function test_the_page_shows_the_settings_in_use_but_never_the_password(): void
    {
        $this->signedIn()->get('/admin/email')
            ->assertOk()
            ->assertSee('smtp.example.net:465')
            ->assertSee('SSL/TLS')
            ->assertSee('hello@strictmedia.co.uk')
            ->assertSee('Set, 19 characters')
            ->assertDontSee(self::PASSWORD)
            ->assertDontSee('role="alert"', false);
    }

    public function test_the_admin_bar_links_to_the_page(): void
    {
        $this->signedIn()->get('/admin')->assertSee('href="'.route('admin.email').'"', false);
    }

    public function test_a_mailer_that_does_not_send_is_flagged(): void
    {
        config(['mail.default' => 'log']);

        $this->signedIn()->get('/admin/email')
            ->assertSee("Emails aren't being sent")
            ->assertSeeText('are only written to the log file, if at all')
            ->assertSee('Log file (not sent)');
    }

    public function test_a_cached_config_older_than_the_env_file_is_flagged(): void
    {
        config(['mail.default' => 'log']);
        $this->usingEnv([
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'smtp.example.net',
            'MAIL_PORT' => '465',
            'MAIL_PASSWORD' => 'the new password',
            'MAIL_SCHEME' => 'null',
        ], cached: true);

        $this->signedIn()->get('/admin/email')
            ->assertSee("The site isn't using your latest .env")
            ->assertSeeText('MAIL_MAILER is smtp in .env, but log in the saved copy')
            ->assertSeeText('MAIL_PASSWORD is different')
            ->assertSee('php'.PHP_MAJOR_VERSION.PHP_MINOR_VERSION.' artisan config:cache')
            ->assertDontSeeText('MAIL_HOST is')
            ->assertDontSeeText('MAIL_PORT is')
            ->assertDontSee('the new password')
            ->assertDontSee(self::PASSWORD)
            // Until the cache is refreshed, the other checks would only describe the old settings
            ->assertDontSee("Emails aren't being sent");
    }

    public function test_env_values_are_read_the_way_laravel_reads_them(): void
    {
        config(['mail.mailers.smtp.password' => 'S3cret', 'mail.from.name' => true]);
        // .env values that env() unquotes or turns into true, as config:cache saved them
        $this->usingEnv(['MAIL_PASSWORD' => '"S3cret"', 'MAIL_FROM_NAME' => 'True', 'MAIL_PORT' => '465'], cached: true);

        $this->signedIn()->get('/admin/email')->assertDontSee("isn't using your latest .env", false);
    }

    public function test_files_changed_after_the_config_was_cached_are_flagged(): void
    {
        $this->usingEnv([], cached: true, newerThanCache: ['.env', 'config/site.php']);

        $this->signedIn()->get('/admin/email')
            ->assertSee('The saved settings are older than the files they come from')
            ->assertSeeText('.env and config/site.php changed after it was made');
    }

    public function test_a_missing_enquiry_address_is_flagged(): void
    {
        config(['site.contact.notify' => null]);
        $this->usingEnv([], cached: true);

        $this->signedIn()->get('/admin/email')
            ->assertSee("Enquiries aren't emailed to anyone")
            ->assertSeeText('older than this version of the site');
    }

    public function test_the_encryption_shown_is_the_one_laravel_uses(): void
    {
        // Symfony encrypts from the start on port 465 even when the scheme says smtp
        config(['mail.mailers.smtp.scheme' => 'smtp']);
        $this->signedIn()->get('/admin/email')->assertSee('SSL/TLS');

        config(['mail.mailers.smtp.port' => 587]);
        $this->signedIn()->get('/admin/email')->assertSee('STARTTLS, when the server offers it');

        config(['mail.mailers.smtp.require_tls' => true]);
        $this->signedIn()->get('/admin/email')->assertSee('STARTTLS (required)');
    }

    public function test_a_cached_config_that_matches_the_env_file_is_not_flagged(): void
    {
        $this->usingEnv(['MAIL_HOST' => 'smtp.example.net', 'MAIL_PORT' => '465', 'MAIL_SCHEME' => 'smtps'], cached: true);

        $this->signedIn()->get('/admin/email')
            ->assertSee('Saved copy of .env (config cache)')
            ->assertDontSee("isn't using your latest .env");
    }

    public function test_a_mistake_in_the_env_file_is_flagged_without_showing_the_value(): void
    {
        $env = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($env, "APP_NAME=STRICTMEDIA\nMAIL_PASSWORD=has a space\n");

        try {
            $this->app->useEnvironmentPath(dirname($env))->loadEnvironmentFrom(basename($env));
            $check = new MailCheck(cached: true);
        } finally {
            unlink($env);
        }

        $this->assertSame([], $check->dotenv);
        $this->assertStringStartsWith('The `MAIL_PASSWORD` line: encountered unexpected whitespace.', $check->dotenvError);
        $this->assertStringNotContainsString('has a space', $check->dotenvError);
    }

    public function test_settings_that_mail_servers_refuse_are_flagged(): void
    {
        config([
            'mail.mailers.smtp.scheme' => 'smtps',
            'mail.mailers.smtp.port' => 587,
            'mail.mailers.smtp.password' => '',
            'mail.from.address' => 'hello@example.com',
        ]);

        $this->signedIn()->get('/admin/email')
            ->assertSee('MAIL_PASSWORD is empty')
            ->assertSee("Port 587 doesn't take MAIL_SCHEME=smtps")
            ->assertSee("The From address isn't on your mailbox's domain")
            ->assertSeeText('Set MAIL_FROM_ADDRESS=hello@strictmedia.co.uk.');
    }

    public function test_a_from_address_on_the_mailboxs_domain_is_fine(): void
    {
        config(['mail.from.address' => 'website@strictmedia.co.uk']);

        $this->signedIn()->get('/admin/email')->assertDontSee('role="alert"', false);
    }

    public function test_a_scheme_that_laravel_does_not_know_is_flagged(): void
    {
        config(['mail.mailers.smtp.scheme' => 'tls']);

        $this->signedIn()->get('/admin/email')->assertSee("MAIL_SCHEME can't be tls");
    }

    public function test_a_test_email_goes_to_the_address_given(): void
    {
        Mail::fake();

        $this->signedIn()->post('/admin/email', ['to' => 'ann@example.co.uk'])
            ->assertRedirect('/admin/email')
            ->assertSessionHas('test.sent', true);

        Mail::assertSent(TestEmail::class, fn (TestEmail $mail) => $mail->hasTo('ann@example.co.uk'));

        $this->signedIn()->get('/admin/email')
            ->assertSee('Sent to ann@example.co.uk')
            ->assertSee('value="ann@example.co.uk"', false);
    }

    public function test_the_address_has_to_be_an_email_address(): void
    {
        Mail::fake();

        $this->signedIn()->post('/admin/email', ['to' => 'nope'])
            ->assertRedirect('/admin/email')
            ->assertSessionHasErrors(['to' => 'Enter an email address, like name@company.co.uk.']);

        $this->signedIn()->post('/admin/email', ['to' => ['ann@example.co.uk']])->assertSessionHasErrors('to');

        Mail::assertNothingSent();
    }

    public function test_a_failed_test_shows_the_mail_servers_error_and_what_to_do(): void
    {
        Mail::shouldReceive('to')->andThrow(new TransportException(
            'Failed to authenticate on SMTP server with username "hello@strictmedia.co.uk" using the following authenticators: "LOGIN", "PLAIN". '
            .'Authenticator "LOGIN" returned "Expected response code "235" but got code "535", with message "535 Authentication credentials invalid".".'
        ));

        $this->signedIn()->post('/admin/email', ['to' => 'hello@strictmedia.co.uk'])->assertSessionHas('test.sent', false);

        $this->signedIn()->get('/admin/email')
            ->assertSee("Couldn't send it to hello@strictmedia.co.uk", false)
            ->assertSee('535 Authentication credentials invalid')
            ->assertSee('The mail server turned down the username or password.');
    }

    public function test_when_the_mailer_does_not_send_the_test_says_so(): void
    {
        config(['mail.default' => 'log']);
        Mail::fake();

        $this->signedIn()->post('/admin/email', ['to' => 'hello@strictmedia.co.uk']);

        $this->signedIn()->get('/admin/email')
            ->assertSee('Not sent')
            // Settings in running text are followed straight by their punctuation
            ->assertSee('>log</code>, so the test', false)
            ->assertDontSee('Sent to hello@strictmedia.co.uk');
    }

    public function test_a_failover_mailer_that_ends_in_the_log_is_flagged(): void
    {
        // Laravel's own failover mailer tries smtp, then log
        config(['mail.default' => 'failover']);

        $this->signedIn()->get('/admin/email')->assertSee('A failed email would look sent');
    }

    public function test_a_mail_url_that_cannot_be_read_is_flagged_rather_than_breaking_the_page(): void
    {
        config(['mail.mailers.smtp.url' => 'smtp://hello%40strictmedia.co.uk:pa#ss@smtp.ionos.co.uk:465']);

        $this->signedIn()->get('/admin/email')->assertOk()->assertSee("MAIL_URL can't be read");
        $this->assertNotNull((new MailCheck(false, []))->explain(new TransportException('535 Authentication credentials invalid')));
    }

    public function test_the_mail_servers_reply_is_kept_to_utf8(): void
    {
        $sent = new SentMessage(new SymfonySentMessage(
            new RawMessage('Test'),
            new Envelope(new Address('hello@strictmedia.co.uk'), [new Address('ann@example.co.uk')]),
        ));
        $sent->appendDebug("[2026-10-22T10:00:00.000000+01:00] > AUTH PLAIN c2VjcmV0\r\n[2026-10-22T10:00:01.000000+01:00] < 250 Requeued mail accept\xE9\r\n");

        $this->assertSame('250 Requeued mail accept?', (new MailCheck(false, []))->serverReply($sent));
    }

    public function test_the_usual_mail_server_errors_are_explained(): void
    {
        $check = new MailCheck(false, []);
        $explain = fn (string $error) => $check->explain(new TransportException($error));

        // A server that goes quiet: what it means depends on how far the conversation got
        $silence = function (string ...$replies) use ($check) {
            $e = new TransportException('Connection to "ssl://smtp.example.net:465" timed out.');
            $e->appendDebug(implode('', array_map(fn ($reply) => "[2026-10-22T10:00:00.000000+01:00] < {$reply}\r\n", $replies)));

            return $check->explain($e);
        };
        $this->assertStringStartsWith("The site connected to smtp.example.net:465, but the server didn't say hello within 15 seconds", $silence());
        $this->assertStringStartsWith('smtp.example.net:465 stopped answering partway through.', $silence('220 ready', '250 OK'));
        $this->assertStringStartsWith("The site sent the email to smtp.example.net:465, but the server didn't confirm it", $silence('220 ready', '354 go ahead'));
        $this->assertStringStartsWith("The site couldn't reach smtp.example.net:465.", $explain(
            'Connection could not be established with host "ssl://smtp.example.net:465": stream_socket_client(): Unable to connect to ssl://smtp.example.net:465 (Connection timed out)'
        ));
        $this->assertStringStartsWith('Nothing answered at smtp.example.net:465.', $explain(
            'Connection could not be established with host "ssl://smtp.example.net:465": stream_socket_client(): Unable to connect to ssl://smtp.example.net:465 (Connection refused)'
        ));
        $this->assertStringStartsWith("There's no server called smtp.example.net.", $explain(
            'Connection could not be established with host "ssl://smtp.example.net:465": stream_socket_client(): php_network_getaddresses: getaddrinfo for smtp.example.net failed: Name or service not known'
        ));
        $this->assertStringStartsWith('The secure connection failed.', $explain(
            'Connection could not be established with host "ssl://smtp.example.net:587": stream_socket_client(): SSL operation failed with code 1. OpenSSL Error messages: error:0A00010B:SSL routines::wrong version number'
        ));
        $this->assertStringStartsWith('The mail server turned down the sender', $explain(
            'Expected response code "250" but got code "550", with message "550 5.7.1 Sender address is not allowed".'
        ));
        $this->assertSame('`MAIL_SCHEME` must be `smtps` (port 465) or `smtp` (port 587).', $check->explain(new RuntimeException(
            'The "tls" scheme is not supported; supported schemes for mailer "smtp" are: "smtp", "smtps".'
        )));
        $this->assertNull($explain('Something else went wrong.'));
    }

    public function test_the_test_email_says_it_came_from_the_admin_panel(): void
    {
        (new TestEmail)
            ->assertHasSubject('Test email from the STRICTMEDIA website')
            ->assertSeeInHtml('Email from the website is working')
            ->assertSeeInHtml(route('admin.email'), false);
    }
}
