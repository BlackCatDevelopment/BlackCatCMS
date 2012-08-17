<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the BSD License.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          jsadmin 
 * @author          WebsiteBaker Project
 * @author          LEPTON Project
 * @copyright       2004-2010, Ryan Djurovich,WebsiteBaker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         BSD License
 * @license_terms   please see info.php of this module
 *
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

  

// Headings and text outputs
$MOD_JSADMIN['TXT_HEADING_B'] 				= 'Please choose the Javascript functions you want to enable';
$MOD_JSADMIN['TXT_PERSIST_ORDER_B'] 		= 'Remember expanded pages';
$MOD_JSADMIN['TXT_AJAX_ORDER_PAGES_B'] 	= 'Reorder pages by the use of drag-and-drop';
$MOD_JSADMIN['TXT_AJAX_ORDER_SECTIONS_B'] = 'Reorder sections by the use of drag-and-drop';
$MOD_JSADMIN['TXT_ERROR_INSTALLINFO_B'] 	= '<h1>Error</h1><p>JavaScript Admin requires the YUI (Yahoo User Interface) framework.<br />The following files are required to get Javascript Admin work as expected:<br /><br />';

?>