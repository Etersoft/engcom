<?php

defined( '_ACCESS' ) or die( 'Direct Access to this location is not allowed.' );


$dbhost	= 'localhost';
$dbname	= 'engcom';
$dbuser	= 'root';
$dbpass	= '';
$dbprefix	= 'mos_';
$dbcharset = 'koi8r';


$conf_absolute_path	= '/var/www/html/engcom';
$conf_life_site		= 'http://localhost/engcom';
$mosConfig_debug	= 0;

$conf_offline_msg	= 'Извините сайт временно не работает. Попробуйте зайти к нам через 15мин';
$conf_sitename		= 'Система управления штанкетными подъёмами';

$config_sessionInMySQL 	= 1;
$config_sessionLifeTime = 3600; 
$config_sessionName 	= 'DICTSID';
$config_charset 		= 'koi8-r';
$config_display 		= '150';
$config_year_low		= '2005';
$config_year_hi			= '2010';


// Настроки словаря
$dictionary_name='Словарь компьютерных терминов EngCom';
$dictionary_version='';
$dictionary_date='09-12-2006';
$dictionary_items='2209';
$dictionary_lang='russian';
$maxSearhLength=50;
$dictionary_email='lav@etersoft.ru';
$dictionary_edit=1;
$dictionary_subject='[engcom] Новый перевод';
$dictionary_wiki='http://l10n.lrn.ru/wiki/EngCom';
$dictionary_index='index.php';
?>