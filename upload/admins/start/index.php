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
 * @copyright       2010-2012, LEPTON Project
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

// exec initial_page
if(file_exists(WB_PATH .'/modules/initial_page/classes/c_init_page.php') && isset($_SESSION['USER_ID'])) {
	require_once (WB_PATH .'/modules/initial_page/classes/c_init_page.php');
	$ins = new c_init_page($database, $_SESSION['USER_ID'], $_SERVER['SCRIPT_NAME']);
}
require_once(WB_PATH.'/framework/class.admin.php');$admin = new admin('Start','start');

// Setup template object
$tpl = new Template(THEME_PATH.'/templates');
$tpl->debug = false;
$tpl->set_file('page', 'start.htt');
$tpl->set_block('page', 'main_block', 'main');

$tpl->set_block('main_block', 'show_preferences_block', 'show_preferences');

// first set all blocks to visible
$tpl->parse('show_preferences', 'show_preferences_block', true);

// Check register_globals:
$warning = (ini_get('register_globals')) ? 'This PHP installation is insecure because register_globals is on! Please contact your administrator.' : '';

// Insert values into the template object
$tpl->set_var(array(
	'WELCOME_MESSAGE' => $MESSAGE['START_WELCOME_MESSAGE'],
	'CURRENT_USER' => $MESSAGE['START_CURRENT_USER'],
	'DISPLAY_NAME' => $admin->get_display_name(),
	'ADMIN_URL' => ADMIN_URL,
	'WB_URL' => WB_URL,
	'THEME_URL' => THEME_URL,
	'NO_CONTENT' => '<p>&nbsp;</p>',
	'WARNING' => $warning
	)
);
// Insert permission values into the template object
$tpl->set_block('main_block', 'show_pages_block', 'show_pages');
if($admin->get_permission('pages') != true)
{
	$tpl->set_var('DISPLAY_PAGES', 'display:none;');
	$tpl->set_block('show_pages', '');
} else {
	$tpl->parse('show_pages', 'show_pages_block', true);
}

$tpl->set_block('main_block', 'show_media_block', 'show_media');
if($admin->get_permission('media') != true)
{
	$tpl->set_var('DISPLAY_MEDIA', 'display:none;');
	$tpl->set_block('show_media', '');
} else {
	$tpl->parse('show_media', 'show_media_block', true);
}

$tpl->set_block('main_block', 'show_addons_block', 'show_addons');
if($admin->get_permission('addons') != true)
{
	$tpl->set_var('DISPLAY_ADDONS', 'display:none;');
	$tpl->set_block('show_addons', '');
} else {
	$tpl->parse('show_addons', 'show_addons_block', true);
}

$tpl->set_block('main_block', 'show_access_block', 'show_access');
if($admin->get_permission('access') != true)
{
	$tpl->set_var('DISPLAY_ACCESS', 'display:none;');
	$tpl->set_block('show_access', '');
} else {
	$tpl->parse('show_access', 'show_access_block', true);
}

$tpl->set_block('main_block', 'show_settings_block', 'show_settings');
if($admin->get_permission('settings') != true)
{
	$tpl->set_var('DISPLAY_SETTINGS', 'display:none;');
	$tpl->set_block('show_settings', '');
} else {
	$tpl->parse('show_settings', 'show_settings_block', true);
}

$tpl->set_block('main_block', 'show_admintools_block', 'show_admintools');
if($admin->get_permission('admintools') != true)
{
	$tpl->set_var('DISPLAY_ADMINTOOLS', 'display:none;');
	$tpl->set_block('show_admintools', '');
} else {
	$tpl->parse('show_admintools', 'show_admintools_block', true);
}

/** 
 *	Try to delete install directory - it's still not needed anymore.
 *	Additional check for the user to be logged in with administrator-rights.
 */
if ( (file_exists(WB_PATH.'/install/')) && ( in_array (1, $admin->get_groups_id() ) ) )
{
	$result = rm_full_dir(WB_PATH.'/install/');
	if (false === $result)
	{
		/**
		 *	Removing the install directory failed! So we are
		 *	in the need to throw an error-message to the user.
		 */
		$tpl->set_var("WARNING", "<br  />".$MESSAGE['START_INSTALL_DIR_EXISTS']."<br />");
	}
}

// Insert "Add-ons" section overview (pretty complex compared to normal)
$addons_overview = $TEXT['MANAGE'].' ';
$addons_count = 0;
if($admin->get_permission('modules') == true)
{
	$addons_overview .= '<a href="'.ADMIN_URL.'/modules/index.php">'.$MENU['MODULES'].'</a>';
	$addons_count = 1;
}
if($admin->get_permission('templates') == true)
{
	if($addons_count == 1) { $addons_overview .= ', '; }
	$addons_overview .= '<a href="'.ADMIN_URL.'/templates/index.php">'.$MENU['TEMPLATES'].'</a>';
	$addons_count = 1;
}
if($admin->get_permission('languages') == true)
{
	if($addons_count == 1) { $addons_overview .= ', '; }
	$addons_overview .= '<a href="'.ADMIN_URL.'/languages/index.php">'.$MENU['LANGUAGES'].'</a>';
}

// Insert "Access" section overview (pretty complex compared to normal)
$access_overview = $TEXT['MANAGE'].' ';
$access_count = 0;
if($admin->get_permission('users') == true) {
	$access_overview .= '<a href="'.ADMIN_URL.'/users/index.php">'.$MENU['USERS'].'</a>';
	$access_count = 1;
}
if($admin->get_permission('groups') == true) {
	if($access_count == 1) { $access_overview .= ', '; }
	$access_overview .= '<a href="'.ADMIN_URL.'/groups/index.php">'.$MENU['GROUPS'].'</a>';
	$access_count = 1;
}

// Insert section names and descriptions
$tpl->set_var(array(
	'PAGES' => $MENU['PAGES'],
	'MEDIA' => $MENU['MEDIA'],
	'ADDONS' => $MENU['ADDONS'],
	'ACCESS' => $MENU['ACCESS'],
	'PREFERENCES' => $MENU['PREFERENCES'],
	'SETTINGS' => $MENU['SETTINGS'],
	'ADMINTOOLS' => $MENU['ADMINTOOLS'],
	'HOME_OVERVIEW' => $OVERVIEW['START'],
	'PAGES_OVERVIEW' => $OVERVIEW['PAGES'],
	'MEDIA_OVERVIEW' => $OVERVIEW['MEDIA'],
	'ADDONS_OVERVIEW' => $addons_overview,
	'ACCESS_OVERVIEW' => $access_overview,
	'PREFERENCES_OVERVIEW' => $OVERVIEW['PREFERENCES'],
	'SETTINGS_OVERVIEW' => $OVERVIEW['SETTINGS'],
	'ADMINTOOLS_OVERVIEW' => $OVERVIEW['ADMINTOOLS']
	)
);

// Parse template object
$tpl->parse('main', 'main_block', false);
$tpl->pparse('output', 'page');

// Print admin footer
$admin->print_footer();

?>