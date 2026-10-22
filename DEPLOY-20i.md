# Deploy STRICTMEDIA to 20i (shared hosting / StackCP)

This app runs on 20i as an ordinary Laravel site: **pure PHP on the server — no Node or
npm there.** The CSS/JavaScript are compiled by Vite and committed in `public/build/`, so
the server only needs **PHP 8.4 + Composer**. You only ever touch npm on your own machine,
and only if you change the design (see [Updating later](#updating-later)).

> Paths and binary names below (PHP binary, Composer path, your package home) vary per
> account — confirm them from your SSH login banner and the My20i panel. 20i doc links are
> at the end.

---

## What you need first

- A 20i hosting package (StackCP) with **your domain pointed at it** (added in My20i, so
  20i's nameservers serve it). Changing the document root does nothing until DNS resolves to
  the package.
- This repository, with `public/build/` committed (already done).
- About 15 minutes.

---

## 1. Set the PHP version to 8.4

My20i → **Manage Hosting** → your package → **PHP** → *Select PHP Version* → **8.4** (or newer).
The packages in `composer.lock` need PHP 8.4.1 or later: on 8.3, every page and every `artisan`
command stops with "Composer detected issues in your platform".

Make sure these extensions are enabled (they usually are by default): `openssl`, `pdo_mysql`,
`mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`.

## 2. Create the MySQL database

StackCP → **MySQL Databases** → create a database and a user, grant the user to the database.
Write down: **host, database name, username, password** (you'll need them in `.env`). 20i shows
the DB host to use — it isn't always `127.0.0.1`.

## 3. Get the code onto the package

Put the Laravel app in a folder **outside** the web root (never serve the project root — it
exposes `.env`, `config/`, etc.). Pick one:

**A. SFTP (simplest).** Upload the whole project into a new folder in your package home, e.g.
`~/laravel/`, i.e. a sibling of `public_html` — **not inside it**. Upload everything *except*:
- `node_modules/` (never needed on the server)
- `.env` (you create it on the server in step 5)
- `.git/` (optional)
- `vendor/` (you install it on the server in step 4; or upload it to skip SSH Composer — see note)

Make sure `public/build/` **is** uploaded (it holds the compiled CSS/JS/fonts).

**B. 20i Git Manager.** StackCP → **Git / Version Control** → connect GitHub → pick this repo →
clone into a path **outside** `public_html` (e.g. `laravel`). Later deploys are a "pull".
Because `public/build/` is committed, the clone already has the built assets.

## 4. Install PHP dependencies over SSH

Enable SSH: My20i → **Manage Hosting** → **SSH Access** (under *Security*) → add your public key.
A new key can take **up to 30 minutes** to start working.

Connect (see 20i's "connect via SSH" doc), then:

```bash
cd ~/laravel

# On 20i, a bare `composer`/`php` can be a stripped-down build. Call the PHP 8.4 binary (20i
# names it php84, with no dot; php85 if you picked 8.5) and Composer 2 explicitly. Your SSH
# login banner lists the exact binary names/paths — adjust these:
php84 $(which composer2) install --no-dev --optimize-autoloader
```

> **No SSH / Composer trouble?** You can instead run `composer install --no-dev
> --optimize-autoloader` **locally** and upload the resulting `vendor/` folder by SFTP. Laravel's
> dependencies are portable, so a vendor built on your machine works on the server. You'd still
> need SSH (or a scheduled task) for the `artisan` commands in steps 5 & 8.

## 5. Create and fill `.env` on the server

Copy the example and edit it:

```bash
cp .env.example .env
```

Set at least these (use your real domain and the DB details from step 2):

```dotenv
APP_NAME=STRICTMEDIA
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.co.uk

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=<from StackCP>
DB_PORT=3306
DB_DATABASE=<from StackCP>
DB_USERNAME=<from StackCP>
DB_PASSWORD=<from StackCP>

# These three use the database; the tables are created by the migration in step 8.
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Unlocks the /admin panel. Use something long and random.
ADMIN_PASSWORD=change-me-to-a-long-random-string
```

Generate the app key:

```bash
php84 artisan key:generate
```

Each enquiry is also emailed to hello@strictmedia.co.uk. That mailbox is at **IONOS**, not 20i
(the domain's main MX record is IONOS's `mx01.ionos.co.uk`), so the site sends through IONOS's
outgoing server, signed in as the mailbox. Replace the `MAIL_` lines from `.env.example` with:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.ionos.co.uk
MAIL_PORT=465
MAIL_USERNAME=hello@strictmedia.co.uk
MAIL_PASSWORD='the-mailbox-password'
MAIL_FROM_ADDRESS=hello@strictmedia.co.uk
MAIL_FROM_NAME="STRICTMEDIA website"
```

- `MAIL_MAILER=smtp` matters: `.env.example` has `log`, which sends nothing (locally it writes emails to
  `storage/logs/laravel.log`; with `LOG_LEVEL=error` they aren't even logged).
- `MAIL_PASSWORD` is the hello@ mailbox's own password (the one that signs in to IONOS Webmail), not
  your IONOS account login. If two-step verification is on for the mailbox, make an app password in
  Webmail (your initials → My Account → Login & Security → App passwords) and use that instead.
- Keep the password in single quotes, which keep every character as typed. Without quotes `.env` cuts
  it off at a `#` and a space breaks the file; in double quotes a `\` breaks it. (Only if the password
  itself has a `'` in it, use double quotes, writing `\"` for `"` and `\\` for `\`.)
- `MAIL_FROM_ADDRESS` must be on the same domain as the mailbox you sign in with, or IONOS refuses it
  ("550 Sender address is not allowed").
- If port 465 won't connect, use `MAIL_PORT=587` with `MAIL_SCHEME=smtp` and `MAIL_REQUIRE_TLS=true`
  (it then encrypts with STARTTLS, and won't send the password unencrypted).
- To send enquiries to a different address, add `ENQUIRY_EMAIL=someone@strictmedia.co.uk`.

After changing `.env`, run `php84 artisan config:cache` again (step 8), or the site keeps using the old
settings. Then check it works: see [Check it](#10-check-it).

## 6. Point the domain's document root at `public/`

My20i → **Manage Hosting** → your package → *Options › Manage* → **Manage Domains → Domains** →
set the **Document Root** for your domain from `public_html` to the app's public folder, e.g.
`laravel/public`, then **Update**.

The target folder must already exist and contain files (it does — `public/index.php` and
`public/.htaccess` are the entry point and URL rewriter; 20i's Apache/LiteSpeed honours the
`.htaccess`). If you can't change the document root on your plan, the fallback is a symlink:
remove the empty `public_html` and recreate it as a link to the app's public dir —
`ln -s ~/laravel/public ~/public_html` (confirm symlinks are allowed on your package first).

## 7. Make storage writable

```bash
chmod -R 775 storage bootstrap/cache
```

Laravel won't run without write access to `storage/` and `bootstrap/cache/`.

## 8. Migrate and cache (over SSH)

```bash
php84 artisan migrate --force      # creates sessions, cache, jobs and enquiries tables
php84 artisan config:cache
php84 artisan route:cache
php84 artisan view:cache
```

## 9. Turn on HTTPS

StackCP → **SSL/TLS Certificates** → enable the free **Let's Encrypt** certificate for the
domain, then force HTTPS. (`trustProxies` is already configured in `bootstrap/app.php`, so
Laravel builds `https://` URLs and secure cookies correctly behind 20i's proxy.)

## 10. Check it

- `https://yourdomain.co.uk` → the landing page, with Fraunces headings and Hanken body text.
- `https://yourdomain.co.uk/admin/login` → sign in with `ADMIN_PASSWORD`.
- Submit the contact form → it should save and show "Enquiry sent." (confirms the database works).
- `https://yourdomain.co.uk/admin/email` → press **Send test email**. It shows the mail settings the site
  is using, flags the usual mistakes (including a config cache older than `.env`), and if sending fails,
  shows the mail server's own error and what to change. The contact form still says "Enquiry sent." when
  only the email fails, since the enquiry is saved for the admin panel, so this page is the way to check.

---

## Updating later

```bash
# 1. If you changed PHP/content/views only — pull or re-upload, then on the server:
php84 artisan migrate --force
php84 artisan config:cache && php84 artisan route:cache && php84 artisan view:cache

# 2. If composer.json changed:
php84 $(which composer2) install --no-dev --optimize-autoloader

# 3. If you changed CSS/JS/fonts — rebuild on YOUR machine (the only time npm is used),
#    then commit/upload the regenerated public/build:
npm install && npm run build
```

Editing only the text in `config/site.php` needs **no rebuild** — just re-run `config:cache`.

---

## Gotchas

- **No npm on the server, ever.** If a guide tells you to run `npm` on 20i, you don't need to —
  the compiled assets ship in `public/build/`.
- **Blank page / HTTP 500:** check `storage/logs/laravel.log`; usually a missing `APP_KEY`,
  unwritable `storage/`, or wrong DB credentials.
- **CSS/JS 404s:** the document root isn't pointing at the app's `public/` folder (step 6), or
  `public/build/` wasn't uploaded.
- **Stale site after an update:** re-run the `config:cache`/`route:cache`/`view:cache` commands;
  they're cached in step 8.
- **Enquiry emails not arriving:** open `/admin/email` and send a test (see step 10). Failed enquiry
  emails are also logged in `storage/logs/laravel.log` as `[contact] Could not email the enquiry`.
  If the test says it was sent but nothing arrives, look in the mailbox's spam folder in IONOS Webmail,
  and check `MAIL_HOST` is IONOS's server: mail sent through 20i's `smtp.stackmail.com` can stay inside
  20i, and doesn't pass the domain's SPF check, which only allows IONOS. Also, in the domain's DNS at 20i
  (My20i → Manage DNS), the MX records should all be IONOS's (`mx00`/`mx01.ionos.co.uk`): an extra
  `mx.stackmail.com` MX sends incoming mail to 20i instead of IONOS whenever IONOS's main server
  can't be reached.

## 20i references

- Change the document root — https://docs.20i.com/web-hosting/change-document-root
- Connect via SSH — https://docs.20i.com/ssh/connect-via-ssh
- Run Composer via SSH — https://docs.20i.com/ssh/run-composer-ssh
- PHP versions over SSH (`php84`, …) — https://docs.20i.com/web-hosting/php-modules-available-on-shared-hosting
- Laravel hosting questions — https://docs.20i.com/laravel-cloud-hosting
