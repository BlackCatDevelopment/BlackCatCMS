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

require_once( WB_PATH . '/framework/class.admin.php' );
$admin = new admin('Addons', 'templates_uninstall');

$file	= trim( $admin->get_post('file') );

// =================================== 
// ! Check if user selected template   
// =================================== 
if ( $file == '' )
{
	header("Location: index.php");
	exit(0);
}

// ================================= 
// ! Include the WB functions file   
// ================================= 
require_once( WB_PATH . '/framework/functions.php' );

// ================================ 
// ! Check if the template exists   
// ================================ 
if ( !is_dir( WB_PATH . '/templates/' . $file ) )
{
	$admin->print_error( 'Not installed' );
}

// ========================================================== 
// ! check whether the template is used as default wb theme   
// ========================================================== 
if ( $file == DEFAULT_THEME || $file == DEFAULT_TEMPLATE )
{
	$temp	= array (
		'name'	=> $file,
		'type'	=> $file == DEFAULT_TEMPLATE ? 
			$admin->lang->translate('standardtemplate') : $admin->lang->translate('standardtheme')
	);
	$admin->lang->translate( 'Can\'t uninstall this template <strong>{{name}}</strong> because it\'s the {{type}}!', $temp );
	$admin->print_error( $msg );
}

else
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
		$admin->print_error( $admin->lang->translate('Cannot Uninstall: the selected file is in use') . $msg . $page_names );
	}
}

// ================================================= 
// ! Check if we have permissions on the directory   
// ================================================= 
if ( !is_writable( WB_PATH . '/templates/' . $file ) )
{
	$admin->print_error( $admin->lang->translate('Cannot uninstall') . WB_PATH . '/templates/' . $file);
}

// ================================== 
// ! Try to delete the template dir   
// ================================== 
if ( !rm_full_dir( WB_PATH . '/templates/' . $file ) )
{
	$admin->print_error( 'Cannot uninstall' );
}
else
{
	// ======================== 
	// ! Remove entry from DB   
	// ======================== 
	$database->query( "DELETE FROM " . TABLE_PREFIX . "addons WHERE directory = '" . $file . "' AND type = 'template'" );
}

// ============================================================= 
// ! Update pages that use this template with default template   
// ============================================================= 
$database->query( "UPDATE " . TABLE_PREFIX . "pages SET template = '" . DEFAULT_TEMPLATE . "' WHERE template = '$file'" );

// ========================= 
// ! Print success message   
// ========================= 
$admin->print_success( 'Uninstalled successfully' );

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>