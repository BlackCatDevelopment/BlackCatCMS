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

// =================================
// ! Include the WB functions file   
// ================================= 
include_once(CAT_PATH . '/framework/functions.php');

$dirh  = CAT_Helper_Directory::getInstance();
$user  = CAT_Users::getInstance();
$val   = CAT_Helper_Validate::getInstance();
$date  = CAT_Helper_DateTime::getInstance();

// check viewing permissions
if ( $val->sanitizePost('load_url') == '' || $user->checkPermission('media','media_view',false) !== true )
{
	header('Location: ' . CAT_ADMIN_URL);
	exit();
}

$load_file		= $val->sanitizePost('load_url');
$load_url		= $val->sanitizePost('folder_path') . '/' . $load_file;
$load_path		= $dirh->sanitizePath( CAT_PATH.'/'.$load_url );

$ajax	= array(
	'initial_folder'		=> $dirh->sanitizePath($load_url),
	'MEDIA_DIRECTORY'		=> MEDIA_DIRECTORY
);

$allowed_img_types			= array('jpg','jpeg','png','gif','tif');

if ( is_dir( $load_path ) )
{
	$ajax['is_folder']		= true;
	$ajax['is_writable']	= is_writable($load_path);

	// ======================================== 
	// ! Get contents for the intitial folder   
	// ======================================== 
	$dir	= scan_current_dir( $load_path );
	// ============================= 
	// ! Add folders to $ajax   
	// ============================= 
	if ( isset($dir['path']) && is_array($dir['path']) )
	{
		foreach ( $dir['path'] as $counter => $folder )
		{
			$ajax['folders'][$counter]['name']		= $folder;
		}
	}
	
	// ================================================ 
	// ! Add files and infos about them to $ajax   
	// ================================================ 
	if ( isset($dir['filename']) && is_array($dir['filename']) )
	{
		$files_array	= array();
		foreach ( $dir['filename'] as $counter => $file )
		{
			$file_path		= $load_path . '/' . $file;
			$filetype		= strtolower( pathinfo($file_path, PATHINFO_EXTENSION) );
			$ajax['files'][]	= array(
				'filetype'			=> $filetype,
				'show_preview'		=> in_array( strtolower($filetype), $allowed_img_types ) ? true : false,
				'filesize'			=> $dirh->getSize($file_path,true),
				'filedate'			=> strftime($date->getDefaultDateFormatShort(), filemtime($file_path)),
				'filetime'			=> strftime($date->getDefaultTimeFormat(), filemtime($file_path)),
				'full_name'			=> $file,
				'filename'			=> substr($file , 0 , -( strlen($filetype) + 1 ) ),
                'load_url'			=> $val->sanitize_url(CAT_URL.'/'.$load_url)
			);
		}
	}
}
else
{
	$ajax['is_folder']	= false;
	$filetype			= strtolower(pathinfo( $load_path , PATHINFO_EXTENSION));
	$ajax['files']		= array(
		'filetype'			=> $filetype,
		'show_preview'		=> in_array( strtolower($filetype), $allowed_img_types ) ? true : false,
		'filesize'			=> $dirh->getSize( $load_path, true ),
		'filedate'			=> strftime($date->getDefaultDateFormatShort(), filemtime( $load_path )),
		'filetime'			=> strftime($date->getDefaultTimeFormat(), filemtime( $load_path )) . ( isset($language_time_string) ? ' '.$language_time_string : '' ),
		'full_name'			=> $load_file,
		'filename'			=> substr($load_file , 0 , -( strlen($filetype) + 1 ) ),
		'load_url'			=> $val->sanitize_url(CAT_URL.'/'.$load_url)
	);
}
// ================================= 
// ! Add permissions to $ajax   
// ================================= 
$ajax['permissions']['media_upload']	= $user->checkPermission('media','media_upload',false);
$ajax['permissions']['media_create']	= $user->checkPermission('media','media_create',false);
$ajax['permissions']['media_rename']	= $user->checkPermission('media','media_rename',false);
$ajax['permissions']['media_delete']	= $user->checkPermission('media','media_delete',false);

// ==================== 
// ! Return results
// ==================== 
header('Content-type: application/json');
print json_encode( $ajax );

?>