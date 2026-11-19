	.cpu "6502"
SID = $d400
SPEED = 5
SPEECH_P = 230
tick = $e0
lead_on = $e1
lead_note = $e2
vc = $e3
arp_off = $e4
bass_on = $e5
bass_note = $e6
dtype = $e7
pwc = $e8
pwdir = $e9
fc = $ea
fdir = $eb
pw_lo = $ec
pw_hi = $ed
tmp_lo = $ee
tmp_hi = $ef
lp = $f0
lead_stac = $fc
sp = $f8
mode = $fa
nib = $fb
ap = $f2
bp = $f4
dp = $f6

	* = $0000
hdr:
	.text "PSID"
	.byte $00,$02,$00,$7c
	.byte $00,$00
	.byte $10,$00
	.byte $10,$03
	.byte $00,$01
	.byte $00,$01
	.byte $00,$00,$00,$01
n1:	.text "Orbit Rush"
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
init_clr:
	sta SID,x
	dex
	bpl init_clr
	lda #$00
	sta SID+5
	lda #$c3
	sta SID+6
	lda #$04
	sta SID+12
	lda #$80
	sta SID+13
	lda #$00
	sta SID+19
	sta SID+16
	lda #$f0
	sta SID+20
	lda #$08
	sta SID+17
	lda #$32
	sta SID+23
	lda #$00
	sta SID+24
	lda #0
	sta mode
	sta nib
	lda #<speech_start
	sta sp
	lda #>speech_start
	sta sp+1
	lda #SPEECH_P-1
	sta $dc04
	lda #0
	sta $dc05
	sta tick
	sta lead_on
	sta bass_on
	sta vc
	sta lead_stac
	sta dtype
	sta arp_off
	sta fc
	sta pwc
	lda #2
	sta pwdir
	lda #1
	sta fdir
	jsr reset_ptrs
	rts

reset_ptrs:
	lda #<lead_tab
	sta lp
	lda #>lead_tab
	sta lp+1
	lda #<arp_tab
	sta ap
	lda #>arp_tab
	sta ap+1
	lda #<bass_tab
	sta bp
	lda #>bass_tab
	sta bp+1
	lda #<drum_tab
	sta dp
	lda #>drum_tab
	sta dp+1
	rts

play:
	lda mode
	bne music_play
	ldy #0
	lda nib
	bne sp_lo
	lda (sp),y
	lsr
	lsr
	lsr
	lsr
	sta SID+24
	lda #1
	sta nib
	rts
sp_lo:
	lda (sp),y
	and #$0f
	sta SID+24
	lda #0
	sta nib
	inc sp
	bne sp_chk
	inc sp+1
sp_chk:
	lda sp+1
	cmp #>speech_end
	bne sp_done
	lda sp
	cmp #<speech_end
	bne sp_done
	lda #1
	sta mode
	lda #$1f
	sta SID+24
	lda #$c7
	sta $dc04
	lda #$4c
	sta $dc05
sp_done:
	rts

music_play:
	jsr do_mod
	jsr do_lead
	jsr do_arp
	jsr do_bass
	inc tick
	lda tick
	cmp #SPEED
	bne play_done
	lda #0
	sta tick
	jsr adv_ptrs
play_done:
	rts

adv_ptrs:
	inc lp
	bne ap1
	inc lp+1
ap1:	inc ap
	bne ap2
	inc ap+1
ap2:	inc bp
	bne ap3
	inc bp+1
ap3:	inc dp
	bne ap4
	inc dp+1
ap4:	lda lp+1
	cmp #>lead_end
	bne ap5
	lda lp
	cmp #<lead_end
	bne ap5
	jsr reset_ptrs
ap5:	rts

do_mod:
	lda pwc
	clc
	adc pwdir
	sta pwc
	beq dm_flip
	cmp #254
	bne dm_pw
dm_flip:
	lda pwdir
	eor #$ff
	clc
	adc #1
	sta pwdir
dm_pw:
	lda pwc
	asl
	sta pw_lo
	lda #0
	rol
	clc
	adc #6
	sta pw_hi
	lda fc
	clc
	adc fdir
	sta fc
	beq df_flip
	cmp #80
	bne df_w
df_flip:
	lda fdir
	eor #$ff
	clc
	adc #1
	sta fdir
df_w:
	lda fc
	clc
	adc #50
	sta SID+22
	rts

do_lead:
	lda tick
	bne dl_run
	ldy #0
	lda (lp),y
	beq dl_run
	cmp #$ff
	bne dl_new
	lda #0
	sta lead_on
	beq dl_run
dl_new:
	pha
	and #$7f
	sta lead_note
	pla
	and #$80
	sta lead_stac
	lda #1
	sta lead_on
	lda #0
	sta vc
dl_run:
	lda tick
	cmp #3
	bne dl_run2
	lda lead_stac
	beq dl_run2
	lda #0
	sta lead_on
dl_run2:
	lda lead_on
	beq dl_off
	ldx lead_note
	lda vc
	cmp #12
	bcc dl_nt
	and #2
	beq dl_nt
	inx
dl_nt:
	lda freq_lo,x
	sta tmp_lo
	lda freq_hi,x
	sta tmp_hi
dl_novib:
	lda tmp_lo
	sta SID
	lda tmp_hi
	sta SID+1
	lda pw_lo
	sta SID+2
	lda pw_hi
	sta SID+3
	ldx #$41
	ldy #2
	lda lead_stac
	beq dl_thr
	ldy #1
dl_thr:
	sty tmp_hi
	lda vc
	cmp tmp_hi
	bcc dl_wf
	ldx #$11
dl_wf:
	lda #$d3
	sta tmp_hi
	lda #$00
	sta tmp_lo
	ldy tick
	cpy #4
	bne dl_w
	ldy #1
	lda (lp),y
	beq dl_w
	ldx #$40
	lda #$00
	sta tmp_lo
	sta tmp_hi
dl_w:	lda tmp_lo
	sta SID+5
	lda tmp_hi
	sta SID+6
	stx SID+4
	inc vc
	rts
dl_off:
	lda #$00
	sta SID+5
	sta SID+6
	lda #$40
	sta SID+4
	rts

do_arp:
	lda tick
	bne da_run
	ldy #0
	lda (ap),y
	sta arp_off
da_run:
	lda arp_off
	beq da_off
	lda tick
	and #3
	clc
	adc arp_off
	tax
	lda chord_tab,x
	tax
	lda freq_lo,x
	sta SID+7
	lda freq_hi,x
	sta SID+8
	lda #$00
	sta SID+9
	lda #$06
	sta SID+10
	ldx #$41
	lda tick
	cmp #4
	bne da_w
	ldx #$40
	lda #$00
	sta SID+12
	sta SID+13
	stx SID+11
	rts
da_w:	lda #$04
	sta SID+12
	lda #$80
	sta SID+13
	stx SID+11
	rts
da_off:
	lda #$00
	sta SID+12
	sta SID+13
	lda #$40
	sta SID+11
	rts

do_bass:
	lda tick
	bne db_run
	ldy #0
	lda (bp),y
	beq db_d
	cmp #$ff
	bne db_n
	lda #0
	sta bass_on
	beq db_d
db_n:	sta bass_note
	lda #1
	sta bass_on
db_d:	ldy #0
	lda (dp),y
	sta dtype
db_run:
	lda dtype
	beq db_bass
	tax
	lda tick
	cmp dlen,x
	bcs db_bass
	txa
	asl
	asl
	clc
	adc tick
	tax
	lda dfreq,x
	sta SID+15
	lda #0
	sta SID+14
	lda #$f0
	sta SID+20
	lda dwave,x
	sta SID+18
	rts
db_bass:
	lda bass_on
	beq db_off
	ldx bass_note
	lda freq_lo,x
	sta SID+14
	lda freq_hi,x
	sta SID+15
	lda #$f0
	sta SID+20
	lda #$41
	sta SID+18
	rts
db_off:
	lda #$00
	sta SID+20
	lda #$40
	sta SID+18
	rts

	.include "data.asm"
speech_start:
	.binary "speech.bin"
speech_end:
	.here
