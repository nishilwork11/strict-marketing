# Agency landing page (Laravel)

A single-page landing page for a web, marketing, software and print business, with a private [admin panel](#admin-panel) for the enquiries sent through its contact form. It's the Laravel version of the Next.js site in `D:\Strict-marketing`: the same design, copy, effects and animation, with the pages rendered by Blade, the interactions in plain JavaScript and the enquiries kept in MySQL.

Apart from the admin panel there are no other pages: every menu and button link scrolls to a section on the page (email and phone links open the visitor's mail or phone app).

## Run it

You need PHP 8.3 or later, Composer, Node.js and MySQL (Laragon's MySQL works as it is).

```bash
composer install
npm install
cp .env.example .env          # then set DB_* and ADMIN_PASSWORD
php artisan key:generate
php artisan migrate           # creates the tables in the database named in DB_DATABASE
composer run dev              # http://localhost:8000, with Vite reloading CSS and JavaScript
```

`composer run dev` starts the web server, Vite and the log viewer together. To run them yourself, use `php artisan serve` and `npm run dev` in two terminals. For production, run `npm run build` and serve the `public` folder.

The database is `strict_media_marketing` on MySQL at `127.0.0.1:3306`, user `root` with no password (Laragon's default). Create it with:

```sql
CREATE DATABASE strict_media_marketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Deploy (20i)

The site runs on 20i shared hosting (StackCP) as an ordinary Laravel app: pure PHP on the server, with no Node or npm needed there. The CSS and JavaScript are compiled once with Vite and shipped in `public/build/`, so the server only needs PHP 8.3 and Composer.

See **[DEPLOY-20i.md](DEPLOY-20i.md)** for the full walkthrough — uploading the code, `composer install` over SSH, pointing the domain's document root at `public/`, creating the MySQL database and `.env`, and the production `artisan` migrate/cache commands.

## Change the content

All text lives in `config/site.php`: brand name, emails, phone, address, case studies, services, print products, process steps and form options. The views read from it, so you rarely need to touch them. If you cache config in production (`php artisan config:cache`), run it again after editing.

The work grid shows every project in `case_studies`, and the six marked `'featured' => true` also get a full case study card further down; the rest link straight to their site. Each project needs screenshots in `public/images/work/`, named after its `id`:

- `{id}.webp`: the top of its home page, 1080 × 675 (a 1440 × 900 browser window at 0.75 scale).
- `{id}-full.webp`: the page scrolled further, up to 1080 × 2700. A featured case study's cover scrolls through it on hover.

`php artisan test` checks every project has its screenshots. The descriptions and facts come from each site's own pages, so check them with the client when a site changes.

The fonts are downloaded from Google Fonts when the assets are built and served from this site, each with a size-matched fallback so the text doesn't jump when it loads (`vite.config.js`): Hanken Grotesk for body text, and Fraunces — a high-contrast display serif — for the large headings (any element given the `font-display` class).

## Animation

The CSS is the Next.js site's `globals.css`, unchanged, in `resources/css/app.css`. Tailwind CSS 4 builds it with Vite.

- Scroll reveals: add `data-reveal="up"` (fade and rise), `"words"` (headline words slide up), `"line"` (top rule draws, then text rises), `"crop"`, `"unmask"` (work covers open upwards from a slight zoom) or `"mask"` (text slides up out of a clipping box) to an element. `resources/js/motion/reveal.js` marks it as shown when it scrolls into view; the CSS is in `app.css` under "Scroll reveals". Set `--i` in the element's style to stagger a group.
- `<x-split-words>` splits a heading into words (`mode="reveal" | "load" | "scrub"`), and `<x-count-up>` counts a figure up from zero. The hero headline blurs into focus on load; add the `misregister` class to a revealed heading to have it settle out of CMYK misregistration, as the print headline does.
- The service ticker (`resources/js/site/ticker.js`) speeds up with page scroll, runs backwards while scrolling up, and leans into its direction of travel when you scroll fast.
- The case studies are a stack of cards (`resources/js/site/case-studies.js`): each holds near the top of the screen while the next slides up over it, and the one underneath shrinks back and fades (CSS under "Case study cards" in `app.css`). A card taller than the screen holds by its bottom edge, so it's always seen in full. Hovering a case study's cover scrolls down that site's page. Clicking a featured project in the work grid scrolls to its case; the others open their site in a new tab.
- Add `data-magnetic` to a button or link to have it lean toward a mouse pointer (`resources/js/motion/magnetic.js`); a value such as `data-magnetic="0.4"` sets the pull. Put an `<x-roll-label>` directly inside a button or link and its label rolls up on hover.
- While the contact form is sending, cyan, magenta and yellow dots bounce in the send button.
- Text links use the `link-line` class: the underline draws in from the left on hover. In the header it also stays under the section in view.
- Hero fade-out, the print panel's grow-in and the header progress bar use CSS scroll timelines. In browsers without them, those three stay still and everything else still works.
- Everything turns off when the visitor's system asks for reduced motion.

## Contact form

`app/Http/Controllers/ContactController.php` checks the enquiry with the same rules and messages as before. The form sends it with `fetch` and shows the answer in place (`resources/js/site/contact-form.js`); without JavaScript it posts normally and the page comes back with the same messages, or with "Enquiry sent".

Valid enquiries are saved to the `enquiries` table for the admin panel and written to `storage/logs/laravel.log`. To also receive them by email, set the `MAIL_*` values in `.env` and send one from `deliver()` in the controller; an example is in its comment.

Spam protection is a hidden honeypot field. Add rate limiting or a captcha if spam becomes a problem.

## Admin panel

Enquiries from the contact form are listed in a table at `/admin`, with counts of new, recent and won enquiries, a 14-day chart, search and status filters. Click an enquiry to open its own page with everything that was sent, where you can reply, call, set its status (New, Contacted, Won or Lost) or delete it.

To switch it on, set a password in `.env` (or your host's environment variables):

```bash
ADMIN_PASSWORD=a-long-random-password
```

Sign in at `/admin/login`. You stay signed in for a week on that browser, and changing the password signs everyone out. Without `ADMIN_PASSWORD` the panel stays locked. Each wrong password waits a second before answering, and an address can try at most 10 times a minute.

To copy enquiries across from the Next.js site's `data/enquiries.json`, run:

```bash
php artisan enquiries:import D:/Strict-marketing/data/enquiries.json
```

It keeps their ids, dates and statuses, and skips any already imported, so it's safe to run again.

| Part | File |
|---|---|
| Table and model | `database/migrations/…_create_enquiries_table.php`, `app/Models/Enquiry.php`, `app/Enums/EnquiryStatus.php` |
| Password and session cookie | `config/admin.php`, `app/Support/AdminSession.php`, `app/Http/Middleware/RequireAdmin.php` |
| Pages and actions | `routes/web.php`, `app/Http/Controllers/Admin/`, `resources/views/admin/` |
| Components | `resources/views/components/admin/`, `app/View/Components/Admin/` |
| Date labels (UK time) | `app/Support/AdminDates.php` |
| Browser behaviour | `resources/js/admin/` |

## Sections

| Section | File (in `resources/views/components/site/`) |
|---|---|
| Header (transparent over the hero, white card after scrolling) | `header.blade.php`, with `resources/js/site/header.js` |
| Hero with animated background | `hero.blade.php` |
| Service ticker | `ticker.blade.php` |
| Intro statement and work grid | `work.blade.php` |
| Case study cards, stacking on scroll | `case-studies.blade.php` |
| Platforms grid | `platforms.blade.php` |
| Services accordion | `services.blade.php` |
| Print section with product marquee | `print.blade.php` |
| Process | `process.blade.php` |
| Contact details and form | `contact.blade.php`, `contact-form.blade.php` |
| Footer (wordmark, back to top, contact emails) | `footer.blade.php` |

The page itself is `resources/views/home.blade.php`, and the shared pieces (arrow circle, roll label, logo, case cover and so on) are in `resources/views/components/`.

## From Next.js to Laravel

| Next.js | Laravel |
|---|---|
| `src/content/site.ts` | `config/site.php` |
| `src/app/page.tsx`, `src/components/*.tsx` | `resources/views/home.blade.php`, `resources/views/components/` |
| Client components (`"use client"`) | `resources/js/` modules |
| `src/app/globals.css` | `resources/css/app.css` |
| `next/font` | Vite font plugin and `@fonts` |
| Server Actions | Controllers in `app/Http/Controllers/` |
| `src/proxy.ts` | `app/Http/Middleware/RequireAdmin.php` |
| JSON file or Postgres store | MySQL `enquiries` table |

## Tests

```bash
php artisan test
```

The tests cover the contact form's rules and messages, the honeypot, the admin sign-in and session, the inbox, status changes, deleting and the UK date labels. They run on an in-memory SQLite database, so they never touch MySQL.
