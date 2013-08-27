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
 *   @license          http://www.gnu.org/licenses/gpl.html
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
include_once( sanitize_path ( CAT_PATH . '/framework/functions.php' ) );
$backend = CAT_Backend::getInstance('Media','media',false);
$dirh    = CAT_Helper_Directory::getInstance();
$val     = CAT_Helper_Validate::getInstance();
$user    = CAT_Users::getInstance();

header('Content-type: application/json');

// ================================================ 
// ! Check if user has permission to upload files   
// ================================================ 
if ( $user->checkPermission('media','media_upload',false) !== true )
{
    $ajax    = array(
        'message'    => $backend->lang()->translate('You don\'t have the permission to upload a file. Check your system settings.'),
        'success'    => false
    );
    print json_encode( $ajax );
    exit();
}
elseif ( is_array($val->sanitizePost('upload_counter')) )
{
    if ( $val->sanitizePost('folder_path') != '' )
    {
        $file_path     = sanitize_path( CAT_PATH . $val->sanitizePost('folder_path') );
    }
    else
    {
        $ajax    = array(
            'message'    => $backend->lang()->translate('No directory was selected'),
            'success'    => false
        );
        print json_encode( $ajax );
        exit();
    }

    if ( !is_writeable($file_path) )
    {
        $ajax    = array(
            'message'    => $backend->lang()->translate('Directory is not writeable.'),
            'success'    => false
        );
        print json_encode( $ajax );
        exit();
    }

    $upload_counter        = $val->sanitizePost('upload_counter');
    $file_overwrite        = $val->sanitizePost('overwrite');

    // ============================================================================ 
    // ! Create an array to check whether uploaded file is allowed to be uploaded   
    // ============================================================================ 
    $allowed_file_types        = explode(',', UPLOAD_ALLOWED);
    foreach ( $upload_counter as $file_id )
    {
        $field_name    = 'upload_' . $file_id;

        if ( isset( $_FILES[$field_name]['name'] ) && $_FILES[$field_name]['name'] != '' )
        {
            // =========================================== 
            // ! Get file extension of the uploaded file   
            // =========================================== 
            $file_extension    = (strtolower( pathinfo( $_FILES[$field_name]['name'], PATHINFO_EXTENSION ) ) == '')
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
                    $ajax    = array(
                        'message'    => $backend->lang()->translate('No files!'),
                        'success'    => false
                    );
                    print json_encode( $ajax );
                    exit();
                }
                else
                {

                    $current = CAT_Helper_Upload::getInstance($_FILES[$field_name]);
                    if ( $current->uploaded )
                    {
                        // If-schleife wenn überschreiben
                        if ( $file_overwrite != '' )
                        {
                            $current->file_overwrite        = true;
                        }
                        else
                        {
                            $current->file_overwrite        = false;
                        }
                        $current->process( $file_path );

                        if ( $current->processed )
                        {
                            $unzip_file  = $val->sanitizePost('unzip_' . $file_id);
                            $delete_file = $val->sanitizePost('delete_zip_' . $file_id);

                            if ( $unzip_file != '' )
                            {
                                $archive = CAT_Helper_Zip::getInstance( $files->file_dst_pathname );
                                $archive->config( 'Path', sanitize_path( $file_path ) );
                                $archive->extract();
                                if ( $archive->errorInfo() != 0 )
                                {
                                    $ajax	= array(
                                		'message'	=> $backend->lang()->translate('The ZIP couldn\'t be unpacked.') . $archive->errorInfo(),
                                		'success'	=> false
                                	);
                                	print json_encode( $ajax );
                                	exit();
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
                            $ajax	= array(
                        		'message'	=> $backend->lang()->translate('File upload error: {{error}}',array('error'=>$current->error)),
                        		'success'	=> false
                        	);
                        	print json_encode( $ajax );
                        	exit();
                        }
                    }
                    else
                    {
                            $ajax	= array(
                        		'message'	=> $backend->lang()->translate('File upload error: {{error}}',array('error'=>$current->error)),
                        		'success'	=> false
                        	);
                        	print json_encode( $ajax );
                        	exit();
                    }
                }
            }
            else
            {
                $ajax	= array(
            		'message'	=> $backend->lang()->translate('No file extension found.'),
            		'success'	=> false
            	);
            	print json_encode( $ajax );
            	exit();
            }
        }
    }
    $ajax	= array(
		'message'	=> $backend->lang()->translate('All files have been uploaded successfully.'),
		'success'	=> true
	);
	print json_encode( $ajax );
	exit();
}
else
{
    $ajax	= array(
		'message'	=> $backend->lang()->translate('File could not be uploaded. Maybe it is too big?'),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
