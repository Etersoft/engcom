<?php

defined( '_ACCESS' ) or die( 'Direct Access to this location is not allowed.' );


$dbhost	= 'localhost';
$dbname	= 'engcom';
$dbuser	= 'engcom';
$dbpass	= 'engcom';
$dbprefix	= 'mos_';
$dbcharset = 'koi8r';


$conf_absolute_path	= '/home/lav/www/engcom.org.ru';
$conf_life_site		= 'http://engcom.org.ru';
$mosConfig_debug	= 0;

$conf_offline_msg	= 'Извините сайт временно не работает. Попробуйте зайти к нам через 15мин';
$conf_sitename		= 'Словарь компьютерных терминов Engcom';

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
$dictionary_date='01-07-2007';
$dictionary_items='2209';
$dictionary_lang='russian';
$maxSearhLength=50;
#$dictionary_email='lav@etersoft.ru';
$dictionary_email='pv@etersoft.ru';
$dictionary_edit=1;
$dictionary_subject='[engcom] Новый перевод';
$dictionary_wiki='http://l10n.lrn.ru/wiki/EngCom';
$dictionary_index='index.php';
$dictionary_download='http://etersoft.ru/download/engcom/encgom-current.tar.bz2';

// настройки для работы Mail
define('_DATE_FORMAT','l, F d Y');
define('_DATE_FORMAT_LC',"%A, %d %B %Y");
define('_DATE_FORMAT_LC2',"%A, %d %B %Y %H:%M");
$mosConfig_mailer 	= 'mail';
$mosConfig_mailfrom = 'MAILFROM';
$mosConfig_fromname = 'FROMNONAME';
$mosConfig_sendmail = '/usr/sbin/sendmail';
$mosConfig_smtpauth = '0';
$mosConfig_smtpuser = '';
$mosConfig_smtppass = '';
$mosConfig_smtphost = 'localhost';
?>