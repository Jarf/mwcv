#!/usr/bin/env bash
# Cuts assets/terminus-full.woff2 down to the characters the site can use and
# writes public/fonts/terminus.woff2. Needs fonttools: pip install fonttools brotli
# The character list is in tools/font-unicodes.txt; build.php warns when the page
# uses a character outside it. Add it there and rerun this script.
set -euo pipefail
cd "$(dirname "$0")/.."
pyftsubset assets/terminus-full.woff2 \
	--unicodes="$(cat tools/font-unicodes.txt)" \
	--layout-features='' --flavor=woff2 \
	--output-file=public/fonts/terminus.woff2
ls -l public/fonts/terminus.woff2
