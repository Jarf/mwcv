#!/usr/bin/env bash
# Rebuilds the theme tune (public/orbit-rush.opus and .m4a) from the SID source.
# Needs: python3 + numpy, 64tass, sidplayfp, ffmpeg. Run from anywhere.
# The speech (sam-welcome.wav) was made with SAM from phonetic text:
#   sam -phonetic -wav sam-welcome.wav "WEHLKAHM TUX MAA5RTIHN WEY5DSAHN DAAT KAAM"
# and is turned into 4-bit samples with: python3 mk_speech.py 230
set -euo pipefail
cd "$(dirname "$0")"
work=$(mktemp -d); trap 'rm -rf "$work"' EXIT
python3 gen.py                       # writes data.asm and intent.json
64tass -b -o "$work/orbit.sid" song.asm
sidplayfp -q -t0:54 -w"$work/full.wav" "$work/orbit.sid" >/dev/null
af="atrim=0:53.5,afade=t=out:st=53:d=0.5,loudnorm=I=-16:TP=-1.5"
ffmpeg -loglevel error -y -i "$work/full.wav" -af "$af" -ac 1 -c:a libopus -b:a 16k ../../public/orbit-rush.opus
ffmpeg -loglevel error -y -i "$work/full.wav" -af "$af" -ac 1 -ar 24000 -c:a aac -b:a 24k -movflags +faststart ../../public/orbit-rush.m4a
rm -f data.asm intent.json
echo "Built public/orbit-rush.opus and .m4a"
