<?php
// --------------------------------------------------------------------------
// $Id: dictionary.class.php,v 1.2 2007/06/30 22:02:48 pv Exp $
// --------------------------------------------------------------------------
defined( '_ACCESS' ) or die( 'Direct Access to this location is not allowed.' );

class mosDictionary extends mosDBTable {
	var $word 		= "";	// varchar(50) NOT NULL default ''
	var $article	= "";	// text NOT NULL default ''

	function mosDictionary( &$_db ) {
		$this->mosDBTable( '#__dictionary', 'word', $_db );
	}
	
	function insert() {
		$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
		if( !$ret ) {
			$this->_error = get_class( $this )."::store failed <br />" . $this->_db->getErrorMsg();
			return false;
		} else {
			return true;
		}
	}
}

?>

