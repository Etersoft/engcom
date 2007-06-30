<?php
// $Id: pageNavigation.php,v 1.2 2007/06/30 22:02:49 pv Exp $
/**
* Page Navigation code
* @package Mambo Open Source
* @Copyright (C) 2000 - 2003 Miro International Pty Ltd
* @ All rights reserved
* @ Mambo Open Source is Free Software
* @ Released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
* @version $Revision: 1.2 $
* ------------------------------------------------------------------------
* modify by Pavel Vainerman pv@etersoft.ru
* ------------------------------------------------------------------------
**/

defined( '_ACCESS' ) or die( 'Direct Access to this location is not allowed.' );

/**
* Page Navigation support
* @package MOS
* @copyright 2000-2003 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html. GNU Public License
* @version $Revision: 1.2 $
*/

/**
* Page navigation support class
*/
class mosPageNav {
/** @var int The record number to start dislpaying from */
	var $limitstart = null;
/** @var int Number of rows to display per page */
	var $limit = null;
/** @var int Total number of rows */
	var $total = null;

	function mosPageNav( $total, $limitstart, $limit ) {
		$this->total = intval( $total );
		$this->limitstart = max( $limitstart, 0 );
		$this->limit = max( $limit, 0 );
	}

/**
* Writes the html limit # input box
* @param string The basic link to include in the href
*/
	function writeLimitBox ( $link ) {
		$limits = array();
		for ($i=5; $i <= 30; $i+=5) {
			$limits[] = mosHTML::makeOption( "$i" );
		}
		$limits[] = mosHTML::makeOption( "50" );
		$limits[] = mosHTML::makeOption( "60" );
		$limits[] = mosHTML::makeOption( "70" );
		$limits[] = mosHTML::makeOption( "100" );

	// build the html select list
//		$link = sefRelToAbs($link.'&amp;limit=\' + this.options[selectedIndex].value + \'&amp;limitstart='.$this->limitstart);
		$link = $link.'&amp;limit=\' + this.options[selectedIndex].value + \'&amp;limitstart='.$this->limitstart;
		echo mosHTML::selectList( $limits, 'limit',
		    'class="inputbox" size="1" onchange="document.location.href=\''.$link.'\';"',
			'value', 'text', $this->limit );
	}

/**
* Writes the html for the pages counter, eg, Results 1-10 of x
*/
	function writePagesCounter() {
		$txt = '';
		$from_result = $this->limitstart+1;
		if ($this->limitstart + $this->limit < $this->total) {
			$to_result = $this->limitstart + $this->limit;
		} else {
			$to_result = $this->total;
		}
		if ($this->total > 0) {
			$txt .= _PN_RESULTS." $from_result - $to_result "._PN_OF." $this->total";
		}
		return $txt;
	}

/**
* Writes the html for the leafs counter, eg, Page 1 of x
*/
	function writeLeafsCounter() {
		$txt = '';
		$page = $this->limitstart+1;
		if ($this->total > 0) {
			$txt .= _PN_PAGE." $page "._PN_OF." $this->total";
		}
		return $txt;
	}

/**
* Writes the html links for pages, eg, previous, next, 1 2 3 ... x
* @param string The basic link to include in the href
*/
	function writePagesLinks( $link ) {
		$txt = '';

		$displayed_pages = 10;
		$total_pages = ceil( $this->total / $this->limit );
		$this_page = ceil( ($this->limitstart+1) / $this->limit );
		$start_loop = (floor(($this_page-1)/$displayed_pages))*$displayed_pages+1;
		if ($start_loop + $displayed_pages - 1 < $total_pages) {
			$stop_loop = $start_loop + $displayed_pages - 1;
		} else {
			$stop_loop = $total_pages;
		}

		$link .= "&amp;limit=$this->limit";

		if ($this_page > 1) {
			$page = ($this_page - 2) * $this->limit;
			$txt .= "\n<a href=\"$link&amp;limitstart=0\" class=\"pagenav\" title=\"first page\"><< "._PN_START."</a>";
			$txt .= "\n<a href=\"$link&amp;limitstart=$page\" class=\"pagenav\" title=\"previous page\">< "._PN_PREVIOUS."</a>";
		} else {
			$txt .= "\n<span class=\"pagenav\"><< "._PN_START."</span>";
			$txt .= "\n<span class=\"pagenav\">< "._PN_PREVIOUS."</span>";
		}

		for ($i=$start_loop; $i <= $stop_loop; $i++) {
			$page = ($i - 1) * $this->limit;
			if ($i == $this_page) {
				$txt .= "\n<span class=\"pagenav\"> $i </span>";
			} else {
				$txt .= "\n<a href=\"$link&amp;limitstart=$page\" class=\"pagenav\"><strong>$i</strong></a>";
			}
		}

		if ($this_page < $total_pages) {
			$page = $this_page * $this->limit;
			$end_page = ($total_pages-1) * $this->limit;
			$txt .= "\n<a href=\"$link&amp;limitstart=$page\" class=\"pagenav\" title=\"next page\"> "._PN_NEXT." ></a>";
			$txt .= "\n<a href=\"$link&amp;limitstart=$end_page\" class=\"pagenav\" title=\"end page\"> "._PN_END." >></a>";
		} else {
			$txt .= "\n<span class=\"pagenav\">"._PN_NEXT." ></span>";
			$txt .= "\n<span class=\"pagenav\">"._PN_END." >></span>";
		}
		return $txt;
	}
}

?>