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

// =================================== 
//
//
// ! needs to be moved to the right file!
//
//
// =================================== 
function getSize($file)
{
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

// ================================= 
// ! Include the WB functions file   
// ================================= 
include_once(LEPTON_PATH . '/framework/functions.php');

require_once(LEPTON_PATH . '/framework/class.admin.php');
$admin = new admin('Media', 'media');

// =========================================================================== 
// ! Create the controller, it is reusable and can render multiple templates 	
// =========================================================================== 
global $parser;
$data_dwoo=array();

// $memory_limit = ini_get('memory_limit');
// $post_max_size = ini_get('post_max_size');
// $upload_max_filesize = ini_get('upload_max_filesize');

$allowed_img_types = array('jpg','jpeg','png','gif','tif');

$data_dwoo['maxUploadFiles']		= 12;
$data_dwoo['allowed_file_types']	= str_replace(',','|',RENAME_FILES_ON_UPLOAD);

$data_dwoo['MEDIA_DIRECTORY']		= MEDIA_DIRECTORY;

// ==================================================================================================================================== 
// ! Set the initial folder to view (mediaroot or homefolder). If the user don't have permissions to see media, redirect to admin_url   
// ==================================================================================================================================== 
if ($admin->get_permission('media')==true){
	$data_dwoo['initial_folder']
        = ( $admin->get_user_id() == 1 || (HOME_FOLDERS && $admin->get_home_folder()=='') || !HOME_FOLDERS )
        ? sanitize_path(MEDIA_DIRECTORY)
        : sanitize_path(MEDIA_DIRECTORY.$admin->get_home_folder());
}
else {
	header('Location: ' . ADMIN_URL);
}

// ======================================== 
// ! Get contents for the intitial folder   
// ======================================== 
$dir = scan_current_dir(LEPTON_PATH . $data_dwoo['initial_folder']);

// ============================= 
// ! Add folders to $data_dwoo   
// ============================= 
if(isset($dir['path']) && is_array($dir['path']))
{
	foreach($dir['path'] as $counter => $folder)
	{
		$data_dwoo['folders'][$counter]['NAME'] = $folder;
	}
}
// ================================================ 
// ! Add files and infos about them to $data_dwoo   
// ================================================ 
if(isset($dir['filename']) && is_array($dir['filename']))
{
	foreach($dir['filename'] as $counter => $file)
	{
		$file_path									= sanitize_path(LEPTON_PATH . $data_dwoo['initial_folder'].'/'.$file);
		$data_dwoo['files'][$counter]['FILETYPE']	= strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
		$data_dwoo['files'][$counter]['show_preview'] = ( in_array( strtolower($data_dwoo['files'][$counter]['FILETYPE']), $allowed_img_types ) ) ? true : false;

		$data_dwoo['files'][$counter]['FILESIZE']	= byte_convert(getSize($file_path));

		$data_dwoo['files'][$counter]['FILEDATE']	= date (DEFAULT_DATE_FORMAT, filemtime($file_path));
		$data_dwoo['files'][$counter]['FILETIME']	= date (DEFAULT_TIME_FORMAT, filemtime($file_path));
		$data_dwoo['files'][$counter]['FULL_NAME']	= $file;
		$data_dwoo['files'][$counter]['NAME']		= substr($file , 0 , -( strlen($data_dwoo['files'][$counter]['FILETYPE'])+1 ) );
	}
}

// ================================= 
// ! Add permissions to $data_dwoo   
// ================================= 
$data_dwoo['permissions']['media_upload']	= $admin->get_permission('media_upload');
$data_dwoo['permissions']['media_create']	= $admin->get_permission('media_create');
$data_dwoo['permissions']['media_rename']	= $admin->get_permission('media_rename');
$data_dwoo['permissions']['media_delete']	= $admin->get_permission('media_delete');

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_media_index.lte', $data_dwoo);

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>