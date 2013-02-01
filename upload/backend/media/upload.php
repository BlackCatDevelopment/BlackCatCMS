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
include_once( sanitize_path ( CAT_PATH . '/framework/functions.php' ) );

require_once( sanitize_path( CAT_PATH . '/framework/class.admin.php' ) );
$admin	= new admin('Media', 'media');

// ================================================ 
// ! Check if user has permission to upload files   
// ================================================ 
if ( $admin->get_permission('media_upload') != true )
{
	header('Location: ' . CAT_ADMIN_URL);
}
else if ( is_array($admin->get_post('upload_counter')) )
{
	if ( $admin->get_post('folder_path') != '' )
	{
		$file_path	 = sanitize_path( CAT_PATH . $admin->get_post('folder_path') );
	}
	else
	{
		$admin->print_error( 'No directory was selected', false );
	}
	if ( !is_writeable($file_path) )
	{
		$admin->print_error( 'Directory is not writeable.', false );
	}
	$upload_counter		= $admin->get_post('upload_counter');
	$file_overwrite		= $admin->get_post('overwrite');
	// ============================================================================ 
	// ! Create an array to check whether uploaded file is allowed to be uploaded   
	// ============================================================================ 
	$allowed_file_types		= explode(',', RENAME_FILES_ON_UPLOAD);
	foreach ( $upload_counter as $file_id )
	{
		$field_name	= 'upload_' . $file_id;

		if ( isset( $_FILES[$field_name]['name'] ) && $_FILES[$field_name]['name'] != '' )
		{
			// =========================================== 
			// ! Get file extension of the uploaded file   
			// =========================================== 
			$file_extension		= (strtolower( pathinfo( $_FILES[$field_name]['name'], PATHINFO_EXTENSION ) ) == '') ? false : strtolower( pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION));
			// ====================================== 
			// ! Check if file extension is allowed   
			// ====================================== 
			if ( isset( $file_extension ) && in_array( $file_extension, $allowed_file_types ) )
			{
				// ======================================= 
				// ! Try to include the upload.class.php   
				// ======================================= 
				$files		= $admin->get_helper( 'Upload', $_FILES[$field_name] );

				if ( $files->uploaded )
				{
					// If-schleife wenn überschreiben
					if ( $file_overwrite != '' )
					{
						$files->file_overwrite		= true;
					}
					else
					{
						$files->file_overwrite		= false;
					}
					// Replace with allowed images
					//$files->allowed = array('image/*');
		
					$files->process( $file_path );
					if ( $files->processed )
					{
						$unzip_file			= $admin->get_post('unzip_' . $file_id);
						$delete_file		= $admin->get_post('delete_zip_' . $file_id);

						if ( $unzip_file != '' )
						{
							// ======================================= 
							// ! Try to include the Zip-Helper.php   
							// ======================================= 
							$helper_link		= sanitize_path( CAT_PATH . '/framework/LEPTON/Helper/Zip.php' );
							if ( file_exists( $helper_link ) )
							{
								require_once( $helper_link );
							}
							else {
								$admin->print_error('The Zip helper was not found. Please check if "' . $helper_link . '" is installed.', false);
							}
		
							// =============================== 
							// ! Create the class for PclZip   
							// =============================== 
							$archive	= new CAT_Helper_Zip( $files->file_dst_pathname );
							$archive->config( 'Path', sanitize_path( $file_path ) );
							$archive->extract();
							if ( $archive->errorInfo() != 0 )
							{
								$admin->print_error( 'The ZIP couldn\'t be unpacked.' . $archive->errorInfo(), false );
							}
							// ============================================== 
							// ! Delete archiv after everything worked fine   
							// ============================================== 
							if ( $delete_file != '' )
							{
								rm_full_dir( $files->file_dst_pathname );
							}
						}
						// ================================= 
						// ! Clean the upload class $files   
						// ================================= 
						$files->clean();
					}
					else 
					{
						$admin->print_error( 'An error occurred: ' . $files->error, false );
					}
				}
				else $admin->print_error( 'An error occurred: ' . $files->error, $files->log, false );
			}
			else $admin->print_error( 'No file extension were found.', false );
		}
	}
	$admin->print_success('All files have been uploaded successfully.', false );
}
else $admin->print_error( 'File could not be uploaded. Maybe it is too big?', false );
// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>