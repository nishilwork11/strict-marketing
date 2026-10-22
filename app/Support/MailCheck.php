<?php

namespace App\Support;

use Dotenv\Dotenv;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Arr;
use Illuminate\Support\ConfigurationUrlParser;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Throwable;

// What the email page in the admin panel shows: the mail settings the site is using
// right now, the mistakes it can spot in them, and what a mail server's error means.
class MailCheck
{
    // Where each mail setting in .env ends up in the site's config
    const SETTINGS = [
        'MAIL_MAILER' => 'mail.default',
        'MAIL_SCHEME' => 'mail.mailers.smtp.scheme',
        'MAIL_HOST' => 'mail.mailers.smtp.host',
        'MAIL_PORT' => 'mail.mailers.smtp.port',
        'MAIL_USERNAME' => 'mail.mailers.smtp.username',
        'MAIL_PASSWORD' => 'mail.mailers.smtp.password',
        'MAIL_FROM_ADDRESS' => 'mail.from.address',
        'MAIL_FROM_NAME' => 'mail.from.name',
        'ENQUIRY_EMAIL' => 'site.contact.notify',
    ];

    // How to write a value with #, spaces, quotes or backslashes in .env so it's read as typed
    const QUOTING = 'Put it in single quotes, which keep every character as typed, or, if it has a \' in it, in double quotes with `\"` for `"` and `\\\\` for `\\`.';

    // Whether the settings come from a saved copy of .env (php artisan config:cache)
    public readonly bool $cached;

    /** @var array<string, string|null> */
    public readonly array $dotenv;

    // What's wrong with .env, when it has a mistake that stops it being read
    public readonly ?string $dotenvError;

    /** @var list<string> .env and config files changed since the settings were saved */
    public readonly array $newerThanCache;

    /**
     * @param  bool|null  $cached  Whether the config is cached; asks the app when left out
     * @param  array<string, string|null>|null  $dotenv  What .env says; reads the file when left out
     * @param  list<string>|null  $newerThanCache  Files changed since the config was cached; checks when left out
     */
    public function __construct(?bool $cached = null, ?array $dotenv = null, ?array $newerThanCache = null)
    {
        $this->cached = $cached ?? app()->configurationIsCached();

        $error = null;

        if ($dotenv === null) {
            $path = app()->environmentFilePath();
            $contents = is_file($path) ? (string) file_get_contents($path) : '';

            try {
                $dotenv = Dotenv::parse($contents);
            } catch (Throwable $e) {
                $dotenv = [];
                $error = $this->dotenvMistake($contents, $e);
            }
        }

        $this->dotenv = $dotenv;
        $this->dotenvError = $error;
        $this->newerThanCache = $newerThanCache ?? ($this->cached ? $this->filesNewerThanCache() : []);
    }

    /**
     * The settings in use, as label => value. Null shows as "Not set", and the password
     * only as its length, which is enough to spot one that .env has cut short.
     *
     * @return array<string, string|null>
     */
    public function settings(): array
    {
        $rows = ['Sending with' => match ($this->transport()) {
            'smtp' => 'SMTP server',
            'log' => 'Log file (not sent)',
            'array' => 'Nowhere (kept for tests)',
            'sendmail' => "This server's sendmail",
            default => (string) config('mail.default'),
        }];

        if ($this->transport() === 'smtp') {
            $mailer = $this->mailer();
            $password = (string) ($mailer['password'] ?? '');
            $tls = $this->connection()['tls'] ?? null;

            $rows['Server'] = $this->server();
            $rows['Encryption'] = match ($tls) {
                true => 'SSL/TLS',
                false => filter_var($mailer['require_tls'] ?? false, FILTER_VALIDATE_BOOL) ? 'STARTTLS (required)' : 'STARTTLS, when the server offers it',
                null => "Can't tell from these settings",
            };
            $rows['Username'] = ((string) ($mailer['username'] ?? '')) ?: null;
            $rows['Password'] = $password !== '' ? 'Set, '.mb_strlen($password).' '.Str::plural('character', mb_strlen($password)) : null;
        }

        $rows['From'] = trim(config('mail.from.name').' <'.config('mail.from.address').'>');
        $rows['Enquiries go to'] = ((string) config('site.contact.notify')) ?: null;
        $rows['Read from'] = $this->cached ? 'Saved copy of .env (config cache)' : '.env';

        return $rows;
    }

    /**
     * Mistakes in the settings that stop email arriving, most important first. In the
     * text, `backticks` mark settings and commands.
     *
     * @return list<array{title: string, body: string, items: list<string>}>
     */
    public function problems(): array
    {
        $problems = [];
        $add = function (string $title, string $body, array $items = []) use (&$problems) {
            $problems[] = ['title' => $title, 'body' => $body, 'items' => $items];
        };
        $cache = $this->artisan('config:cache');

        if ($this->dotenvError) {
            $add('The .env file has a mistake in it', "{$this->dotenvError} Fix it before running `{$cache}`, or the site will stop working.");
        }

        // Until the cache is refreshed, nothing else here says what .env would do
        if ($changed = $this->changedSinceCached()) {
            $add(
                "The site isn't using your latest .env",
                "Its settings were saved with `{$cache}` before .env last changed. Run `{$cache}` on the server again to use these:",
                $changed,
            );

            return $problems;
        }

        if ($this->newerThanCache) {
            $add(
                'The saved settings are older than the files they come from',
                'The site uses a copy saved by `'.$cache.'`, and '.Arr::join(array_map(fn ($file) => "`{$file}`", $this->newerThanCache), ', ', ' and ')
                .' changed after it was made. Run `'.$cache.'` on the server again, so the site uses the latest settings.',
            );
        }

        if (! filter_var(config('site.contact.notify'), FILTER_VALIDATE_EMAIL)) {
            $add(
                "Enquiries aren't emailed to anyone",
                'The site has no address to send them to'
                .($this->cached ? ', which happens when its saved settings are older than this version of the site. Run `'.$cache.'` on the server.' : '. Set `ENQUIRY_EMAIL` in .env.'),
            );
        }

        if ($this->urlIsBroken()) {
            $add("MAIL_URL can't be read", "Every email fails until it's fixed. In the password, write # as `%23`, / as `%2F` and ? as `%3F`, or remove `MAIL_URL` and use the separate `MAIL_` settings.");
        }

        if ($this->fallsBackToNothing()) {
            $add(
                'A failed email would look sent',
                '`MAIL_MAILER` is `'.config('mail.default').'`, which hands an email to the log when the mail server fails, so a failure looks like a success, here and for enquiries. Set `MAIL_MAILER=smtp`.',
            );
        }

        if (! $this->sends()) {
            $add("Emails aren't being sent", '`MAIL_MAILER` is `'.config('mail.default').'`, so emails '
                .($this->transport() === 'log' ? 'are only written to the log file, if at all' : 'are kept in memory and thrown away')
                .". Set `MAIL_MAILER=smtp`, with your mailbox's `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME` and `MAIL_PASSWORD`.");
        }

        if ($this->transport() !== 'smtp') {
            return $problems;
        }

        $mailer = $this->mailer();
        $scheme = (string) ($mailer['scheme'] ?? '');
        $port = (int) ($mailer['port'] ?? 0);
        $username = (string) ($mailer['username'] ?? '');
        $from = (string) config('mail.from.address');

        if ($username !== '' && (string) ($mailer['password'] ?? '') === '') {
            $add('MAIL_PASSWORD is empty', "The site signs in to the mail server as {$username} with no password, which the server will refuse.");
        }

        if (! in_array($scheme, ['', 'smtp', 'smtps'], true)) {
            $add("MAIL_SCHEME can't be {$scheme}", 'Use `MAIL_SCHEME=smtps` for port 465, or `MAIL_SCHEME=smtp` for port 587 (add `MAIL_REQUIRE_TLS=true` so it always encrypts, with STARTTLS).');
        } elseif ($scheme === 'smtps' && in_array($port, [25, 587, 2525], true)) {
            $add(
                "Port {$port} doesn't take MAIL_SCHEME=smtps",
                "smtps encrypts from the start, which mail servers do on port 465. For port {$port}, set `MAIL_SCHEME=smtp` (with `MAIL_REQUIRE_TLS=true` it always encrypts, with STARTTLS).",
            );
        }

        // IONOS, like most mail servers, only sends from the domain of the mailbox you sign in with
        if (str_contains($username, '@') && strcasecmp(Str::afterLast($username, '@'), Str::afterLast($from, '@')) !== 0) {
            $add(
                "The From address isn't on your mailbox's domain",
                "Emails are sent from {$from} but the site signs in as {$username}. Mail servers refuse that, or the email lands in spam. Set `MAIL_FROM_ADDRESS={$username}`.",
            );
        }

        return $problems;
    }

    /**
     * What a mail server's error means, and what to change, with `backticks` round
     * settings. Null when it's not one of the usual errors.
     */
    public function explain(Throwable $e): ?string
    {
        $message = $e->getMessage();
        $host = (string) ($this->mailer()['host'] ?? '');
        $server = $this->server();
        $ports = 'usually 465 with `MAIL_SCHEME=smtps`, or 587 with `MAIL_SCHEME=smtp`';
        $timeout = (float) ($this->mailer()['timeout'] ?? ini_get('default_socket_timeout'));

        return match (true) {
            str_contains($message, 'scheme is not supported') => '`MAIL_SCHEME` must be `smtps` (port 465) or `smtp` (port 587).',
            // Connected, then the server went quiet: not a wrong address
            (bool) preg_match('/^Connection to ".+" timed out\.$/', $message) => $this->explainSilence($e, $server, $timeout),
            (bool) preg_match('/authenticat|\b535\b/i', $message) => "The mail server turned down the username or password. `MAIL_USERNAME` is usually the whole email address, and `MAIL_PASSWORD` that mailbox's password. If the password has a #, a space or a quote in it, .env may pass on only part of it: compare its length with the Password row. ".self::QUOTING,
            (bool) preg_match('/getaddrinfo|getaddresses|Name or service not known|No such host/i', $message) => "There's no server called {$host}. Check `MAIL_HOST` for typos.",
            (bool) preg_match('/timed out|did not properly respond|unreachable/i', $message) => "The site couldn't reach {$server}. Check `MAIL_HOST` and `MAIL_PORT` ({$ports}). If they're right, the hosting may block outgoing mail on that port: try the other one, or ask the hosting company.",
            (bool) preg_match('/refused/i', $message) => "Nothing answered at {$server}. Check `MAIL_HOST` and `MAIL_PORT`: {$ports}.",
            (bool) preg_match('/certificate|SSL operation failed|SSL routines|enable crypto|wrong version number/i', $message) => "The secure connection failed. Check `MAIL_PORT` and `MAIL_SCHEME` ({$ports}), and set `MAIL_HOST` to the server name your email provider gives, not an IP address.",
            (bool) preg_match('/\b5\d\d\b.*(sender|from|relay|not allowed|denied|rejected|not owned)/is', $message) => 'The mail server turned down the sender or the recipient. Set `MAIL_FROM_ADDRESS` to the mailbox you sign in with (`MAIL_USERNAME`), and check the address you sent to.',
            default => null,
        };
    }

    /**
     * The mail server's last reply when it took the email, such as "250 OK queued as 1A2B3C".
     */
    public function serverReply(?SentMessage $sent): ?string
    {
        // Only the server's lines ("<") are read: the site's own lines include its login
        preg_match_all('/^\[[^\]]*\] < (.+)$/m', $sent?->getDebug() ?? '', $replies);

        // The session is stored as JSON, which can't hold bytes that aren't UTF-8
        return ($last = end($replies[1])) ? mb_scrub(mb_substr(trim($last), 0, 500), 'UTF-8') : null;
    }

    // What it means when a server stops answering, from how far the conversation got. Only its
    // replies ("<" lines) are read: the site's own lines include its login
    protected function explainSilence(Throwable $e, string $server, float $timeout): string
    {
        $debug = $e instanceof TransportExceptionInterface ? $e->getDebug() : '';
        $more = "if it keeps happening, set `MAIL_TIMEOUT` in .env to more seconds (it's {$timeout} now)";

        return match (true) {
            (bool) preg_match('/^\[[^\]]*\] < 354/m', $debug) => "The site sent the email to {$server}, but the server didn't confirm it within {$timeout} seconds, so it may still arrive. Check the inbox before trying again, and {$more}.",
            ! preg_match('/^\[[^\]]*\] < 220/m', $debug) => "The site connected to {$server}, but the server didn't say hello within {$timeout} seconds, which usually means the port and the encryption don't match. Use 465 with `MAIL_SCHEME=smtps`, or 587 with `MAIL_SCHEME=smtp`.",
            default => "{$server} stopped answering partway through. Try again, and {$more}.",
        };
    }

    // Whether emails leave the site, rather than going to the log or nowhere
    public function sends(): bool
    {
        return ! in_array($this->transport(), ['log', 'array'], true);
    }

    // The artisan command to run on the server. 20i names its PHP commands php83, php84 and so on,
    // so this is the one for the PHP version the site runs on
    protected function artisan(string $command): string
    {
        return 'php'.PHP_MAJOR_VERSION.PHP_MINOR_VERSION.' artisan '.$command;
    }

    protected function urlIsBroken(): bool
    {
        $mailer = (array) config('mail.mailers.'.config('mail.default'));

        try {
            ! empty($mailer['url']) && (new ConfigurationUrlParser)->parseConfiguration($mailer);
        } catch (InvalidArgumentException) {
            return true;
        }

        return false;
    }

    // A failover or round robin mailer that can hand an email to log or array hides a failed send
    protected function fallsBackToNothing(): bool
    {
        if (! in_array($this->transport(), ['failover', 'roundrobin'], true)) {
            return false;
        }

        foreach ((array) config('mail.mailers.'.config('mail.default').'.mailers') as $member) {
            if (in_array(config("mail.mailers.{$member}.transport"), ['log', 'array'], true)) {
                return true;
            }
        }

        return false;
    }

    protected function transport(): string
    {
        $name = config('mail.default');

        return (string) (config("mail.mailers.{$name}.transport") ?? $name);
    }

    /**
     * The default mailer's settings, with MAIL_URL applied the way Laravel applies it.
     *
     * @return array<string, mixed>
     */
    protected function mailer(): array
    {
        $mailer = (array) config('mail.mailers.'.config('mail.default'));

        if (! empty($mailer['url'])) {
            try {
                $mailer = array_merge($mailer, (new ConfigurationUrlParser)->parseConfiguration($mailer));
            } catch (InvalidArgumentException) {
                // A MAIL_URL that can't be read, which problems() points out
            }
        }

        return $mailer;
    }

    /**
     * Where Laravel would connect to send, and whether it encrypts from the start, worked
     * out by Laravel and Symfony themselves without connecting. Null when the settings
     * can't make an SMTP connection, such as an unknown MAIL_SCHEME.
     *
     * @return array{host: string, port: int, tls: bool}|null
     */
    protected function connection(): ?array
    {
        try {
            $transport = app('mail.manager')->createSymfonyTransport($this->mailer());
        } catch (Throwable) {
            return null;
        }

        $stream = $transport instanceof SmtpTransport ? $transport->getStream() : null;

        return $stream instanceof SocketStream
            ? ['host' => $stream->getHost(), 'port' => $stream->getPort(), 'tls' => $stream->isTLS()]
            : null;
    }

    protected function server(): string
    {
        $connection = $this->connection();
        $mailer = $this->mailer();

        return $connection
            ? $connection['host'].':'.$connection['port']
            : ($mailer['host'] ?? '').':'.($mailer['port'] ?? '');
    }

    /**
     * The mail settings that differ between .env and the cached copy the site is using.
     * The password is only said to be different, never shown.
     *
     * @return list<string>
     */
    protected function changedSinceCached(): array
    {
        if (! $this->cached) {
            return [];
        }

        $changed = [];

        foreach (self::SETTINGS as $key => $setting) {
            if (! array_key_exists($key, $this->dotenv)) {
                continue;
            }

            $inEnv = $this->envValue($this->dotenv[$key]);
            $inUse = config($setting);

            if ((string) $inEnv === (string) $inUse) {
                continue;
            }

            $changed[] = $key === 'MAIL_PASSWORD'
                ? '`MAIL_PASSWORD` is different'
                : "`{$key}` is ".$this->shown($inEnv).' in .env, but '.$this->shown($inUse).' in the saved copy';
        }

        return $changed;
    }

    // A value from .env the way env() reads it (Illuminate\Support\Env::getOption)
    protected function envValue(?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => preg_match('/\A([\'"])(.*)\1\z/', $value, $matches) ? $matches[2] : $value,
        };
    }

    protected function shown(mixed $value): string
    {
        return match (true) {
            $value === null, $value === '' => 'not set',
            is_bool($value) => $value ? 'true' : 'false',
            default => (string) $value,
        };
    }

    /**
     * .env and the config files that changed after the config was cached, so the site
     * still uses their old contents.
     *
     * @return list<string>
     */
    protected function filesNewerThanCache(): array
    {
        $cachedAt = @filemtime(app()->getCachedConfigPath());

        if (! $cachedAt) {
            return [];
        }

        $files = [app()->environmentFilePath(), ...glob(config_path('*.php')) ?: []];

        return array_values(array_map(
            fn (string $file) => str_replace('\\', '/', Str::after($file, base_path().DIRECTORY_SEPARATOR)),
            array_filter($files, fn (string $file) => @filemtime($file) > $cachedAt),
        ));
    }

    // Which line of .env is wrong and why, without repeating what's on it, since that may be a password
    protected function dotenvMistake(string $contents, Throwable $e): string
    {
        $why = trim((string) preg_replace('/^Failed to parse dotenv file\.\s*|\s*at \[.*$/s', '', $e->getMessage()), " .\n");

        foreach (preg_split('/\R/', $contents) ?: [] as $line) {
            try {
                Dotenv::parse($line);
            } catch (Throwable) {
                $name = trim(strtok($line, '=') ?: '');

                if (preg_match('/^(export\s+)?[A-Z0-9_.]+$/i', $name)) {
                    return "The `{$name}` line: ".lcfirst($why).'. '.self::QUOTING;
                }

                break;
            }
        }

        return ucfirst($why).'.';
    }
}
