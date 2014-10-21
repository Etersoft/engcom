#!/usr/bin/php 
<?php
// -----------------------------------------------------
// $Id: mova2xml.php,v 1.7 2006/02/04 21:36:30 pv Exp $
// $Date: 2006/02/04 21:36:30 $
// -----------------------------------------------------
$DEBUG=0;

/*
	if( $argc < 2 )
	{
		echo "\n\nНе задан входной файл! ";
		echo "\nusage: ".$argv[0]." file.dict\n\n";
		exit();
	}
	$src = $argv[1];
*/	
	$src='EngCom.source';
	$dictname = $src;
	if( isset($argv[2]) )
		$dictname = $argv[2];

	$dictdate='';
	if( $dictdate = filectime($src) )
		$dictdate = date('d-m-Y',$dictdate);


// ===================================================================
// ВНИМАНИЕ!!!
// указанные типы используются также в конверторах
// xml2html.xsl и xml2mova.xsl
// поэтому внося изменения сюда необходимо изменять и там...
// (иначе они не будут понимать новые типы)
// ===================================================================

// перечень типов для частей речи
$types['_a.'] 	= "adjective"; 	// имя прилагательное";
$types['_adv.'] = "adverb"; 	// наречие";
$types['_n.']	= "noun"; 		// существительное";
$types['_v.']	= "verb"; 		// глагол";
$types['_mis.'] = "misuse"; 	// неправильное употребление";

// перечень типов для вариантов перевода
$variant_types['_полигр.']	= "typography"; 	// типографское дело";
$variant_types['_нерек.']	= "depricated"; 	// не рекомендовано для использования";
$variant_types['_жаргон.']	= "jargon"; 		// жаргонное выражение, например 'железо'";
$variant_types['_жарг.']	= "jargon"; 		// жаргонное выражение, например 'железо'";
$variant_types['_интерф.']	= "dialog"; 		// перевод в интерфейсах с пользователем";
$variant_types['_прогр.']	= "program"; 		// при программировании";
$variant_types['_уст.']		= "obsolete"; 		// устаревшее и в основном не использующееся в настоящее время";

// ===================================================================
function debug($str)
{
	global $DEBUG;
	if($DEBUG) echo $str;
}

// -------------------------------------------------------------------------------------------
require_once("fast_template.class.php");
// -------------------------------------------------------------------------------------------
class Variant 
{
	var $text=""; 		// вариант
	var $type=array(); 	// key из массива спец. символов
	function Variant() 
	{
	}

	function myPrint() 
	{
		global $variant_types;
		echo "$this->text ";
		if( count($this->type) <= 1 )
		{
			if( isset($this->type[0]) )
				echo $this->text." - type: ???";
		}
		else
		{
			echo "type: ";
			$cn = count($this->type);
			for($i=0; $i<$cn;$i++)
				echo " ".$variant_types[$this->type[$i]];
		}
	}

	function varToXML() 
	{
		global $variant_types;

		if( $this->text=='' )
			return array('','');

		$text = htmlspecialchars($this->text);
		$type = array();

		// преобразуем типы
		if( count($this->type) < 1 )
			$type = '';
		else
		{
			while( list($k1,$v1) = each($this->type) )
			{
				if( isset($variant_types[$v1]) )
					array_push($type,$variant_types[$v1]);
				else	// добавляем как есть, если не нашли
					array_push($type,$v1);
			}
		}

		return array($text,$type);
	}
}
// -------------------------------------------------------------------------------------------
class Meaning 
{
	var $variants 	= array(); // варианты
	var $links 		= array();

	function Meaning() 
	{
	}

	function myPrint() 
	{
		echo "<ul><u>варианты:</u>";
		while( list($k1,$v1) = each($this->variants) )
		{
			echo "<li>";
			$v1->myPrint();
			echo "</li>";
		}
		echo "</ul><ul><u>links:</u>";
		while( list($k1,$v1) = each($this->links) )
		{
		    echo "<li>$v1</li>";
		}
		echo "</ul>";
	}

	function varToXML()
	{
		if( !count($this->variants) )
			return "";

		$tpl = new FastTemplate("templates");
		$tpl->define(array(
			'main'=>'variant.tpl'
		));

		debug("var count: ".count($this->variants)."\n");
		reset($this->variants);
		while( list($k1,$v1) = each($this->variants) )
		{
			debug("var: ".$v1->text."\n");
			list($txt,$type) = $v1->varToXml();
			if( $txt=='' )
				continue;

			if( !is_array($type) )
			{
				$tpl->assign( array(
					'TXT'	=> $txt,
					'TYPE'	=> $type
				));

				$tpl->parse('MAIN',".main");
			}
			else
			{
				// если вариант имеет несколько типов
				// то делаем каждый тип вариантом перевода!
				while( list($k2,$v2) = each($type) )
				{
					$tpl->assign( array(
						'TXT'	=> $txt,
						'TYPE'	=> $v2
					));
					$tpl->parse('MAIN',".main");
				}
			}
		}		

		return $tpl->fetch('MAIN');
	}

	function linksToXML()
	{
		if( !count($this->links) )
			return "";

		$tpl = new FastTemplate("templates");
		$tpl->no_error();
		$tpl->define(array(
			'main' => 'link.tpl'
		));
		
		reset($this->links);
		while( list($k1,$v1) = each($this->links) )
		{
			$tpl->assign( array(
				'TXT' => $v1
			));
			$tpl->parse('MAIN',".main");
		}		

		return $tpl->fetch('MAIN');
	}

}
// -------------------------------------------------------------------------------------------
class Part
{
	var $type = array();		// ключ в массиве типов
	var $meanings = array(); 	// смыслы
	function Part($type="") 
	{
		$this->type=$type;
	}

	function myPrint() 
	{
		global $types;
//		echo " type: ($this->type) "; echo $types[$this->type];
		while( list($k1,$v1) = each($this->meanings) )
		{
//			echo "<p> смысл("; echo $k1+1; echo ")";
			$v1->myPrint();
		}
	}

	function toXML() 
	{
		if( count($this->meanings) <=0 )
			return;
	
		$tpl = new FastTemplate("templates");
		$tpl->define(array(
			'main'=>'meaning.tpl'
		));
		
		while( list($k1,$v1) = each($this->meanings) )
		{
			debug("variants: ".count($v1->variants)."\n");
			$tpl->assign( array(
				'VARIANTS'	=> $v1->varToXML(),
				'LINKS'		=> $v1->linksToXML()
			));

			$tpl->parse('MAIN',".main");
		}		

		return $tpl->fetch('MAIN');
	}
}
// -------------------------------------------------------------------------------------------
class Doc
{
	var $word = "";			// слово
	var $parts = array();	// часть речи

	function Doc(&$word) 
	{
		$this->word=$word;
	}

	function myPrint() 
	{
//		echo "<P>'<u><i>$this->word</u></i>'</P>";
		while( list($k1,$v1) = each($this->parts) )
		{
			echo "<p>"; echo $k1+1; echo ")";
			$v1->myPrint();
		}
	}

	function toXML() 
	{
		if( count($this->parts) <=0 )
			return "";

		$tpl = new FastTemplate("templates");
		$tpl->define(array(
			'main'=>'article.tpl',
			'part'=>'part.tpl'
		));

		global $types;
		if( count($this->parts) )
		{
			reset($this->parts);
			while( list($k1,$v1) = each($this->parts) )
			{
				$type = '';
			    if( is_array($v1->type) )
				{
					// здесь надобы разобрать типы
					// пока берем первый
					$type = $v1->type[0];
				}
				else
					$type = $v1->type;

				if( isset($types[$type]) )
					$type=$types[$type];

				$tpl->assign( array(
					'NAME' 		=> $type,
					'MEANINGS' 	=> $v1->toXML()
				));

				$tpl->parse('PARTS',".part");
			}
		}
		
		$tpl->assign( array(
			'WORD' => $this->word
		));
	
		$tpl->parse('MAIN',"main");
		
		// из-за некоторых особенностей форматирования
		// при использовании шаблонов образуются пустые строки
		// приходится убирать их за два прохода
		$str=$tpl->fetch('MAIN');
		return preg_replace("/([\n]{2,})/","\n",$str);
	}
}
// -------------------------------------------------------------------------------------------
function parse_variant(&$str, &$variant)
{
	// варианты тоже могут иметь типы
	// _жарг. | _интерф. | _полигр
	// поэтому их отдельно необходимо определить
	$tmp = array();	
	// tmp[0] - исходная строка
	// tmp[1] - тип
	// tmp[2] - вариант
	debug("\nstring: $str\n");
	if( preg_match("/^(\_[^.]{1,}\.) (.*)$/",$str,$tmp) )
	{
		array_push($variant->type,trim($tmp[1]));
		$variant->text = trim($tmp[2]);
		debug($tmp[1]."  |  ".$tmp[2]."\n");
	}
	else
		$variant->text = trim($str);
}
// -------------------------------------------------
function parse_meaning_links($str, &$mm)
{
	$lstr = array();	
	// lstr[0] - исходная строка
	// lstr[1] - всё, что до ссылок
	// lstr[2] - сcылки разделённые ','
	// регистро-независимый поиск ( СМ. | см. | См. | сМ. )
	debug("\nstring: $str\n");
	if( preg_match("/^(.*)см\.(.*)$/i",$str,$lstr) )
	{
		// Здесь неплохо бы проверять, что count($lstr) < 2
		// иначе получается, что в тексте несколько 'см.'
		$links=preg_split("/,/",$lstr[2]);
		while( list($key, $val) = each($links) )
		{
			$val = trim($val);
			if( !$val ) 
				continue;

			array_push($mm->links,trim($val));
			debug("link($key): $val\n");
		}
		
		// удаляем все сслыки вместе с 'см.'
		$str = $lstr[1];
	}
	
	return $str;
}
// -------------------------------------------------
function parse_meaning(&$str, &$mm)
{
	// Получаем определения
	// они разделены символом ';'
	$def = preg_split("/;/",$str);
	debug("string: $str\n");
	while( list($key, $val) = each($def) )
	{
		$val = trim($val);
		debug("var[$key] $val\n");
		if(!$val)
			continue;
		
		// в вариантах могут встретится ссылки вида 'см. linkname'
		// они тоже стоят после ';'
		// поэтому сперва проходим по link-ам и удаляем их из исходной 
		// строки складывая в отдельный массив (mm->links)
		$val = parse_meaning_links($val,$mm);
		if( $val!= '' )
		{
			$variant = new Variant();
			parse_variant($val,$variant);
			array_push($mm->variants,$variant);
		}
	}
	debug("meaning variants: ".count($mm->variants)."\n");
	debug("meaning links   : ".count($mm->links)."\n");
}
// -------------------------------------------------
function parse_parts(&$str, &$part)
{
	// разбиваем по смыслам
	// это: 1> xxx 2> xxx и т.п.
	$defs = preg_split("([1-9]{1,}\>)",$str);
	debug("\nstring: $str\n");
	while( list($key, $val) = each($defs) )
	{
		$val = trim($val);
		debug("meaning($key): $val\n");
		if(!$val)
			continue;

		$meaning = new Meaning();
		parse_meaning($val,$meaning);
		array_push($part->meanings, $meaning);
	}
	debug("parts meanings: ".count($part->meanings)."\n");
}
// -------------------------------------------------
function parse_article(&$str, &$doc)
{
	// разбиваем по частям речи
	// это по "цифрам с точкой" типа 1. xxx 2. xxx и т.п.
	$defs = preg_split("([1-9]+\.)",$str);
	debug("\nstring: $str\n");
	while( list($key, $val) = each($defs) )
	{
		$val = trim($val);
		if( !$val )
			continue;

		$part = new Part();

		// проверка определена ли часть речи
		// _v. | _n. | _a. и т.п.
		// определение обязательно идёт в начале строки

		$tmp = array();
		// tmp[0] - исходная строка
		// tmp[1] - часть речи
		// tmp[2] - остальная строка
		if( preg_match("/^(\_[a-z]{1,}\.) (.*)$/",$val,$tmp) )
		{
			$part->type = trim($tmp[1]);
			$val 		= trim($tmp[2]);
			debug("\n".$part->type." | $val\n");
		}

		parse_parts($val,$part);
		array_push($doc->parts,$part);
	}

	debug("doc parts: ".count($doc->parts)."\n");
}
// -----------------------------------------------------------------------------
function parse_word(&$str)
{
	// делим строку по первым встретившимся ДВУМ пробелам
	$defs = preg_split("/([\ ]{2})/",$str);
	if( count($defs) < 2 )
		return array(0,0);
	debug("\n".$defs[0]."  |  ".$defs[1]."\n");
	return $defs;
}
// -----------------------------------------------------------------------------
// =============================================================================
	$fp = fopen($src,"r");
	if( !$fp )
	{
		echo "\n<p> Не удалось открыть файл $src";
		exit;
	}

	$fout = fopen("$src.xml","w");
	if( !$fout )
	{
		echo "\n<p> Не удалось открыть файл '$src.xml' для записи\n";
		exit;
	}

	fwrite($fout,"<?xml version=\"1.0\" encoding=\"koi8-r\" ?>\n");
	fwrite($fout,"<dictionary name=\"$dictname\" last_modify=\"$dictdate\">\n");

	$wcount=0;
	while( $str=trim(fgets($fp)) )
	{
		if( !$str )
			continue;
			
		list($word,$article) = parse_word($str);
		if(!$word)
		{
			echo "\n<p> Не удалось выделить СЛОВО из -> '$str'\n";
			continue;
		}

//		echo "\nword: $word\n";
		$wcount++;
		$doc = new Doc($word);		
		parse_article($article, $doc);
//		$doc->myPrint();
		fwrite($fout,"\n"); 
		fwrite($fout, $doc->toXML());
	}
	fwrite($fout,"\n</dictionary>\n");

	fclose($fp);
	fclose($fout);
	
	echo "Обработано $wcount словарных статей.\n";
	
	exit(0);
// =============================================================================
?>
