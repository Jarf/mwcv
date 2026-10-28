# martinwadeson.com

Personal CV site: one PHP page (`index.php`) rendered with Twig (`tpl/home.twig`), styled from `scss/main.scss`. A MySQL table holds the visitor count.

## Priorities

Every change should keep the site **performant, lightweight, secure and SEO/GEO friendly**:
- Ship as few bytes and requests as possible, with no third-party scripts, fonts or trackers.
- Keep the CSP and security headers in `.htaccess` strict, and keep secrets in `.env` only.
- Keep content in the server-rendered HTML, with accurate meta tags and JSON-LD.
- Live Lighthouse (mobile and desktop) is 100 in every category; keep it there.

## Building CSS

`css/main.css` is committed. After editing `scss/main.scss`, rebuild it:

```
npx -y sass@1.83.0 --style=compressed --no-source-map scss/main.scss css/main.css
```

The starfield uses random star positions, so every build differs; commit the rebuilt file with the SCSS change.

## Deploying

Run `./deploy.sh` once changes are committed and pushed to master. It:
1. checks the working tree is clean, pushed and passes `php -l`, and that the CSS was rebuilt if the SCSS changed;
2. checks the server's `.env` has every key in `.env.example`;
3. updates the server checkout, runs `composer install` if `composer.lock` changed, and tests the Apache config;
4. smoke-tests the live site and rolls back to the previous commit if anything fails.

**Keep `deploy.sh` up to date.** When a change needs something new on the server (an `.env` key, a folder, a build step, a package), or adds something worth checking after a deploy (a page, a header, a redirect), update `deploy.sh` in the same commit. New `.env` keys go in `.env.example` (no values), and they must be added to the server's `.env` before deploying.

Server: `ssh root@comparison.love`, app in `/var/www/mwcv` (a git checkout of master; PHP-FPM runs as `www-data`). Never print `.env` values; list key names only.

## Commits

No Claude attribution lines (`Co-Authored-By`, `Claude-Session`) in commit messages.
