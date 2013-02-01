<?php

/**
 *  @author         Ryan Djurovich
 *  @author         Rudolph Lartey
 *  @author         John Maats
 *  @author         Dietrich Roland Pehlke
 *  @copyright      2004-2011 Ryan Djurovich, Rudolph Lartey, John Maats, Dietrich Roland Pehlke
 *  @license        see info.php of this module
 *  @todo           separate HTML from code, in addition the used HTML is no longer 
 *                  valid and uses deprecated attributes i.e. cellpadding a.s.o.
 *  @version        $Id$
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

// Get id
if(!isset($_GET['field_id']) OR !is_numeric($_GET['field_id'])) {
	header("Location: index.php");
	exit(0);
} else {
	$field_id = $_GET['field_id'];
}

// Include WB admin wrapper script
require(CAT_PATH.'/modules/admin.php');

// Include the ordering class
require(CAT_PATH.'/framework/class.order.php');

global $admin;
global $TEXT;
global $page_id;

// Create new order object an reorder
$order = new order(CAT_TABLE_PREFIX.'mod_form_fields', 'position', 'field_id', 'section_id');
if($order->move_up($field_id)) {
	$admin->print_success($TEXT['SUCCESS'], CAT_ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
} else {
	$admin->print_error($TEXT['ERROR'], CAT_ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();
