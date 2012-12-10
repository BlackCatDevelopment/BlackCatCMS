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
$admin = new admin('Pages', 'pages', false);

// Set header for json
header('Content-type: application/json');

if ( !$admin->get_permission('pages') )
{
	$ajax['message']	= $admin->lang->translate('You don\'t have the permission to proceed this action.');
	$ajax['success']	= false;

	print json_encode( $ajax );
	exit();
}


// =============== 
// ! Get page id   
// =============== 
if ( ( !is_array( $admin->get_post('pageid') )		&& ( $admin->get_post('table') == 'pages' ) ) ||
	 ( !is_array( $admin->get_post('sectionid') )	&& ( $admin->get_post('table') == 'sections' ) ) ||
	 ( $admin->get_post('table') != 'pages' && $admin->get_post('table') != 'sections' ) )
{
	$ajax['message']	= $admin->lang->translate('You send corrupt data.' );
	$ajax['success']	= false;

	print json_encode( $ajax );
	exit();
}

// ======================= 
// ! Reorder the section   
// ======================= 
require(LEPTON_PATH . '/framework/class.order.php');

$id_field	= ( $admin->get_post('table') == 'pages' ) ? 'page_id' : 'section_id';
$new_array	= ( $admin->get_post('table') == 'pages' ) ? $admin->get_post('pageid') : $admin->get_post('sectionid');

foreach ( $new_array as $index => $element)
{
	$new_array[$index]	= intval( str_replace( 'pageid_' ,'', str_replace('sectionid_' ,'', $element) ) );
}


$order		= new order(TABLE_PREFIX.$admin->get_post('table'), 'position', $id_field);
$reorder	= $order->reorder_by_array( $new_array );

if ( $reorder === true )
{
	$ajax['message']	= $admin->lang->translate('Page re-ordered successfully.');
	$ajax['success']	= true;

	print json_encode( $ajax );
	exit();
}
else
{
	$ajax['message']	= $admin->lang->translate( $reorder.': Error re-ordering page');
	$ajax['success']	= false;

	print json_encode( $ajax );
	exit();
}

?>