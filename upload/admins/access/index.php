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



	require_once(WB_PATH.'/framework/class.admin.php');
	$admin = new admin('Access', 'access');

	// Setup template object
	$tpl = new Template(THEME_PATH.'/templates');
	$tpl->set_file('page', 'access.htt');
	$tpl->set_block('page', 'main_block', 'main');
	// Insert values into the template object
	$tpl->set_var(array(
			'ADMIN_URL' => ADMIN_URL,
			'THEME_URL' => THEME_URL,
			'WB_URL' => WB_URL,
			'ACCESS' => $MENU['ACCESS']
		)
	);
	$tpl->set_block('main_block', 'show_cmd_users_block', 'show_cmd_users');
	if ( $admin->get_permission('users') == true )
	{
	// Insert section name and description
		$tpl->set_var(array('USERS' => $MENU['USERS'],'USERS_OVERVIEW' => $OVERVIEW['USERS'],));
		$tpl->parse('show_cmd_users', 'show_cmd_users_block', true);
	}else{
		$tpl->set_block('show_cmd_users', '');
	}

	$tpl->set_block('main_block', 'show_cmd_groups_block', 'show_cmd_groups');
	if ( $admin->get_permission('groups') == true )
	{
	// Insert section name and description
		$tpl->set_var(array('GROUPS' => $MENU['GROUPS'],'GROUPS_OVERVIEW' => $OVERVIEW['GROUPS'],));
		$tpl->parse('show_cmd_groups', 'show_cmd_groups_block', true);
	}else{
		$tpl->set_block('show_cmd_groups', '');
	}

	// Parse template object
	$tpl->parse('main', 'main_block', false);
	$tpl->pparse('output', 'page');

	// Print admin footer
	$admin->print_footer();

?>