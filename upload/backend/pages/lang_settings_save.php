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

$users   = CAT_Users::getInstance();
$val     = CAT_Helper_Validate::getInstance();
$backend = CAT_Backend::getInstance('Pages', 'pages_settings');

if (!$users->checkPermission('pages','pages_settings')){
	$backend->print_error( 'You do not have permissions to modify this page' );
}

// ===============
// ! Get page id
// ===============
$page_id = $val->sanitizePost('page_id','numeric');
if ( ! $page_id )
{
	$backend->print_error( 'Missing page ID!' );
}

// Include the WB functions file
require_once( CAT_PATH . '/framework/functions.php' );

// get form data
$language			= $val->sanitizePost('map_language',NULL,true);
$link_page_id       = $val->sanitizePost('link_page_id','numeric',true);

if ( ! $link_page_id )
{
	$backend->print_error('No page to link to!', CAT_ADMIN_URL . '/pages/lang_settings.php?page_id=' . $page_id );
}

// =====================================
// ! check if linked page has given lang
// =====================================
$page = CAT_Helper_Page::getPage($page_id);
if ( $page['language'] !== $language )
{
    $backend->print_error("The page you've chosen does not have the right language! (".$page['language']." !== $language)");
}

// ===============================================
// ! check if there's already a page for this lang
// ===============================================
$results = $backend->db()->query('SELECT * FROM `' . CAT_TABLE_PREFIX . 'page_langs` WHERE page_id = "' . $page_id . '" AND lang = "'.$language.'"');
if ( $backend->db()->is_error() )
{
	$backend->print_error( $backend->db()->get_error() );
}
if ( $results->numRows() )
{
    $backend->print_error( 'There is already a page for this language!' );
}


// =========================================
// ! Update page settings in the pages table
// =========================================
$backend->db()->query(sprintf(
    'REPLACE INTO `%spage_langs` VALUES ( "%d", "%s", "%d" )',
    CAT_TABLE_PREFIX, $page_id, $language, $link_page_id
));

if ( $backend->db()->is_error() )
{
	$backend->print_error($backend->db()->get_error(), CAT_ADMIN_URL . '/pages/lang_settings.php?page_id=' . $page_id );
}

// Check if there is a db error, otherwise say successful
if ( $backend->db()->is_error() )
{
	$backend->print_error($backend->db()->get_error(), CAT_ADMIN_URL . '/pages/lang_settings.php?page_id=' . $page_id );
}
else
{
	$backend->print_success('Page saved successfully', CAT_ADMIN_URL . '/pages/lang_settings.php?page_id=' . $page_id );
}

// ====================== 
// ! Print admin footer   
// ====================== 
$backend->print_footer();

?>