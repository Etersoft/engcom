#!/bin/sh
echo ���������, �� ������ ���� �� ��� ����� ������
echo ================
for i in q w e r t y u i o p a s d f g h j k l z x c v b n m " "
do
	echo "========== $i ========"
	grep "$i$i$i" EngCom.source | grep -v "www\."
done

echo 
echo ���������, � ������ ������� ����������� ������ ���� ��� �������...
echo ================
grep -v "  " EngCom.source

echo ��������� �������������
echo ================
echo "������: "
grep "_������." EngCom.source | wc -l
echo ================
echo "����: "
grep "_����." EngCom.source | wc -l
