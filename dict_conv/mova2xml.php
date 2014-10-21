#!/usr/bin/php 
<?php
// -----------------------------------------------------
// $Id: mova2xml.php,v 1.5 2005/03/08 20:08:12 pv Exp $
// $Date: 2005/03/08 20:08:12 $
// -----------------------------------------------------


	if( $argc < 2 )
	{
		echo "\n\nНе задан входной файл! ";
		echo "\nusage: ".$argv[0]." file.dict\n\n";
		exit();
	}

	$src = $argv[1];
	$dictname = $src;
	if( isset($argv[2]) )
		$dictname = $argv[2];


// ===================================================================
// ВНИМАНИЕ!!!
// указанные типы используются также в конверторах
// xml2html.xsl и xml2mova.xsl
// поэтому внося изменения сюда необходимо изменять и там...
// (иначе они не будут понимать новые типы)
// ===================================================================

// перечень типов для частей речи
$types['_a.'] 	= "adjective"; // имя прилагательное";
$types['_adv.'] = "adverb"; // наречие";
$types['_n.']= "noun"; // существительное";
$types['_v.']= "verb"; // глагол";
$types['_mis.'] = "misuse"; // неправильное употребление";

// перечень типов для вариантов перевода
$variant_types['_полигр.']= "typography"; // типографское дело";
$variant_types['_нерек.']= "depricated"; // не рекомендовано для использования";
$variant_types['_жаргон.']= "jargon"; // жаргонное выражение, например 'железо'";
$variant_types['_интерф.']= "dialog"; // перевод в интерфейсах с пользователем";
$variant_types['_прогр.']= "program"; // при программировании";
$variant_types['_уст.']= "obsolete"; // устаревшее и в основном не использующееся в настоящее время";

// ===================================================================


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
			echo " type: $this->type[0]";
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

		if( !count($this->text) )
			return array("","");

		$text = htmlspecialchars($this->text);
		$type = "";

		if( count($this->type)<=1 )
			$type=$variant_types[$this->type[0]];
		else
		{
//			$type = $variant_types[$this->type[0]];
//			echo "\nПотеряли типы для слова: $text оставляем $type\n";

			$type = array();
			// преобразуем типы
			$cn = count($this->type);
			for($i=0; $i<$cn; $i++ )
			{
				$tt = $variant_types[$this->type[$i]];
				if( $tt )
					array_push($type,$tt);
				else	// добавляем как есть, если не нашли
					array_push($type, $this->type[$i]);
			}
		}
		return array($text,$type);
	}
}
// -------------------------------------------------------------------------------------------
class Meaning 
{
	var $variants = array(); // варианты
	var $links = array();
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

		reset($this->variants);
		while( list($k1,$v1) = each($this->variants) )
		{
			list($txt,$type) = $v1->varToXml();
			if( $txt )
			{
				if( !is_array($type) )
				{
					$tpl->assign( array(
						'TXT'=>$txt,
						'TYPE'=>$type
					));
					$tpl->parse('MAIN',".main");
				}
				else
				{
					// если вариант имеет несколько типов
					// то делаем каждый тип вариантом перевода!
					$cn = count($type);
					for($i=0; $i<$cn; $i++ )
					{
						$tpl->assign( array(
							'TXT'=>$txt,
							'TYPE'=>$type[$i]
						));
						$tpl->parse('MAIN',".main");
					}
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
		echo " type: ($this->type) "; echo $types[$this->type];
		while( list($k1,$v1) = each($this->meanings) )
		{
			echo "<p> смысл("; echo $k1+1; echo ")";
			$v1->myPrint();
		}
	}

	function toXML() 
	{
		$tpl = new FastTemplate("templates");
		$tpl->define(array(
			'main'=>'meaning.tpl'
		));
		
		while( list($k1,$v1) = each($this->meanings) )
		{
			$tpl->assign( array(
				'VARIANTS'=> $v1->varToXML(),
				'LINKS'=> $v1->linksToXML()
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
		echo "<P>'<u><i>$this->word</u></i>'</P>";

		while( list($k1,$v1) = each($this->parts) )
		{
			echo "<p>"; echo $k1+1; echo ")";
			$v1->myPrint();
		}
	}

	function toXML() 
	{
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
				$type = $v1->type;
			    if( is_array($v1->type) )
				{
					// здесь надобы разобрать типы
					// пока берем первый
					$type=$v1->type[0];
				}

				$tpl->assign( array(
					'NAME' => $types[$type],
					'MEANINGS' => $v1->toXML()
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
		$str=str_replace("\n\n","\n",$str);
		return str_replace("\n\n","\n",$str);
	}
}
// -------------------------------------------------------------------------------------------

function parse_variant_type(&$str, &$var)
{
	// шаблон: _type. _type2. _typeN. text

	// по идее определение типа должно идти впереди
	// поэтому как только определения кончились
	// прекращаем поиск
	$tmp = explode(". ",$str);
	reset($tmp);
	while( list($key, $val) = each($tmp) )
	{
		$val = trim($val);
		if(!$val)
			continue;

		if( eregi("(^_.*$)",$val) )
			array_push($var->type,"$val.");
		else
		{
			$var->text = $val;
			break;
		}
	}	
}
// -------------------------------------------------
function parse_variant(&$str, &$variant)
{
	global $variant_types;
	$str = trim($str);
	if( !eregi("(_.*\.)",$str) )
	{
		$variant->text = $str;
		return;
	}
	parse_variant_type(&$str,$variant);
}
// -------------------------------------------------
function parse_meaning_links(&$str, &$mm)
{
	$str = str_replace("См.","см.",$str);
	$links = explode("см.",$str);
	// т.к. ссылки идут после первого 'см.'
	// пропускаем первый элемент 
	each($links); 
	
	// далее разбираем
	while( list($key, $val) = each($links) )
	{
		$val = trim($val);
		if(!$val)
			continue;

		// разбираем каждый links на отдельные части
		// по признаку ','
		$tmp = explode(",",$val);
		while( list(,$lnk) = each($tmp) )
		{
			array_push(&$mm->links,trim($lnk));
		}

		// Удаляем link
		$str = str_replace($val,"",$str);
		$str = str_replace("см.","",$str);
	}
}
// -------------------------------------------------
function parse_meaning(&$str, &$mm)
{
	// Получаем определения
	$def = explode(";",$str);
	while( list($key, $val) = each($def) )
	{
		$val = trim($val);
		if(!$val)
			continue;
			
		$var = new Variant();
		// сперва проходим по link-ам
		// т.к. они удалятся
		parse_meaning_links($val,$mm);

		parse_variant($val,$var);
		array_push($mm->variants,$var);
	}
}
// -------------------------------------------------
function parse_parts(&$str, &$part)
{
	// разбиваем по смыслам
	$str = ereg_replace("[0-9]+>", "{}",$str); 
	$defs = explode("{}",$str);
	while( list($key, $val) = each($defs) )
	{
		$val = trim($val);
		if(!$val)
			continue;
			
		$meaning = new Meaning();
		parse_meaning($val,$meaning);
		array_push(&$part->meanings, $meaning);
	}
}
// -------------------------------------------------
function parse_article(&$str, &$doc)
{
	// разбиваем по частям речи
	$str = ereg_replace("[0-9]+\.","{}",$str); 
	$defs = explode("{}",$str);
	while( list($key, $val) = each($defs) )
	{
		$val = trim($val);
		if(!$val)
			continue;
		
		$part = new Part();

		// проверка определена ли часть речи
		// определение обязательно идёт в начале строки
//			if( eregi("(^_.*$)",$val) )
		if( eregi("(^_.*\.)",$val) )
		{
			list($type,$tmp)=parse_word($val," ");
			if( $tmp )
			{
				global $types;
				// проверка на существование части речи
				// если нет, значит это указана не часть речи
				// а тип варианта перевода и мы не должны 
				// вырезать его из строки
				if( isset($types[$type]) )
				{
					$part->type = $type;
					$val = $tmp;
				}
			}
		}
//		else
//			echo "<p>ТИП НЕ ОПРЕДЕЛЁН";

		parse_parts($val,$part);
		array_push(&$doc->parts,$part);
	}
}
// -------------------------------------------------
function parse_word(&$str, $sep="  ")
{
	// делит сроку по ПЕРВОМУ разделителю 
	$pos = strpos($str,$sep);
	if( !$pos )	
		return array(0,0);
	
	return array( substr($str,0,$pos), substr($str,$pos+1) );
}
// -------------------------------------------------
// -------------------------------------------------
// -------------------------------------------------
//	echo "<p align='center'>Разбираем файл <b>'$src'</b> </p>";
	$fp = fopen($src,"r");
	if( !$fp )
	{
		echo "\n<p> Не удалось открыть файл $src";
		exit;
	}

	$fout = fopen("$src.xml","w");
	if( !$fout )
	{
		echo "\n<p> Не удалось открыть файл '$src.xml' для записи";
		exit;
	}

	fwrite($fout,"<?xml version=\"1.0\" encoding=\"koi8-r\" ?>\n");
	fwrite($fout,"<dictionary name=\"$dictname\" >\n");

//	echo "<p align='center'> =====================================";
	while( $str=fgets($fp) )
	{
		$str = trim($str);
		if(!$str)
			continue;
			
//		echo "<p align='center'> ---------------------";
//		echo "<p><b>разбираем</b>: $str";
		list($word,$article) = parse_word($str);
		if(!$word)
		{
			echo "\n<p> Не удалось выделить СЛОВО из -> '$str'";
			continue;
		}
		else
//			echo "<p>'<u><i>$word</u></i>'<p>";

		$doc = new Doc($word);		
		parse_article($article, $doc);
//		$doc->myPrint();
		fwrite($fout,"\n"); 
		fwrite($fout, $doc->toXML($tpl)); 
	}
	fwrite($fout,"\n</dictionary>\n");

	fclose($fp);
	fclose($fout);
// -------------------------------------------------
?>
