<?php

/**
 * This file is part of an ADDON for use with Black Cat CMS Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          menu-link
 * @author          WebsiteBaker Project, LEPTON Project
 * @copyright       2004-2010, WebsiteBaker Project
 * @copyright       2010-2011, LEPTON Project 
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



/*
Since there is nothing to show and users shouldn't really know this
page exists, we might as well give them a link to the home page.
*/

// check if module language file exists for the language set by the user (e.g. DE, EN)
if(!file_exists(CAT_PATH .'/modules/menu_link/languages/'.LANGUAGE .'.php')) {
	// no module language file exists for the language set by the user, include default module language file EN.php
	require_once(CAT_PATH .'/modules/menu_link/languages/EN.php');
} else {
	// a module language file exists for the language defined by the user, load it
	require_once(CAT_PATH .'/modules/menu_link/languages/'.LANGUAGE .'.php');
}

?>
<a href="<?php echo CAT_URL; ?>">
<?php echo $MOD_MENU_LINK['TEXT']; ?>
</a>
