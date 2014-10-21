#!/bin/sh

if [ -z "$1" ]; then
	echo "Не задан исходный xml-файл"
	echo "Usage: $0 dictfile.xml "	
	exit;
fi

xsltproc xml2html.xsl $1 >$1.html