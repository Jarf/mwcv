#!/usr/bin/env bash
# Deploy master to the live server, build it, smoke-test it, and roll back on failure.
#
# Usage: ./deploy.sh          deploy
#        ./deploy.sh --check  smoke-test the live site only
#
# Keep this script up to date: when a change needs a new server step (a new
# .env key, folder, build step, cron job or check), add it here in the same commit.
set -euo pipefail

SERVER="root@comparison.love"
APP_DIR="/var/www/mwcv"
SITE="https://martinwadeson.com"
VHOSTS="/etc/apache2/sites-available/mwcv.conf /etc/apache2/sites-available/mwcv-le-ssl.conf"

cd "$(dirname "$0")"

fail() { echo "✗ $*" >&2; exit 1; }
ok() { echo "✓ $*"; }

# --- Smoke test ---------------------------------------------------------------

# Contains "bot" so the visitor counter skips it
UA="mwcv-deploy-bot"
HEADERS=$(mktemp)
trap 'rm -f "$HEADERS"' EXIT

check() { # path expected_status [header_regex] [body_regex]
	local body code
	body=$(curl -s --compressed -D "$HEADERS" -A "$UA" "$SITE$1") || return 1
	code=$(head -1 "$HEADERS" | awk '{print $2}')
	[ "$code" = "$2" ] || { echo "  $1 returned $code, expected $2" >&2; return 1; }
	if [ -n "${3:-}" ] && ! grep -qiE "$3" "$HEADERS"; then
		echo "  $1 missing header /$3/" >&2; return 1
	fi
	if [ -n "${4:-}" ] && ! grep -qE "$4" <<<"$body"; then
		echo "  $1 body missing /$4/" >&2; return 1
	fi
}

smoke_test() {
	local css size
	check / 200 '^content-security-policy: default-src .none.*connect-src .self.' '<h1>Martin Wadeson</h1>' || return 1
	size=$(curl -s -o /dev/null -w '%{size_download}' -H 'Accept-Encoding: gzip' -A "$UA" "$SITE/")
	[ "$size" -lt 12000 ] || { echo "  / isn't compressed ($size bytes)" >&2; return 1; }
	css=$(curl -s -A "$UA" "$SITE/" | grep -oE '/css/main\.css\?v=[0-9a-f]+') || { echo "  No stylesheet link" >&2; return 1; }
	check "$css" 200 '^cache-control: max-age=31536000' &&
	check /counter.svg 200 '^cache-control: no-store' '<svg' &&
	check /counter.php 404 &&
	check /nope 404 '' 'Game over' &&
	check /index.html 301 &&
	check /llms.txt 200 '' '^# Martin Wadeson' &&
	check /sitemap.xml 200 '' '<lastmod>' &&
	check /robots.txt 200 &&
	check /.env 404 &&
	check /.git/config 404 &&
	check /deploy.sh 404 &&
	check /build.php 404 &&
	check /data/cv.php 404 &&
	check /vendor/autoload.php 404
}

# ./deploy.sh --check runs only the smoke test against the live site
if [ "${1:-}" = "--check" ]; then
	smoke_test && ok "Live site passes smoke test" || fail "Live site fails smoke test"
	exit 0
fi

# --- Local checks -----------------------------------------------------------

[ "$(git rev-parse --abbrev-ref HEAD)" = "master" ] || fail "Not on master"
[ -z "$(git status --porcelain)" ] || fail "Uncommitted changes; commit them first"

git fetch -q origin
LOCAL=$(git rev-parse HEAD)
[ "$LOCAL" = "$(git rev-parse origin/master)" ] || fail "master isn't pushed (or is behind origin)"
ok "master is clean and pushed (${LOCAL:0:7})"

for f in $(git ls-files '*.php'); do
	php -l "$f" >/dev/null || fail "PHP syntax error in $f"
done
ok "PHP syntax"

# Renders every template, so template errors stop the deploy here
php build.php >/dev/null || fail "Build failed"
ok "Site builds"

LIVE=$(ssh -o BatchMode=yes "$SERVER" "git -C $APP_DIR rev-parse HEAD")
if [ "$LIVE" = "$LOCAL" ]; then
	ok "Server already on ${LOCAL:0:7}; nothing to deploy"
	exit 0
fi
git merge-base --is-ancestor "$LIVE" "$LOCAL" || fail "Server is on a commit that isn't in master (${LIVE:0:7})"

CHANGED=$(git diff --name-only "$LIVE" "$LOCAL")
if grep -q '^scss/' <<<"$CHANGED" && ! grep -q '^public/css/main.css$' <<<"$CHANGED"; then
	fail "scss/ changed but public/css/main.css didn't; rebuild the CSS (see CLAUDE.md)"
fi
ok "Compiled CSS matches SCSS changes"

COMPOSER_CHANGED=0
grep -q '^composer.lock$' <<<"$CHANGED" && COMPOSER_CHANGED=1

# Every key in .env.example must exist in the server's .env (names only)
REQUIRED_KEYS=$(grep -oE '^[A-Z_]+=' .env.example | tr -d '=' | tr '\n' ' ')

# --- Deploy -------------------------------------------------------------------

echo "Deploying ${LIVE:0:7} → ${LOCAL:0:7}"
ssh -o BatchMode=yes "$SERVER" bash -s -- "$APP_DIR" "$LOCAL" "$COMPOSER_CHANGED" "$REQUIRED_KEYS" "$VHOSTS" <<'REMOTE'
set -euo pipefail
APP_DIR=$1 TARGET=$2 COMPOSER_CHANGED=$3 REQUIRED_KEYS=$4 VHOSTS=$5
cd "$APP_DIR"

for key in $REQUIRED_KEYS; do
	grep -q "^$key=" .env || { echo "✗ Server .env is missing $key" >&2; exit 1; }
done
echo "✓ Server .env has all keys"

for vhost in $VHOSTS; do
	grep -qE "DocumentRoot\s+$APP_DIR/public/?\s*$" "$vhost" || { echo "✗ $vhost doesn't serve $APP_DIR/public" >&2; exit 1; }
done
echo "✓ Apache serves public/ only"

git fetch -q origin
git merge -q --ff-only "$TARGET"
echo "✓ Checked out $(git rev-parse --short HEAD)"

if [ "$COMPOSER_CHANGED" = 1 ]; then
	composer install -q --no-dev --optimize-autoloader --no-interaction
	echo "✓ Composer packages installed"
fi

php build.php >/dev/null
echo "✓ Built static pages"

# Daily rebuild keeps "years of experience" current
CRON="17 3 * * * root cd $APP_DIR && php build.php >/dev/null"
if [ "$(cat /etc/cron.d/mwcv 2>/dev/null)" != "$CRON" ]; then
	echo "$CRON" > /etc/cron.d/mwcv
	echo "✓ Installed daily rebuild cron"
fi

apache2ctl configtest 2>&1 | grep -q 'Syntax OK' || { echo "✗ Apache config test failed" >&2; exit 1; }
echo "✓ Apache config"
REMOTE

# --- Smoke test the deploy, rolling back on failure ----------------------------

if smoke_test; then
	ok "Live site passes smoke test"
	echo "Deployed ${LOCAL:0:7} to $SITE"
else
	echo "✗ Smoke test failed; rolling back to ${LIVE:0:7}" >&2
	ssh -o BatchMode=yes "$SERVER" "cd $APP_DIR && git reset -q --hard $LIVE \
		&& { [ $COMPOSER_CHANGED = 0 ] || composer install -q --no-dev --optimize-autoloader --no-interaction; } \
		&& { [ ! -f build.php ] || php build.php >/dev/null; }"
	smoke_test && echo "Rolled back; the live site is working on ${LIVE:0:7}" >&2 \
		|| echo "Rolled back, but the site still fails the smoke test; check it now" >&2
	exit 1
fi
