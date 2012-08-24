<?php
/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
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
include_once(LEPTON_PATH . '/framework/functions.php');

require_once(LEPTON_PATH . '/framework/class.admin.php');
$admin = new admin('Media', 'media');

if ( $admin->get_post('load_url') == '' || $admin->get_permission('media') != true )
{
	header('Location: ' . ADMIN_URL);
	exit();
}
$open_folder		= $admin->get_post('open_folder');

// =========================================================================== 
// ! Create the controller, it is reusable and can render multiple templates 	
// =========================================================================== 
global $parser;
$data_dwoo=array();

$data_dwoo['initial_folder']	= $admin->get_post('folder_path') . '/' . $admin->get_post('load_url');
$allowed_img_types				= array('jpg','jpeg','png','gif','tif');
$data_dwoo['MEDIA_DIRECTORY']	= MEDIA_DIRECTORY;
$data_dwoo['open_folder']		= $open_folder;

// ======================================== 
// ! Get contents for the intitial folder   
// ======================================== 
$dir	= scan_current_dir(LEPTON_PATH . $data_dwoo['initial_folder']);

// ============================= 
// ! Add folders to $data_dwoo   
// ============================= 
if ( isset($dir['path']) && is_array($dir['path']) )
{
	foreach ( $dir['path'] as $counter => $folder )
	{
		$data_dwoo['folders'][$counter]['NAME']		= $folder;
	}
}
// ================================================ 
// ! Add files and infos about them to $data_dwoo   
// ================================================ 
if ( isset($dir['filename']) && is_array($dir['filename']) )
{
	foreach ( $dir['filename'] as $counter => $file )
	{
		$file_path		= LEPTON_PATH . $data_dwoo['initial_folder'] . '/' . $file;

		$data_dwoo['files'][$counter]['FILETYPE']			= strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
		$data_dwoo['files'][$counter]['show_preview']		= in_array( strtolower($data_dwoo['files'][$counter]['FILETYPE']), $allowed_img_types ) ? true : false;
		$data_dwoo['files'][$counter]['FILESIZE']			= byte_convert(getSize($file_path));
		$data_dwoo['files'][$counter]['FILEDATE']			= date (DEFAULT_DATE_FORMAT, filemtime($file_path));
		$data_dwoo['files'][$counter]['FILETIME']			= date (DEFAULT_TIME_FORMAT, filemtime($file_path));
		$data_dwoo['files'][$counter]['FULL_NAME']			= $file;
		$data_dwoo['files'][$counter]['NAME']				= substr($file , 0 , -( strlen($data_dwoo['files'][$counter]['FILETYPE']) + 1 ) );
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
echo '<div id="fc_main_content">';
$parser->output('backend_media_get_contents.lte', $data_dwoo);
echo '</div>';

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>