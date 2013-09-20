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

$backend = CAT_Backend::getInstance('Pages','pages_delete',false);
$users   = CAT_Users::getInstance();
$val     = CAT_Helper_Validate::getInstance();

header('Content-type: application/json');

// Get perms
if ( ! $users->checkPermission('pages','pages_delete',false) )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You do not have the permission to delete a page.'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$page_id        = $val->sanitizePost('page_id','numeric');

// Get page id
if (!$page_id)
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You sent an invalid value'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

if ( !CAT_Helper_Page::getPagePermission( $page_id, 'admin' ) )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('You do not have the permission to restore this page.'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

// Find out more about the page
$page = CAT_Helper_Page::properties($page_id);

if (!$page)
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('Page not found'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$visibility		= $page['visibility'];

if ( CAT_Registry::get('PAGE_TRASH') !== 'false' )
{
	if ( $visibility == 'deleted' )
	{
		// Function to change all child pages visibility to deleted
		function restore_subs($parent = 0) {
			global $backend;
			// Query pages
			$query_menu = $backend->db()->query(sprintf(
                "SELECT page_id FROM `%spages` WHERE parent = '%d' ORDER BY position ASC",
                CAT_TABLE_PREFIX,$parent
            ));
			// Check if there are any pages to show
			if($query_menu->numRows() > 0)
			{
				// Loop through pages
				while ( $page = $query_menu->fetchRow(MYSQL_ASSOC) )
				{
					// Update the page visibility to 'deleted'
					$backend->db()->query(sprintf(
                        "UPDATE `%spages` SET visibility = 'public' WHERE page_id = '%d' LIMIT 1",
                        CAT_TABLE_PREFIX,$page['page_id']
                    ));
					// Run this function again for all sub-pages
					restore_subs( $page['page_id'] );
				}
			}
		}
		// Update the page visibility to 'deleted'
		$database->query(sprintf(
            "UPDATE `%spages` SET visibility = 'public' WHERE page_id = '%d.' LIMIT 1",
            CAT_TABLE_PREFIX,$page_id
        ));
		// Run trash subs for this page
		restore_subs( $page_id );
	}
}

// Check if there is a db error, otherwise say successful
if ( $backend->db()->is_error() )
{
	$ajax	= array(
		'message'	=> $backend->db()->get_error(),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
else
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate('Page restored successfully'),
		'success'	=> true
	);
	print json_encode( $ajax );
	exit();
}
exit();
?>