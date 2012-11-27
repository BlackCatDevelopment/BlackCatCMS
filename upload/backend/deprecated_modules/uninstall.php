<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
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

require_once(WB_PATH . '/framework/class.admin.php');
$admin		= new admin('Addons', 'modules_uninstall');

$file		= $admin->add_slashes( $admin->get_post('file') );

// Check if user selected a module
if ( trim($file) == '' )
{
	header("Location: index.php");
	exit(0);
}

global $parser;
$js_back	= ADMIN_URL . '/modules/index.php';

// Include the WB functions file
require_once(WB_PATH . '/framework/functions.php');

// Check if the module exists
if ( !is_dir(WB_PATH . '/modules/' . $file) )
{
	$admin->print_error( 'Not installed' , false );
}
else
{
	// check if the module is still in use
	$info	= $database->query("SELECT section_id, page_id FROM " . TABLE_PREFIX . "sections WHERE module='" . $file . "'");

	if ( $info->numRows() > 0 )
	{
		// ================================================= 
		// ! Module is in use, so we have to warn the user   
		// =================================================  
		$temp				= explode(";", $admin->lang->translate( 'this page;these pages' ) );
		$add				= $info->numRows() == 1 ? $temp[0] : $temp[1];

		$values = array(
			'type'			=> $admin->lang->translate( 'Modul' ),
			'type_name'		=> $file,
			'pages'			=> $add
		);
		$data_dwoo['message']	= $admin->lang->translate( '{{type}} <b>{{type_name}}</b> could not be uninstalled, because it is still in use on {{pages}}', $values );

		$data_dwoo['pages']		= array();
		while ( false != ( $data = $info->fetchRow( MYSQL_ASSOC ) ) )
		{
			// skip negative page id's
			if ( substr( $data['page_id'], 0, 1 ) == '-' )
			{
				continue;
			}
			$temp			= $database->query("SELECT page_title FROM " . TABLE_PREFIX . "pages WHERE page_id = " . $data['page_id']);
			$temp_title		= $temp->fetchRow( MYSQL_ASSOC );

			$data_dwoo['pages'][]	= array(
				'id'		=> $data['page_id'],
				'title'		=> $temp_title['page_title']
			);
		}
		$data_dwoo['header']	= $admin->lang->translate( 'Cannot Uninstall: the selected {{type}} is in use.' , $values );

		// ============================================= 
		// ! Printing out the error-message and die().   
		// ============================================= 
		$admin->print_error( $parser->get( 'backend_modules_uninstall.lte', $data_dwoo ), $js_back );
	}
}

/**
 *	Test for the standard wysiwyg-editor ...
 *
 */
if ( (defined('WYSIWYG_EDITOR')) && ( $file == WYSIWYG_EDITOR ) )
{
	$type	= array(
		'type'	=> $admin->lang->translate( 'Modul' )
	);
	$data_dwoo['header']	= $admin->lang->translate( 'Cannot Uninstall: the selected {{type}} is in use.' , $type );
	$values = array(
		'type'				=> $admin->lang->translate( 'Modul' ),
		'name'				=> WYSIWYG_EDITOR,
		'standard'			=> $admin->lang->translate( 'Default WYSIWYG' )
	);
	$data_dwoo['message']	= $admin->lang->translate( 'Can\'t uninstall the {{type}} <b>{{name}}</b>, because it is the {{standard}}!', $values );
	$admin->print_error( $parser->get( 'backend_modules_uninstall.lte', $data_dwoo ), $js_back );
}

// Check if we have permissions on the directory
if ( !is_writable(WB_PATH . '/modules/' . $file) )
{
	$data_dwoo['header']	= $admin->lang->translate( 'Unable to write to the target directory' );
	$admin->print_error( $parser->get( 'backend_modules_uninstall.lte', $data_dwoo ), $js_back );
}

// Run the modules' uninstall script if there is one
if (file_exists(WB_PATH . '/modules/' . $file . '/uninstall.php'))
{
	require(WB_PATH . '/modules/' . $file . '/uninstall.php');
}

// Try to delete the module dir
if ( !rm_full_dir(WB_PATH . '/modules/' . $file) )
{
	$data_dwoo['header']	= $admin->lang->translate( 'Cannot uninstall' );
	$admin->print_error( $parser->get( 'backend_modules_uninstall.lte', $data_dwoo ), $js_back );
}
else
{
	// Remove entry from DB
	$database->query("DELETE FROM " . TABLE_PREFIX . "addons WHERE directory = '" . $file . "' AND type = 'module'");
}

// ============================= 
// ! remove module permissions   
// ============================= 
$stmt = $database->query('SELECT * FROM ' . TABLE_PREFIX . 'groups WHERE group_id <> 1');
if ($stmt->numRows() > 0)
{
	while ( $row = $stmt->fetchRow(MYSQL_ASSOC) )
	{
		$gid		= $row['group_id'];
		// get current value
		$modules = explode(',', $row['module_permissions']);
		// remove uninstalled module
		if (in_array($file, $modules))
		{
			$i = array_search($file, $modules);
			array_splice($modules, $i, 1);
			$modules = array_unique($modules);
			asort($modules);
			// Update the database
			$module_permissions		= implode(',', $modules);
			$query					= "UPDATE " . TABLE_PREFIX . "groups SET module_permissions='$module_permissions' WHERE group_id='$gid';";
			$database->query($query);
			// ignore errors; we can't roll back anyway!
		}
	}
}

// ========================= 
// ! Print success message   
// ========================= 
$admin->print_success(
	$parser->get(
		'backend_modules_uninstall.lte',
		array(
			'header'	=> $admin->lang->translate( 'Uninstalled successfully' )
		)
	)
);

// Print admin footer
$admin->print_footer();

?>