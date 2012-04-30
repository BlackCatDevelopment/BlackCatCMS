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
if(!isset($_POST['field_id']) OR !is_numeric($_POST['field_id'])) {
	header("Location: ".ADMIN_URL."/pages/index.php");
	exit(0);
} else {
	$field_id = $_POST['field_id'];
}

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(WB_PATH.'/modules/admin.php');

global $admin;
global $page_id;
global $section_id;
global $MESSAGE;
global $TEXT;
global $database;

// Validate all fields
if($admin->get_post('title') == '' OR $admin->get_post('type') == '') {
	$admin->print_error($MESSAGE['GENERIC']['FILL_IN_ALL'], WB_URL.'/modules/form/modify_field.php?page_id='.$page_id.'&section_id='.$section_id.'&field_id='.$field_id);
} else {
	$title = str_replace(array("[[", "]]"), '', htmlspecialchars($admin->get_post('title'), ENT_QUOTES));
	$type = $admin->add_slashes($admin->get_post('type'));
	$required = (int) $admin->add_slashes($admin->get_post('required'));
}
$value = '';

// Update row
$database->query("UPDATE ".TABLE_PREFIX."mod_form_fields SET title = '$title', type = '$type', required = '$required' WHERE field_id = '$field_id'");

// If field type has multiple options, get all values and implode them
$list_count = $admin->get_post('list_count');
if(is_numeric($list_count)) {
	$values = array();
	for($i = 1; $i <= $list_count; $i++) {
		if($admin->get_post('value'.$i) != '') {
			$values[] = str_replace(",","&#44;",$admin->get_post('value'.$i));
		}
	}
	$value = implode(',', $values);
}

// Get extra fields for field-type-specific settings
if($admin->get_post('type') == 'textfield') {
	$length = $admin->get_post_escaped('length');
	$value = str_replace(array("[[", "]]"), '', $admin->get_post_escaped('value'));
	$database->query("UPDATE ".TABLE_PREFIX."mod_form_fields SET value = '$value', extra = '$length' WHERE field_id = '$field_id'");
} elseif($admin->get_post('type') == 'textarea') {
	$value = str_replace(array("[[", "]]"), '', $admin->get_post_escaped('value'));
	$database->query("UPDATE ".TABLE_PREFIX."mod_form_fields SET value = '$value', extra = '' WHERE field_id = '$field_id'");
} elseif($admin->get_post('type') == 'heading') {
	$extra = str_replace(array("[[", "]]"), '', $admin->get_post('template'));
	if(trim($extra) == '') $extra = '<h2 class="field_heading">{TITLE}{FIELD}</h2>';
	$extra = $admin->add_slashes($extra);
	$database->query("UPDATE ".TABLE_PREFIX."mod_form_fields SET value = '', extra = '$extra' WHERE field_id = '$field_id'");
} elseif($admin->get_post('type') == 'select') {
	$extra = $admin->get_post_escaped('size').','.$admin->get_post_escaped('multiselect');
	$database->query("UPDATE ".TABLE_PREFIX."mod_form_fields SET value = '$value', extra = '$extra' WHERE field_id = '$field_id'");
} elseif($admin->get_post('type') == 'checkbox') {
	$extra = str_replace(array("[[", "]]"), '', $admin->get_post_escaped('seperator'));
	$database->query("UPDATE ".TABLE_PREFIX."mod_form_fields SET value = '$value', extra = '$extra' WHERE field_id = '$field_id'");
} elseif($admin->get_post('type') == 'radio') {
	$extra = str_replace(array("[[", "]]"), '', $admin->get_post_escaped('seperator'));
	$database->query("UPDATE ".TABLE_PREFIX."mod_form_fields SET value = '$value', extra = '$extra' WHERE field_id = '$field_id'");
}

// Check if there is a db error, otherwise say successful
if($database->is_error()) {
	$admin->print_error($database->get_error(), WB_URL.'/modules/form/modify_field.php?page_id='.$page_id.'&section_id='.$section_id.'&field_id='.$field_id);
} else {
	$admin->print_success($TEXT['SUCCESS'], WB_URL.'/modules/form/modify_field.php?page_id='.$page_id.'&section_id='.$section_id.'&field_id='.$field_id);
}

// Print admin footer
$admin->print_footer();

?>