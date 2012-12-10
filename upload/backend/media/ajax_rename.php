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

// ================================= 
// ! Include the WB functions file   
// ================================= 
include_once(LEPTON_PATH . '/framework/functions.php');

require_once(LEPTON_PATH . '/framework/class.admin.php');
$admin	= new admin('Media', 'media', false);

// Set header for json
header('Content-type: application/json');

// ================== 
// ! Get user input   
// ================== 
$file_path		= sanitize_path( ($admin->strip_slashes($admin->get_get('file_path')) ) );
$rename_file 	= $admin->strip_slashes($admin->get_get('rename_file'));
$new_name		= trim( $admin->strip_slashes($admin->get_get('new_name')) );
$new_extension	= trim( $admin->strip_slashes($admin->get_get('extension')) );

//unset($ajax);

// =============================================================================== 
// ! Check if user has permission to rename files and if all posts are not empty   
// =============================================================================== 
if ( $new_name == '' || $rename_file == '' || $file_path == '' || $admin->get_permission('media_rename') !== true )
{
	$ajax['message']	= $admin->get_permission('media_rename') != true ? $admin->lang->translate('You don\'t have the permission to rename files') : $admin->lang->translate('You send an empty value.');
	$ajax['success']	= false;

	print json_encode( $ajax );
	exit();
}
else {
	// ================================ 
	// ! Check if folder is writeable   
	// ================================ 
	if ( is_writable(LEPTON_PATH . $file_path) )
	{
		$file = sanitize_path( LEPTON_PATH . $file_path . '/' . $rename_file );
		
		// Check if a new extension were sent
		if ( $new_extension == '' && !is_dir( $file ) )
		{
			// if file is a folder (so there is no extension) keep extension clear, if it is a file, add a "." and the extension
			$new_extension = (strtolower(pathinfo( $file, PATHINFO_EXTENSION)) == '') ? '' : strtolower(pathinfo( $file, PATHINFO_EXTENSION) );
		}
		if ( substr($new_extension, 0, 1) != '.'  && !is_dir( $file ) )
		{
			$new_extension	= '.' . $new_extension;
		}
		
		// ========================================== 
		// ! Combine path, filenames and extensions   
		// ========================================== 
		$new_rename	= sanitize_path( LEPTON_PATH . $file_path . '/' . $new_name . $new_extension );

		// ================================= 
		// ! Try to rename the file/folder   
		// ================================= 
		if ( rename( $file, $new_rename ) )
		{
			$ajax	= array(
				'message'	=> $admin->lang->translate('Rename successful.'),
				'new_name'	=> $new_name,
				'extension'	=> $new_extension,
				'success'	=> true
			);
		}
		else {
			$ajax	= array(
				'message'	=> $admin->lang->translate('Rename unsuccessful.'),
				'new_name'	=> $rename_file,
				'extension'	=> $new_extension,
				'success'	=> false
			);
		}
	}
	else {
		$ajax	= array(
			'message'	=> $admin->lang->translate('Unable to write to the target directory.'),
			'success'	=> false
		);
	}
}

print json_encode( $ajax );
exit();

?>