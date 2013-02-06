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
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 * @license			http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

ob_start();

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

header( "Cache-Control: no-cache, must-revalidate" );
header( "Pragma: no-cache" );
header( "Content-Type: text/html; charset:utf-8;" );

include realpath(dirname(__FILE__)).'/../../framework/class.admin.php';
$admin = new admin('Settings', 'settings_basic');

$curr_user_is_admin = ( in_array(1, CAT_Users::getInstance()->get_groups_id()) );

if ( ! $curr_user_is_admin ) {
    echo "<div style='border: 2px solid #CC0000; padding: 5px; text-align: center; background-color: #ffbaba;'>You're not allowed to use this function!</div>";
    exit;
}

$settings = array();
$sql      = 'SELECT `name`, `value` FROM `'.CAT_TABLE_PREFIX.'settings`';
if ( $res_settings = $database->query( $sql ) ) {
    while ($row = $res_settings->fetchRow( )) {
        $settings[ strtoupper($row['name']) ] = ( $row['name'] != 'catmailer_smtp_password' ) ? htmlspecialchars($row['value']) : $row['value'];
	}
}
ob_clean();

// send mail
if( $admin->mail( $settings['SERVER_EMAIL'], $settings['SERVER_EMAIL'], $settings['CATMAILER_DEFAULT_SENDERNAME'], $admin->lang->translate('This is the required test mail: CAT mailer is working') ) ) {
    echo "<div style='border: 2px solid #006600; padding: 5px; text-align: center; background-color: #dff2bf;'>",
         $admin->lang->translate('The test eMail was sent successfully. Please check your inbox.'),
         "</div>";
}
else {
    $message = ob_get_clean();
    echo "<div style='border: 2px solid #CC0000; padding: 5px; text-align: center; background-color: #ffbaba;'>",
         $admin->lang->translate('The test eMail could not be sent! Please check your settings!'),
         "<br />$message<br /></div>";
}

?>