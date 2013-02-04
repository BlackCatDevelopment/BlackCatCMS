<?php

/**
 * This file is part of LEPTON2 Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author			LEPTON2 Project
 * @copyright		2012, LEPTON2 Project
 * @link			http://lepton2.org
 * @license			http://www.gnu.org/licenses/gpl.html
 * @license_terms	please see LICENSE and COPYING files in your package
 *
 */
 

// include class.secure.php to protect this file and the whole CMS!
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
// end include class.secure.php

// =================================================== 
// ! Include the class.admin.php and WB functions file
// =================================================== 
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

$pg = CAT_Pages::getInstance($permission);
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