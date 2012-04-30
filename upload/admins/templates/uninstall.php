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



// Check if user selected template
if(!isset($_POST['file']) OR $_POST['file'] == "") {
	header("Location: index.php");
	exit(0);
} else {
	$file = $_POST['file'];
}

// Extra protection
if(trim($file) == '') {
	header("Location: index.php");
	exit(0);
}

require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Addons', 'templates_uninstall');

// Include the WB functions file
require_once(WB_PATH.'/framework/functions.php');

// Check if the template exists
if(!is_dir(WB_PATH.'/templates/'.$file)) {
	$admin->print_error($MESSAGE['GENERIC_NOT_INSTALLED']);
}

/**
*	Check if the template is the standard-template or still in use
*/
if (!array_key_exists('CANNOT_UNINSTALL_IS_DEFAULT_TEMPLATE', $MESSAGE['GENERIC'] ) )
	$MESSAGE['GENERIC_CANNOT_UNINSTALL_IS_DEFAULT_TEMPLATE'] = "Can't uninstall this template <b>{{name}}</b> because it's the standardtemplate!";

// check whether the template is used as default wb theme
if($file == DEFAULT_THEME) {
	$temp = array ('name' => $file );
	$msg = replace_all( $MESSAGE['GENERIC_CANNOT_UNINSTALL_IS_DEFAULT_TEMPLATE'], $temp );
	$admin->print_error( $msg );
}

if ($file == DEFAULT_TEMPLATE) {
	$temp = array ('name' => $file );
	$msg = replace_all( $MESSAGE['GENERIC_CANNOT_UNINSTALL_IS_DEFAULT_TEMPLATE'], $temp );
	$admin->print_error( $msg );

} else {
	
	/**
	*	Check if the template is still in use by a page ...
	*/
	$info = $database->query("SELECT page_id, page_title FROM ".TABLE_PREFIX."pages WHERE template='".$file."' order by page_title");
	
	if ($info->numRows() > 0) {
		/**
		*	Template is still in use, so we're collecting the page-titles
		*/
		
		/**
		*	The base-message template-string for the top of the message
		*/
		if (!array_key_exists("CANNOT_UNINSTALL_IN_USE_TMPL", $MESSAGE['GENERIC'])) {
			$add = $info->numRows() == 1 ? "this page" : "these pages";
			$msg_template_str  = "<br /><br />{{type}} <b>{{type_name}}</b> could not be uninstalled because it is still in use by {{pages}}";
			$msg_template_str .= ":<br /><i>click for editing.</i><br /><br />";
		} else {
			$msg_template_str = $MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE_TMPL'];
			$temp = explode(";",$MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE_TMPL_PAGES']);
			$add = $info->numRows() == 1 ? $temp[0] : $temp[1];
		}
		/**
		*	The template-string for displaying the Page-Titles ... in this case as a link
		*/
		$page_template_str = "- <b><a href='../pages/settings.php?page_id={{id}}'>{{title}}</a></b><br />";
		
		$values = array ('type' => 'Template', 'type_name' => $file, 'pages' => $add);
		$msg = replace_all ( $msg_template_str,  $values );
		
		$page_names = "";
		
		while ($data = $info->fetchRow() ) {
			
			$page_info = array(
				'id'	=> $data['page_id'], 
				'title' => $data['page_title']
			);
			
			$page_names .= replace_all ( $page_template_str, $page_info );
		}
		
		/**
		*	Printing out the error-message and die().
		*/
		$admin->print_error($MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE'].$msg.$page_names);
	}
}

// Check if we have permissions on the directory
if(!is_writable(WB_PATH.'/templates/'.$file)) {
	$admin->print_error($MESSAGE['GENERIC_CANNOT_UNINSTALL'].WB_PATH.'/templates/'.$file);
}

// Try to delete the template dir
if(!rm_full_dir(WB_PATH.'/templates/'.$file)) {
	$admin->print_error($MESSAGE['GENERIC_CANNOT_UNINSTALL']);
} else {
	// Remove entry from DB
	$database->query("DELETE FROM ".TABLE_PREFIX."addons WHERE directory = '".$file."' AND type = 'template'");
}

// Update pages that use this template with default template
// $database = new database();
$database->query("UPDATE ".TABLE_PREFIX."pages SET template = '".DEFAULT_TEMPLATE."' WHERE template = '$file'");

// Print success message
$admin->print_success($MESSAGE['GENERIC_UNINSTALLED']);

// Print admin footer
$admin->print_footer();

?>