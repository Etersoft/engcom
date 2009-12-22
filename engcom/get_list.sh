#!/bin/sh
# вывод только слов
infile=EngCom.source
cat $infile | sed -e "s/  /	/g" | cut -f 1 | less
