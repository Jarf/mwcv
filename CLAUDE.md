# martinwadeson.com

Personal CV site with a retro space/arcade look. It's a static page: `build.php` renders the Twig templates in `tpl/` from `data/cv.php` into `public/`. Apache serves only `public/`. The one dynamic file is the hit counter (`public/counter.php`, served as `/counter.svg`), which reads a MySQL table.

## Priorities

Every change should keep the site **performant, lightweight, secure and SEO/GEO friendly**:
- Ship as few bytes and requests as possible: no JavaScript, no third-party scripts, fonts or trackers.
- Keep the CSP and security headers in `public/.htaccess` strict (no inline styles or scripts), and secrets in `.env` only.
- Keep all content in the static HTML with plain-language headings, accurate meta tags and JSON-LD.
- Live Lighthouse (mobile and desktop) is 100 in every category; keep it there.

## Layout

| Path | What it is |
|---|---|
| `data/cv.php` | All site content. Keep it in line with the owner's CV. `{years}` becomes the years since `career_start`. |
| `tpl/` | `index.html.twig` (the page), `404.html.twig`, `llms.txt.twig`, `sitemap.xml.twig` |
| `build.php` | Writes `public/index.html`, `404.html`, `llms.txt` and `sitemap.xml` (gitignored). Runs on deploy and daily by cron. |
| `scss/main.scss` | Styles, compiled to `public/css/main.css` (committed) |
| `public/` | Web root: CSS, font, images, icons, `.htaccess`, `robots.txt`, `counter.php` |
| `assets/` | Sources not served: full font, original screenshots |
| `tools/` | `make-stars.php` (starfield tile), `subset-font.sh` and `font-unicodes.txt` (font subset) |
| `include/config.php`, `classes/db.php` | `.env` loading and the DB class, used only by the counter |

Each heading pairs an arcade label with a plain one (`Player 1 · Profile`). Screens show both; print shows only the plain label, so printing the page gives a plain CV.

## Building

```
php build.php                                                                  # pages
npx -y sass@1.83.0 --style=compressed --no-source-map scss/main.scss public/css/main.css   # CSS
```

- Commit the rebuilt CSS with any SCSS change.
- The font is a subset. If `build.php` warns about characters the font lacks, add them to `tools/font-unicodes.txt` and run `tools/subset-font.sh` (needs `pip install fonttools brotli`).
- Project screenshots are 4:3 WebP at 360 and 600px wide (`public/img/<name>-360.webp`, `-600.webp`), made from the originals in `assets/`.
- No inline `style` attributes: the CSP blocks them.

To preview locally, serve `public/` (for example `php -S 127.0.0.1:8098 -t public`). `.htaccess` doesn't apply there, and the counter needs a database.

## Deploying

Run `./deploy.sh` once changes are committed and pushed to master. It:
1. checks the working tree is clean and pushed, passes `php -l` and builds, and that the CSS was rebuilt if the SCSS changed;
2. checks the server's `.env` has every key in `.env.example` and that Apache's document root is `public/`;
3. updates the server checkout, runs `composer install` if `composer.lock` changed, builds the pages, installs the daily rebuild cron (`/etc/cron.d/mwcv`) and tests the Apache config;
4. smoke-tests the live site (headers, compression, caching, counter, 404s, blocked files) and rolls back to the previous commit if anything fails.

**Keep `deploy.sh` up to date.** When a change needs something new on the server (an `.env` key, a folder, a build step, a package, a cron job), or adds something worth checking after a deploy (a page, a header, a redirect), update `deploy.sh` in the same commit. New `.env` keys go in `.env.example` (no values), and they must be added to the server's `.env` before deploying.

Server: `ssh root@comparison.love`, app in `/var/www/mwcv` (a git checkout of master; PHP-FPM runs as `www-data`; vhosts `/etc/apache2/sites-available/mwcv.conf` and `mwcv-le-ssl.conf`). Never print `.env` values; list key names only.

## Commits

No Claude attribution lines (`Co-Authored-By`, `Claude-Session`) in commit messages.
