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
 *   @license		  http://www.gnu.org/licenses/gpl.html
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

// ================================= 
// ! Include the WB functions file   
// ================================= 
include_once( sanitize_path ( CAT_PATH . '/framework/functions.php' ) );

require_once( sanitize_path( CAT_PATH . '/framework/class.admin.php' ) );
$admin	= new admin('Media', 'media');

$dirh  = CAT_Helper_Directory::getInstance();
$val   = CAT_Helper_Validate::getInstance();
$user  = CAT_Users::getInstance();

// ================================================ 
// ! Check if user has permission to upload files   
// ================================================ 
if ( $user->checkPermission('media','media_upload',false) !== true )
{
	header('Location: ' . CAT_ADMIN_URL);
}
else if ( is_array($val->sanitizePost('upload_counter')) )
{
	if ( $val->sanitizePost('folder_path') != '' )
	{
		$file_path	 = sanitize_path( CAT_PATH . $val->sanitizePost('folder_path') );
	}
	else
	{
		$admin->print_error( 'No directory was selected', false );
	}
	if ( !is_writeable($file_path) )
	{
		$admin->print_error( 'Directory is not writeable.', false );
	}
	$upload_counter		= $val->sanitizePost('upload_counter');
	$file_overwrite		= $val->sanitizePost('overwrite');
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
			$file_extension	= (strtolower( pathinfo( $_FILES[$field_name]['name'], PATHINFO_EXTENSION ) ) == '')
                            ? false
                            : strtolower( pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION))
                            ;
			// ====================================== 
			// ! Check if file extension is allowed   
			// ====================================== 
			if ( isset( $file_extension ) && in_array( $file_extension, $allowed_file_types ) )
			{
				// ======================================= 
				// ! Try to include the upload helper
				// ======================================= 
                if ( ! is_array($_FILES) || ! count($_FILES) )
                {
                    $admin->print_error('No files!');
                }
                else
                {

                    $current = CAT_Helper_Upload::getInstance($_FILES[$field_name]);

    				if ( $current->uploaded )
				{
					// If-schleife wenn überschreiben
					if ( $file_overwrite != '' )
					{
    						$current->file_overwrite		= true;
					}
					else
					{
    						$current->file_overwrite		= false;
					}
		
    					$current->process( $file_path );

    					if ( $current->processed )
					{
    						$unzip_file			= $val->sanitizePost('unzip_' . $file_id);
    						$delete_file		= $val->sanitizePost('delete_zip_' . $file_id);

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
    							$archive = CAT_Helper_Zip::getInstance( $files->file_dst_pathname );
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
    								$dirh->removeDirectory( $files->file_dst_pathname );
							}
						}
						// ================================= 
						// ! Clean the upload class $files   
						// ================================= 
    						$current->clean();
					}
					else 
					{
    						$admin->print_error( 'An error occurred (processed false): ' . $current->error, false );
    					}
					}
    				else $admin->print_error( 'An error occurred (uploaded false): ' . $current->error, false );
				}
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