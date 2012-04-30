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
 * @version         $Id$
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

if (!function_exists('w_debug')) {
function w_debug($s) {
	$fp = fopen(WB_PATH.'/_debug_.txt', 'a');
	fwrite($fp, "$s\r\n");
	fclose($fp);
}
}

//echo " TZ called ";
$timezone_table = array(
	"Pacific/Kwajalein",
	"Pacific/Samoa",
	"Pacific/Honolulu",
	"America/Anchorage",
	"America/Los_Angeles",
	"America/Phoenix",
	"America/Mexico_City",
	"America/Lima",
	"America/Caracas",
	"America/Halifax",
	"America/Buenos_Aires",
	"Atlantic/Reykjavik",
	"Atlantic/Azores",
	"Europe/London",
	"Europe/Berlin",
	"Europe/Kaliningrad",
	"Europe/Moscow",
	"Asia/Tehran",
	"Asia/Baku",
	"Asia/Kabul",
	"Asia/Tashkent",
	"Asia/Calcutta",
	"Asia/Colombo",
	"Asia/Bangkok",
	"Asia/Hong_Kong",
	"Asia/Tokyo",
	"Australia/Adelaide",
	"Pacific/Guam",
	"Etc/GMT+10",
	"Pacific/Fiji"
);

if (!defined("DEFAULT_TIMEZONESTRING")) define("DEFAULT_TIMEZONESTRING", "Europe/Berlin");

?>