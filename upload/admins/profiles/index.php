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



/**
 *	M.f.i	599 (Aldus)
 *
 *
 */
	function admin_profiles_index(&$database)
	{
        global $MESSAGE;
		$err = array();
		$output = '';
		$submit_action = 'show';
//		$submit_action = ( isset($_POST['action_delete']) ? 'delete' : $submit_action );
//		$submit_action = ( isset($_POST['action_save']) ? 'save' : $submit_action );
		switch($submit_action)
		{
			case 'delete': // delete the user profile
//				$admin =& new admin('Access', 'users_delete');
//				$user_id = $admin->checkIDKEY('user_id');
//				delete_user($err, $admin, $database, $user_id);
//				break;
			case 'save': // insert/update user profile
//
//				$admin =& new admin('Access', 'users_add');
//
//				$admin =& new admin('Access', 'users_modify');
//
//
//				break;
			default: // show user profile with modify mask
				$admin =& new admin('Access', 'users');
				$user_id = $admin->checkIDKEY('user_id');
				$user_id = ( $user_id == 0 ? $admin->get_user_id() : $user_id );
//				$output  = show_usermask($err, $admin, $database, $user_id);
				if(isset( $_POST['user_id']))
				{
					$target = ADMIN_URL.'/users/index.php';
				}else {
					$target = ADMIN_URL.'/preferences/index.php';
				}
				$err[] = 'This function is not available yet!<br /><br/ >
				<form name="FORM_BACK" id="FORM_BACK" action="'.$target.'" method="post">
				<input type="hidden" name="user_id" value="'.$user_id.'" />
				<input type="submit" name="action_modify" style="width: 100px;" value="go back..." />
				</form><br />br/>';


		} // end of switch
		if(sizeof($err) == 0)
		{
			echo $output;
			$admin->print_footer();
		}else {
			$err_msg = ( (sizeof($err) > 0) ? implode('<br />', $err) : '' );
			$admin->print_error($err_msg);
		}
	}
// start user maintenance
	require_once(WB_PATH.'/framework/class.admin'.PAGE_EXTENSION);
	require_once('actions.inc'.PAGE_EXTENSION);
	admin_profiles_index($database);
	exit;
// end of file

?>