<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Thomas Hornik (thorn), LEPTON Project, Black Cat Development
 *   @copyright       2008-2011, Thomas Hornik (thorn)
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://www.blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
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

$table = CAT_TABLE_PREFIX.'mod_captcha_control';

// check user permissions
if ( ! $admin->get_permission('admintools') )
{
    $admin->print_error( 'Sorry, you do not have permissions to view this page' );
}

// check if data was submitted
if(isset($_POST['save_settings']))
{
	// get configuration settings
	$enabled_captcha = ($_POST['enabled_captcha'] == '1') ? '1' : '0';
	$enabled_asp = ($_POST['enabled_asp'] == '1') ? '1' : '0';
	$captcha_type = $admin->add_slashes($_POST['captcha_type']);
	
	// update database settings
	$database->query("UPDATE $table SET
		enabled_captcha = '$enabled_captcha',
		enabled_asp = '$enabled_asp',
		captcha_type = '$captcha_type'
	");

	// save text-captchas
	if($captcha_type == 'text') { // ct_text
		$text_qa=$admin->add_slashes($_POST['text_qa']);
		if(!preg_match('/### .*? ###/', $text_qa)) {
			$database->query("UPDATE $table SET ct_text = '$text_qa'");
		}
	}
	
	// check if there is a database error, otherwise say successful
	if($database->is_error()) {
		$admin->print_error($database->get_error(), $js_back);
	} else {
		$admin->print_success($MESSAGE['PAGES']['SAVED'], CAT_ADMIN_URL.'/admintools/tool.php?tool=captcha_control');
	}

} else {
	
	// include captcha-file
	require_once(CAT_PATH .'/include/captcha/captcha.php');

	// load text-captchas
	$text_qa='';
	if($query = $database->query("SELECT ct_text FROM $table")) {
		$data = $query->fetchRow();
		$text_qa = $data['ct_text'];
	}
	if($text_qa == '')
		$text_qa = $admin->lang->translate('Delete this all to add your own entries'."\n".'or your changes won\'t be saved!'."\n".'### example ###'."\n".'Here you can enter Questions and Answers.'."\n".'Use:'."\n".'?What\'s Claudia Schiffer\'s first name?'."\n".'!Claudia'."\n".'?Question 2'."\n".'!Answer 2'."\n".''."\n".'if language doesn\'t matter.'."\n".' ... '."\n".'Or, if language do matter, use:'."\n".'?EN:What\'s Claudia Schiffer\'s first name?'."\n".'!Claudia'."\n".'?EN:Question 2'."\n".'!Answer 2'."\n".'?DE:Wie ist der Vorname von Claudia Schiffer?'."\n".'!Claudia'."\n".' ... '."\n".'### example ###'."\n".'');

	// connect to database and read out captcha settings
	if($query = $database->query("SELECT * FROM $table")) {
		$data = $query->fetchRow();
		$enabled_captcha = $data['enabled_captcha'];
		$enabled_asp = $data['enabled_asp'];
		$captcha_type = $data['captcha_type'];
	} else {
		// something went wrong, use dummy value
		$enabled_captcha = '1';
		$enabled_asp = '1';
		$captcha_type = 'calc_text';
	}
		
}

global $parser;
$parser->setPath( CAT_PATH.'/modules/captcha_control/templates/default' );

$parser->output(
    'tool.lte',
    array(
        'ttf_image' => CAT_URL.'/include/captcha/captchas/ttf_image.png',
        'calc_image' => CAT_URL.'/include/captcha/captchas/calc_image.png',
        'calc_ttf_image' => CAT_URL.'/include/captcha/captchas/calc_ttf_image.png',
        'old_image' => CAT_URL.'/include/captcha/captchas/old_image.png',
        'calc_text' => CAT_URL.'/include/captcha/captchas/calc_text.png',
        'text' => CAT_URL.'/include/captcha/captchas/text.png',
        'action' => $_SERVER['REQUEST_URI'],
        'useable_captchas' => $useable_captchas,
        'captcha_type' => $captcha_type,
        'text_qa' => $text_qa,
        'enabled_captcha' => $enabled_captcha,
        'enabled_asp' => $enabled_asp,
    )
);

?>