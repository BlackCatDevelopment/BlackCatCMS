<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */
 
// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php

// Define that this file is loaded
if(!defined('ERROR_REPORTING_LEVELS_LOADED')) {
	define('ERROR_REPORTING_LEVELS_LOADED', true);
}

// Create array
$ER_LEVELS = array();

// Add values to list
if(isset($TEXT['SYSTEM_DEFAULT'])) {
	$ER_LEVELS[''] = $TEXT['SYSTEM_DEFAULT'];
} else {
	$ER_LEVELS[''] = 'System Default';
}
$ER_LEVELS['6135'] = 'E_ALL^E_NOTICE'; // standard: E_ALL without E_NOTICE
$ER_LEVELS['0'] = 'E_NONE';
$ER_LEVELS['6143'] = 'E_ALL';
$ER_LEVELS['8191'] = htmlentities('E_ALL&E_STRICT'); // for programmers

?>