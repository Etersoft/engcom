# Written by Lav (c) 2002
# Создаём пригодный для mova словарь из исходных файлов

DICT = EngCom
PACKET = engcom-1.36
DSRC = ${DICT}.source
DDST = ${DICT}.koi

all: ${DDST}

${DDST}: ${DICT}.header ${DSRC}
	cp -f ${DSRC} ${DSRC}.old
	cat ${DSRC}.old | sort > ${DSRC}
	cat ${DICT}.header ${DSRC} | iconv -f utf8 -t koi8-r > ${DDST}

.PHONY: clean distr
clean:
	rm -f ${DDST} ${DSRC}.old

distr: ${DDST}
	cd ../.. ; tar cvfj ${PACKET}.tar.bz2 ${PACKET}/dict/${DSRC} ${PACKET}/dict/${DICT}.header ${PACKET}/dict/Makefile ${PACKET}/docs
	