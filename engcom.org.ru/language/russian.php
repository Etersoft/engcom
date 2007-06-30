<?php
// --------------------------------------------------------------------------
// $Id: russian.php,v 1.2 2007/06/30 22:02:49 pv Exp $
// --------------------------------------------------------------------------
defined( '_ACCESS' ) or die( 'Direct Access to this location is not allowed.' );

// Main page
define('_DICT_SEARCH','Искать');
define('_DICT','Словарь');
define('_DICT_SEARCH_METHOD','Метод поиска');
define('_DICT_SEARCH_WORD','По слову');
define('_DICT_SEARCH_ALL','По статьям');
define('_DICT_NOTFOUND','Не найдено!');
define('_DICT_RESULT','Получено записей');
define('_DICT_SEE_IN','См. также в');

// Admin menu
define('_PARAMETERS','Параметры');
define('_ADMIN_MENU','Меню администратора');
define('_DICT_UPLOAD','Загрузка файла словаря');
define('_DICT_DOWNLOAD','Загрузить');
define('_DICT_DOWNLOAD_WARN1','Не считать начало файла.');

// Settings
define('_INTERFACE_LANG','Язык интерфейса');
define('_MAX_LENGTH','Максимальная длина слова');
define('_MAX_LEN_COMM','максимальная разрешённая длина слова при поиске');
define('_DICT_NAME','Название словаря');
define('_DICT_VER','Версия');
define('_DICT_DATE','Дата модификации');
define('_DICT_EMAIL','email редактора словаря');
define('_DICT_EDIT','Разрешить добавлять свой перевод');
define('_DICT_SUBJ','Тема письма');

// Warnings
define('_DICT_WARN1','Выражение содержит недопустимые символы или меньше');
define('_DICT_WARN2','символов');
define('_DICT_UPDATE_WARNING',"<p><b>ВНИМАНИЕ!</b><br><font color='red'>Обязятельно</font> сделайте резервный дамп БД.<br>Перед внесением новых данных <font color='red'>словарь будет очищен!</font><br>Также <font color='red'>будет очищена</font> база коментариев!");

$abcLine = array ('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
?>
