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

if(defined('CAT_URL')) {
	
	// Create table
	// $database->query("DROP TABLE IF EXISTS `".CAT_TABLE_PREFIX."mod_wrapper`");
	$mod_wrapper = 'CREATE TABLE IF NOT EXISTS `'.CAT_TABLE_PREFIX.'mod_wrapper` ('
		. ' `section_id` INT NOT NULL DEFAULT \'0\','
		. ' `page_id` INT NOT NULL DEFAULT \'0\','
		. ' `url` TEXT NOT NULL,'
		. ' `height` INT NOT NULL DEFAULT \'0\','
		. ' `width` INT NOT NULL DEFAULT \'0\','
		. ' `wtype` VARCHAR(50) NOT NULL DEFAULT \'0\','
		. ' PRIMARY KEY ( `section_id` ) '
		. ' )';
	$database->query($mod_wrapper);
}

// add files to class_secure
$addons_helper = new CAT_Helper_Addons();
foreach(
    array( 'save.php' )
    as $file
) {
    if ( false === $addons_helper->sec_register_file( 'wrapper', $file ) )
    {
         error_log( "Unable to register file -$file-!" );
    }
}

?>