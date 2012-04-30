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
if(!isset($_FILES['userfile'])) {
	header("Location: index.php");
	exit(0);
}

require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Addons', 'templates_install');

// Include the WB functions file
require_once(WB_PATH.'/framework/functions.php');

// Set temp vars
$temp_dir = WB_PATH.'/temp/';
$temp_file = $temp_dir . $_FILES['userfile']['name'];
$temp_unzip = WB_PATH.'/temp/unzip/';

// Try to upload the file to the temp dir
if(!move_uploaded_file($_FILES['userfile']['tmp_name'], $temp_file)) {
	$admin->print_error($MESSAGE['GENERIC_CANNOT_UPLOAD']);
}

// Include the PclZip class file (thanks to 
require_once(WB_PATH.'/modules/lib_lepton/pclzip/pclzip.lib.php');

// Remove any vars with name "template_directory" and "theme_directory"
unset($template_directory);
unset($theme_directory);

// Setup the PclZip object
$archive = new PclZip($temp_file);
// Unzip the files to the temp unzip folder
$list = $archive->extract(PCLZIP_OPT_PATH, $temp_unzip);

// Check if uploaded file is a valid Add-On zip file
if (!($list && file_exists($temp_unzip . 'index.php'))) $admin->print_error($MESSAGE['GENERIC']['INVALID_ADDON_FILE']);

// Include the templates info file
require($temp_unzip.'info.php');

// Perform Add-on requirement checks before proceeding
require(WB_PATH . '/framework/addon.precheck.inc.php');
preCheckAddon($temp_file);

// check if the template is valid for Lepton 2.x; there must not be any
// register_frontend_modfiles() calls in the header!
if ( ! valid_lepton_template( $temp_unzip . 'index.php' ) ) {
    rm_full_dir($temp_unzip);
    $admin->print_error(
		$admin->lang->translate(
			'<h1>Invalid LEPTON 2.x Template!</h1>'
		  . 'The template uses <tt>register_frontend_modfiles()</tt>, which is deprecated. '
		  . 'Use <tt>get_page_headers()</tt> instead. Please inform the template author.<br />'
		  . 'Detailed information about the <tt>get_page_headers()</tt> method are available in the LEPTON Wiki:<br />'
		  . '<a href="http://wiki.lepton-cms.org/en/index.php?title=Manual:Tutorials/Headers">http://wiki.lepton-cms.org/en/index.php?title=Manual:Tutorials/Headers</a>'
		)
	);
}

// Delete the temp unzip directory
rm_full_dir($temp_unzip);

// Check if the file is valid
if(!isset($template_directory)) {
	if(file_exists($temp_file)) { unlink($temp_file); } // Remove temp file
	$admin->print_error($MESSAGE['GENERIC_INVALID']);
}

/**
 *	Check if this module is already installed
 *	and compare versions if so
 *
 */
$new_template_version=$template_version;

if(is_dir(WB_PATH.'/templates/'.$template_directory)) {
	
	if(file_exists(WB_PATH.'/templates/'.$template_directory.'/info.php')) {
		
		require_once(WB_PATH.'/templates/'.$template_directory.'/info.php');
		
		$temp_error = false;
		$temp_msg = "";
		/**
		 *	Version to be installed is older than currently installed version
		 *
		 */
		if (versionCompare($template_version, $new_template_version, '>=')) {
			$temp_error = true;
			$temp_msg = $MESSAGE['GENERIC_ALREADY_INSTALLED'];
		}
		
		/**
		 *	Additional tests for required vars.
		 *
		 */
		if(	(!isset($template_license))		||
			(!isset($template_author))		||
			(!isset($template_directory))	||
			(!isset($template_author))		||
			(!isset($template_version))		||
			(!isset($template_function))	
		) {
			$temp_error = true;
			$temp_msg = $MESSAGE["TEMPLATES_MISSING_PARTS_NOTICE"];
		}
		
		if ( true === $temp_error ) {
			if(file_exists($temp_file)) unlink($temp_file);	// Remove temp file
			$admin->print_error( $temp_msg );
		}
	}
	$success_message=$MESSAGE['GENERIC_UPGRADED'];
} else {
	$success_message=$MESSAGE['GENERIC_INSTALLED'];
}

// Check if template dir is writable
if(!is_writable(WB_PATH.'/templates/')) {
	if(file_exists($temp_file)) { unlink($temp_file); } // Remove temp file
	$admin->print_error($MESSAGE['TEMPLATES_BAD_PERMISSIONS']);
}

// Set template dir
$template_dir = WB_PATH.'/templates/'.$template_directory;

// Make sure the template dir exists, and chmod if needed
if(!file_exists($template_dir)) {
	make_dir($template_dir);
} else {
	change_mode($template_dir, 'dir');
}

// Unzip template to the template dir
$list = $archive->extract(PCLZIP_OPT_PATH, $template_dir);
if(!$list) {
	$admin->print_error($MESSAGE['GENERIC_CANNOT_UNZIP']);
}

// Delete the temp zip file
if(file_exists($temp_file)) { unlink($temp_file); }

// Chmod all the uploaded files
$dir = dir($template_dir);
while(false !== $entry = $dir->read()) {
	// Skip pointers
	if(substr($entry, 0, 1) != '.' AND $entry != '.svn' AND !is_dir($template_dir.'/'.$entry)) {
		// Chmod file
		change_mode($template_dir.'/'.$entry);
	}
}

// Load template info into DB
load_template($template_dir);

// Print success message
$admin->print_success($success_message);

// Print admin footer
$admin->print_footer();

?>