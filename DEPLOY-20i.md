# Deploy STRICTMEDIA to 20i (shared hosting / StackCP)

This app runs on 20i as an ordinary Laravel site: **pure PHP on the server — no Node or
npm there.** The CSS/JavaScript are compiled by Vite and committed in `public/build/`, so
the server only needs **PHP 8.3 + Composer**. You only ever touch npm on your own machine,
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

## 1. Set the PHP version to 8.3

My20i → **Manage Hosting** → your package → **PHP** → *Select PHP Version* → **8.3**.

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

# On 20i, a bare `composer`/`php` can be a stripped-down build. Call the PHP 8.3 binary and
# Composer 2 explicitly. Your SSH login banner lists the exact binary name/path — adjust these:
php8.3 $(which composer2) install --no-dev --optimize-autoloader
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
php8.3 artisan key:generate
```

(To receive enquiries by email as well as in the admin panel, also set the `MAIL_*` values and
wire up `deliver()` in `app/Http/Controllers/ContactController.php` — see the main README.)

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
php8.3 artisan migrate --force      # creates sessions, cache, jobs and enquiries tables
php8.3 artisan config:cache
php8.3 artisan route:cache
php8.3 artisan view:cache
```

## 9. Turn on HTTPS

StackCP → **SSL/TLS Certificates** → enable the free **Let's Encrypt** certificate for the
domain, then force HTTPS. (`trustProxies` is already configured in `bootstrap/app.php`, so
Laravel builds `https://` URLs and secure cookies correctly behind 20i's proxy.)

## 10. Check it

- `https://yourdomain.co.uk` → the landing page, with Fraunces headings and Hanken body text.
- `https://yourdomain.co.uk/admin/login` → sign in with `ADMIN_PASSWORD`.
- Submit the contact form → it should save and show "Enquiry sent." (confirms the database works).

---

## Updating later

```bash
# 1. If you changed PHP/content/views only — pull or re-upload, then on the server:
php8.3 artisan migrate --force
php8.3 artisan config:cache && php8.3 artisan route:cache && php8.3 artisan view:cache

# 2. If composer.json changed:
php8.3 $(which composer2) install --no-dev --optimize-autoloader

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

## 20i references

- Change the document root — https://docs.20i.com/web-hosting/change-document-root
- Connect via SSH — https://docs.20i.com/ssh/connect-via-ssh
- Run Composer via SSH — https://docs.20i.com/ssh/run-composer-ssh
- Laravel hosting questions — https://docs.20i.com/laravel-cloud-hosting
