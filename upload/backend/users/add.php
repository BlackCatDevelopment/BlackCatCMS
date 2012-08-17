<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
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

require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Access', 'users_add');

if (isset($_SESSION['au'])) unset($_SESSION['au']);
$_SESSION['au'] = array();
// Get details entered
$groups_id = NULL;
if ( isset( $_POST['groups'] ) ) {
	$groups_id = implode(",", $admin->add_slashes($_POST['groups'])); //should check permissions
}

$active = (isset($_POST['active']) && is_numeric($_POST['active'])) ? $_POST['active'] : false;

$username_fieldname = $admin->get_post_escaped('username_fieldname');

$username = strtolower($admin->get_post_escaped($username_fieldname));

$_SESSION['au']['username'] = $username;

$password = $admin->get_post('password');

$password2 = $admin->get_post('password2');
if ($password == $password2) $_SESSION['au']['password'] = $password;
$display_name = $admin->get_post_escaped('display_name');
$_SESSION['au']['display_name'] = $display_name;
$email = $admin->get_post_escaped('email');
$_SESSION['au']['email'] = $email;
$home_folder = $admin->get_post_escaped('home_folder');
$default_language = DEFAULT_LANGUAGE;

// Create a back link
$js_back = ADMIN_URL.'/users/index.php';

// Check values
if($groups_id == '') {
	$admin->print_error($MESSAGE['USERS_NO_GROUP'], $js_back);
}

if (strlen( $username ) < 3) {
	$admin->print_error( $MESSAGE['USERS_USERNAME_TOO_SHORT'], $js_back);
}

if(!preg_match('/^[a-z]{1}[a-z0-9@\._-]{2,}$/i', $username)) {
	$admin->print_error( $MESSAGE['USERS_NAME_INVALID_CHARS'], $js_back);
}

if(strlen($password) < AUTH_MIN_PASS_LENGTH) {
	$admin->print_error($MESSAGE['USERS_PASSWORD_TOO_SHORT'], $js_back);
}
if($password != $password2) {
	$admin->print_error($MESSAGE['USERS_PASSWORD_MISMATCH'], $js_back);
}
if($email != '')
{
	if($admin->validate_email($email) == false)
    {
		$admin->print_error($MESSAGE['USERS_INVALID_EMAIL'], $js_back);
	}
} else { // e-mail must be present
	$admin->print_error($MESSAGE['SIGNUP_NO_EMAIL'], $js_back);
}

// choose group_id from groups_id - workaround for still remaining calls to group_id (to be cleaned-up)
$gid_tmp = explode(',', $groups_id);
if(in_array('1', $gid_tmp)) $group_id = '1'; // if user is in administrator-group, get this group
else $group_id = $gid_tmp[0]; // else just get the first one
unset($gid_tmp);

// Check if username already exists
$results = $database->query("SELECT user_id FROM ".TABLE_PREFIX."users WHERE username = '$username'");
if($results->numRows() > 0) {
	$admin->print_error($MESSAGE['USERS_USERNAME_TAKEN'], $js_back);
}

// Check if the email already exists
$results = $database->query("SELECT user_id FROM ".TABLE_PREFIX."users WHERE email = '".$admin->add_slashes($_POST['email'])."'");
if($results->numRows() > 0)
{
	if(isset($MESSAGE['USERS_EMAIL_TAKEN']))
    {
		$admin->print_error($MESSAGE['USERS_EMAIL_TAKEN'], $js_back);
	} else {
		$admin->print_error($MESSAGE['USERS_INVALID_EMAIL'], $js_back);
	}
}

/**
 *	MD5 supplied password
 *
 */
$md5_password = md5($password);

/**
 *	Inser the user-data into the database
 *
 */
$fields = array(
	'group_id'	=> $group_id,
	'groups_id'	=> $groups_id,
	'active'	=> $active,
	'username'	=> $username,
	'password'	=> $md5_password,
	'display_name'	=> $display_name,
	'home_folder'	=> $home_folder,
	'email'	=> $email,
	'timezone_string'	=> DEFAULT_TIMEZONE_STRING,	// **!
	'language'	=> $default_language
);

$fields = array_map("mysql_real_escape_string", $fields);

$query  = "INSERT INTO `".TABLE_PREFIX."users` ";
$query .= "(`".implode("`,`", array_keys( $fields ) )."`) ";
$query .= "VALUES('". implode("','", array_values( $fields ) )."')";

$database->query($query);
if($database->is_error()) {
	$admin->print_error($database->get_error());
} else {
	if (isset($_SESSION['au'])) unset($_SESSION['au']);
	$last_id = $database->get_one("SELECT LAST_INSERT_ID()");
	$new_user = '<input type="hidden" name="new_user_id" value="'.$last_id.'" />';
	// ================================ 
	// ! Generate username field name   
	// ================================ 
	$username_fieldname = 'username_';
	$salt = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEZ_+-";
	$salt_len = strlen($salt) -1;
	$i = 0;
	while (++$i <= 7) {
		$num = mt_rand(0, $salt_len);
		$username_fieldname .= $salt[ $num ];
	}
	$new_username_fieldname = '<input type="hidden" name="username_fieldname" value="'.$username_fieldname.'" />';
	$admin->print_success($MESSAGE['USERS_ADDED'].$new_user.$new_username_fieldname);
}

/**
 *	Print admin footer
 *
 */
$admin->print_footer();

?>