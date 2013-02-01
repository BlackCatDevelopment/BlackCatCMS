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
    include (CAT_PATH . '/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/framework/class.secure.php')) {
    include ($_SERVER['DOCUMENT_ROOT'] . '/framework/class.secure.php');
} else {
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));
    $dir = $_SERVER['DOCUMENT_ROOT'];
    $inc = false;
    foreach ($subs as $sub) {
        if (empty($sub)) continue;
        $dir .= '/' . $sub;
        if (file_exists($dir . '/framework/class.secure.php')) {         
            include ($dir . '/framework/class.secure.php');
            $inc = true;
            break;        
        }    
    }    
    if (! $inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}
// end include class.secure.php

global $admin;
global $database;
global $page_id;
global $section_id;

// Insert an extra rows into the database
$header = '<div class=\"lepton_form\">';
$field_loop = addslashes('<div class="field_title"><span class="title">{TITLE}</span><span class="required">{REQUIRED}</span><span class="sign">:</span></div><div class="field_input"><span class="field">{FIELD}</span></div>');
$footer = addslashes('<div class="lep_footerform">
<div class="lep_button"><input type="submit" name="submit" value="Submit Form" /></div>
</div>
</div>');

$email_to = $admin->get_email();
$email_from = '';
$email_fromname = '';
$email_subject = 'Results from form on website...';

$success_page = 'none';
$success_email_to = '';
$success_email_from = $admin->get_email();
$success_email_fromname = '';
$success_email_text = addslashes('<div class="success_email_text">Thank you for submitting your form on</div><div class="website_title"> ' . WEBSITE_TITLE . '</div>');
$success_email_text = addslashes($success_email_text);

$success_email_subject = 'You have submitted a form';

$max_submissions = 50;

$stored_submissions = 50;

$use_captcha = true;

$database->query("INSERT INTO ". CAT_TABLE_PREFIX . "mod_form_settings (page_id,section_id,header,field_loop,footer,email_to,email_from,email_fromname,email_subject,success_page,success_email_to,success_email_from,success_email_fromname,success_email_text,success_email_subject,max_submissions,stored_submissions,use_captcha) VALUES ('$page_id','$section_id','$header','$field_loop','$footer','$email_to','$email_from','$email_fromname','$email_subject','$success_page','$success_email_to','$success_email_from','$success_email_fromname','$success_email_text','$success_email_subject','$max_submissions','$stored_submissions','$use_captcha')");
