<?php

/**
 * This file is part of an ADDON for use with Black Cat CMS Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          class.Images.php
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 *
 *
 */

// try to include LEPTON class.secure.php to protect this file and the whole CMS!
if (defined('CAT_PATH')) {	
	if (defined('LEPTON_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php'); 
} else {
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) { 
			include($dir.'/framework/class.secure.php'); $inc = true;	break; 
		} 
	}
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include LEPTON class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}
// end include LEPTON class.secure.php

$module_directory     = 'lib_images';
$module_name          = 'class.Images.php for LEPTON';
$module_function      = 'library';
$module_version       = '0.2';
$module_platform      = '2.x';
$module_author        = 'class.Images.php - Manuel Reinhard, this module - Bianka Martinovic';
$module_license       = 'GNU General Public License';
$module_description   = 'class.Images.php - A class to handle and manipulate images';
$module_home          = 'http://www.sprain.ch/';
$module_guid          = 'C6A310F1-A270-4079-9EF3-977AA7EF380F';

?>
