<?php

/**
 * This file is part of LEPTON2 Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author			LEPTON2 Project
 * @copyright		2012, LEPTON2 Project
 * @link			http://lepton2.org
 * @license			http://www.gnu.org/licenses/gpl.html
 * @license_terms	please see LICENSE and COPYING files in your package
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {
	include(LEPTON_PATH.'/framework/class.secure.php');
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

require_once(LEPTON_PATH . '/framework/class.admin.php');
$admin		= new admin('Addons', 'modules_uninstall');


// Get name and type of add on
$type			= $admin->add_slashes( $admin->get_post('type') );
$language_name	= $admin->get_post('file');
$file			= $type == 'languages' ? $language_name . '.php' : $language_name;

// Check if user selected a module
if ( trim($file) == '' || trim($type) == '' )
{
	header("Location: index.php");
	exit(0);
}

$js_back	= ADMIN_URL . '/addons/index.php';

// Include the WB functions file
require_once( LEPTON_PATH . '/framework/functions.php');

// Check if the module exists
if ( !file_exists( LEPTON_PATH . '/' . $type  . '/' . $file) )
{
	$admin->print_error( 'Not installed' , $js_back );
}
// Check if we have permissions on the directory
if ( !is_writable( LEPTON_PATH . '/' . $type . '/' . $file) )
{
	$admin->print_error( 'Unable to write to the target directory' , $js_back );
}

	// Check if the language is in use
if ( $type == 'languages' && ( $language_name == DEFAULT_LANGUAGE || $language_name == LANGUAGE ) )
{
	$temp	= array (
		'name'	=> $language_name,
		'type'	=> $language_name == DEFAULT_LANGUAGE ? 
			$admin->lang->translate('standard language') : $admin->lang->translate('language')
	);
	$admin->print_error( $admin->lang->translate( 'Can\'t uninstall this language <strong>{{name}}</strong> because it\'s the {{type}}!', $temp ), $js_back );
}
elseif ( $type == 'languages' )
{
	$query_users	= $database->query("SELECT user_id FROM " . TABLE_PREFIX . "users WHERE language = '" . $language_name . "' LIMIT 1");
	if ( $query_users->numRows() > 0 )
	{
		$admin->print_error( 'Cannot Uninstall: the selected file is in use', $js_back );
	}
}
elseif ( $type == 'modules' )
{
	// check if the module is still in use
	$info	= $database->query("SELECT section_id, page_id FROM " . TABLE_PREFIX . "sections WHERE module = '" . $file . "'");
	
	if ( $info->numRows() > 0 )
	{
		// ================================================= 
		// ! Module is in use, so we have to warn the user   
		// =================================================  
		$temp				= explode(";", $admin->lang->translate( 'this page;these pages' ) );
		$add				= $info->numRows() == 1 ? $temp[0] : $temp[1];
	
		$values = array(
			'type'			=> $admin->lang->translate( 'Module' ),
			'type_name'		=> $file,
			'pages'			=> $add
		);
	
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
	
			$values['pages'][]	= array(
				'id'		=> $data['page_id'],
				'title'		=> $temp_title['page_title']
			);
		}
		// ============================================= 
		// ! Printing out the error-message and die().   
		// ============================================= 
		$admin->print_error( $admin->lang->translate( 'Cannot Uninstall: the selected {{type}} is in use.' , $values ), $js_back );
	}
	/**
	 *	Test for the standard wysiwyg-editor ...
	 *
	 */
	if ( (defined('WYSIWYG_EDITOR')) && ( $file == WYSIWYG_EDITOR ) )
	{
		$values = array(
			'type'				=> $admin->lang->translate( 'Module' ),
			'name'				=> WYSIWYG_EDITOR,
			'standard'			=> $admin->lang->translate( 'Default WYSIWYG' )
		);
		$admin->print_error( $admin->lang->translate( 'Can\'t uninstall the {{name}} <b>{{name}}</b>, because it is the {{standard}}!', $values ), $js_back );
	}
	// Run the modules' uninstall script if there is one
	if (file_exists( LEPTON_PATH . '/' . $type . '/' . $file . '/uninstall.php'))
	{
		require( LEPTON_PATH . '/' . $type . '/' . $file . '/uninstall.php');
	}
}
elseif ( $type == 'templates' && ( $file == DEFAULT_THEME || $file == DEFAULT_TEMPLATE ) )
{
	$temp	= array (
		'name'	=> $file,
		'type'	=> $file == DEFAULT_TEMPLATE ? 
			$admin->lang->translate('standardtemplate') : $admin->lang->translate('standardtheme')
	);
	$admin->print_error( $admin->lang->translate( 'Can\'t uninstall this template <strong>{{name}}</strong> because it\'s the {{type}}!', $temp ), $js_back );
}

elseif ( $type == 'templates' )
{
	
	/**
	*	Check if the template is still in use by a page ...
	*/
	$info	= $database->query( "SELECT page_id, page_title FROM " . TABLE_PREFIX . "pages WHERE template='" . $file . "' order by page_title" );
	if ( $info->numRows() > 0 )
	{
		/**
		*	Template is still in use, so we're collecting the page-titles
		*/
		/**
		*	The base-message template-string for the top of the message
		*/
		$msg_template_str	= '{{type}} <strong>{{type_name}}</strong> could not be uninstalled, because it is still in use on {{pages}}:';
		$temp				= explode( ';', $admin->lang->translate( 'this page;these pages' ) );
		$add				= $info->numRows() == 1 ? $temp[0] : $temp[1];
		/**
		*	The template-string for displaying the Page-Titles ... in this case as a link
		*/
		$page_template_str	= "<li><a href='../pages/settings.php?page_id={{id}}'>{{title}}</a></li>";

		$values = array (
			'type'		=> 'Template',
			'type_name'	=> $file,
			'pages'		=> $add
		);
		$msg	= $admin->lang->translate( $msg_template_str,  $values );

		$page_names			 = '<ul>';
		while ($data = $info->fetchRow() )
		{
			$page_info = array(
				'id'	=> $data['page_id'], 
				'title'	=> $data['page_title']
			);
			$page_names		.= $admin->lang->translate( $page_template_str, $page_info );
		}
		$page_names			.= '</ul>';
		/**
		*	Printing out the error-message and die().
		*/
		$admin->print_error( 'Cannot Uninstall: the selected file is in use' . $msg . $page_names, $js_back );
	}
}
else {
	$admin->print_error( 'Type of add on not found.' , $js_back );
}


// Try to delete the module dir
if ( !rm_full_dir(LEPTON_PATH . '/' . $type . '/' . $file) )
{
	$admin->print_error( 'Cannot uninstall', $js_back );
}
else
{
	// Remove entry from DB
	if ( $type != 'languages' ) $database->query("DELETE FROM " . TABLE_PREFIX . "addons WHERE directory = '" . $file . "' AND type = '" . substr( $type, 0, -1 ) . "'");
	else $database->query("DELETE FROM " . TABLE_PREFIX . "addons WHERE directory = '" . $language_name . "' AND type = '" . substr( $type, 0, -1 ) . "'");
}

// ============================= 
// ! remove module permissions   
// ============================= 
if ( $type != 'languages' )
{
	$stmt = $database->query('SELECT * FROM ' . TABLE_PREFIX . 'groups WHERE group_id <> 1');
	if ($stmt->numRows() > 0)
	{
		while ( $row = $stmt->fetchRow(MYSQL_ASSOC) )
		{
			$gid		= $row['group_id'];
			// get current value
			$permissions = explode(',', $row[ substr( $type, 0, -1 ) . '_permissions']);
			// remove uninstalled module
			if (in_array($file, $permissions))
			{
				$i = array_search($file, $permissions);
				array_splice($permissions, $i, 1);
				$permissions = array_unique($permissions);
				asort($permissions);
				// Update the database
				$addon_permissions		= implode(',', $permissions);
				$database->query("UPDATE " . TABLE_PREFIX . "groups SET " . substr( $type, 0, -1 ) . "_permissions = '$addon_permissions' WHERE group_id='$gid'");
			}
		}
	}
}

// ========================= 
// ! Print success message   
// ========================= 
$admin->print_success( 'Uninstalled successfully' );

// Print admin footer
$admin->print_footer();

?>