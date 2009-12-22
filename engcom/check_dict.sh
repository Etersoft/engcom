#!/bin/sh
echo Проверяем, не должно быть по три буквы подряд
echo ================
for i in q w e r t y u i o p a s d f g h j k l z x c v b n m " "
do
	echo "========== $i ========"
	grep "$i$i$i" EngCom.source | grep -v "www\."
done

echo 
echo Проверяем, в каждой строчке обязательно должны быть два пробела...
echo ================
grep -v "  " EngCom.source

echo Проверяем использование
echo ================
echo "жаргон: "
grep "_жаргон." EngCom.source | wc -l
echo ================
echo "жарг: "
grep "_жарг." EngCom.source | wc -l
