#!/usr/bin/php 
<?php
require_once("fast_template.class.php");

// перечень управляющих символов
$types['_a.'] 	= "adjective"; // имя прилагательное";
$types['_adv.'] = "adverb"; // наречие";
$types['_n.']= "noun"; // существительное";
$types['_v.']= "verb"; // глагол";
$types['_mis.'] = "misuse"; // неправильное употребление";


$ext_types['_полигр.']= "typography"; // типографское дело";
$ext_types['_нерек.']= "depricated"; // не рекомендовано для использования";
$ext_types['_жаргон.']= "jargon"; // жаргонное выражение, например 'железо'";
$ext_types['_интерф.']= "dialog"; // перевод в интерфейсах с пользователем";
$ext_types['_прогр.']= "program"; // при программировании";
$ext_types['_уст.']= "obsolete"; // устаревшее и в основном не использующееся в настоящее время";
//$ext_types['_разг.']= "разговорное, не рекомендуемое для использования в письменном виде; жаргонное";

//$link_types['см'] = "см.";
	
$src="EngCom.source";
$dictname = "EngCom";

// -------------------------------------------------
class Variant 
{
	var $text = ""; // вариант
	var $extypes = array(); 	// key из массива спец. символов
	function Variant() 
	{
	}

	function myPrint() 
	{
		echo "$this->text ";
		if( count($this->extypes) )
		{
			echo " ext: ";
			reset($this->extypes);
			while( list($k1,$v1) = each($this->extypes) )
			{
				echo " $v1 ";
			}
		}
	}

	function varToXML() 
	{
		if( !count($this->text) )
			return array("","");

		$type = "";
		if( count($this->extypes) )
		{
			global $ext_types;
			// Пока выдаем только первый тип
			$type=$ext_types[$this->extypes[0]];
			// А в дальнейшем надо договорится 
			// надо разбирать
			// while( list($k1,$v1) = each($this->extypes) )
		}

	
		// 
		$text = htmlspecialchars($this->text);
		return array($text,$type);
	}
}

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
				$tpl->assign( array(
					'TXT'=>$txt,
					'TYPE'=>$type
				));

				$tpl->parse('MAIN',".main");
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
		return "\n".$tpl->fetch('MAIN');
	}
}

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
		return $tpl->fetch('MAIN');
	}
}

// -------------------------------------------------
function parse_word(&$str, $sep="  ")
{
	$pos = strpos($str,$sep);
	if( !$pos )	
		return array(0,0);
	
	return array( substr($str,0,$pos), substr($str,$pos+1) );
}
// -------------------------------------------------

function parse_variant_type(&$str, &$var)
{
	// шаблон: _type. _type2. _typeN. text

	// по идее определение типа должно идти впереди
	// поэтому как только определения кончились
	// прекращаем поиск
	$tmp = explode(".",$str);
	reset($tmp);
	while( list($key, $val) = each($tmp) )
	{
		$val = trim($val);
		if(!$val)
			continue;

		if( eregi("(^_.*$)",$val) )
			array_push($var->extypes,"$val.");
		else if( $val )
		{
			$var->text = $val;
			break;
		}
	}	
}
// -------------------------------------------------
function parse_variant(&$str, &$variant)
{
	global $ext_types;
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
	each($links); // пропускаем первый элемент
	while( list($key, $val) = each($links) )
	{
		$val = trim($val);
		if(!$val)
			continue;

		array_push(&$mm->links,$val);
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
	$str = ereg_replace("([0-9]+>)", "{}",$str); 
	$defs = explode("{}",$str);
	while( list($key, $val) = each($defs) )
	{
		$val = trim($val);
		if(!$val)
			continue;
			
		$meaning = new Meaning();
//		echo "<p>&nbsp;&nbsp;&nbsp;$key) $val";
		parse_meaning($val,$meaning);
		array_push(&$part->meanings, $meaning);
	}
}
// -------------------------------------------------
function parse_article(&$str, &$doc)
{
	// разбиваем по частям речи
	$str = ereg_replace("([0-9]+\.)","{}",$str); 
	$defs = explode("{}",$str);
	while( list($key, $val) = each($defs) )
	{
		$val = trim($val);
		if(!$val)
			continue;

		$part = new Part();
		// проверка определён ли тип
		if( eregi("(_*+\.)",$val) )
		{
			list($type,$val)=parse_word($val," ");
			if($val)
				$part->type = $type;
		}
//		else
//			echo "<p>ТИП НЕ ОПРЕДЕЛЁН";

		parse_parts($val,$part);
		array_push(&$doc->parts,$part);
	}
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
