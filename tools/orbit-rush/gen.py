import re
SEMI={'C':0,'D':2,'E':4,'F':5,'G':7,'A':9,'B':11}
def midi(t):
    m=re.fullmatch(r'([A-G])(#?)(\d)',t); assert m,t
    return (int(m[3])+1)*12+SEMI[m[1]]+(1 if m[2] else 0)
def nidx(mi): return mi-23
A_MIN={7,9,11,0,2,4,6}   # G major pitch classes
CHORDS={'G':(43,[0,4,7]),'Em':(40,[0,3,7]),'C':(48,[0,4,7]),'D':(50,[0,4,7])}
def arp(c):
    r,iv=CHORDS[c]; r+=12
    return [nidx(r),nidx(r+iv[1]),nidx(r+iv[2]),nidx(r+12)]
def toks(s):
    t=s.split(); assert len(t)==16,(len(t),s); return t
def lead_bar(s,chord):
    out=[]; ch=set((CHORDS[chord][0]+i)%12 for i in CHORDS[chord][1])
    for i,t in enumerate(toks(s)):
        stac=t.endswith('!'); t=t.rstrip('!')
        if t=='.': out.append(0)
        elif t=='-': out.append(255)
        else:
            m=midi(t); assert m%12 in A_MIN,(t,s)
            if m>=96: m-=12
            if i==0: assert m%12 in ch,('strong beat not chord tone',t,chord,i)
            out.append(nidx(m)|(0x80 if stac else 0))
    return out
def bass_bar(pat,chord):
    r=CHORDS[chord][0]; out=[]
    for t in toks(pat):
        out.append({'r':nidx(r),'o':nidx(r+12),'f':nidx(r+7),'.':0,'-':255}[t])
    return out
def bits(s): 
    t=s.split(); assert len(t)==16; return [int(x) for x in t]

# drums: 1 kick 2 snare 3 hat
def drum(k=(),s=(),h=()):
    b=[0]*16
    for i in h: b[i]=3
    for i in s: b[i]=2
    for i in k: b[i]=1
    return b
D1=drum([0,8,10],[4,12],[2,6,14])
DF=drum([0,4,8,12],[],[2,6,10,14])
DC=drum([0,8,10],[4,12],[1,2,3,5,6,7,9,11,13,14,15])
def fill(d): d=list(d); 
def withfill(d):
    d=list(d)
    for i in (12,13,14,15): d[i]=2
    return d
DB=drum([0],[],[4,8,12])
DR=drum([],[0,2,4,6,8,9,10,11,12,13,14,15],[])

AF=bits('1 1 1 1 1 1 1 1 1 1 1 1 1 1 1 1')
AG=bits('1 1 0 1 1 0 1 1 1 1 0 1 1 0 1 0')

BV='r . r o r . o r r . r o r o r .'
BC='r r o r r f o r r r o r r f o f'
BB='r . . . . . . . r . . . . . . .'
BI='r . r . r . r . r . r . r . r .'

VERSE=['G5! . B5! . D6! . G6! . B6 . . . G6 . . .','E6! . G6! . B6! . G6! . E6 . . . B5 . . .',
 'C6! . E6! . G6! . E6! . C6 . E6 . G6 . . .','D6! . F#6! . A6! . F#6! . D6 . . . A5 . . .',
 'B5! D6! G6! B6 . G6! D6! B5 . G5! B5! D6 . . . .','B6! G6! E6! B5 . E6! G6! B6 . . . . G6 . . .',
 'G6! E6! C6! G5 . C6! E6! G6 . E6 . C6 . . . .','A5! D6! F#6! A6! D6! A5! F#5! D5 . . . . . . . .']
CHOR=['G6 . . . E6 . . . C6 . . . E6 . G6 .','A6 . . . F#6 . . . D6 . . . F#6 . A6 .',
 'B6 . . . G6 . . . D6 . . . G6 . B6 .','G6 . B6 . G6 . E6 . B5 . E6 . G6 . . .',
 'C6! E6! G6! E6! C6! E6! G6! . E6! G6! A6 . G6 . E6 .','D6! F#6! A6! F#6! D6! F#6! A6! . F#6! A6! B6 . A6 . F#6 .',
 'B5! D6! G6! D6! B5! D6! G6! . D6! G6! B6 . G6 . D6 .','G6 . . . D6 . . . B5 . . . G5 . . .']
BRK=['B5 . . . . . . . E6 . . . . . . .','C6 . . . . . . . G6 . . . . . . .',
 'D6 . . . . . . . A6 . . . . . . .','A6 . A6! . A6! . A6! A6! F#6! . D6! . F#6! A6! D6! F#6!']
INTRO=['- . . . . . . . . . . . . . . .']+['. . . . . . . . . . . . . . . .']*3
VC=['G','Em','C','D']*2
CC=['C','D','G','Em','C','D','G','G']
BC4=['Em','C','D','D']
IC=['G','Em','C','D']

lead=[];arpv=[];bass=[];drm=[]
def bar(ch,l,a,b,d):
    lead.extend(lead_bar(l,ch)); 
    arpv.extend([CH_IDS_[ch]*4 if x else 0 for x in a])
    bass.extend(bass_bar(b,ch)); drm.extend(d)
CH_IDS_={'G':1,'Em':2,'C':3,'D':4}
# chord table (id 0 dummy)
chord_tab=[0]*4
for c in ['G','Em','C','D']: chord_tab+=arp(c)
# intro
for i,c in enumerate(IC):
    bar(c,INTRO[i],AF,BI if i<2 else BV,DF if i<2 else (D1 if i==2 else withfill(D1)))
for i,c in enumerate(VC):
    bar(c,VERSE[i],AG,BV,withfill(D1) if i%4==3 else D1)
# chorus: F,G,Am,C etc; arp ids only for chords in table: Am F C G -> all present
for i,c in enumerate(CC):
    bar(c,CHOR[i],AF,BC,withfill(DC) if i%4==3 else DC)
for i,c in enumerate(BC4):
    bar(c,BRK[i],AF,BB,DB if i<3 else DR)
for i,c in enumerate(VC):
    bar(c,VERSE[i],AG,BV,withfill(D1) if i%4==3 else D1)
assert len(lead)==len(arpv)==len(bass)==len(drm)==512,(len(lead),len(arpv),len(bass),len(drm))
# F chord in chorus bar 'Am' fine; check chord ids exist
def fr(n): 
    hz=440*2**((n+23-69)/12); return round(hz*16777216/985248)
NN=80
fl=[0]+[fr(n)&255 for n in range(1,NN)]; fh=[0]+[fr(n)>>8 for n in range(1,NN)]
assert fr(69-23)==7493 or abs(fr(69-23)-7493)<2,fr(46)
def bl(name,vals,per=16):
    s=name+':\n'
    for i in range(0,len(vals),per):
        s+='\t.byte '+','.join('$%02x'%v for v in vals[i:i+per])+'\n'
    return s
out=bl('freq_lo',fl)+bl('freq_hi',fh)+bl('chord_tab',chord_tab,4)
out+=bl('vib_tab',[0,0x10,0x18,0x10,0,0xf0,0xe8,0xf0],8)
out+=bl('dlen',[0,3,3,1],4)
# index = type*4+tick
kw=[0x81,0x11,0x11,0]; kf=[0x10,0x08,0x04,0]
sw=[0x81,0x81,0x81,0]; sf=[0x28,0x20,0x18,0]
hw=[0x81,0,0,0]; hf=[0x38,0,0,0]
out+=bl('dwave',[0]*4+kw+sw+hw,4)+bl('dfreq',[0]*4+kf+sf+hf,4)
out+='lead_tab:\n'+''.join('\t.byte '+','.join('$%02x'%v for v in lead[i:i+16])+'\n' for i in range(0,512,16))+'lead_end:\n\t.byte $%02x\n'%lead[0]
out+=bl('arp_tab',arpv+[arpv[0]],16)+bl('bass_tab',bass+[bass[0]],16)+bl('drum_tab',drm+[drm[0]],16)
open('data.asm','w').write(out)
import json; json.dump(dict(lead=lead,arp=arpv,bass=bass,drum=drm,chord=chord_tab),open('intent.json','w'))
print('ok')
