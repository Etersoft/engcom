<?php
	defined( '_ACCESS' ) or die( 'Direct Access to this location is not allowed.' );
// ----------------------------------------------------------------------------------	
function correct($token, $maxlen=50)
{
	$token = substr($token, 0, $maxlen);
	$token = preg_replace("/[^\w\-\x7F-\xFF\s]/", " ", $token);
//	запрет на слова менье 1-ой буквы...
//	$good_words = trim(preg_replace("/\s(\S{1})\s/", " ", preg_replace("/ +/", "  "," $token ")));
//	$good_words = preg_replace("/ +/", " ", $good_words);

	$good_words = preg_replace("/ +/", " ", trim($token));
	if( $good_words )
		return $good_words;

	return 0;
}
// ----------------------------------------------------------------------------------	
function search(&$word,$method)
{
	global $database, $maxSearhLength;
	// Строка не должна быть больше $maxSearhLength символов
	$word  = substr($word, 0, $maxSearhLength); 
	$good_word = correct($word,$maxSearhLength);

	if( !$good_word )
	{
		echo "<script> alert('"._DICT_WARN1." 1 "._DICT_WARN2."'); window.history.go(-1); </script>\n";
		exit();
	}		

	$query = "";
	switch($method)
	{
		case 1:
			// т.к. статья хранится в html виде, то надо искать только между тегов (т.е. ..>...<...)
			$up_word = strtoupper($good_word);
			$low_word = strtolower($good_word);
			$query = "SELECT * FROM #__dictionary WHERE ";
			$query .= "word LIKE '%$up_word%' OR word LIKE '%$low_word%' ";
			$query .= "OR article LIKE '%>%$up_word%<%' OR word LIKE '%>%$low_word%<%' ";
			$query .= "ORDER BY word ASC ";
		break;
		
		default:
			$query = "SELECT * FROM #__dictionary WHERE word='$good_word'";
		break;
	}

	$database->setQuery($query);
	$rows = $database->loadObjectList();
	if(!$result = $database->query()) {
		echo $database->stderr();
		return false;
	}

	HTML_dictionary::showResult($rows,$good_word,$method);
}
// ----------------------------------------------------------------------------------	
function searchInFOLDOC(&$word,$method)
{
	$url = "http://wombat.doc.ic.ac.uk/foldoc/foldoc.cgi?query=$word&action=Search";
	ob_start();
	readfile($url);
	$content = ob_get_contents();
	ob_end_clean();
	echo "<p><b>Content:<br>";
	echo $content;
}
// ----------------------------------------------------------------------------------	

function glossary($letter)
{
	// защита от неверного запроса
	if( strlen($letter) > 1 )
	{
		echo "<script>window.history.go(-1); </script>\n";
		exit();
	}		

	global $database;

	$query = "SELECT * FROM #__dictionary WHERE word LIKE '".strtoupper($letter)."%' OR word LIKE '".strtolower($letter)."%' ORDER BY word ASC ";
	$database->setQuery($query);
	$rows = $database->loadObjectList();
	if(!$result = $database->query()) {
		echo $database->stderr();
		return false;
	}

	HTML_dictionary::showResult($rows,$letter);
}
// ----------------------------------------------------------------------------------	
function addnew($word)
{
	$row->word 		= $word;
	$row->article 	= '';
	
	HTML_dictionary::editForm($row);
}
// ----------------------------------------------------------------------------------	
function edit($word)
{
	global $dictionary_edit;

	if( !$dictionary_edit )
	{
		mosRedirect( HTML_dictionary::refIndex() );
		exit();
	}

	global $database;

	// защитные меры
	$word = substr($word,0,50);

	if( strlen($word)>2 && !correct($word) )
	{
		echo "<script> alert('Bad word format'); window.history.go(-1); </script>\n";
		exit();
	}

	$row = new mosDictionary($database);
	if( !$row->load($word) )
	{
		echo "<script> alert('Word not finded in dictionary!'); window.history.go(-1); </script>\n";
		exit();
	}

	HTML_dictionary::editForm($row);
}
// ----------------------------------------------------------------------------------	
function save($word)
{
	global $_REQUEST, $dictionary_edit, $dictionary_subject, $dictionary_index;

	if( !$dictionary_edit )
	{
		mosRedirect( HTML_dictionary::refIndex() );
		exit();
	}

	$word 		= mosGetParam($_REQUEST, 'word', '');
	$links 		= mosGetParam($_REQUEST, 'w_links', '');
	$new_word 	= mosGetParam($_REQUEST, 'w_newword', '');
	$var  		= mosGetParam($_REQUEST, 'w_variant', '');
	$comm 		= mosGetParam($_REQUEST, 'w_comm', '');
	$author 	= mosGetParam($_REQUEST, 'w_author', '');
	$a_email 	= mosGetParam($_REQUEST, 'w_email', '');
	$a_email2 	= mosGetParam($_REQUEST, 'w_e', '');

	if( $a_email != $a_email2 )
	{
		echo "<script> alert('Не совпадат адреса электронной почты!'); window.history.go(-1); </script>\n";
		exit();
	}

	if( !$word )
	{
		echo "<script> alert('Не указана словарная статья!'); window.history.go(-1); </script>\n";
		exit();
	}

	if( !$var )
	{
		echo "<script> alert('Не указан новый перевод!'); window.history.go(-1); </script>\n";
		exit();
	}

	// безопасность
	$word 		= substr($word,0,50);
	$comm 		= substr($comm,0,255);
	$word 		= addslashes(htmlspecialchars($word));
	$var 		= addslashes(htmlspecialchars($var));
	$new_word 	= addslashes(htmlspecialchars($new_word));
	$links 		= addslashes(htmlspecialchars($links));
	$comm 		= addslashes(htmlspecialchars($comm));
	$author 	= addslashes(htmlspecialchars($author));
	$a_email 	= addslashes(htmlspecialchars($a_email));

	global $dictionary_email;
		
	$message = "В словарь добавлен новый вариант перевода:";
	$message .="\n---------------------------------------\n";
	$message .= "word: $word\n";
	if ( $new_word != '' )
	{
		$message .="\n---------------------------------------\n";	
		$message .= "Новый вариант слова:\n $new_word";
	}
	$message .="\n---------------------------------------\n";	
	$message .= "перевод:\n $var";
	$message .="\n---------------------------------------\n";
	$message .= "links: $links";
	$message .="\n---------------------------------------\n";
	$message .= "Дополнительная информация:\n $comm";
	$message .="\n---------------------------------------\n";	
	$message .= "Автор:  $author\n";
	$message .= "email:  $a_email";
	$message .="\n---------------------------------------\n";	

	$m_from = 'webmaster@etersoft.ru';
	if( $a_email != '' )
		$m_from	= $a_email;

	if( !mosMail($m_from,'webmaster',$dictionary_email, $dictionary_subject, $message) )
	{
		echo "<script> alert('Не удалось отослать ваш вариант! Если Вам не сложно попробуйте ещё раз позже'); window.history.go(-1); </script>\n";
		exit();
	}

	echo "<script> alert('Спасибо! Ваш перевод отправлен редактору!'); window.history.go(-1); </script>\n";
//	mosRedirect( HTML_dictionary::refindex() );
}
// ----------------------------------------------------------------------------------
?>
