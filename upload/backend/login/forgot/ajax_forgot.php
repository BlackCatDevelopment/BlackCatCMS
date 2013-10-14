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
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

define('CAT_LOGIN_PHASE',true);

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

$val   = CAT_Helper_Validate::getInstance();
$email = $val->sanitizePost('email',NULL,true);
$ajax  = array();

header('Content-type: application/json');

if(!count(CAT_Helper_Addons::getInstance()->getLibraries('mail')))
{
    	$ajax	= array(
		'message'	=> $val->lang()->translate('Unable to mail login details - no mailer library installed!'),
		'success'	=> false
	);
}
else
{
    // Check if the user has already submitted the form, otherwise show it
    if ( $email && $val->sanitize_email($email) )
    {
        list($result,$message) = CAT_Users::handleForgot($email);
        $ajax	= array(
    		'message'	=> $message,
    		'success'	=> $result
    	);
    }
    else
    {
    	$ajax	= array(
    		'message'	=> $val->lang()->translate('You must enter an email address'),
    		'success'	=> false
    	);
    }
}
print json_encode( $ajax );
exit();