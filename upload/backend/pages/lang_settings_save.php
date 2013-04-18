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
    if (defined('CAT_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
    include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));    $dir = $_SERVER['DOCUMENT_ROOT'];
    $inc = false;
    foreach ($subs as $sub) {
        if (empty($sub)) continue; $dir .= '/'.$sub;
        if (file_exists($dir.'/framework/class.secure.php')) {
            include($dir.'/framework/class.secure.php'); $inc = true;    break;
	}
	}
    if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

$users = CAT_Users::getInstance();
$val   = CAT_Helper_Validate::getInstance();

// ===================================================
// ! Include the class.admin.php and WB functions file
// =================================================== 
require_once(CAT_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages_settings');

if (!$users->checkPermission('pages','pages_settings')){
	header("Location: index.php");
	exit(0);
}

$page_id = $val->sanitizePost('page_id','numeric');

// =============== 
// ! Get page id   
// =============== 
if ( ! $page_id )
{
	header("Location: index.php");
	exit(0);
}

// Include the WB functions file
require_once( CAT_PATH . '/framework/functions.php' );


// get form data
$language			= $val->sanitizePost('map_language',NULL,true);
$page               = $val->sanitizePost('link_page_id','numeric',true);

if ( ! $page )
{
	header("Location: index.php");
	exit(0);
}

// =====================================
// ! check if linked page has given lang
// =====================================
$results		= $users->db()->query('SELECT * FROM `' . CAT_TABLE_PREFIX . 'pages` WHERE `page_id` = ' . $page);
$results_array	= $results->fetchRow( MYSQL_ASSOC );

if ( $results_array['language'] !== $language )
{
    $admin->print_error("The page you've chosen does not have the right language! (".$results_array['language']." !== $language");
}


// ===============================================
// ! check if there's already a page for this lang
// ===============================================
$results		= $users->db()->query('SELECT * FROM `' . CAT_TABLE_PREFIX . 'page_langs` WHERE page_id = "' . $page_id . '" AND lang = "'.$language.'"');

if ( $users->db()->is_error() )
{
	$admin->print_error( $users->db()->get_error() );
}
if ( $results->numRows() )
{
    $admin->print_error( 'There is already a page for this language!' );
}


// =========================================
// ! Update page settings in the pages table
// =========================================

$sql	= 'REPLACE INTO `' . CAT_TABLE_PREFIX . 'page_langs` VALUES ( ';
$sql	.= '"'.$page_id.'", "'.$language.'", "'.$page.'" ) ';

$users->db()->query($sql);

if ( $users->db()->is_error() )
{
	$admin->print_error($users->db()->get_error(), CAT_ADMIN_URL . '/pages/lang_settings.php?page_id=' . $page_id );
}

// Check if there is a db error, otherwise say successful
if ( $users->db()->is_error() )
{
	$admin->print_error($users->db()->get_error(), CAT_ADMIN_URL . '/pages/lang_settings.php?page_id=' . $page_id );
}
else
{
	$admin->print_success('Page saved successfully', CAT_ADMIN_URL . '/pages/lang_settings.php?page_id=' . $page_id );
}

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>