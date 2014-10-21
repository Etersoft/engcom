#!/bin/sh

REM=etersoft@sites.host03

echo "convert mova to xml..."
./mova2xml.php EngCom.source > $0.log
echo "convert xml to html..."
./xml2html.sh EngCom.source.xml
echo "make OK"
iconv -f koi8-r -t utf8 <EngCom.source.xml.html >EngCom.source.xml.html.utf8

scp EngCom.source.xml.html.utf8 $REM:/home/etersoft/www/engcom.org.ru/EngCom.source.xml.html || exit
ssh $REM "cd /home/etersoft/www/engcom.org.ru && php download.php && rm EngCom.source.xml.html"

echo "Note: fix dictionary statistics in configure.php"
