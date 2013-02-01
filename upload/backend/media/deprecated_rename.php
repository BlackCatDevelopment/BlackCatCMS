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
if (defined('CAT_PATH')) {
	include(CAT_PATH . '/framework/class.secure.php');
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
include_once(CAT_PATH . '/framework/functions.php');

require_once(CAT_PATH . '/framework/class.admin.php');
$admin	= new admin('Media', 'media');

// ================== 
// ! Get user input   
// ================== 
$file_path		= $admin->strip_slashes($admin->get_post('file_path'));
$rename_file 	= $admin->strip_slashes($admin->get_post('rename_file'));
$new_name		= trim($admin->strip_slashes($admin->get_post('new_name')));
$new_extension	= trim($admin->strip_slashes($admin->get_post('new_extension')));

// =============================================================================== 
// ! Check if user has permission to rename files and if all posts are not empty   
// =============================================================================== 
if ( $new_name=='' || $rename_file=='' || $file_path=='' || $admin->get_permission('media_rename')!=true )
{
	header('Location: ' . CAT_ADMIN_URL);
}

else {
	// ================================ 
	// ! Check if folder is writeable   
	// ================================ 
	if (is_writable(CAT_PATH . $file_path))
	{
		$file = CAT_PATH . $file_path.'/'.$rename_file;
		
		// Check if a new extension were sent
		if ( $new_extension == '')
		{
			// if file is a folder (so there is no extension) keep extension clear, if it is a file, add a "." and the extension
			$new_extension = (strtolower(pathinfo($file_path.'/'.$rename_file, PATHINFO_EXTENSION)) == '') ? '' : '.'.strtolower(pathinfo($file_path.'/'.$rename_file, PATHINFO_EXTENSION));
		}
		
		// ========================================== 
		// ! Combine path, filenames and extensions   
		// ========================================== 
		$old_name	= CAT_PATH . $file_path.'/'.$rename_file;
		$new_name	= CAT_PATH . $file_path.'/'.$new_name.$new_extension;

	//////////////////////////////////////////////////////////////////////
		/*
		$ext = trim($admin->strip_slashes($admin->get_post('extension')));
		$ext = (empty($ext)) ? '' : '.'.$ext;
		$old_file = media_filename(trim($admin->strip_slashes($admin->get_post('old_name')))).$ext;
		$rename_file = media_filename(trim($admin->strip_slashes($admin->get_post('name')))).$ext;
		$type = trim($admin->strip_slashes($admin->get_post('filetype')));
		// perhaps change dots in underscore by tpye = directory
		$rename_file = trim($rename_file,'.');
		*/
	//////////////////////////////////////////////////////////////////////
		/*
		Do we need to replace "." with "_" ?
		if( is_dir($old_name) )
		{
			$new_name = str_replace('.', '_', $new_name);
		}
		*/
		/*
		
		As you currently cannot rename the extension of a file this is not needed - it will be added later!
		
		elseif (!preg_match("/\." . $allowed_file_types . "$/i", $rename_file) )
		{
			$admin->print_error($TEXT['EXTENSION'].': '.$MESSAGE['GENERIC_INVALID'],false);
		}
		*/
		// ================================= 
		// ! Try to rename the file/folder   
		// ================================= 
		if ( rename($old_name, $new_name) )
		{
			$admin->print_success( 'Rename successful', false );
		}
		else {
			$admin->print_error( 'Rename unsuccessful', false );
		}
	}
	else $admin->print_error( 'Unable to write to the target directory' );
}

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>