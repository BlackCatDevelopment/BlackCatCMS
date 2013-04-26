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

include_once(CAT_PATH . '/framework/functions.php');

$backend = CAT_Backend::getInstance('Media','media',false);
$users   = CAT_Users::getInstance();
$val     = CAT_Helper_Validate::getInstance();

header('Content-type: application/json');

$ajax['folder_path'] = $val->sanitizePost('folder_path');

if ( $ajax['folder_path'] == '' || !$users->checkPermission('Media','media_create') )
{
	$ajax	= array(
		'message'	=> 'You don\'t have the permission to create a folder. Check your system settings.',
		'created'	=> false
	);
	print json_encode( $ajax );
	exit();
}
else
{
	// ================================ 
	// ! Check if folder is writeable   
	// ================================ 
	if ( is_writable(CAT_PATH . $ajax['folder_path']) )
	{
		$create_folder		= CAT_PATH . $ajax['folder_path'] .'/' . $backend->lang()->translate('New folder');
		$counter			= 1;
		while ( is_dir($create_folder) )
		{
			$create_folder	= CAT_PATH . $ajax['folder_path'] . '/' . $backend->lang()->translate('New folder') . ' ' . $counter;
			$counter++;
		}
		// =====================================================
		// ! Try to create new folder; also creates an index.php
		// =====================================================
		if(CAT_Helper_Directory::createDirectory($create_folder,NULL,true))
		{
			CAT_Helper_Directory::setPerms($create_folder);
			if( is_writable($create_folder) )
			{
				$ajax['message']	= $backend->lang()->translate( 'Folder created successfully' );
				$ajax['created']	= true;
			}
			else {
				$ajax['message']	= $backend->lang()->translate( 'Unable to write to the target directory' );
				$ajax['created']	= false;
			}
		}
		else {
			$ajax['message'] = $backend->lang()->translate( 'Unable to write to the target directory' );
			$ajax['created']	= false;
		}
	}
	else {
		$ajax['message'] = $backend->lang()->translate( 'Unable to write to the target directory' );
		$ajax['created']	= false;
	}
	print json_encode( $ajax );
}

?>