<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
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



require_once('../../config.php');

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(WB_PATH.'/modules/admin.php');

// Update id, anchor and target
if(isset($_POST['menu_link'])) {
	$foreign_page_id = $admin->add_slashes($_POST['menu_link']);
	$page_target = $admin->add_slashes($_POST['page_target']);
	$url_target = $admin->add_slashes($_POST['target']);
	$r_type = $admin->add_slashes($_POST['r_type']);
	if(isset($_POST['extern']))
		$extern = $admin->add_slashes($_POST['extern']);
	else
		$extern='';

	$table_pages = TABLE_PREFIX.'pages';
	$table_mod = TABLE_PREFIX.'mod_menu_link';
	$database->query("UPDATE `$table_pages` SET `target` = '$url_target' WHERE `page_id` = '$page_id'");
	$database->query("UPDATE `$table_mod` SET `target_page_id` = '$foreign_page_id', `anchor` = '$page_target', `extern` = '$extern', `redirect_type` = '$r_type' WHERE `page_id` = '$page_id'");
}

// Check if there is a database error, otherwise say successful
if($database->is_error()) {
	$admin->print_error($database->get_error(), $js_back);
} else {
	$admin->print_success($MESSAGE['PAGES']['SAVED'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();

?>