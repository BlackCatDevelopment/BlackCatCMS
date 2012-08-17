<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */
 
// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
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

// Get id
if(isset($_GET['page_id']) AND is_numeric($_GET['page_id'])) {
	if(isset($_GET['section_id']) AND is_numeric($_GET['section_id'])) {
		$page_id = $_GET['page_id'];
		$id = $_GET['section_id'];
		$id_field = 'section_id';
		$common_field = 'page_id';
		$table = TABLE_PREFIX.'sections';
	} else {
		$id = $_GET['page_id'];
		$id_field = 'page_id';
		$common_field = 'parent';
		$table = TABLE_PREFIX.'pages';
	}
} else {
	header("Location: index.php");
	exit(0);
}

// Create new admin object and print admin header
require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages_settings');

// Include the ordering class
require(WB_PATH.'/framework/class.order.php');

// Create new order object an reorder
$order = new order($table, 'position', $id_field, $common_field);
if($id_field == 'page_id') {
	if($order->move_up($id)) {
		$admin->print_success($MESSAGE['PAGES_REORDERED']);
	} else {
		$admin->print_error($MESSAGE['PAGES_CANNOT_REORDER']);
	}
} else {
	if($order->move_up($id)) {
		$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/sections.php?page_id='.$page_id);
	} else {
		$admin->print_error($TEXT['ERROR'], ADMIN_URL.'/pages/sections.php?page_id='.$page_id);
	}
}

// Print admin footer
$admin->print_footer();

?>