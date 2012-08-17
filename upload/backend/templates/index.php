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

require_once(WB_PATH.'/framework/class.admin.php');
$admin	= new admin('Addons', 'templates');

$data_dwoo['URL']	= array(
	'MODULES'		=> $admin->get_permission('templates')  ? ADMIN_URL . '/modules/index.php' : false,
	'TEMPLATES'		=> ADMIN_URL . '/templates/index.php',
	'LANGUAGES'		=> $admin->get_permission('languages')  ? ADMIN_URL . '/languages/index.php' : false,
);

// Insert values into template list
$result = $database->query("SELECT * FROM " . TABLE_PREFIX . "addons WHERE type = 'template' order by name");
if ( $result->numRows() > 0 )
{
	$counter=0;
	while ( $addon = $result->fetchRow() )
	{
		// check if a template description exists for the displayed backend language
		$tool_description = false;
		if ( function_exists('file_get_contents') && file_exists(WB_PATH.'/templates/'.$addon['directory'].'/languages/' . LANGUAGE .'.php') )
		{
			// read contents of the template language file into string
			$data					= @file_get_contents(WB_PATH .'/templates/' .$addon['directory'] .'/languages/' . LANGUAGE .'.php');
			// use regular expressions to fetch the content of the variable from the string
			$tool_description		= get_variable_content('template_description', $data, false, false);
			// replace optional placeholder {WB_URL} with value stored in config.php
			if ( $tool_description !== false && strlen(trim($tool_description)) != 0 )
			{
				$tool_description	= str_replace('{WB_URL}', WB_URL, $tool_description);
			}
			else
			{
				$tool_description	= false;
			}
		}
		if ( $tool_description !== false )
		{
			// Override the template-description with correct desription in users language
			$addon['description']			= $tool_description;
		}

		$data_dwoo['templates'][$counter]	= array(
			'NAME'			=> $addon['name'],
			'VALUE'			=> $addon['directory'],
			'AUTHOR'		=> $addon['author'],
			'DESCRIPTION'	=> $addon['description'],
			'VERSION'		=> $addon['version'],
			'DESIGNED_FOR'	=> $addon['platform'],
			'LICENSE'		=> $addon['license']
		);

		// ===================================================== 
		// ! Check whether icon is available for the admintool   
		// ===================================================== 
		if ( file_exists(WB_PATH.'/templates/'.$addon['directory'].'/icon.png') )
		{
			list($icon_width, $icon_height, $icon_type, $icon_attr)		= getimagesize( WB_PATH . '/templates/' . $addon['directory'] . '/icon.png' );
			$data_dwoo['templates'][$counter]['ICON']		= ($icon_width == 32 && $icon_height == 32 && $icon_type == 3) ?
				WB_URL . '/templates/' . $addon['directory'] . '/icon.png' :
				false;
		}
		else
		{
			$data_dwoo['templates'][$counter]['ICON']		= false;
		}
		if ( file_exists( WB_PATH.'/templates/' . $addon['directory'] . '/preview.jpg') )
		{
			list($preview_width, $preview_height, $preview_type, $preview_attr)		= getimagesize( WB_PATH . '/templates/' . $addon['directory'] . '/preview.jpg' );
			$data_dwoo['templates'][$counter]['PREVIEW']		= ($preview_width == 200 && $preview_height == 160 && $preview_type == 2) ?
				WB_URL . '/templates/' . $addon['directory'] . '/preview.jpg' :
				false;
		}
		else
		{
			$data_dwoo['templates'][$counter]['PREVIEW']	= false;
		}

		if ( file_exists( WB_PATH . '/templates/' . $addon['directory'] . '/index.php') )
		{

			if ( ! valid_lepton_template( WB_PATH . '/templates/' . $addon['directory'] . '/index.php' ) )
			{
				$data_dwoo['templates'][$counter]['WARNING']	=
					 '<h2>Invalid LEPTON 2.x Template!</h2>'
					.'The template uses <tt>register_frontend_modfiles()</tt>, which is deprecated. '
					.'Use <tt>get_page_headers()</tt> instead. Please inform the template author.<br />'
					.'Detailed information about the <tt>get_page_headers()</tt> method are available in the LEPTON Wiki:<br />'
					.'<a href="http://wiki.lepton-cms.org/en/index.php?title=Manual:Tutorials/Headers">http://wiki.lepton-cms.org/en/index.php?title=Manual:Tutorials/Headers</a>';
			}
		}
		$counter++;
	}
}
$data_dwoo['DEFAULT_THEME']		= DEFAULT_THEME;
$data_dwoo['DEFAULT_TEMPLATE']	= DEFAULT_TEMPLATE;


require_once(WB_PATH . '/framework/class.pages.php');
$pages = new pages();

$data_dwoo['groups']				= $pages->get_groups('' , '', false);

// Insert permissions values
$data_dwoo['permissions']['TEMPLATES_VIEW']			= $admin->get_permission('templates_view')		? true : false;
$data_dwoo['permissions']['TEMPLATES_INSTALL']		= $admin->get_permission('templates_install')	? true : false;
$data_dwoo['permissions']['TEMPLATES_UNINSTALL']	= $admin->get_permission('templates_uninstall')	? true : false;

// print page
$parser->output( 'backend_templates_index.lte', $data_dwoo );

// Print admin footer
$admin->print_footer();

?>