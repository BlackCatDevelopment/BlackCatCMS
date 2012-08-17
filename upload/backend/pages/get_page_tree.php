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
if (defined('LEPTON_PATH')) {	
	include(LEPTON_PATH.'/framework/class.secure.php'); 
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

// =================================================== 
// ! Include the class.admin.php and WB functions file
// =================================================== 
require_once(LEPTON_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages');

if (!$admin->get_permission('pages'))
{
	header("Location: index.php");
	exit(0);
}


// =============== 
// ! Get page id   
// =============== 
if ( !is_numeric( $admin->get_get('page_id') ) )
{
	header("Location: index.php");
	exit(0);
}
else
{
	$page_id = $admin->get_get('page_id');
}

global $parser;
$data_dwoo = array();

$permission		= array(
			'pages'				=> $admin->get_permission('pages')			? true : false,
			'pages_add'			=> $admin->get_permission('pages_add')		? true : false,
			'pages_add_l0'		=> $admin->get_permission('pages_add_l0')	? true : false,
			'pages_modify'		=> $admin->get_permission('pages_modify')	? true : false,
			'pages_delete'		=> $admin->get_permission('pages_delete')	? true : false,
			'pages_settings'	=> $admin->get_permission('pages_settings')	? true : false,
			'pages_intro'		=> ( $admin->get_permission('pages_intro') != true || INTRO_PAGE != 'enabled' ) ? false : true
);

// ================================================================ 
// ! Include class.pages.php to simply get all pages for dropdown   
// ================================================================ 
require_once(LEPTON_PATH . '/framework/class.pages.php');
$pages = new pages( $permission );

//$pages->current_page['id']					= $page_id;
//$pages->current_page['parent']				= $results_array['parent'];

$data_dwoo['child_pages']		= $pages->make_list( $page_id );


// ============================================= 
// ! Include template info file (if it exists)   
// ============================================= 
//$data_dwoo['TEMPLATE_MENU'] = $pages->get_template_menus( $results_array['template'], $results_array['menu'] );


// ==================== 
// ! Parse the header 	
// ==================== 
$parser->output('backend_pages_get_page_tree.lte', $data_dwoo);

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>