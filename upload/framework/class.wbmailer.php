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
 *
 *
 */


// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {	
	include(LEPTON_PATH.'/framework/class.secure.php'); 
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


/*
wbmailer class
This class is a subclass of the PHPMailer class and replaces the mail() function of PHP
*/

// Include PHPMailer class
require_once(LEPTON_PATH."/include/lib_phpmailer/class.phpmailer.php");

class wbmailer extends PHPMailer 
{
	// new websitebaker mailer class (subset of PHPMailer class)
	// setting default values 

	function wbmailer() {
		// set mailer defaults (PHP mail function)
		$db_wbmailer_routine = "phpmail";
		$db_wbmailer_smtp_host = "";
		$db_wbmailer_default_sendername = "WB Mailer";
		$db_server_email = SERVER_EMAIL;

		// get mailer settings from database
		$database = new database();
		$query = "SELECT * FROM " .TABLE_PREFIX. "settings";
		$results = $database->query($query);
		while($setting = $results->fetchRow()) {
			if ($setting['name'] == "wbmailer_routine") { $db_wbmailer_routine = $setting['value']; }
			if ($setting['name'] == "wbmailer_smtp_host") { $db_wbmailer_smtp_host = $setting['value']; }
			if ($setting['name'] == "wbmailer_smtp_auth") { $db_wbmailer_smtp_auth = (bool)$setting['value']; }
			if ($setting['name'] == "wbmailer_smtp_username") { $db_wbmailer_smtp_username = $setting['value']; }
			if ($setting['name'] == "wbmailer_smtp_password") { $db_wbmailer_smtp_password = $setting['value']; }
			if ($setting['name'] == "wbmailer_default_sendername") { $db_wbmailer_default_sendername = $setting['value']; }
			if ($setting['name'] == "server_email") { $db_server_email = $setting['value']; }
		}

		// set method to send out emails
		if($db_wbmailer_routine == "smtp" AND strlen($db_wbmailer_smtp_host) > 5) {
			// use SMTP for all outgoing mails send by Website Baker
			$this->IsSMTP();                                            
			$this->Host = $db_wbmailer_smtp_host;
			// check if SMTP authentification is required
			if ($db_wbmailer_smtp_auth == "true" && strlen($db_wbmailer_smtp_username) > 1 && strlen($db_wbmailer_smtp_password) > 1) {
				// use SMTP authentification
				$this->SMTPAuth = true;     	  								// enable SMTP authentification
				$this->Username = $db_wbmailer_smtp_username;  	// set SMTP username
				$this->Password = $db_wbmailer_smtp_password;	  // set SMTP password
			}
		} else {
			// use PHP mail() function for outgoing mails send by Website Baker
			$this->IsMail();
		}

		// set language file for PHPMailer error messages
		if(defined("LANGUAGE")) {
			$this->SetLanguage(strtolower(LANGUAGE),"language");    // english default (also used if file is missing)
		}

		// set default charset
		if(defined('DEFAULT_CHARSET')) { 
			$this->CharSet = DEFAULT_CHARSET; 
		} else {
			$this->CharSet='utf-8';
		}

		// set default sender name
		if($this->FromName == 'Root User') {
			if(isset($_SESSION['DISPLAY_NAME'])) {
				$this->FromName = $_SESSION['DISPLAY_NAME'];            // FROM NAME: display name of user logged in
			} else {
				$this->FromName = $db_wbmailer_default_sendername;			// FROM NAME: set default name
			}
		}

		/* 
			some mail provider (lets say mail.com) reject mails send out by foreign mail 
			relays but using the providers domain in the from mail address (e.g. myname@mail.com)
		*/
		$this->From = $db_server_email;                           // FROM MAIL: (server mail)

		// set default mail formats
		$this->IsHTML(true);                                        
		$this->WordWrap = 80;                                       
		$this->Timeout = 30;
	}
}

?>