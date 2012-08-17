<?php

/**
 *
 * @module          initial_page
 * @author          Ralf Hertsch, Dietrich Roland Pehlke 
 * @copyright       2010-2011, Ralf Hertsch, Dietrich Roland Pehlke
 * @link            http://www.LEPTON-cms.org
 * @license         copyright, all rights reserved
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



$module_directory	= 'initial_page';
$module_name		= 'Initial Page';
$module_function	= 'tool';
$module_version		= '0.1.3';
$module_platform	= '1.0';
$module_author		= 'Ralf Hertsch, Dietrich Roland Pehlke';
$module_license		= 'copyright, all rights reserved';
$module_license_terms	= 'usage only with written permission, use with LEPTON core is allowed';
$module_description	= 'This module allows to set up an initial_page in the backend for each user';
$module_guid		= "237D63F7-4199-48C7-89B2-DF8E2D8AEE5F";

/**
 *
 *	0.1.3	2011-02-09	- Bugfix inside c_init_page for default param 3 in method update_user.
 *						- Add german language file within some translations.
 *
 */

?>