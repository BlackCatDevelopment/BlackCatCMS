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

// ================================= 
// ! Include the WB functions file   
// ================================= 
include_once(LEPTON_PATH . '/framework/functions.php');

require_once(LEPTON_PATH . '/framework/class.admin.php');
$admin			= new admin('Media', 'media');

$file			= $admin->get_get('file');
$file_path		= $admin->get_get('file_path');


if ( $file == '' || $file_path == '' || $admin->get_permission('media_delete') != true )
{
	header('Location: '.ADMIN_URL);
}

else {
	// ============================ 
	// ! Try to delete file/folder
	// ============================ 
	$link	= LEPTON_PATH . $file_path . '/' . $file;
	if ( file_exists($link) )
	{
		$kind	= is_dir($link) ? 'dir' : 'file';
		if ( rm_full_dir( $link ) )
		{
			if ( $kind == 'dir') $admin->print_success( 'Folder deleted successfully', false );
			else $admin->print_success( 'File deleted successfully', false );
		}
		else
		{
			if ( $kind == 'dir') $admin->print_error( 'Can\'t delete the selected directory', false );
			else $admin->print_error( 'Can\'t delete the selected file', false );
		}
	}
	else $admin->print_error( 'Couldn\'t find the folder or file' );
}

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>