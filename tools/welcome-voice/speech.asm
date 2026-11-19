; Plays the 4-bit speech sample through the SID volume register, then stops.
; Build with build.sh (needs 64tass, sidplayfp, ffmpeg).
	.cpu "6502"
SID = $d400
SPEECH_P = 230		; CPU cycles per sample (about 4.3 kHz)
sp = $f8
nib = $fb
done = $fc

	* = $0000
	.text "PSID"
	.byte $00,$02,$00,$7c
	.byte $00,$00
	.byte $10,$00
	.byte $10,$03
	.byte $00,$01
	.byte $00,$01
	.byte $00,$00,$00,$01
n1:	.text "Welcome"
	.fill 32-(*-n1),0
n2:	.text "martinwadeson.com"
	.fill 32-(*-n2),0
n3:	.text "2026 martinwadeson.com"
	.fill 32-(*-n3),0
	.byte $00,$14
	.byte $00,$00,$00,$00
	.word $1000
	.logical $1000
	jmp init
	jmp play

init:
	ldx #$18
	lda #0
clr:	sta SID,x
	dex
	bpl clr
	sta nib
	sta done
	lda #<speech_start
	sta sp
	lda #>speech_start
	sta sp+1
	lda #SPEECH_P-1
	sta $dc04
	lda #0
	sta $dc05
	rts

play:
	lda done
	bne finished
	ldy #0
	lda nib
	bne lo
	lda (sp),y
	lsr
	lsr
	lsr
	lsr
	sta SID+24
	lda #1
	sta nib
	rts
lo:	lda (sp),y
	and #$0f
	sta SID+24
	lda #0
	sta nib
	inc sp
	bne chk
	inc sp+1
chk:	lda sp+1
	cmp #>speech_end
	bne finished
	lda sp
	cmp #<speech_end
	bne finished
	lda #1
	sta done
	lda #0
	sta SID+24
finished:
	rts

speech_start:
	.binary "speech.bin"
speech_end:
	.here
