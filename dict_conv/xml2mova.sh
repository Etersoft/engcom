#!/bin/sh

if [ -z "$1" ]; then
	echo "�� ����� �������� xml-����"
	echo "Usage: conv_xml_to_dict.sh dictfile.xml "	
	exit;
fi

xsltproc xml2mova.xsl $1 >$1.dict