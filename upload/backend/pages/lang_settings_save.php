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
require_once(LEPTON_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages_settings');

if (!$admin->get_permission('pages_settings')){
	header("Location: index.php");
	exit(0);
}

// =============== 
// ! Get page id   
// =============== 
if ( !is_numeric( $admin->get_post('page_id') ) )
{
	header("Location: index.php");
	exit(0);
}
else
{
	$page_id = $admin->get_post('page_id');
}


// Include the WB functions file
require_once( LEPTON_PATH . '/framework/functions.php' );


// get form data
$language			= $admin->get_post_escaped('map_language');
$page               = $admin->get_post_escaped('link_page_id');

if ( !is_numeric( $page ) )
{
	header("Location: index.php");
	exit(0);
}

// =====================================
// ! check if linked page has given lang
// =====================================
$results		= $database->query('SELECT * FROM `' . TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $page);
$results_array	= $results->fetchRow( MYSQL_ASSOC );

if ( $results_array['language'] !== $language )
{
    $admin->print_error("The page you've chosen does not have the right language! (".$results_array['language']." !== $language");
}


// ===============================================
// ! check if there's already a page for this lang
// ===============================================
$results		= $database->query('SELECT * FROM `' . TABLE_PREFIX . 'page_langs` WHERE page_id = "' . $page_id . '" AND lang = "'.$language.'"');

if ( $database->is_error() )
{
	$admin->print_error( $database->get_error() );
}
if ( $results->numRows() )
{
    $admin->print_error( 'There is already a page for this language!' );
}


// =========================================
// ! Update page settings in the pages table
// =========================================

$sql	= 'REPLACE INTO `' . TABLE_PREFIX . 'page_langs` VALUES ( ';
$sql	.= '"'.$page_id.'", "'.$language.'", "'.$page.'" ) ';

$database->query($sql);

if ( $database->is_error() )
{
	$admin->print_error($database->get_error(), ADMIN_URL . '/pages/lang_settings.php?page_id=' . $page_id );
}

// Check if there is a db error, otherwise say successful
if ( $database->is_error() )
{
	$admin->print_error($database->get_error(), ADMIN_URL . '/pages/lang_settings.php?page_id=' . $page_id );
}
else
{
	$admin->print_success('Page settings saved successfully', ADMIN_URL . '/pages/lang_settings.php?page_id=' . $page_id );
}

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>