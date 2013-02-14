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
	include(CAT_PATH.'/framework/class.secure.php');
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

require_once(CAT_PATH.'/framework/class.admin.php');
$admin = new admin('Access', 'groups');

require CAT_PATH.'/framework/CAT/Helper/Addons.php';
$addons = new CAT_Helper_Addons();

// =========================== 
// ! Add permissions to Dwoo   
// =========================== 
$data_dwoo['permissions']['GROUPS_ADD']		= $admin->get_permission('groups_add')		? true : false;
$data_dwoo['permissions']['GROUPS_MODIFY']	= $admin->get_permission('groups_modify')	? true : false;
$data_dwoo['permissions']['GROUPS_DELETE']	= $admin->get_permission('groups_delete')	? true : false;
$data_dwoo['permissions']['USERS']			= $admin->get_permission('users')			? true : false;


// =========================================================================== 
// ! Create the controller, it is reusable and can render multiple templates 	
// =========================================================================== 
global $parser;

// $items	= $admin->get_controller('Pages')->get_linked_by_language($page_id);

$data_dwoo['templates']			= $addons->get_addons( DEFAULT_TEMPLATE , 'template' );
$data_dwoo['languages']			= $addons->get_addons( DEFAULT_LANGUAGE , 'language' );
$data_dwoo['modules']			= $addons->get_addons( -1 , 'module', 'page' );
$data_dwoo['admintools']		= $addons->get_addons( -1 , 'module', 'tool' );
$data_dwoo['groups']			= $admin->users->get_groups('','',false);

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_groups_index.lte', $data_dwoo);

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>