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

global $database;
global $page_id;
global $section_id;

// Delete page
$database->query("DELETE FROM ".CAT_TABLE_PREFIX."mod_form_fields WHERE page_id = '$page_id' AND section_id='$section_id'");
$database->query("DELETE FROM ".CAT_TABLE_PREFIX."mod_form_settings WHERE page_id = '$page_id' AND section_id='$section_id'");
