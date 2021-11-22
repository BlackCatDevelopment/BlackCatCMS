<?php

/**
 * This file is part of an ADDON for use with Black Cat CMS Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          show_menu2
 * @author          Brofield, LEPTON Project, Black Cat Development
 * @copyright       2006-2010 Brofield
 * @copyright       2010-2012, LEPTON Project
 * @copyright       2012-2014, Black Cat Development
 * @link            http://code.jellycan.com/files/show_menu2-README.txt
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'framework/class.secure.php')) { 
		include($root.'framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php

 


$module_directory = 'show_menu2';
$module_name = 'show_menu2';
$module_function = 'snippet';
$module_version = '4.9.7';
$module_platform = '2.x';
$module_author = 'Brodie Thiesfield, Aldus, erpe, Black Cat Development';
$module_license = 'GNU General Public License';
$module_license_terms = '-';
$module_description = 'A code snippet providing a complete replacement for the built-in menu functions. See <a href="http://code.jellycan.com/files/show_menu2-README.txt" target="_blank">readme</a> for details.';
$module_guid      = 'b859d102-881d-4259-b91d-b5a1b57ab100';

/**
 *  4.9.7   - replaced deprecated /e modifier in preg_replace()
 *
 *	4.9.6	- removed deprecated code for old menus in LEPTON 2.x
 *
 *	4.9.5	- removed unused fields menu_icon and page_icon
 * 
 *	4.9.4	- Bugfix inside include.php - method 'ifTest'. Add missing breaks inside the switch.
 *			- Add modulo operator to the conditions.
 *			- CodeChanges inside include.php - method 'replace' - change case(-bodys) for 'ac' and 'a'.
 *
 *
 */
?>