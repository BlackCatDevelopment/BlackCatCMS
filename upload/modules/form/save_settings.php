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

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(WB_PATH.'/modules/admin.php');

global $admin;
global $database;
global $section_id;
global $page_id;
global $TEXT;

// This code removes any <?php tags and adds slashes
$friendly = array('&lt;', '&gt;', '?php');
$raw = array('<', '>', '');
$header = $admin->add_slashes($_POST['header']);
$field_loop = $admin->add_slashes($_POST['field_loop']);
$footer = $admin->add_slashes($_POST['footer']);
$email_to = $admin->add_slashes($_POST['email_to']);
$use_captcha = $admin->add_slashes($_POST['use_captcha']);
if($_POST['email_from_field'] == '') {
	$email_from = $admin->add_slashes($_POST['email_from']);
} else {
	$email_from = $admin->add_slashes($_POST['email_from_field']);
}
$email_fromname = $admin->add_slashes($_POST['email_fromname']);
$email_subject = $admin->add_slashes($_POST['email_subject']);
$success_page = $admin->add_slashes($_POST['success_page']);
$success_email_to = $admin->add_slashes($_POST['success_email_to']);
$success_email_from = $admin->add_slashes($_POST['success_email_from']);
$success_email_fromname = $admin->add_slashes($_POST['success_email_fromname']);
$success_email_text = $admin->add_slashes($_POST['success_email_text']);
$success_email_subject = $admin->add_slashes($_POST['success_email_subject']);
if(!is_numeric($_POST['max_submissions'])) {
	$max_submissions = 50;
} else {
	$max_submissions = $_POST['max_submissions'];
}
if(!is_numeric($_POST['stored_submissions'])) {
	$stored_submissions = 1000;
} else {
	$stored_submissions = $_POST['stored_submissions'];
}
// Make sure max submissions is not greater than stored submissions if stored_submissions <>0
if($max_submissions > $stored_submissions) {
	$max_submissions = $stored_submissions;
}

// Update settings
$database->query("UPDATE ".TABLE_PREFIX."mod_form_settings SET header = '$header', field_loop = '$field_loop', footer = '$footer', email_to = '$email_to', email_from = '$email_from', email_fromname = '$email_fromname', email_subject = '$email_subject', success_page = '$success_page', success_email_to = '$success_email_to', success_email_from = '$success_email_from', success_email_fromname = '$success_email_fromname', success_email_text = '$success_email_text', success_email_subject = '$success_email_subject', max_submissions = '$max_submissions', stored_submissions = '$stored_submissions', use_captcha = '$use_captcha' WHERE section_id = '$section_id'");

// Check if there is a db error, otherwise say successful
if($database->is_error()) {
	$admin->print_error($database->get_error(), ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
} else {
	$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();

?>