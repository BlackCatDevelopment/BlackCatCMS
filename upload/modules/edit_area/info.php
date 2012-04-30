<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @author		Christophe Dolivet (EditArea)
 * @author		Christian Sommer (WB wrapper)
 * @author		LEPTON Project
 * @copyright	2009-2010, Website Baker Project 
 * @copyright       2010-2011, LEPTON Project
 * @link			http://www.LEPTON-cms.org
 * @license		http://www.gnu.org/licenses/gpl.html
 * @license_terms	please see info.php of this module
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



/**
 *	1.8.3	2011-01-28
 *			- Bugfix inside edit_area_full.js for Safari.
 *
 *
 */
$module_directory = 'edit_area';
$module_name = 'Editarea';
$module_function = 'WYSIWYG';
$module_version = '1.8.3';
$module_platform = '1.x';
$module_author = 'Christophe Dolivet, Christian Sommer';
$module_license = 'GNU General Public License';
$module_description = 'Small and easy code editor';
$module_home = 'http://www.cdolivet.com';
$module_guid = '7E293596-59AC-4010-8351-5836313DE387';

?>