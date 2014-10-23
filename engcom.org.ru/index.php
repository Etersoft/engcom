<?php
/*
 * --------------------------------------------------------------------------
 * Copyright (c) 2004 Free Software Foundation, Inc. Licensed under GPL v.2.
 * Copyright (c) 2004 Pavel Vainerman
 * Author: Pavel Vainerman <pv@etersoft.ru>
 * $Id: index.php,v 1.3 2007/06/30 22:15:20 pv Exp $
 * --------------------------------------------------------------------------
*/
	define( '_ACCESS', 1 );
	include_once('globals.php');
	require_once('includes/miniMOS.php');
	require_once('configuration.php');
	require_once('dict_info.php');
	require_once('includes/database.php');
	require_once('includes/pageNavigation.php');
	require_once('includes/dictionary.html.php');
	require_once('includes/dictionary.class.php');
	require_once('includes/dictionary.php');
	require_once('language/russian.php');
//	require_once('includes/phpmailer/class.phpmailer.php');
	require_once('includes/phpmailer/mmail.php');
	
	$database 	= new database( $dbhost, $dbuser, $dbpass, $dbname, $dbprefix, $dbcharset );
	$task		= mosGetParam( $_REQUEST, 'task', '' );
	$act		= mosGetParam( $_REQUEST, 'act', '' );
	$word 		= mosGetParam( $_REQUEST, 'word', '' );

	if( $word && $task=='' )
		$task = 'search';
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title><?php echo $conf_sitename; ?></title>
<link rel="stylesheet" href="css/main.css" type="text/css" />
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $config_charset; ?>">
</head>
<body>
<?php
	switch($task)
	{
		case 'glossary':
			$letter = mosGetParam($_REQUEST, 'letter','');
			glossary($letter);
		break;
		
		case 'search':
			$method = mosGetParam($_REQUEST, 'search_method', 0);
			search($word,$method);
		break;

		case 'addnew':
			addnew($word);
		break;
		
		case 'edit':
			edit($word);
		break;

		case 'save':
			save($word);
		break;
	
		default:
			HTML_dictionary::viewSearchForm();
			echo "<p>"; echo HTML_dictionary::showABCLine('');
		break;
	}
?>
</body>
<script type="text/javascript">
document.getElementById('id_word').focus();
document.getElementById('id_word').select();
</script>
</html>
