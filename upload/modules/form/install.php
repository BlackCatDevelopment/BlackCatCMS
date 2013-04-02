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
	if (defined('LEPTON_VERSION')) include(CAT_PATH.'/framework/class.secure.php'); 
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

/**
 * @internal erpe 2011-08-04 - this must be proved in upcoming release when install/upgrade/deinstall process is reworked
 */		
//	$database->query("DROP TABLE IF EXISTS `".CAT_TABLE_PREFIX."mod_form_fields`");
//	$database->query("DROP TABLE IF EXISTS `".CAT_TABLE_PREFIX."mod_form_submissions`");
//	$database->query("DROP TABLE IF EXISTS `".CAT_TABLE_PREFIX."mod_form_settings`");

// Create tables
$mod_form = 'CREATE TABLE IF NOT EXISTS `'.CAT_TABLE_PREFIX.'mod_form_fields` ( `field_id` INT NOT NULL AUTO_INCREMENT,'
	. ' `section_id` INT NOT NULL DEFAULT \'0\' ,'
	. ' `page_id` INT NOT NULL DEFAULT \'0\' ,'
	. ' `position` INT NOT NULL DEFAULT \'0\' ,'
	. ' `title` VARCHAR(255) NOT NULL DEFAULT \'\' ,'
	. ' `type` VARCHAR(255) NOT NULL DEFAULT \'\' ,'
	. ' `required` INT NOT NULL DEFAULT \'0\' ,'
	. ' `value` TEXT NOT NULL ,'
	. ' `extra` TEXT NOT NULL ,'
	. ' PRIMARY KEY ( `field_id` ) '
	. ' )';
$database->query($mod_form);

$mod_form = 'CREATE TABLE IF NOT EXISTS `'.CAT_TABLE_PREFIX.'mod_form_settings` ('
	. ' `section_id` INT NOT NULL DEFAULT \'0\' ,'
	. ' `page_id` INT NOT NULL DEFAULT \'0\' ,'
	. ' `header` TEXT NOT NULL ,'
	. ' `field_loop` TEXT NOT NULL ,'
	. ' `footer` TEXT NOT NULL ,'
	. ' `email_to` TEXT NOT NULL ,'
	. ' `email_from` VARCHAR(255) NOT NULL DEFAULT \'\' ,'
	. ' `email_fromname` VARCHAR(255) NOT NULL DEFAULT \'\' ,'
	. ' `email_subject` VARCHAR(255) NOT NULL DEFAULT \'\' ,'
	. ' `success_page` TEXT NOT NULL ,'
	. ' `success_email_to` TEXT NOT NULL ,'
	. ' `success_email_from` VARCHAR(255) NOT NULL DEFAULT \'\' ,'
	. ' `success_email_fromname` VARCHAR(255) NOT NULL DEFAULT \'\' ,'
	. ' `success_email_text` TEXT NOT NULL ,'
	. ' `success_email_subject` VARCHAR(255) NOT NULL DEFAULT \'\' ,'
	. ' `stored_submissions` INT NOT NULL DEFAULT \'0\' ,'
	. ' `max_submissions` INT NOT NULL DEFAULT \'0\' ,'
	. ' `use_captcha` INT NOT NULL DEFAULT \'0\' ,'
	. ' PRIMARY KEY ( `section_id` ) '
	. ' )';
$database->query($mod_form);

$mod_form = 'CREATE TABLE IF NOT EXISTS `'.CAT_TABLE_PREFIX.'mod_form_submissions` ( `submission_id` INT NOT NULL AUTO_INCREMENT,'
	. ' `section_id` INT NOT NULL DEFAULT \'0\' ,'
	. ' `page_id` INT NOT NULL DEFAULT \'0\' ,'
	. ' `submitted_when` INT NOT NULL DEFAULT \'0\' ,'
	. ' `submitted_by` INT NOT NULL DEFAULT \'0\','
	. ' `body` TEXT NOT NULL,'
	. ' PRIMARY KEY ( `submission_id` ) '
	. ' )';
$database->query($mod_form);

$addons_helper = CAT_Helper_Addons::getInstance();

// add files to class_secure
foreach(
    array( 'add_field.php', 'delete_field.php', 'delete_submission.php',
           'modify_field.php', 'modify_settings.php', 'move_down.php',
           'move_up.php', 'save_field.php', 'save_settings.php', 'view_submission.php' )
    as $file
) {
    if ( false === $addons_helper->sec_register_file( 'form', $file ) )
    {
         error_log( "Unable to register file -$file-!" );
    }
}

