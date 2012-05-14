<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          dropleps
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 * @version         $Id$
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
	include(WB_PATH.'/framework/class.secure.php');
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
// end include class.secure.php

/**
 * this method may be called by modules to handle a droplep upload
 **/
function dropleps_upload( $input ) {

    global $database, $admin;
    
	if ( ! function_exists('sanitize_path') ) {
	    @require WB_PATH.'/framework/functions.php';
	}

    // Set temp vars
    $temp_dir   = sanitize_path( WB_PATH.'/temp/' );
    $temp_file  = sanitize_path( $temp_dir . $_FILES[$input]['name'] );
    $temp_unzip = sanitize_path( WB_PATH.'/temp/unzip/' );
    $errors     = array();

    // Try to upload the file to the temp dir
    if( ! move_uploaded_file( $_FILES[$input]['tmp_name'], $temp_file ) )
    {
   	    return array( 'error', $admin->lang->translate( 'Upload failed' ) );
    }

    $result = dropleps_import( $temp_file, $temp_unzip );

    // Delete the temp zip file
    if( file_exists( $temp_file) )
    {
        unlink( $temp_file );
    }
    rm_full_dir($temp_unzip);

    // show errors
    if ( isset( $result['errors'] ) && is_array( $result['errors'] ) && count( $result['errors'] ) > 0 ) {
        return array( 'error', $result['errors'], NULL );
    }
    
    // return success
    return array( 'success', $result['count'] );
    
}   // end function dropleps_upload()

/**
 * this method may be called by modules to handle a droplep import
 **/
function dropleps_import( $temp_file, $temp_unzip ) {

    global $admin, $database;

    $errors  = array();
    $imports = array();
    $count   = 0;
    
    if ( method_exists( $admin, 'get_helper' ) ) {
    	$list = $admin->get_helper('Zip',$temp_file)->config( 'Path', $temp_unzip )->extract();
	}
	else {
	    if ( ! class_exists( 'PclZip' ) ) {
	        // Include the PclZip class file
    		require_once(LEPTON_PATH.'/modules/lib_pclzip/pclzip.lib.php');
		}
		$archive = new PclZip($temp_file);
    	$list    = $archive->extract(PCLZIP_OPT_PATH, $temp_unzip);
	}

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
                        $errors[$file] = $admin->lang->translate( 'No valid Droplep file (missing description and/or usage instructions)' );
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
                    $found = $database->get_one("SELECT * FROM ".TABLE_PREFIX."mod_droplets WHERE name='$name'");
                    if ( $found && $found > 0 ) {
                        $stmt = 'REPLACE';
                        $id   = $found;
                    }
                    // execute
                    $result = $database->query("$stmt INTO ".TABLE_PREFIX."mod_droplets VALUES('$id','$name','$code','$description','".time()."','".$admin->get_user_id()."',1,1,0,1,'$usage')");
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
        rm_full_dir($temp_unzip);
    }

    return array( 'count' => $count, 'errors' => $errors, 'imported' => $imports );

}   // end function dropleps_import()

?>