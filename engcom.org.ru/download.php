#!/usr/bin/php
<?php
/*
	global $argc, $argv;
	if( $argc < 2 )
	{
		echo "\n\nНе задан входной файл! ";
		echo "\nusage: ".$argv[0]." file.dict.html\n\n";
		exit();
	}
*/
	$filename = 'EngCom.source.xml.html';
	define( '_ACCESS', 1 );
	require_once('configuration.php');
	require_once('includes/database.php');
	require_once('includes/dictionary.class.php');
	// --------------------------------------------------------------------------
	$database = new database( $dbhost, $dbuser, $dbpass, $dbname, $dbprefix, $dbcharset );
	// --------------------------------------------------------------------------
	function upload( $file )
	{
		global $database, $dictionary_version, $dictionary_date,$dictionary_items;

		$fp = fopen($file,'r');
		if( !$fp )
		{
			echo "Не удалось открыть файл '$file' для чтения'.\n";
			exit();
		}
	
		// Очищаем БД коментариев!
		$database->setQuery("DELETE FROM #__dictionary_additions");
		if(!$result = $database->query()) 
		{
			echo "dberr: " . $database->stderr() ."\n";
			exit();
		}

		// Очищаем словарь! (чтобы избежать дублирования статей)
		$database->setQuery("DELETE FROM #__dictionary");
		if(!$result = $database->query()) 
		{
			echo "dberr: " . $database->stderr()."\n";
			exit();
		}

		// первая строка это название словаря
		$str=fgets($fp);
		if(!$str)
		{
			fclose($fp);
//			@unlink($file);
			echo "err: " ._DICT_DOWNLOAD_WARN1."\n";
			exit();
		}

		// получаем название словаря
		$tmp = explode("{*}",$str);
		$dictionary_name = trim($tmp[0]);
		if( isset($tmp[1]) )
			$dictionary_version = trim($tmp[1]);
		if( isset($tmp[2]) )
			$dictionary_date = trim($tmp[2]);

		// разбираем статьи
		$tmp = "";
		$dict_items = 0;
		while( $str=fgets($fp) )
		{
			$str = trim($str);
			if( $str=="{==}" )
			{
				save($tmp);
				$tmp="";
				$dict_items++;
			}
			else
				$tmp=$tmp.$str;
		}
		fclose($fp);

//		@unlink($file['tmp_name']);
		
		$dictionary_items = $dict_items;
//		saveConfig($option,0);		
//		mosRedirect( "index2.php?option=$option", "Dictionary update complete (items: $dictionary_items)" );
		echo "\n\nDictionary update complete (items: $dictionary_items)\n";
	}	
	
	// --------------------------------------------------------------------------
	function save(&$str) 
	{
		global $database;
		// Начинаем разбор файла
		// сперва отделем слово от статьи по признаку '{|}'
		$data = explode("{|}",$str);
		$data[0] = trim($data[0]);
		$data[1] = trim($data[1]);

//		$ind = HTML_dictionary::refIndex("word="); // task=search
//		// Преобразуем ссылки
//		$data[1] = preg_replace('/\{link:([^:]+)\}/',"<a href=\"$ind\\1\">\\1</a>",$data[1]);

		$row = new mosDictionary($database);
		$row->set("word", addslashes($data[0]));
		$row->set("article", addslashes($data[1]));
		if( !$row->insert() ) 
		{
			echo "dberr: ".$row->getError()."\n";
			return 0;
		}
	
		return 1;
	}
	// --------------------------------------------------------------------------
	upload($filename);
	// --------------------------------------------------------------------------
?>