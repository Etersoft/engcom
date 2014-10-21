#!/bin/sh

echo "convert mova to xml..."
./mova2xml.php EngCom.source
echo "convert xml to html..."
./xml2html.sh EngCom.source.xml
echo "make OK"
