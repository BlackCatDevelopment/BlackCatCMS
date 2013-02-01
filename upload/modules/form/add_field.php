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

// Include WB admin wrapper script
require(CAT_PATH.'/modules/admin.php');

global $page_id;
global $section_id;
global $database;
global $admin;
global $TEXT;

// Include the ordering class
require(CAT_PATH.'/framework/class.order.php');
// Get new order
$order = new order(CAT_TABLE_PREFIX.'mod_form_fields', 'position', 'field_id', 'section_id');
$position = $order->get_new($section_id);

// Insert new row into database
$database->query("INSERT INTO ".CAT_TABLE_PREFIX."mod_form_fields (`section_id` ,`page_id`, `position`, `required`, `value`, `extra`) VALUES ('$section_id', '$page_id', '$position', '0', '', '')");

// Get the id
$field_id = $database->get_one("SELECT LAST_INSERT_ID()");

// Say that a new record has been added, then redirect to modify page
if($database->is_error()) {
	$admin->print_error($database->get_error(), CAT_URL.'/modules/form/modify_field.php?page_id='.$page_id.'&section_id='.$section_id.'&field_id='.$field_id);
} else {
	$admin->print_success($TEXT['SUCCESS'], CAT_URL.'/modules/form/modify_field.php?page_id='.$page_id.'&section_id='.$section_id.'&field_id='.$field_id);
}

// Print admin footer
$admin->print_footer();