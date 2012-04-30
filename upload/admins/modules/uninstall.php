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
if ( defined( 'WB_PATH' ) )
{
	include( WB_PATH . '/framework/class.secure.php' );
}
elseif ( file_exists( $_SERVER[ 'DOCUMENT_ROOT' ] . '/framework/class.secure.php' ) )
{
	include( $_SERVER[ 'DOCUMENT_ROOT' ] . '/framework/class.secure.php' );
}
else
{
	$subs = explode( '/', dirname( $_SERVER[ 'SCRIPT_NAME' ] ) );
	$dir  = $_SERVER[ 'DOCUMENT_ROOT' ];
	$inc  = false;
	foreach ( $subs as $sub )
	{
		if ( empty( $sub ) )
			continue;
		$dir .= '/' . $sub;
		if ( file_exists( $dir . '/framework/class.secure.php' ) )
		{
			include( $dir . '/framework/class.secure.php' );
			$inc = true;
			break;
		}
	}
	if ( !$inc )
		trigger_error( sprintf( "[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER[ 'SCRIPT_NAME' ] ), E_USER_ERROR );
}
// end include class.secure.php

require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Addons', 'modules_uninstall');

// Check if user selected module
if(!isset($_POST['file']) OR $_POST['file'] == "") {
	header("Location: index.php");
	exit(0);
} else {
	$file = $admin->add_slashes($_POST['file']);
}

// Extra protection
if(trim($file) == '') {
	header("Location: index.php");
	exit(0);
}

// Include the WB functions file
require_once(WB_PATH.'/framework/functions.php');

// Check if the module exists
if(!is_dir(WB_PATH.'/modules/'.$file)) {
	$admin->print_error($MESSAGE['GENERIC_NOT_INSTALLED']);
}

$info = $database->query("SELECT `section_id`,`page_id` FROM `".TABLE_PREFIX."sections` WHERE `module`='".$_POST['file']."'" );

/**
 *	If the module is in use we've to warn the user.
 */
if ( $info->numRows() > 0) {

	/**
	 *	Try to get unique page_ids if e.g. the modul is used in more than one section on a page.
	 */
	$page_ids = array();
	while ( false != ($data = $info->fetchRow( MYSQL_ASSOC ) ) ) {
		if (!in_array($data['page_id'], $page_ids)) $page_ids[] = $data['page_id'];
	}
	
	if (!array_key_exists("CANNOT_UNINSTALL_IN_USE_TMPL", $MESSAGE['GENERIC'])) {
		$add = count( $page_ids ) == 1 ? "this page" : "these pages";
		$msg_template_str  = "<br /><br />{{type}} <b>{{type_name}}</b> could not be uninstalled because it is still in use on {{pages}}";
		$msg_template_str .= ":<br /><i>click for editing.</i><br /><br />";
	} else {
		$msg_template_str = $MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE_TMPL'];
		$temp = explode(";",$MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE_TMPL_PAGES']);
		$add = count( $page_ids ) == 1 ? $temp[0] : $temp[1];
	}

	/**
	 *	The template-string for displaying the Page-Titles ... in this case as a link
	 */
	$leptoken_str = (isset($_GET['leptoken'])) ? "&leptoken=".$_GET['leptoken'] : "";
	$page_template_str = "- <b><a href='../pages/sections.php?page_id={{id}}".$leptoken_str."'>{{title}}</a></b><br />";
	
	$values = array ('type' => 'Modul', 'type_name' => $file, 'pages' => $add );
	$msg = replace_all ( $msg_template_str,  $values );
		
	$page_names = "";
	
	foreach($page_ids as $temp_id) {	
		$temp = $database->query("SELECT `page_title` from `".TABLE_PREFIX."pages` where `page_id`=".$temp_id);
		$temp_title = $temp->fetchRow( MYSQL_ASSOC );
		
		$page_info = array(
			'id'	=> $temp_id, 
			'title' => $temp_title['page_title']
		);
			
		$page_names .= replace_all ( $page_template_str, $page_info );
	}
	
	/**
	 *	Printing out the error-message and die().
	 */
	$admin->print_error(str_replace ($TEXT['FILE'], "Modul", $MESSAGE['GENERIC_CANNOT_UNINSTALL_IN_USE']).$msg.$page_names);
}

/**
 *	Test for the standard wysiwyg-editor ...
 *
 */
if ( (defined('WYSIWYG_EDITOR') ) && ($_POST['file'] == WYSIWYG_EDITOR ) ) {
	$admin->print_error("The module <b>".WYSIWYG_EDITOR."</b> is the current standard wysiwyg editor and cannot be uninstalled unless you change the settings!");
}

// Check if we have permissions on the directory
if(!is_writable(WB_PATH.'/modules/'.$file)) {
	$admin->print_error($MESSAGE['GENERIC_CANNOT_UNINSTALL']);
}

// Run the modules uninstall script if there is one
if(file_exists(WB_PATH.'/modules/'.$file.'/uninstall.php')) {
	require(WB_PATH.'/modules/'.$file.'/uninstall.php');
}

// Try to delete the module dir
if(!rm_full_dir(WB_PATH.'/modules/'.$file)) {
	$admin->print_error($MESSAGE['GENERIC_CANNOT_UNINSTALL']);
} else {
	// Remove entry from DB
	$database->query("DELETE FROM ".TABLE_PREFIX."addons WHERE directory = '".$file."' AND type = 'module'");
}

// remove module permissions
$stmt = $database->query( 'SELECT * FROM '.TABLE_PREFIX.'groups WHERE group_id <> 1' );
if ( $stmt->numRows() > 0 ) {
    while( $row = $stmt->fetchRow(MYSQL_ASSOC) ) {
        $gid = $row['group_id'];
        // get current value
        $modules = explode(',', $row['module_permissions'] );
        // remove uninstalled module
        if ( in_array( $file, $modules ) ) {
            $i = array_search( $file, $modules );
            array_splice( $modules, $i, 1 );
            $modules = array_unique($modules);
            asort($modules);
            // Update the database
            $module_permissions = implode(',', $modules);
            $query = "UPDATE ".TABLE_PREFIX."groups SET module_permissions='$module_permissions' WHERE group_id='$gid';";
            $database->query($query);
            // ignore errors; we can't roll back anyway!
        }
    }
}

// Print success message
$admin->print_success($MESSAGE['GENERIC_UNINSTALLED']);

// Print admin footer
$admin->print_footer();

?>