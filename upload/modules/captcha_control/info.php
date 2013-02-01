<?php

/**
 * This file is part of an ADDON for use with Black Cat CMS Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 *
 * @author          Thomas Hornik (thorn),LEPTON Project
 * @copyright       2008-2012, Thomas Hornik (thorn),LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 *
 *
 */
 
 
/*
 -----------------------------------------------------------------------------------------
  This is a control-tool for captcha and ASP
 -----------------------------------------------------------------------------------------
V1.0 - inital version

*/
// include class.secure.php to protect this file and the whole CMS!
if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
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




$module_directory 	= 'captcha_control';
$module_name 		  	= 'Captcha and Advanced-Spam-Protection (ASP) Control';
$module_function 		= 'tool';
$module_version 		= '1.01';
$module_platform 		= '1.x';
$module_author 	  	= 'Thomas Hornik (thorn),LEPTON Project';
$module_license 		= 'GNU General Public License';
$module_license_terms = 'GNU General Public License';
$module_description 	= 'Admin-Tool to control CAPTCHA and ASP';
$module_guid     		= 'c29c5f1a-a72a-4137-b5cd-62982809bd38';
?>