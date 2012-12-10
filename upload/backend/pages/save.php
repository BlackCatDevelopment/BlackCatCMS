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
	include(LEPTON_PATH . '/framework/class.secure.php');
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

require_once( LEPTON_PATH . '/framework/class.admin.php');
$admin			= new admin('Pages', 'pages_modify');
$page_id		= intval( $admin->get_post('page_id') );
$section_id		= intval( $admin->get_post('section_id') );

// Get page & section id
if( $page_id == '' || !is_numeric($page_id) || $section_id == '' || !is_numeric($section_id) )
{
	header("Location: index.php");
	exit(0);
}

// Get perms
$results			= $database->query( 'SELECT `admin_groups`,`admin_users` FROM `' . TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $page_id );
$results_array		= $results->fetchRow( MYSQL_ASSOC );
$old_admin_groups	= explode(',', str_replace('_', '', $results_array['admin_groups']));
$old_admin_users	= explode(',', str_replace('_', '', $results_array['admin_users']));
$in_old_group		= false;
foreach( $admin->get_groups_id() as $cur_gid )
{
	if (in_array($cur_gid, $old_admin_groups))
	{
		$in_old_group	= true;
	}
}
if ( ( !$in_old_group ) && !is_numeric( array_search( $admin->get_user_id(), $old_admin_users ) ) )
{
	$admin->print_error('You do not have permissions to modify this page');
}
// Get page module
$module = $database->get_one( 'SELECT `module` FROM `' . TABLE_PREFIX . 'sections` WHERE `page_id`=' . $page_id . ' AND `section_id`=' . $section_id );
if ( !$module )
{
	$admin->print_error( $database->is_error() ? $database->get_error() : 'Page not found' );
}

// Update the pages table
$now	= time();
$sql	 = 'UPDATE `' . TABLE_PREFIX . 'pages` SET ';
$sql	.= '`modified_when` = ' . $now . ', `modified_by` = ' . $admin->get_user_id() . ' ';
$sql	.= 'WHERE `page_id` = ' . $page_id;
$database->query($sql);

// Include the modules saving script if it exists
if ( file_exists( LEPTON_PATH . '/modules/' . $module . '/save.php' ) )
{
	include_once( LEPTON_PATH . '/modules/' . $module . '/save.php' );
}
// Check if there is a db error, otherwise say successful
if($database->is_error())
{
	$admin->print_error( $database->get_error(), ADMIN_URL . '/pages/modify.php?page_id=' . $page_id );
}
else
{
	$admin->print_success( 'Page saved successfully', ADMIN_URL . '/pages/modify.php?page_id=' . $page_id );
}

?>