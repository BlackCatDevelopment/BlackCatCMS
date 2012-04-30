<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
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



// Include the language file
require(WB_PATH.'/languages/'.DEFAULT_LANGUAGE.'.php');
// Include the database class file and initiate an object
require(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Start', 'start', false, false);
$database = new database();

// Get the website title
$results = $database->query("SELECT value FROM ".TABLE_PREFIX."settings WHERE name = 'title'");
$results = $results->fetchRow();
$website_title = $results['value'];

// Check if the user has already submitted the form, otherwise show it
if(isset($_POST['email']) AND $_POST['email'] != "") {
	
	$email = htmlspecialchars($_POST['email'],ENT_QUOTES);
	
	// Check if the email exists in the database
	$query = "SELECT user_id,username,display_name,email,last_reset,password FROM ".TABLE_PREFIX."users WHERE email = '".$admin->add_slashes($_POST['email'])."'";
	$results = $database->query($query);
	if($results->numRows() > 0) {

		// Get the id, username, email, and last_reset from the above db query
		$results_array = $results->fetchRow();
		
		// Check if the password has been reset in the last 2 hours
		$last_reset = $results_array['last_reset'];
		$time_diff = time()-$last_reset; // Time since last reset in seconds
		$time_diff = $time_diff/60/60; // Time since last reset in hours
		if($time_diff < 2) {
			
			// Tell the user that their password cannot be reset more than once per hour
			$message = $MESSAGE['FORGOT_PASS_ALREADY_RESET'];
			
		} else {
			
			$old_pass = $results_array['password'];
			
			// Generate a random password then update the database
			$new_pass = '';
			$salt = "abcdefghijklmnpqrstuvwxyz0123456789";
			srand((double)microtime()*1000000);
			$i = 0;
			while ($i <= 7) {
				$num = rand() % 35;
				$tmp = substr($salt, $num, 1);
				$new_pass .= $tmp;
				$i++;
			}
			
			$database->query("UPDATE ".TABLE_PREFIX."users SET password = '".md5($new_pass)."', last_reset = '".time()."' WHERE user_id = '".$results_array['user_id']."'");
			
			if($database->is_error()) {
				// Error updating database
				$message = $database->get_error();
			} else {
				// Setup email to send
				$mail_to = $email;
				$mail_subject = $MESSAGE['SIGNUP2_SUBJECT_LOGIN_INFO'];

				// Replace placeholders from language variable with values
				$search = array('{LOGIN_DISPLAY_NAME}', '{LOGIN_WEBSITE_TITLE}', '{LOGIN_NAME}', '{LOGIN_PASSWORD}');
				$replace = array($results_array['display_name'], WEBSITE_TITLE, $results_array['username'], $new_pass); 
				$mail_message = str_replace($search, $replace, $MESSAGE['SIGNUP2_BODY_LOGIN_FORGOT']);

				// Try sending the email
				if($admin->mail(SERVER_EMAIL,$mail_to,$mail_subject,$mail_message)) { 
					$message = $MESSAGE['FORGOT_PASS_PASSWORD_RESET'];
					$display_form = false;
				} else {
					$database->query("UPDATE ".TABLE_PREFIX."users SET password = '".$old_pass."' WHERE user_id = '".$results_array['user_id']."'");
					$message = $MESSAGE['FORGOT_PASS_CANNOT_EMAIL'];
				}
			}
		
		}
		
	} else {
		// Email doesn't exist, so tell the user
		$message = $MESSAGE['FORGOT_PASS_EMAIL_NOT_FOUND'];
		// and delete the wrong Email
		$email = '';
	}
	
} else {
	$email = '';
}

if(!isset($message)) {
	$message = $MESSAGE['FORGOT_PASS_NO_DATA'];
	$message_color = '000000';
} else {
	$message_color = 'FF0000';
}
	
// Setup the template
$template = new Template(THEME_PATH.'/templates');
$template->set_file('page', 'login_forgot.htt');
$template->set_block('page', 'main_block', 'main');
if(defined('FRONTEND')) {
	$template->set_var('ACTION_URL', 'forgot.php');
} else {
	$template->set_var('ACTION_URL', 'index.php');
}
$template->set_var('EMAIL', $email);

if(isset($display_form)) {
	$template->set_var('DISPLAY_FORM', 'display:none;');
}

$template->set_var(array(
				'SECTION_FORGOT' => $MENU['FORGOT'],
				'MESSAGE_COLOR' => $message_color,
				'MESSAGE' => $message,
				'WB_URL' => WB_URL,
				'ADMIN_URL' => ADMIN_URL,
				'THEME_URL' => THEME_URL,
				'VERSION' => VERSION,
				'LANGUAGE' => strtolower(LANGUAGE),
				'TEXT_EMAIL' => $TEXT['EMAIL_ADDRESS'],
				'TEXT_SEND_DETAILS' => $TEXT['SEND_DETAILS'],
				'TEXT_HOME' => $TEXT['HOME'],
				'TEXT_NEED_TO_LOGIN' => $TEXT['NEED_TO_LOGIN']
				) );

if(defined('FRONTEND')) {
	$template->set_var('LOGIN_URL', WB_URL.'/account/login.php');
} else {
	$template->set_var('LOGIN_URL', ADMIN_URL);
}
$template->set_var('INTERFACE_URL', ADMIN_URL.'/interface');	

if(defined('DEFAULT_CHARSET')) {
	$charset=DEFAULT_CHARSET;
} else {
	$charset='utf-8';
}

$template->set_var('CHARSET', $charset);	

$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');

?>