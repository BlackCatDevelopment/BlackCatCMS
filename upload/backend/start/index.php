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

// exec initial_page
if(file_exists(CAT_PATH .'/modules/initial_page/classes/c_init_page.php') && isset($_SESSION['USER_ID'])) {
	require_once (CAT_PATH .'/modules/initial_page/classes/c_init_page.php');
	$ins = new c_init_page($database, $_SESSION['USER_ID'], $_SERVER['SCRIPT_NAME']);
}

$user  = CAT_Users::getInstance();
$lang  = CAT_Helper_I18n::getInstance();
$widget = CAT_Helper_Widget::getInstance();
$addonh = CAT_Helper_Addons::getInstance();

// this will redirect to the login page if the permission is not set
$user->checkPermission('start','start',false);

// ================================================ 
// ! Check if installation directory still exists   
// ================================================ 
if( file_exists(CAT_PATH.'/install/') ) {
	// Check if user is part of Adminstrators group
	if( in_array (1, $user->get_groups_id() ) )
	{
		/** 
		 *	Try to delete it - it's still not needed anymore.
		 */
		if (function_exists('rm_full_dir') ) {
			rm_full_dir(CAT_PATH.'/install/');
		}
	}
}

// =========================================================================== 
// ! Create the controller, it is reusable and can render multiple templates 	
// =========================================================================== 
global $parser;

$tpl_data = array();

// ===================================================== 
// ! Insert permission values into the template object   
// ===================================================== 
foreach(
    array(
        'media',
        'addons',
        'access',
        'settings',
        'admintools',
    ) as $item
) {
    $tpl_data['sections'][$item]['permission']			              = $user->checkPermission($item,$item,false);
    $tpl_data['sections'][$item]['name']				              = $item;
    $tpl_data['sections'][$item]['title']				              = $lang->translate(ucfirst($item));
    $tpl_data['sections'][$item]['description']			              = ( isset($OVERVIEW[strtoupper($item)]) ? $OVERVIEW[strtoupper($item)] : NULL );
}

foreach(
    array(
        'modules',
        'templates',
        'languages',
    ) as $item
) {
    $tpl_data['sections']['addons']['subpages'][$item]['permission']  = $user->checkPermission('addons',$item,false);
    $tpl_data['sections']['addons']['subpages'][$item]['name']        = $item;
    $tpl_data['sections']['addons']['subpages'][$item]['title']       = $lang->translate(ucfirst($item));
    $tpl_data['sections']['addons']['subpages'][$item]['description'] = ( isset($OVERVIEW[strtoupper($item)]) ? $OVERVIEW[strtoupper($item)] : NULL );
}

foreach(
    array(
        'users',
        'groups',
    ) as $item
) {
    $tpl_data['sections']['access']['subpages'][$item]['permission']  = $user->checkPermission('access',$item,false);
    $tpl_data['sections']['access']['subpages'][$item]['name']        = $item;
    $tpl_data['sections']['access']['subpages'][$item]['title']       = $lang->translate(ucfirst($item));
    $tpl_data['sections']['access']['subpages'][$item]['description'] = ( isset($OVERVIEW[strtoupper($item)]) ? $OVERVIEW[strtoupper($item)] : NULL );

}

include CAT_PATH.'/framework/class.admin.php';
$admin = new admin('start','start');

// ============
// ! Widgets
// ============
$widgets = $widget->getWidgets();

foreach( $widgets as $widget )
{
    $path = pathinfo($widget,PATHINFO_DIRNAME);
    $info = $content = NULL;
    if ( file_exists($path.'/info.php') )
    {
        $info = $addonh->checkInfo($path);
    }
    if ( file_exists($path.'/languages/'.LANGUAGE.'.php') )
    {
        $admin->lang->addFile(LANGUAGE.'.php', $path.'/languages/');
    }
    ob_start();
        include($widget);
        $content = ob_get_contents();
    ob_clean();
    $tpl_data['widgets'][] = array_merge( $info, array('content'=>$content) );
}

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_start_index.lte', $tpl_data);

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();


?>