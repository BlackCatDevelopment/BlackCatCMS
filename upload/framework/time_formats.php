<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */
 
// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {	
	include(LEPTON_PATH.'/framework/class.secure.php'); 
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
if(!defined('TIME_FORMATS_LOADED')) {
	define('TIME_FORMATS_LOADED', true);
}

// Create array
$TIME_FORMATS = array();

// Get the current time (in the users timezone if required)
$actual_time = time();

// Add values to list
$TIME_FORMATS['g:i|A'] = date('g:i A', $actual_time);
$TIME_FORMATS['g:i|a'] = date('g:i a', $actual_time);
$TIME_FORMATS['H:i:s'] = date('H:i:s', $actual_time);
$TIME_FORMATS['H:i'] = date('H:i', $actual_time);

// Add "System Default" to list (if we need to)
if(isset($user_time) AND $user_time == true) {
	if(isset($TEXT['SYSTEM_DEFAULT'])) {
		$TIME_FORMATS['system_default'] = date(DEFAULT_TIME_FORMAT, $actual_time).' ('.$TEXT['SYSTEM_DEFAULT'].')';
	} else {
		$TIME_FORMATS['system_default'] = date(DEFAULT_TIME_FORMAT, $actual_time).' (System Default)';
	}
}

// Reverse array so "System Default" is at the top
$TIME_FORMATS = array_reverse($TIME_FORMATS, true);

?>