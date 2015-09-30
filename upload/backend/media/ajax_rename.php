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

include_once(CAT_PATH . '/framework/functions.php');

$backend = CAT_Backend::getInstance('Media','media',false);
$users   = CAT_Users::getInstance();
$val     = CAT_Helper_Validate::getInstance();

header('Content-type: application/json');

// ==================
// ! Get user input   
// ================== 
$file_path		= CAT_Helper_Directory::sanitizePath($val->strip_slashes($val->sanitizePost('file_path')));
$rename_file 	= $val->strip_slashes($val->sanitizePost('rename_file'));
$new_name		= trim( $val->strip_slashes($val->sanitizePost('new_name')) );
$new_extension	= trim( $val->strip_slashes($val->sanitizePost('extension')) );

// ===============================================================================
// ! Check if user has permission to rename files and if all params are there
// =============================================================================== 
if ( $new_name == '' || $rename_file == '' || $file_path == '' || $users->checkPermission('Media','media_rename') !== true )
{
	$message = $users->checkPermission('Media','media_rename') !== true
                        ? $backend->lang()->translate('You do not have the permission to rename files')
                        : $backend->lang()->translate('You sent an empty value');
	print CAT_Object::json_error($message);
	exit();
}
else
{
	// ================================ 
	// ! Check if folder is writeable   
	// ================================ 
	if ( is_writable(CAT_PATH . $file_path) )
	{
		$file = CAT_Helper_Directory::sanitizePath( CAT_PATH . $file_path . '/' . $rename_file );
		// Check if a new extension was sent
		if ( $new_extension == '' && !is_dir( $file ) )
		{
			// if file is a folder (so there is no extension) keep extension clear, if it is a file, add a "." and the extension
			$new_extension = (strtolower(pathinfo($file,PATHINFO_EXTENSION)) == '')
                           ? ''
                           : strtolower(pathinfo($file,PATHINFO_EXTENSION));
		}
		if ( substr($new_extension, 0, 1) != '.'  && !is_dir( $file ) )
		{
			$new_extension	= '.' . $new_extension;
		}
		
		// ========================================== 
		// ! Combine path, filenames and extensions   
		// ========================================== 
		$new_rename	= CAT_Helper_Directory::sanitizePath( CAT_PATH . $file_path . '/' . $new_name . $new_extension );

		// ================================= 
		// ! Try to rename the file/folder   
		// ================================= 
		if ( file_exists( $new_rename ) && ( $file != $new_rename ) )
		{
			$ajax	= array(
				'message'	=> $backend->lang()->translate('File already exists'),
				'new_name'	=> $rename_file,
				'extension'	=> $new_extension,
				'success'	=> false
			);
		}
		elseif ( rename( $file, $new_rename ) )
		{
			$ajax	= array(
				'message'	=> $backend->lang()->translate('Rename successful'),
				'new_name'	=> $new_name,
				'extension'	=> $new_extension,
				'success'	=> true
			);
		}
		else {
			$ajax	= array(
				'message'	=> $backend->lang()->translate('Rename unsuccessful'),
				'new_name'	=> $rename_file,
				'extension'	=> $new_extension,
				'success'	=> false
			);
		}
	}
	else {
		$ajax	= array(
			'message'	=> $backend->lang()->translate('Unable to write to the target directory.'),
			'success'	=> false
		);
	}
}

print json_encode( $ajax );
exit();

?>