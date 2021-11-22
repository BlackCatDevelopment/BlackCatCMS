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
 *   @copyright       2016, Black Cat Development
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'framework/class.secure.php')) {
		include($root.'framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

$user    = CAT_Users::getInstance();

// if the user has access to the backend dashboard only, redirect to frontend
if(!$user->is_root() && CAT_Helper_Validate::getInstance()->fromSession('SYSTEM_PERMISSIONS') == 0) {
    $page = $user->get_initial_page();
    if($page) {
        header('Location: '.$page);
    } else {
        if(headers_sent()) {
            header('Refresh: 0; URL='.CAT_URL."/index.php\n\n", true, 302);
        } else {
            header('Location: '.CAT_URL.'/index.php');
        }
    }
    exit;
}

global $parser;

$backend = CAT_Backend::getInstance('start');
$lang    = CAT_Helper_I18n::getInstance();
$widget  = CAT_Helper_Widget::getInstance();

// this will redirect to the login page if the permission is not set
$user->checkPermission('start','start',false);

// ================================================ 
// ! Check if installation directory still exists   
// ================================================ 
if( file_exists(CAT_PATH.'/install/') )
    CAT_Helper_Directory::removeDirectory(CAT_PATH.'/install/');

// exec initial_page
if($val->sanitizeGet('initial') || ! $user->checkPermission('start','start') === true )
{
    $page = $user->get_initial_page();
    if ( $page )
        header( 'Location: '.$page );
}

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
    $tpl_data['sections'][$item]['permission']  = $user->checkPermission($item,$item,false);
    $tpl_data['sections'][$item]['name']        = $item;
    $tpl_data['sections'][$item]['title']       = $lang->translate(ucfirst($item));
    $tpl_data['sections'][$item]['description'] = ( isset($OVERVIEW[strtoupper($item)]) ? $OVERVIEW[strtoupper($item)] : NULL );
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

// ============
// ! Widgets
// ============
$tpl_data['dashboard'] = CAT_Helper_Dashboard::renderDashboard('global',false);

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_start_index', $tpl_data);

// ====================== 
// ! Print admin footer   
// ====================== 
$backend->print_footer();
