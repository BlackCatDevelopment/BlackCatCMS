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

    global $database, $admin;
    
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
   	    return array( 'error', $admin->lang->translate( 'Upload failed' ) );
    }

    $result = droplets_import( $temp_file, $temp_unzip );

    // Delete the temp zip file
    if( file_exists( $temp_file) )
    {
        unlink( $temp_file );
    }
    CAT_Helper_Directory::getInstance()->removeDirectory($temp_unzip);

    // show errors
    if ( isset( $result['errors'] ) && is_array( $result['errors'] ) && count( $result['errors'] ) > 0 ) {
        return array( 'error', $result['errors'], NULL );
    }
    
    // return success
    return array( 'success', $result['count'] );
    
}   // end function droplets_upload()

/**
 * this method may be called by modules to handle a droplet import
 **/
function droplets_import( $temp_file, $temp_unzip ) {

    global $admin, $database;

    $errors  = array();
    $imports = array();
    $count   = 0;

    // extract file
    $list = CAT_Helper_Zip::getInstance($temp_file)->config( 'Path', $temp_unzip )->extract();
    
    // now, open all *.php files and search for the header;
    // an exported droplet starts with "//:"
    if ( $dh = @opendir($temp_unzip) ) {
        while ( false !== ( $file = readdir($dh) ) )
        {
            if ( $file != "." && $file != ".." )
            {
                if ( preg_match( '/^(.*)\.php$/i', $file, $name_match ) ) {
                    $description = NULL;
                    $usage       = NULL;
                    $code        = NULL;
                    // Name of the Droplet = Filename
                    $name        = $name_match[1];
                    // Slurp file contents
                    $lines       = file( $temp_unzip.'/'.$file );
                    // First line: Description
                    if ( preg_match( '#^//\:(.*)$#', $lines[0], $match ) ) {
                        $description = addslashes( $match[1] );
                    }
                    // Second line: Usage instructions
                    if ( preg_match( '#^//\:(.*)$#', $lines[1], $match ) ) {
                        $usage       = addslashes( $match[1] );
                    }
                    if ( ! $description && ! $usage ) {
                        // invalid file
                        $errors[$file] = $admin->lang->translate( 'No valid Droplet file (missing description and/or usage instructions)' );
                        continue;
                    }
                    // Remaining: Droplet code
                    $code = implode( '', array_slice( $lines, 2 ) );
                    // replace 'evil' chars in code
                    $tags = array('<?php', '?>' , '<?');
                    $code = addslashes(str_replace($tags, '', $code));
                    // Already in the DB?
                    $stmt  = 'INSERT';
                    $id    = NULL;
                    $found = $database->get_one("SELECT * FROM ".CAT_TABLE_PREFIX."mod_droplets WHERE name='$name'");
                    if ( $found && $found > 0 ) {
                        $stmt = 'REPLACE';
                        $id   = $found;
                    }
                    // execute
                    $result = $database->query("$stmt INTO ".CAT_TABLE_PREFIX."mod_droplets VALUES('$id','$name','$code','$description','".time()."','".$admin->get_user_id()."',1,1,0,1,'$usage')");
                    if( ! $database->is_error() ) {
                        $count++;
                        $imports[$name] = 1;
                    }
                    else {
                        $errors[$name] = $database->get_error();
                    }
                }
            }
        }
        closedir($dh);
        // check for data directory
        if ( file_exists( $temp_unzip.'/data' ) ) {
            // copy all files
            $dh = @opendir($temp_unzip.'/data');
            if ( is_resource($dh) ) {
                while ( false !== ( $file = readdir($dh) ) )
        		{
                	if ( $file != "." && $file != ".." )
	            	{
	                	if ( preg_match( '/^(.*)\.txt/i', $file ) ) {
	                	    copy( $temp_unzip.'/data/'.$file, dirname(__FILE__).'/data/'.$file );
	                	}
					}
				}
                closedir($dh);
            }
        }
        CAT_Helper_Directory::getInstance()->removeDirectory($temp_unzip);
    }

    return array( 'count' => $count, 'errors' => $errors, 'imported' => $imports );

}   // end function droplets_import()

?>