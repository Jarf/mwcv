#!/usr/bin/env bash
# Rebuilds public/welcome.opus and .m4a (the spoken "Welcome to martinwadeson.com").
# Needs: 64tass, sidplayfp, ffmpeg. The speech was made with SAM from phonetic text:
#   sam -phonetic -wav sam-welcome.wav "WEHLKAHM TUX MAA5RTIHN WEY5DSAHN DAAT KAAM"
# and turned into 4-bit samples with: python3 mk_speech.py 230  (needs numpy)
set -euo pipefail
cd "$(dirname "$0")"
work=$(mktemp -d); trap 'rm -rf "$work"' EXIT
64tass -b -o "$work/welcome.sid" speech.asm >/dev/null
sidplayfp -q -t0:04 -w"$work/full.wav" "$work/welcome.sid" >/dev/null 2>&1
af="atrim=0:2.7,afade=t=out:st=2.6:d=0.1,loudnorm=I=-16:TP=-1.5"
ffmpeg -loglevel error -y -i "$work/full.wav" -af "$af" -ac 1 -c:a libopus -b:a 24k ../../public/welcome.opus
ffmpeg -loglevel error -y -i "$work/full.wav" -af "$af" -ac 1 -ar 24000 -c:a aac -b:a 24k -movflags +faststart ../../public/welcome.m4a
echo "Built public/welcome.opus and .m4a"
