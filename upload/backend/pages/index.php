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

require_once( LEPTON_PATH . '/framework/class.admin.php' );
$admin = new admin('Start', 'start');

header( 'Location: ' . ADMIN_URL . '/start/index.php?leptoken=' . $admin->getToken() );
exit(0);

// end include class.secure.php
/*
require_once(LEPTON_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages');
// Include the WB functions file
require_once(LEPTON_PATH.'/framework/functions.php');

/*
urlencode function and rawurlencode are mostly based on RFC 1738.
However, since 2005 the current RFC in use for URIs standard is RFC 3986.
Here is a function to encode URLs according to RFC 3986.
*/
/*
function url_encode($string) {
	$string = html_entity_decode($string,ENT_QUOTES,'UTF-8');
	$entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
	$replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
	return str_replace($entities, $replacements, rawurlencode($string));
}

// ================================= 
// ! Add permissions to $data_dwoo   
// ================================= 
$data_dwoo['permission']['pages']			= $admin->get_permission('pages') ? true : false;
$data_dwoo['permission']['pages_add']		= $admin->get_permission('pages_add') ? true : false;
$data_dwoo['permission']['pages_add_l0']	= $admin->get_permission('pages_add_l0') ? true : false;
$data_dwoo['permission']['pages_modify']	= $admin->get_permission('pages_modify') ? true : false;
$data_dwoo['permission']['pages_delete']	= $admin->get_permission('pages_delete') ? true : false;
$data_dwoo['permission']['pages_settings']	= $admin->get_permission('pages_settings') ? true : false;
$data_dwoo['permission']['pages_intro']		= ( $admin->get_permission('pages_intro') != true || INTRO_PAGE != 'enabled' ) ? false : true;


if ( $data_dwoo['permission']['pages'] == true )
{
	// Will be reviewed and optimized!
	require_once(LEPTON_PATH . '/framework/class.pages.php');
	$pages = new pages( $data_dwoo['permission'] );

	$data_dwoo['DISPLAY_MENU_LIST']				= MULTIPLE_MENUS	!= false ? true : false;
	$data_dwoo['DISPLAY_LANGUAGE_LIST']			= PAGE_LANGUAGES	!= false ? true : false;
	$data_dwoo['DISPLAY_SEARCHING']				= SEARCH			!= false ? true : false;

	// ========================== 
	// ! Get info for pagesTree   
	// ========================== 
	// list of first level of pages
	$data_dwoo['pages']				= $pages->make_list( 0, true );
	//$data_dwoo['pages']				= $pages->get_sections();
	$data_dwoo['pages_editable']	= $pages->pages_editable;
}

// print page
$parser->output( 'backend_pages_index.lte', $data_dwoo );

// Print admin
$admin->print_footer();
*/
?>