<?php

/**
 *  @template       Algos Backend-Theme
 *  @version        see info.php of this template
 *  @author         Jurgen Nijhuis, Dietrich Roland Pehlke
 *  @copyright      2009-2012 Jurgen Nijhuis, Dietrich Roland Pehlke
 *  @license        GNU General Public License
 *  @license terms  see info.php of this template
 *  @platform       LEPTON, see info.php of this template
 *  @requirements   PHP 5.2.x and higher
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


// OBLIGATORY VARIABLES
$template_directory			= 'algos';
$template_name				= 'Algos Theme';
$template_function			= 'theme';
$template_version			= '1.2.2';
$template_platform			= '1.0.x';
$template_author			= 'Jurgen Nijhuis, Dietrich Roland Pehlke (last)';
$template_license			= '<a href="http://www.gnu.org/licenses/gpl.html">GNU General Public License</a>';
$template_license_terms		= '-';
$template_description		= 'default backend theme for LEPTON CMS';
$template_guid				= 'AD6296ED-31BD-49EB-AE23-4DD76B7ED778';
?>