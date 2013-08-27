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

$backend  = CAT_Backend::getInstance('Access', 'groups');
$users    = CAT_Users::getInstance();
$addons   = CAT_Helper_Addons::getInstance();
$tpl_data = array();

global $parser;

// =========================== 
// ! Add permissions to Dwoo   
// =========================== 
$tpl_data['permissions']['GROUPS_ADD']		= $users->checkPermission('Access','groups_add')	? true : false;
$tpl_data['permissions']['GROUPS_MODIFY']	= $users->checkPermission('Access','groups_modify')	? true : false;
$tpl_data['permissions']['GROUPS_DELETE']	= $users->checkPermission('Access','groups_delete')	? true : false;
$tpl_data['permissions']['USERS']			= $users->checkPermission('Access','users')			? true : false;

$tpl_data['templates']		= $addons->get_addons( DEFAULT_TEMPLATE , 'template' );
$tpl_data['languages']		= $addons->get_addons( DEFAULT_LANGUAGE , 'language' );
$tpl_data['modules']		= $addons->get_addons( -1 , 'module', 'page' );
$tpl_data['admintools']		= $addons->get_addons( -1 , 'module', 'tool' );
$tpl_data['groups']			= $users->get_groups('','',false);
$tpl_data['members']        = NULL;

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_groups_index', $tpl_data);

// ====================== 
// ! Print admin footer   
// ====================== 
$backend->print_footer();

?>