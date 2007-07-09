<?php
// --------------------------------------------------------------------------
// $Id: dictionary.html.php,v 1.5 2007/07/09 16:31:45 pv Exp $
// --------------------------------------------------------------------------
defined( '_ACCESS' ) or die( 'Direct Access to this location is not allowed.' );

class HTML_dictionary {

// --------------------------------------------------------------------------
function refIndex( $params='' )
{
	global $dictionary_index;

	if( !isset($dictionary_index) || $dictionary_index == '' )
	{
		if( $params!='' )
			return "index.php?$params";

		return 'index.php';
	}

	if( $params!='' )
		return "$dictionary_index?$params";
	
	return $dictionary_index;
}

// --------------------------------------------------------------------------

function viewSearchForm($word='',$mid=0)
{
	global $dictionary_name,$dictionary_version,$dictionary_items,$config_live_site,$dictionary_wiki,$dictionary_date,$dictionary_download;
	$list[] = mosHTML::makeOption( '0', _DICT_SEARCH_WORD );
	$list[] = mosHTML::makeOption( '1', _DICT_SEARCH_ALL );
	$methods = mosHTML::selectList( $list, "search_method", "title='"._DICT_SEARCH_METHOD."' class='inputbox' size='1' ",'value', 'text', $mid);
?>
<form action='<?php echo HTML_dictionary::refIndex(); ?>' method='POST' name='searchForm'>
<h1 class="contentheading"><?php echo $dictionary_name; ?></h1>
<p><b>Версия от</b>&nbsp;<?php echo $dictionary_date; ?>
&nbsp;&nbsp;<b>Словарных статей:</b> <?php echo $dictionary_items; ?>
&nbsp;&nbsp;<b>Описание:</b> <a href="<?php echo $config_live_site;?>/content/view/30/63/">О проекте</a>
&nbsp;&nbsp;<b>Загрузить:</b> <a href="<?php echo $dictionary_download; ?>">engcom-current.tar.bz2</a>
</p><p><b>Замечания и предложения:</b> <a href="<?php echo $dictionary_wiki; ?>/TODO"><?php echo $dictionary_wiki;?>/TODO</a>
</p>
<p>&nbsp;</p>
<p><?php echo _DICT_SEARCH_METHOD."&nbsp;&nbsp;:"; echo $methods; ?>
  <input type='hidden' name='option' value='com_dictionary'>
  <input type='hidden' name='task' value='search'>
  <input type='text' class='inputbox' name='word' size=20 value="<?php echo $word; ?>">
  <input type='submit' class='button' value="<?php echo _DICT_SEARCH;?>">
</form>

<?php
}

// --------------------------------------------------------------------------
function showABCLine($char='')
{
	global $abcLine;

	$char = strtoupper($char);
	$navitems=array();
	
	$ind = HTML_dictionary::refIndex("task=glossary");

//	while (list(, $ltrval) = each($abcLine))
	foreach(range('A','Z') as $ltrval )
	{
		if($char == $ltrval) $navitems[] = "<font color='red'>$ltrval</font>";
		else $navitems[] = "<a href='$ind&letter=$ltrval'>$ltrval</a>";
    }
	$nav = "<div align=center>" . implode(" | ", $navitems) . "\n</div>\n\n";
	return $nav;
}

// --------------------------------------------------------------------------
function showResult(&$rows, $word, $method=0)
{
	global $dictionary_edit,$dictionary_wiki,$config_live_site,$dictionary_index;

	HTML_dictionary::viewSearchForm($word,$method);
	echo "<p> "; 
//	if( strlen($word) > 1 )
	echo HTML_dictionary::showABCLine($word[0]);
	
	$numrows = count($rows);
	if( !$numrows )
	{
		echo "<P><b>'$word'</b>  "._DICT_NOTFOUND."</p>";

		if( $dictionary_edit )
		{
?>
		<p>&nbsp;&nbsp;<a href="<?php echo HTML_dictionary::refIndex("task=addnew&word=$word"); ?> "><strong>Можно добавить...</strong></a></p>
<?php
		}

		return;
	}

	echo "<P><i><small>"._DICT_RESULT.":</i> $numrows</small>";

	$ind = HTML_dictionary::refIndex("word="); // task=search
?>
<table cellpadding="3" cellspacing="3" border="0" width="100%">
<?php
	$dwiki = stripslashes($dictionary_wiki);
	for($i=0 ; $i<$numrows; $i++)
	{
		$row = $rows[$i];

		if( $dictionary_edit )
		{
?>
<tr>
	<td valign='top' style='border-bottom-style: dashed; border-bottom-width: 1px;'>
<?php
		} 

		// делаем слово ссылкой
		$row->article = preg_replace("/>[^<]{0,1}($row->word)/i","<a href='$config_live_site/$row->word'>\\1</a>", $row->article);

		// Преобразуем ссылки на другие слова
		$row->article = preg_replace("/{link:([^\}]{1,})}/i","<a href='$config_live_site/\\1'>\\1</a>",$row->article);
	
		$row->article = stripslashes($row->article);

		// выделяем слово
		// исключение '&' - это если в тексте встречаются &lt; &gt; и т.д. а поиск идёт по буквам 'l','g' и т.п.
		$row->article = preg_replace("/\>([^<\&\{]*)($word)/i", ">\\1<font color='red'>\\2</font>", $row->article);

		// формируем меню (после раскраски, чтобы слова в меню не подсвечивались)
		$dlnk = HTML_dictionary::refIndex("task=edit&word=$row->word");
		$w_menu = '<ul style="padding-left: 5px;">';
		$w_menu .="<li><a href=\"$dlnk\">Предложить</a></li>";
		$w_menu .="<li><a href=\"$dwiki/$row->word\">Обсудить</a></li>";
		$w_menu .= "</ul><hr style='padding-left: 10px; padding-right: 10px;' width='98%' align='center'><ul style='padding-left: 5px;'>";
		$w_menu .="<li><a href=\"http://wikipedia.org/wiki/$row->word\">Wikipedia</a></li>";
		$w_menu .="<li><a href=\"http://wiktionary.org/wiki/$row->word\">Wiktionary</a></li>";
		$w_menu .="<li><a href=\"http://foldoc.org/?query=$row->word&action=Search\">FOLDOC</a></li>";
		$w_menu .="<li><a href=\"http://www.onelook.com/cgi-bin/cgiwrap/bware/dofind.cgi?word=$row->word\">OneLook</a></li>";
		$w_menu .= '</ul>';
		$row->article = preg_replace("/\{MENU\}/",$w_menu, $row->article);
		
		echo $row->article;
		echo "\n";
?>
</td></tr>
<?php
	}
?>
</table>	
<?php
}
// --------------------------------------------------------------------------
function editForm($row)
{
?>
<br><a href="<?php echo HTML_dictionary::refIndex(); ?>">Вернуться к словарю</a>
<form action='<?php echo HTML_dictionary::refIndex(); ?>' method='post' name='dict_form'>
<table cellpadding="2" cellspacing="3" border="0" width="100%">
<?php
	if( $row->article != '' )
	{
		global $config_live_site,$dictionary_wiki;
		
		$dwiki 	= stripslashes($dictionary_wiki);
		$dlnk 	= HTML_dictionary::refIndex("task=edit&word=$row->word");
		
		$w_menu = '<ul style="padding-left: 5px;">';
		$w_menu .="<li><a href=\"$dlnk\">Предложить</a></li>";
		$w_menu .="<li><a href=\"$dwiki/$row->word\">Обсудить</a></li>";
		$w_menu .= "</ul><hr style='padding-left: 10px; padding-right: 10px;' width='98%' align='center'><ul style='padding-left: 5px;'>";
		$w_menu .="<li><a href=\"http://wikipedia.org/wiki/$row->word\">Wikipedia</a></li>";
		$w_menu .="<li><a href=\"http://wiktionary.org/wiki/$row->word\">Wiktionary</a></li>";
		$w_menu .="<li><a href=\"http://foldoc.org/?query=$row->word&action=Search\">FOLDOC</a></li>";
		$w_menu .="<li><a href=\"http://www.onelook.com/cgi-bin/cgiwrap/bware/dofind.cgi?word=$row->word\">OneLook</a></li>";
		$w_menu .= '</ul>';
		$row->article = preg_replace("/\{MENU\}/",$w_menu, $row->article);
?>
	<tr><td colspan='2' align='left'><h4>Оригинальная статья:</h4>
			<p><?php echo $row->article; ?>
		</td></tr>
<?php
	}
?>
<tr><td colspan='2'><hr></td></tr>
<tr><td colspan='2' align='left'><h4>Добавить вариант перевода:</h4></td></tr>
<tr><th width='100px' align='left'>Слово:&nbsp;</th><td>
		<input type='text' class='inputbox' name='w_newword' value='<?php echo $row->word; ?>' size='70' maxlength='150' />
</td></tr>
<tr><th width='150px' align='left'>Перевод:</th>
	<td><textarea id='input' class='inputbox' name="w_variant" rows='4' cols='70'></textarea></td>
</tr>
<tr><th width='150px' align='left'>Ссылки на другие статьи:</th>
	<td><input type='text' class='inputbox' name='w_links' value='' size='70' maxlength='150'/></td>
</tr>
<tr><th width='150px' align='left'>Дополнительная информация:</th>
	<td><textarea id='input' class='inputbox' name="w_comm" rows='4' cols='70'></textarea>
</td></tr>
<tr>
	<th width='150px' align='left'>Автор:</th>
	<td><input type='text' class='inputbox' name='w_author' value='' size='70' maxlength='150'/></td>
</tr>
<tr>
	<th width='150px' align='left'>email:</th>
	<td><input type='text' class='inputbox' name='w_email' value='' size='70' maxlength='150'/></td>
</tr>
<tr><th colspan="2">&nbsp;</th></tr>

<tr><td colspan='2' align='left'>
	<input type='submit' name='submit' class='button' value='Отослать' />
	<input type='hidden' name='task' value='save' />
	<input type='hidden' name='word' value="<?php echo $row->word; ?>" />
</td></tr>
</table>
</form>
<?php
}

// ----------------------------------------------------------------------------------
} // enf of class HTML_dictionary
// ----------------------------------------------------------------------------------
?>