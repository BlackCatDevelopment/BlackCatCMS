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
 * @license			http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
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

$backend = CAT_Backend::getInstance('Addons', 'modules_uninstall');
$val     = CAT_Helper_Validate::getInstance();

// Get name and type of add on
$type			= $val->sanitizePost('type',NULL,true);
$language_name	= $val->sanitizePost('file');
$file			= $type == 'languages' ? $language_name . '.php' : $language_name;

// Check if user selected a module
if ( trim($file) == '' || trim($type) == '' )
{
	header("Location: index.php");
	exit(0);
}

$js_back	= CAT_ADMIN_URL . '/addons/index.php';

// Include the WB functions file
require_once( CAT_PATH . '/framework/functions.php');

// Check if the module exists
if ( !file_exists( CAT_PATH . '/' . $type  . '/' . $file) )
{
	$backend->print_error( 'Not installed' , $js_back );
}
// Check if we have permissions on the directory
if ( !is_writable( CAT_PATH . '/' . $type . '/' . $file) )
{
	$backend->print_error( 'Unable to write to the target directory' , $js_back );
}

	// Check if the language is in use
if ( $type == 'languages' && ( $language_name == DEFAULT_LANGUAGE || $language_name == LANGUAGE ) )
{
	$temp	= array (
		'name'	=> $language_name,
		'type'	=> $language_name == DEFAULT_LANGUAGE ? 
			$backend->lang()->translate('standard language') : $backend->lang()->translate('language')
	);
	$admin->print_error( $backend->lang()->translate( 'Can\'t uninstall this language <strong>{{name}}</strong> because it\'s the {{type}}!', $temp ), $js_back );
}
elseif ( $type == 'languages' )
{
	$query_users	= $backend->db()->query("SELECT user_id FROM " . CAT_TABLE_PREFIX . "users WHERE language = '" . $language_name . "' LIMIT 1");
	if ( $query_users->numRows() > 0 )
	{
		$backend->print_error( 'Cannot Uninstall: the selected file is in use', $js_back );
	}
}
elseif ( $type == 'modules' )
{
	// check if the module is still in use
	$info	= $backend->db()->query("SELECT section_id, page_id FROM " . CAT_TABLE_PREFIX . "sections WHERE module = '" . $file . "'");
	
	if ( $info->numRows() > 0 )
	{
		// ================================================= 
		// ! Module is in use, so we have to warn the user   
		// =================================================  
		$temp				= explode(";", $admin->lang->translate( 'this page;these pages' ) );
		$add				= $info->numRows() == 1 ? $temp[0] : $temp[1];
	
		$values = array(
			'type'			=> $backend->lang()->translate( 'Module' ),
			'type_name'		=> $file,
			'pages'			=> $add
		);
	
		// ============================================= 
		// ! Printing out the error-message and die().   
		// ============================================= 
		print_r($values);*/
		$backend->print_error( $backend->lang()->translate( 'Cannot Uninstall: the selected {{type}} is in use.' , $values ), $js_back );
	}
	/**
	 *	Test for the standard wysiwyg-editor ...
	 *
	 */
	if ( (defined('WYSIWYG_EDITOR')) && ( $file == WYSIWYG_EDITOR ) )
	{
		$values = array(
			'type'				=> $backend->lang()->translate( 'Module' ),
			'name'				=> WYSIWYG_EDITOR,
			'standard'			=> $backend->lang()->translate( 'Default WYSIWYG' )
		);
		$backend->print_error( $backend->lang()->translate( 'Can\'t uninstall the {{name}} <b>{{name}}</b>, because it is the {{standard}}!', $values ), $js_back );
	}
	// Run the modules' uninstall script if there is one
	if (file_exists( CAT_PATH . '/' . $type . '/' . $file . '/uninstall.php'))
	{
		require( CAT_PATH . '/' . $type . '/' . $file . '/uninstall.php');
	}
}
elseif ( $type == 'templates' && ( $file == DEFAULT_THEME || $file == DEFAULT_TEMPLATE ) )
{
	$temp	= array (
		'name'	=> $file,
		'type'	=> $file == DEFAULT_TEMPLATE ? 
			$backend->lang()->translate('standardtemplate') : $backend->lang()->translate('standardtheme')
	);
	$backend->print_error( $backend->lang()->translate( 'Can\'t uninstall this template <strong>{{name}}</strong> because it\'s the {{type}}!', $temp ), $js_back );
}

elseif ( $type == 'templates' )
{
	
	/**
	*	Check if the template is still in use by a page ...
	*/
	$info	= $backend->db()->->query( "SELECT page_id, page_title FROM " . CAT_TABLE_PREFIX . "pages WHERE template='" . $file . "' order by page_title" );
	if ( $info->numRows() > 0 )
	{
		/**
		*	Template is still in use, so we're collecting the page-titles
		*/
		/**
		*	The base-message template-string for the top of the message
		*/
		$msg_template_str	= '{{type}} <strong>{{type_name}}</strong> could not be uninstalled, because it is still in use on {{pages}}:';
		$temp				= explode( ';', $backend->lang()->translate( 'this page;these pages' ) );
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
		$msg	= $backend->lang()->translate( $msg_template_str,  $values );

		$page_names			 = '<ul>';
		while ($data = $info->fetchRow() )
		{
			$page_info = array(
				'id'	=> $data['page_id'], 
				'title'	=> $data['page_title']
			);
			$page_names		.= $backend->lang()->translate( $page_template_str, $page_info );
		}
		$page_names			.= '</ul>';
		/**
		*	Printing out the error-message and die().
		*/
		$backend->print_error( 'Cannot Uninstall: the selected file is in use' . $msg . $page_names, $js_back );
	}
}
else {
	$backend->print_error( 'Type of add on not found.' , $js_back );
}


// Try to delete the module dir
if ( !rm_full_dir(CAT_PATH . '/' . $type . '/' . $file) )
{
	$backend->print_error( 'Cannot uninstall', $js_back );
}
else
{
	// Remove entry from DB
	if ( $type != 'languages' ) $backend->db()->->query("DELETE FROM " . CAT_TABLE_PREFIX . "addons WHERE directory = '" . $file . "' AND type = '" . substr( $type, 0, -1 ) . "'");
	else $backend->db()->->query("DELETE FROM " . CAT_TABLE_PREFIX . "addons WHERE directory = '" . $language_name . "' AND type = '" . substr( $type, 0, -1 ) . "'");
}

// ============================= 
// ! remove module permissions   
// ============================= 
if ( $type != 'languages' )
{
	$stmt = $backend->db()->->query('SELECT * FROM ' . CAT_TABLE_PREFIX . 'groups WHERE group_id <> 1');
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
				$backend->db()->->query("UPDATE " . CAT_TABLE_PREFIX . "groups SET " . substr( $type, 0, -1 ) . "_permissions = '$addon_permissions' WHERE group_id='$gid'");
			}
		}
	}
}

// ========================= 
// ! Print success message   
// ========================= 
$backend->print_success( 'Uninstalled successfully' );

// Print admin footer
$backend->print_footer();

?>