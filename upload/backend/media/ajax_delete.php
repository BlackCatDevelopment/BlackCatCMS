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

// =================================
// ! Include the WB functions file   
// ================================= 
include_once(CAT_PATH . '/framework/functions.php');

$backend = CAT_Backend::getInstance('Media','media',false);
$users   = CAT_Users::getInstance();
$val     = CAT_Helper_Validate::getInstance();

header('Content-type: application/json');

$ajax['file']			= $val->sanitizePost('file');
$ajax['file_path']		= $val->sanitizePost('file_path');

if (  $ajax['file'] == '' ||  $ajax['file_path'] == '' || $users->checkPermission('media','media_delete') !== true )
{
	$ajax	= array(
		'message'	=> 'You don\'t have the permission to delete this file. Check your system settings.',
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
else
{
	// ============================ 
	// ! Try to delete file/folder
	// ============================ 
	$link	= CAT_PATH .  $ajax['file_path'] . '/' .  $ajax['file'];
	if ( file_exists($link) )
	{
		$kind	= is_dir($link) ? 'dir' : 'file';
		if ( is_dir($link) && CAT_Helper_Directory::removeDirectory( $link ) )
		{
			$ajax['message']		= $backend->lang()->translate( 'Folder deleted successfully' );
			$ajax['success']		= true;
		}
        elseif ( is_file($link) && unlink($link) )
        {
            $ajax['message']		= $backend->lang()->translate( 'File deleted successfully' );
            $ajax['success']		= true;
        }
		else
		{
			$ajax['message']		= $kind == 'dir' ? $backend->lang()->translate( 'Cannot delete the selected directory' ) : $backend->lang()->translate( 'Cannot delete the selected file' );
			$ajax['success']		= false;
		}
	}
	else
	{
		$ajax['message']	= $backend->lang()->translate( 'Couldn\'t find the folder or file' );
		$ajax['success']	= false;
	}
	print json_encode( $ajax );
}

?>