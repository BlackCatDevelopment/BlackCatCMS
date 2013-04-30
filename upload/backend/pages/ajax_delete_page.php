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

// Include the LEPTON functions file
require_once(CAT_PATH . '/framework/functions.php');

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
		'message'	=> $backend->lang()->translate('You do not have permissions to modify this page'),
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

// Check if we should delete it or just set the visibility to 'deleted'
if ( PAGE_TRASH != 'disabled' && $visibility != 'deleted' )
{
	$ajax_status	= 1;
	// Page trash is enabled and page has not yet been deleted
    CAT_Helper_Page::deletePage($page_id,true);
} else {
	$ajax_status	= 0;
    CAT_Helper_Page::deletePage($page_id);
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
		'message'	=> $backend->lang()->translate('Page(s) deleted successfully'),
		'status'	=> $ajax_status,
		'success'	=> true
	);
	print json_encode( $ajax );
	exit();
}
exit();
?>