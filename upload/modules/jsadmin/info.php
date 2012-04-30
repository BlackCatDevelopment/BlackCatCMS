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
 * @version         $Id$
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

 
$module_directory = 'jsadmin';
$module_name = 'Javascript Admin (deprecated!)';
$module_function = 'tool';
$module_version = '1.4.0';
$module_platform = '2.x';
$module_author = 'Stepan Riha, Swen Uth';
$module_license	= 'BSD License';
$module_description = 'This module adds Javascript functionality to LEPTON Admin to improve some of the UI interactions using YahooUI library.<br />'
					. 'This module is marked deprecated in LEPTON 2.0';
$module_guid      = '463c1963-82f4-4fa6-a432-8c45d8c33249';

?>