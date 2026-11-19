import subprocess,sys,numpy as np
P=int(sys.argv[1]); rate=985248/P
subprocess.run(['ffmpeg','-loglevel','error','-y','-i','sam-welcome.wav','-af',f'highpass=f=150,lowpass=f={rate*0.45:.0f},silenceremove=start_periods=1:start_threshold=-45dB,areverse,silenceremove=start_periods=1:start_threshold=-45dB,areverse,aresample={rate:.0f}','-ac','1','-f','u8','sp.raw'],check=True)
r=np.frombuffer(open('sp.raw','rb').read(),dtype=np.uint8).astype(float)-128
r=np.clip(r/np.percentile(np.abs(r),99.7),-1,1)
q=np.clip(np.round((r*0.5+0.5)*15),0,15).astype(np.uint8)
if len(q)%2: q=np.append(q,8)
open('speech.bin','wb').write(((q[0::2]<<4)|q[1::2]).astype(np.uint8).tobytes())
print(len(q),'samples at',round(rate),'Hz =',round(len(q)/rate,2),'s,',len(q)//2,'bytes')
