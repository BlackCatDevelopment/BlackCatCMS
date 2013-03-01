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
	include(CAT_PATH . '/framework/class.secure.php');
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

require_once ( CAT_PATH . '/framework/class.admin.php' );
$admin		= new admin('Pages', 'pages_add', false);

header('Content-type: application/json');

if ( !$admin->get_permission('pages_add') )
{
	$ajax	= array(
		'message'	=>  $admin->lang->translate('You do not have the permission to add a page.'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// ================================= 
// ! Add permissions to $data_dwoo   
// ================================= 
$permission['pages']			= $admin->get_permission('pages') ? true : false;
$permission['pages_add']		= $admin->get_permission('pages_add') ? true : false;
$permission['pages_add_l0']		= $admin->get_permission('pages_add_l0') ? true : false;
$permission['pages_modify']		= $admin->get_permission('pages_modify') ? true : false;
$permission['pages_delete']		= $admin->get_permission('pages_delete') ? true : false;
$permission['pages_settings']	= $admin->get_permission('pages_settings') ? true : false;
$permission['pages_intro']		= ( $admin->get_permission('pages_intro') != true || INTRO_PAGE != 'enabled' ) ? false : true;

$pg = CAT_Pages::getInstance(-1,$permission);
$dropdown_list = $pg->pages_list( 0 , 0 );

// ============================================= 
// ! Add result_array to the template variable   
// ============================================= 
$ajax	= array(
		'parent_list'	=> $dropdown_list,
		'success'		=> true
);

// ==================== 
// ! Return values 	
// ==================== 

print json_encode( $ajax );
exit();

?>