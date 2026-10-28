#!/usr/bin/env bash
# Deploy master to the live server, smoke-test it, and roll back on failure.
#
# Usage: ./deploy.sh
#
# Keep this script up to date: when a change needs a new server step (a new
# .env key, folder, build step or check), add it here in the same commit.
set -euo pipefail

SERVER="root@comparison.love"
APP_DIR="/var/www/mwcv"
SITE="https://martinwadeson.com"

cd "$(dirname "$0")"

fail() { echo "✗ $*" >&2; exit 1; }
ok() { echo "✓ $*"; }

# --- Local checks -----------------------------------------------------------

[ "$(git rev-parse --abbrev-ref HEAD)" = "master" ] || fail "Not on master"
[ -z "$(git status --porcelain)" ] || fail "Uncommitted changes; commit them first"

git fetch -q origin
LOCAL=$(git rev-parse HEAD)
[ "$LOCAL" = "$(git rev-parse origin/master)" ] || fail "master isn't pushed (or is behind origin)"
ok "master is clean and pushed ($(git rev-parse --short HEAD))"

for f in $(git ls-files '*.php'); do
	php -l "$f" >/dev/null || fail "PHP syntax error in $f"
done
ok "PHP syntax"

LIVE=$(ssh -o BatchMode=yes "$SERVER" "git -C $APP_DIR rev-parse HEAD")
if [ "$LIVE" = "$LOCAL" ]; then
	ok "Server already on ${LOCAL:0:7}; nothing to deploy"
	exit 0
fi
git merge-base --is-ancestor "$LIVE" "$LOCAL" || fail "Server is on a commit that isn't in master (${LIVE:0:7})"

CHANGED=$(git diff --name-only "$LIVE" "$LOCAL")
if grep -q '^scss/' <<<"$CHANGED" && ! grep -q '^css/main.css$' <<<"$CHANGED"; then
	fail "scss/ changed but css/main.css didn't; rebuild the CSS (see CLAUDE.md)"
fi
ok "Compiled CSS matches SCSS changes"

COMPOSER_CHANGED=0
grep -q '^composer.lock$' <<<"$CHANGED" && COMPOSER_CHANGED=1

# Every key in .env.example must exist in the server's .env (names only)
REQUIRED_KEYS=$(grep -oE '^[A-Z_]+=' .env.example | tr -d '=' | tr '\n' ' ')

# --- Deploy -------------------------------------------------------------------

echo "Deploying ${LIVE:0:7} → ${LOCAL:0:7}"
ssh -o BatchMode=yes "$SERVER" bash -s -- "$APP_DIR" "$LOCAL" "$COMPOSER_CHANGED" "$REQUIRED_KEYS" <<'REMOTE'
set -euo pipefail
APP_DIR=$1 TARGET=$2 COMPOSER_CHANGED=$3 REQUIRED_KEYS=$4
cd "$APP_DIR"

for key in $REQUIRED_KEYS; do
	grep -q "^$key=" .env || { echo "✗ Server .env is missing $key" >&2; exit 1; }
done
echo "✓ Server .env has all keys"

mkdir -p cache
chown www-data:www-data cache
chmod 750 cache

git fetch -q origin
git merge -q --ff-only "$TARGET"
echo "✓ Checked out $(git rev-parse --short HEAD)"

if [ "$COMPOSER_CHANGED" = 1 ]; then
	composer install -q --no-dev --optimize-autoloader --no-interaction
	echo "✓ Composer packages installed"
fi

apache2ctl configtest 2>&1 | grep -q 'Syntax OK' || { echo "✗ Apache config test failed" >&2; exit 1; }
echo "✓ Apache config"
REMOTE

# --- Smoke test, with rollback on failure -------------------------------------

# Contains "bot" so the visitor counter skips it
UA="mwcv-deploy-bot"

check() { # url expected_status [header_regex]
	local out code
	out=$(curl -s -D - -o /dev/null -A "$UA" -H "Accept-Encoding: gzip" "$SITE$1") || return 1
	code=$(head -1 <<<"$out" | awk '{print $2}')
	[ "$code" = "$2" ] || { echo "  $1 returned $code, expected $2" >&2; return 1; }
	if [ -n "${3:-}" ]; then
		grep -qiE "$3" <<<"$out" || { echo "  $1 missing header /$3/" >&2; return 1; }
	fi
}

smoke_test() {
	check / 200 '^content-encoding: gzip' &&
	check /css/main.css 200 '^cache-control: max-age=31536000' &&
	check /nope 404 &&
	check /.env 404 &&
	check /.git/config 404 &&
	check /deploy.sh 404 &&
	check /CLAUDE.md 404 &&
	check /robots.txt 200 &&
	check /sitemap.xml 200 &&
	curl -s -A "$UA" "$SITE/" | grep -q 'You are visitor'
}

if smoke_test; then
	ok "Live site passes smoke test"
	echo "Deployed ${LOCAL:0:7} to $SITE"
else
	echo "✗ Smoke test failed; rolling back to ${LIVE:0:7}" >&2
	ssh -o BatchMode=yes "$SERVER" "cd $APP_DIR && git reset -q --hard $LIVE"
	if [ "$COMPOSER_CHANGED" = 1 ]; then
		ssh -o BatchMode=yes "$SERVER" "cd $APP_DIR && composer install -q --no-dev --optimize-autoloader --no-interaction"
	fi
	smoke_test && echo "Rolled back; the live site is working on ${LIVE:0:7}" >&2 \
		|| echo "Rolled back, but the site still fails the smoke test; check it now" >&2
	exit 1
fi
