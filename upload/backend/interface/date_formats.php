<?php

/**
 * This file is part of LEPTON2 Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author			LEPTON2 Project
 * @copyright		2012, LEPTON2 Project
 * @link			http://lepton2.org
 * @license			http://www.gnu.org/licenses/gpl.html
 * @license_terms	please see LICENSE and COPYING files in your package
 *
 *
 */
 
// include class.secure.php to protect this file and the whole CMS!
if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
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
if(!defined('DATE_FORMATS_LOADED')) {
	define('DATE_FORMATS_LOADED', true);
}

// Create array
$DATE_FORMATS = array();

// Get the current time (in the users timezone if required)
$actual_time = time();

// Add values to list
$DATE_FORMATS['l,|jS|F,|Y'] = date('l, jS F, Y', $actual_time);
$DATE_FORMATS['jS|F,|Y'] = date('jS F, Y', $actual_time);
$DATE_FORMATS['d|M|Y'] = date('d M Y', $actual_time);
$DATE_FORMATS['M|d|Y'] = date('M d Y', $actual_time);
$DATE_FORMATS['D|M|d,|Y'] = date('D M d, Y', $actual_time);
$DATE_FORMATS['d-m-Y'] = date('d-m-Y', $actual_time).' (D-M-Y)';
$DATE_FORMATS['m-d-Y'] = date('m-d-Y', $actual_time).' (M-D-Y)';
$DATE_FORMATS['d.m.Y'] = date('d.m.Y', $actual_time).' (D.M.Y)';
$DATE_FORMATS['m.d.Y'] = date('m.d.Y', $actual_time).' (M.D.Y)';
$DATE_FORMATS['d/m/Y'] = date('d/m/Y', $actual_time).' (D/M/Y)';
$DATE_FORMATS['m/d/Y'] = date('m/d/Y', $actual_time).' (M/D/Y)';
$DATE_FORMATS['j.n.Y'] = date('j.n.Y', $actual_time).' (j.n.Y)';

// Add "System Default" to list (if we need to)
if(isset($user_time) && $user_time == true)
{
	if(isset($TEXT['SYSTEM_DEFAULT']))
	{
		$DATE_FORMATS['system_default'] = date(DEFAULT_DATE_FORMAT, $actual_time).' ('.$TEXT['SYSTEM_DEFAULT'].')';
	} else {
		$DATE_FORMATS['system_default'] = date(DEFAULT_DATE_FORMAT, $actual_time).' (System Default)';
	}
}

// Reverse array so "System Default" is at the top
$DATE_FORMATS = array_reverse($DATE_FORMATS, true);

?>