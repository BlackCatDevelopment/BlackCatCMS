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
 * @version         $Id$
 *
 */
 

// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {
	include(LEPTON_PATH.'/framework/class.secure.php');
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
require_once(LEPTON_PATH . '/framework/class.admin.php');
$admin = new admin('Pages', 'pages');

if ( !$admin->get_permission('pages') )
{
	header("Location: index.php");
	exit(0);
}


// =============== 
// ! Get page id   
// =============== 
if ( ( !is_array( $admin->get_get('pageid') )		&& ( $admin->get_get('table') == 'pages' ) ) ||
	 ( !is_array( $admin->get_get('sectionid') )	&& ( $admin->get_get('table') == 'sections' ) ) ||
	 ( $admin->get_get('table') != 'pages' && $admin->get_get('table') != 'sections' ) )
{
	header("Location: index.php");
	exit(0);
}

if ( $admin->get_permission('pages') )
{
		// ======================= 
		// ! Reorder the section   
		// ======================= 
		require(LEPTON_PATH . '/framework/class.order.php');

		$id_field	= ( $admin->get_get('table') == 'pages' ) ? 'page_id' : 'section_id';
		$new_array	= ( $admin->get_get('table') == 'pages' ) ? $admin->get_get('pageid') : $admin->get_get('sectionid');

		$order		= new order(TABLE_PREFIX.$admin->get_get('table'), 'position', $id_field);
		$reorder	= $order->reorder_by_array( $new_array );
}

if ( $reorder === true )
{
	$admin->print_success('Page re-ordered successfully');
}
else
{
	$admin->print_error( $reorder.': Error re-ordering page');
}

?>