<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version         $Id$
 *
 */
 
// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
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

// Check if user uploaded a file
if(!isset($_FILES['userfile'])||$_FILES['userfile']['size']==0) {
	$leptoken = ( true == isset($_GET['leptoken']) ) ? "?leptoken=".$_GET['leptoken'] : "";
	header("Location: index.php".$leptoken);
	exit(0);
}

require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Addons', 'modules_install');

// Include the WB functions file
require_once(WB_PATH.'/framework/functions.php');

// Check if module dir is writable (doesn't make sense to go on if not)
if ( ! is_writable(WB_PATH.'/modules/') ) {
	  $admin->print_error($MESSAGE['GENERIC_BAD_PERMISSIONS']);
}

// Set temp vars
$temp_dir   = WB_PATH.'/temp/';
$temp_file  = $temp_dir . $_FILES['userfile']['name'];
$temp_unzip = WB_PATH.'/temp/unzip/';

// make sure the temp directory exists, is writable and is empty
rm_full_dir($temp_unzip);
make_dir($temp_unzip);

// Try to upload the file to the temp dir
if(!move_uploaded_file($_FILES['userfile']['tmp_name'], $temp_file))
{
  CLEANUP();
	$admin->print_error($MESSAGE['GENERIC_CANNOT_UPLOAD']);
}

// to avoid problems with two admins installing modules at the same time, we
// create a unique subdir
$temp_subdir = $temp_unzip.basename($_FILES['userfile']['tmp_name']).'/';
make_dir( $temp_subdir );

// Include the PclZip class file
require_once(WB_PATH.'/modules/lib_lepton/pclzip/pclzip.lib.php');

// Setup the PclZip object
$archive = new PclZip($temp_file);
// Unzip the files to the temp unzip folder
$list = $archive->extract(PCLZIP_OPT_PATH, $temp_subdir);

// Check if uploaded file is a valid Add-On zip file
if (!($list && file_exists($temp_subdir . 'index.php')))
{
  CLEANUP();
  $admin->print_error($MESSAGE['GENERIC_INVALID_ADDON_FILE']);
}

// As we are going to check for a valid info.php, let's unset all vars expected
// there to see if they're set correctly
foreach(
    array(
        'module_license', 'module_author'  , 'module_name', 'module_directory',
        'module_version', 'module_function', 'module_description',
        'module_platform'
    ) as $varname
) {
    unset( ${$varname} );
}

// Include the modules info file
require($temp_subdir.'info.php');

// Perform Add-on requirement checks before proceeding
require(WB_PATH . '/framework/addon.precheck.inc.php');
preCheckAddon($temp_file, $temp_subdir);

// Delete the temp unzip directory
// ----- MOVED! Why should we unzip more than once? ------
// rm_full_dir($temp_unzip);

// Check if the file is valid
if(
    (!isset($module_license))		||
    (!isset($module_author))	  ||
    (!isset($module_directory))	||
    (!isset($module_name))		  ||
    (!isset($module_version))	  ||
    (!isset($module_function))	#||
#    (!isset($module_guid))
) {
  CLEANUP();
	$admin->print_error(sprintf($MESSAGE["MOD_MISSING_PARTS_NOTICE"], $module_name));
}

foreach(
    array(
        'module_license', 'module_author'  , 'module_name', 'module_directory',
        'module_version', 'module_function', 'module_description',
        'module_platform'
    ) as $varname
) {
   ${'new_'.$varname} = ${$varname};
   unset( ${$varname} );
}

// So, now we have done all preinstall checks, lets see what to do next
$module_directory   = $new_module_directory;
$action             = "install";

if ( is_dir(WB_PATH.'/modules/'.$module_directory) ) {
    $action = "upgrade";
    // look for old info.php
    if ( file_exists(WB_PATH.'/modules/'.$module_directory.'/info.php') ) {
		    require(WB_PATH.'/modules/'.$module_directory.'/info.php');
    		/**
    		 *	Version to be installed is older than currently installed version
    		 */
    		if ( versionCompare($module_version, $new_module_version, '>=') ) {
            CLEANUP();
    			  $admin->print_error( $MESSAGE['GENERIC_ALREADY_INSTALLED'] );
    		}
    }
}

// Set module directory
$module_dir = WB_PATH.'/modules/'.$module_directory;

// Make sure the module dir exists, and chmod if needed
make_dir($module_dir);

// copy files from temp folder
if ( COPY_RECURSIVE_DIRS( $temp_subdir, $module_dir ) !== true ) {
    CLEANUP();
    $admin->print_error( $MESSAGE['GENERIC_NOT_UPGRADED'] );
}

// remove temp
CLEANUP();

// load info.php again to have current values
if ( file_exists(WB_PATH.'/modules/'.$module_directory.'/info.php') ) {
    require(WB_PATH.'/modules/'.$module_directory.'/info.php');
}
// Run the modules install // upgrade script if there is one
if ( file_exists($module_dir.'/'.$action.'.php') ) {
	  require($module_dir.'/'.$action.'.php');
}

// Print success message
if ( $action=="install" ) {
	  // Load module info into DB
	  load_module(WB_PATH.'/modules/'.$module_directory, false);
	  // let admin set access permissions for modules of type 'page' and 'tool'
	  if ( $module_function == 'page' || $module_function == 'tool' ) {
    	  // get groups
    	  $stmt = $database->query( 'SELECT * FROM '.TABLE_PREFIX.'groups WHERE group_id <> 1' );
    	  if ( $stmt->numRows() > 0 ) {
            echo "<script type=\"text/javascript\">\n",
                 "function markall() {\n",
                 "  for ( i=0; i<document.forms[0].elements.length; i++ ) {\n",
                 "    if ( document.forms[0].elements[i].type == \"checkbox\" ) {\n",
                 "      if ( document.forms[0].group_all.checked == true ) {\n",
                 "        document.forms[0].elements[i].checked=true;\n",
                 "      } else {\n",
                 "        document.forms[0].elements[i].checked=false;\n",
                 "      }\n",
                 "    }\n",
                 "  }\n",
                 "}\n",
                 "</script>\n",
                 "<h2>", $MESSAGE['GENERIC_INSTALLED'], "</h2><br /><br />\n",
                 "<h3>", $TEXT['MODULE_PERMISSIONS'], "</h3><br />\n",
                 "<form method=\"post\" action=\"".ADMIN_URL."/modules/save_permissions.php\">\n",
                 "<input type=\"hidden\" name=\"module\" id=\"module\" value=\"", $module_directory, "\" />\n",
                 "<input type=\"checkbox\" name=\"group_all\" id=\"group_all\" onclick=\"markall();\" /> ", $MESSAGE['ADDON_GROUPS_MARKALL'], "<br />\n"
                 ;
            // let the admin choose which groups can access this module
            while( $row = $stmt->fetchRow(MYSQL_ASSOC) ) {
                echo "<input type=\"checkbox\" name=\"group_id[]\" id=\"group_id[]\" value=\"", $row['group_id'], "\" /> ", $row['name'], "<br />\n";
            }
            echo "<br /><br /><input type=\"submit\" value=\"".$TEXT['SAVE']."\" /></form>";
    	  }
    	  else {
    	      $admin->print_success($MESSAGE['GENERIC_INSTALLED']);
        }
    }
	  else {
	      $admin->print_success($MESSAGE['GENERIC_INSTALLED']);
    }
}
elseif ( $action == "upgrade" ) {
	  upgrade_module($module_directory, false);
	  $admin->print_success($MESSAGE['GENERIC_UPGRADED']);
}

// Print admin footer
$admin->print_footer();

// remove temp dirs/files
function CLEANUP() {
    global $temp_unzip, $temp_file;
    @rm_full_dir($temp_unzip);
    if(file_exists($temp_file)) { unlink($temp_file); } // Remove temp file
}

// recursive function to copy
// all subdirectories and contents:
function COPY_RECURSIVE_DIRS( $dirsource, $dirdest ) {
    if ( is_dir($dirsource) ) {
        $dir_handle=opendir($dirsource);
    }
    while ( $file = readdir($dir_handle) ) {
        if( $file != "." && $file != ".." ) {
            if( ! is_dir($dirsource."/".$file) ) {
                copy ($dirsource."/".$file, $dirdest.'/'.$file);
                if ( $file != '.svn' ) {
                    change_mode($dirdest."/".$file, 'file');
                }
            }
            else {
                make_dir($dirdest."/".$file);
	              COPY_RECURSIVE_DIRS($dirsource."/".$file, $dirdest.'/'.$file);
            }
    }
  }
  closedir($dir_handle);
  return true;
}

?>