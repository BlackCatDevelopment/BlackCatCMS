<?php

/**
 *
 *   @module          initial_page
 *   @author          Ralf Hertsch, Dietrich Roland Pehlke, LEPTON v2.0 Black Cat Edition Development
 *   @copyright       2010-2011, Ralf Hertsch, Dietrich Roland Pehlke
 *   @copyright       2013, LEPTON v2.0 Black Cat Edition Development
 *   @link            http://www.LEPTON-cms.org
 *   @link            http://www.lepton2.org
 *   @license         copyright, all rights reserved
 *   @license_terms   please see info.php of this module
 *   @category        LEPTON2BCE_Modules
 *   @package         initial_page
 *
 */
 
 // include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
} else {
	$oneback = "../";
	$root = $oneback;
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= $oneback;
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php



$MOD_INITIAL_PAGE = array(
	'label_user'	=> "Benutzer",
	'label_page'	=> "Seite",
	'label_default'	=> "Standard Startseite",
	'label_param'	=> "Optionale Parameter",
	'head_select'	=> "Bitte eine Standard Startseite f&uuml;r den Benutzer ausw&auml;hlen"
);

?>