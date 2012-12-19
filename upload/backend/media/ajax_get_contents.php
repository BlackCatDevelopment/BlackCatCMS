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
 * @version         $Id$
 *
 */
 

// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {
	include(LEPTON_PATH . '/framework/class.secure.php');
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

// ========================================================== 
//
//
// ! Those functions needs to be moved to the correct file!
//
//
// ========================================================== 

function getSize($file) { 
	$size = filesize($file); 
	if ($size < 0) 
	if (!(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')) 
		$size = trim(`stat -c%s $file`); 
	else{ 
		$fsobj = new COM("Scripting.FileSystemObject"); 
		$f = $fsobj->GetFile($file); 
		$size = $file->Size; 
	} 
	return $size; 
}
function byte_convert($bytes)
{
	$symbol = array(' bytes', ' KB', ' MB', ' GB', ' TB');
	$exp = 0;
	$converted_value = 0;
	if ($bytes > 0)
	{
		$exp = floor( log($bytes) / log(1024));
		$converted_value = ($bytes / pow( 1024, floor($exp)));
	}
	return sprintf('%.2f '.$symbol[$exp], $converted_value);
}
// ========================================================== 
//
//
// ========================================================== 


// ================================= 
// ! Include the WB functions file   
// ================================= 
require_once(LEPTON_PATH . '/framework/class.admin.php');
$admin = new admin('Media', 'media', false);

include_once(LEPTON_PATH . '/framework/functions.php');

if ( $admin->get_post('load_url') == '' || $admin->get_permission('media') !== true )
{
	header('Location: ' . ADMIN_URL);
	exit();
}
header('Content-type: application/json');

//$open_folder	= $admin->get_post('open_folder');
$load_file		= $admin->get_post('load_url');
$load_url		= $admin->get_post('folder_path') . '/' . $load_file;
$load_path		= LEPTON_PATH . $load_url;// should be sanitize_path( LEPTON_PATH . $load_url );

// =========================================================================== 
// ! Create the controller, it is reusable and can render multiple templates 	
// =========================================================================== 
$ajax	= array(
	'initial_folder'		=> sanitize_path( $load_url),
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
				'filesize'			=> byte_convert(getSize($file_path)),
				'filedate'			=> date (DEFAULT_DATE_FORMAT, filemtime($file_path)),
				'filetime'			=> date (DEFAULT_TIME_FORMAT, filemtime($file_path)),
				'full_name'			=> $file,
				'filename'			=> substr($file , 0 , -( strlen($filetype) + 1 ) )
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
		'filesize'			=> byte_convert(getSize( $load_path )),
		'filedate'			=> date (DEFAULT_DATE_FORMAT, filemtime( $load_path )),
		'filetime'			=> date (DEFAULT_TIME_FORMAT, filemtime( $load_path )),
		'full_name'			=> $load_file,
		'filename'			=> substr($load_file , 0 , -( strlen($filetype) + 1 ) ),
		'load_url'			=> $load_url
	);
}
// ================================= 
// ! Add permissions to $ajax   
// ================================= 
$ajax['permissions']['media_upload']	= $admin->get_permission('media_upload');
$ajax['permissions']['media_create']	= $admin->get_permission('media_create');
$ajax['permissions']['media_rename']	= $admin->get_permission('media_rename');
$ajax['permissions']['media_delete']	= $admin->get_permission('media_delete');

// ==================== 
// ! Parse the site   
// ==================== 
print json_encode( $ajax );

?>