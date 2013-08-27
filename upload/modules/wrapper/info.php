<?php

/**
 * This file is part of an ADDON for use with Black Cat CMS Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          wrapper
 * @author          WebsiteBaker Project
 * @author          LEPTON Project
 * @copyright       2004-2010, WebsiteBaker Project
 * @copyright       2010-2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
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

$module_directory = 'wrapper';
$module_name = 'Wrapper';
$module_function = 'page';
$module_version = '2.7.3';
$module_platform = '2.x';
$module_author = 'Ryan Djurovich, Dietrich Roland Pehlke (last)';
$module_license = 'GNU General Public License';
$module_description = 'This module allows you to wrap your site around another using an inline frame';
$module_guid      = 'a5830654-06f3-402a-9d25-a03c53fc5574';

/**
 *	2.7.3	2012-02-09	- added upgrade.php.
 * 
 *	2.7.1	2010-11-02	- Bugfix inside the html-template to get valid output.
 *						  (Remove missplaced '</form>' closing tag)
 *						- Move the html-template inside the "htt" folder.
 *						- Remove WB 2.7 support.
 *
 */
?>