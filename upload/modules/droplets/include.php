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
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         droplets
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

/**
 * this method may be called by modules to handle a droplet upload
 **/
function droplets_upload( $input ) {

	if ( ! function_exists('sanitize_path') ) {
	    @require CAT_PATH.'/framework/functions.php';
	}

    // Set temp vars
    $temp_dir   = sanitize_path( CAT_PATH.'/temp/' );
    $temp_file  = sanitize_path( $temp_dir . $_FILES[$input]['name'] );
    $temp_unzip = sanitize_path( CAT_PATH.'/temp/unzip/' );
    $errors     = array();

    // Try to upload the file to the temp dir
    if( ! move_uploaded_file( $_FILES[$input]['tmp_name'], $temp_file ) )
    {
   	    return array( 'error', CAT_Helper_Directory::getInstance()->lang()->translate( 'Upload failed' ) );
    }

    $result = droplets_import( $temp_file, $temp_unzip );

    // Delete the temp zip file
    if( file_exists( $temp_file) )
    {
        unlink( $temp_file );
    }
    CAT_Helper_Directory::removeDirectory($temp_unzip);

    // show errors
    if ( isset( $result['errors'] ) && is_array( $result['errors'] ) && count( $result['errors'] ) > 0 ) {
        return array( 'error', $result['errors'], NULL );
    }
    
    // return success
    return array( 'success', $result['count'] );
    
}   // end function droplets_upload()

/**
 * this method may be called by modules to handle a droplet import
 *
 * moved to CAT_Helper_Droplet, which ignores the $temp_unzip param; it is only
 * left for backward compatibility
 *
 **/
function droplets_import( $temp_file, $temp_unzip = NULL ) {
    return CAT_Helper_Droplet::installDroplet($temp_file);
}   // end function droplets_import()

?>