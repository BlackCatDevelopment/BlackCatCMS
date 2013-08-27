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

$backend = CAT_Backend::getInstance('Pages', 'pages', false);
$users   = CAT_Users::getInstance();
$val     = CAT_Helper_Validate::getInstance();

// Set header for json
header('Content-type: application/json');

if ( !$users->checkPermission('Pages','pages') )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You do not have the permission to proceed this action'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// ===============
// ! Get page id   
// ===============
$pages = $val->sanitizePost('page_id');
$sect  = $val->sanitizePost('sectionid');
$table = $val->sanitizePost('table');

if (
       (!is_array($pages) && ($table == 'pages'))
    || (!is_array($sect)  && ($table == 'sections'))
    || ($table != 'pages' && $table != 'sections' )
) {
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You sent invalid data'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// ======================= 
// ! Reorder the section   
// ======================= 
require(CAT_PATH . '/framework/class.order.php');

$id_field	= ( $table == 'pages' ) ? 'page_id' : 'section_id';
$new_array	= ( $table == 'pages' ) ? $pages    : $sect;

foreach ( $new_array as $index => $element)
{
	$new_array[$index]	= intval( str_replace( 'pageid_' ,'', str_replace('sectionid_' ,'', $element) ) );
}

$order		= new order(CAT_TABLE_PREFIX.$table, 'position', $id_field);
$reorder	= $order->reorder_by_array( $new_array );

if ( $reorder === true )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('Re-ordered successfully'),
		'success'	=> true
	);
	print json_encode( $ajax );
	exit();
}
else
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate( $reorder.': Error re-ordering page'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
exit();
?>