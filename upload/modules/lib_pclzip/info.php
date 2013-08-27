<?php

/**
 * This file is part of an ADDON for use with Black Cat CMS Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          pclzip
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 * @version         $Id: info.php 1333 2011-11-08 13:46:34Z erpe $
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



$module_directory    = 'lib_pclzip';
$module_name         = 'pclzip';
$module_function     = 'snippet';
$module_integration	 = 'passive';
$module_version      = '1.0';
$module_platform     = '1.0.x';
$module_requirements = 'PHP 4.3.11 or higher';
$module_author 			 = 'Vincent Blavet';
$module_home				 = 'http://www.phpconcept.net';
$module_license 		 = 'GNU General Public License';
$module_description  = 'ZIP library forLEPTON';
$module_guid         = '0C3AD4DD-9387-40DC-94E6-DC09598C3AAB';

?>