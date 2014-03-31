LDC r0,0x1
LDC r1,0x2
CMPU r0,r1
JMP NEQ,label
JMP T,0x0
label:

