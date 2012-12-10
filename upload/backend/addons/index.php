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
$admin = new admin('Addons', 'addons');


global $parser;
$data_dwoo	= array();

$data_dwoo['URL'] = array(
	'addons'		=> ADMIN_URL . '/modules/index.php',
	'TEMPLATES'		=> $admin->get_permission('templates') ? ADMIN_URL . '/templates/index.php' : false,
	'LANGUAGES'		=> $admin->get_permission('languages') ? ADMIN_URL . '/languages/index.php' : false,
);

// Insert permissions values
$data_dwoo['permissions']['ADVANCED']			= $admin->get_permission('admintools') ? true : false;
$data_dwoo['permissions']['MODULES_VIEW']		= $admin->get_permission('modules_view') ? true : false;
$data_dwoo['permissions']['MODULES_INSTALL']	= $admin->get_permission('modules_install') ? true : false;
$data_dwoo['permissions']['MODULES_UNINSTALL']	= $admin->get_permission('modules_uninstall') ? true : false;


$counter	= 0;
$result		= $database->query("SELECT * FROM " . TABLE_PREFIX . "addons ORDER BY name");
if ($result->numRows() > 0)
{
	while ( $addon = $result->fetchRow() )
	{
		// check if a module description exists for the displayed backend language
		$tool_description	= false;
		switch ($addon['type'])
		{
			case 'module':
				$type	= 'modules';
				break;
			case 'language':
				$type				= 'languages';
				// Clear all variables
				$language_code		= '';
				$language_name		= '';
				$language_version	= '';
				$language_platform	= '';
				$language_author	= '';
				$language_license	= '';

				// Insert values
				if ( file_exists(LEPTON_PATH.'/languages/'.$addon['directory'].'.php'))
				{
					require( LEPTON_PATH . '/languages/' . $addon['directory'] . '.php');
					$addon['name']			= $language_name;
					$addon['author']		= $addon['author'] != '' ? $addon['author'] : $language_author;
					$addon['version']		= $language_version;
					$addon['platform']		= $language_platform;
					$addon['license']		= $language_license;
				}
				require( LEPTON_PATH . '/languages/' . LANGUAGE . '.php');
				break;
			case 'template':
				$type	= 'templates';
				break;
			default:
				$type	= 'modules';
		}
		if ( $type != 'languages' && ( function_exists('file_get_contents') && file_exists(LEPTON_PATH . '/' . $type . '/' . $addon['directory'] . '/languages/' . LANGUAGE . '.php' ) ) )
		{
			// read contents of the module language file into string
			$description			= @file_get_contents(LEPTON_PATH . '/' . $type . '/' . $file . '/languages/' . LANGUAGE . '.php');
			// use regular expressions to fetch the content of the variable from the string
			$tool_description		= get_variable_content('module_description', $description, false, false);
			// replace optional placeholder {WB_URL} with value stored in config.php
			if ($tool_description !== false && strlen(trim($tool_description)) != 0)
			{
				$tool_description	= str_replace('{WB_URL}', WB_URL, $tool_description);
			}
			else
			{
				$tool_description = false;
			}
		}		
		if ($tool_description !== false)
		{
			// Override the module-description with correct desription in users language
			$data_dwoo['addons'][$counter]['description']	= $tool_description;
		}
		// Set a number to dimension $addon[directory] to see
		$modules_count[$addon['directory']] = $addon['directory'];
		
		$data_dwoo['addons'][$counter] = array(
			'name'			=> $addon['name'],
			'author'		=> $addon['author'],
			'description'	=> $addon['description'],
			'version'		=> $addon['version'],
			'platform'		=> $addon['platform'],
			'license'		=> $addon['license'],
			'directory'		=> $addon['directory'],
			'function'		=> $addon['function'],
			'type'			=> $type
		);
		// ================================================== 
		// ! Check whether icon is available for the module   
		// ================================================== 
		if(file_exists(LEPTON_PATH . '/' . $type . '/' . $addon['directory'] . '/icon.png')){
			list($width, $height, $type_of, $attr) = getimagesize( LEPTON_PATH . '/' . $type . '/' . $addon['directory'] . '/icon.png');
			// Check whether file is 32*32 pixel and is an PNG-Image
			$data_dwoo['addons'][$counter]['icon'] = ($width == 32 && $height == 32 && $type_of == 3) ?
				WB_URL . '/' . $type . '/' . $addon['directory'] . '/icon.png' :
				false;
		}

		switch ($addon['function'])
		{
			case NULL:
				$type_name	= $admin->lang->translate( 'Unknown' );
				break;
			case 'page':
				$type_name	= $admin->lang->translate( 'Page' );
				break;
			case 'wysiwyg':
				$type_name	= $admin->lang->translate( 'WYSIWYG Editor' );
				break;
			case 'tool':
				$type_name	= $admin->lang->translate( 'Administration tool' );
				break;
			case 'admin':
				$type_name	= $admin->lang->translate( 'Admin' );
				break;
			case 'administration':
				$type_name	= $admin->lang->translate( 'Administration' );
				break;
			case 'snippet':
				$type_name	= $admin->lang->translate( 'Code-Snippet' );
				break;
			case 'library':
				$type_name	= $admin->lang->translate( 'Library' );
				break;
			default:
				$type_name	= $admin->lang->translate( 'Unknown' );
		}

		$data_dwoo['addons'][$counter]['function'] = $type_name;

		// Check if the module is installable or upgradeable
		$data_dwoo['addons'][$counter]['INSTALL']		= file_exists(LEPTON_PATH . '/' . $type . '/' . $addon['directory'] . '/install.php') ? true : false;
		$data_dwoo['addons'][$counter]['UPGRADE']		= file_exists(LEPTON_PATH . '/' . $type . '/' . $addon['directory'] . '/upgrade.php') ? true : false;

		$counter++;
	}
}

require_once(LEPTON_PATH . '/framework/class.pages.php');
$pages = new pages();

$data_dwoo['groups']				= $pages->get_groups('' , '', false);

// Insert modules which includes a install.php file to install list
$module_files = glob(LEPTON_PATH . '/' . $type . '/*');

foreach ($module_files as $index => $path)
{
	if ( is_dir($path) && empty($modules_count[basename($path)]) )
	{
		if (file_exists($path . '/install.php'))
		{
			$data_dwoo['addons'][$counter]['name']			= basename($path);
			$data_dwoo['addons'][$counter]['directory']		= basename($path);
			$data_dwoo['addons'][$counter]['INSTALL']		= true;
		}
		/*
		When modules are not already installed, they shouldn't be upgradeable
		if (file_exists($path . '/'))
		{
			$data_dwoo['addons'][$counter]['UPGRADE'] = array(
				'directory'		=> basename($path),
				'name'			=> basename($path)
			);
			$data_dwoo['addons'][$counter]['UPGRADE'] = true;
		}*/
		$counter++;

	}
	else
	{
		unset($module_files[$index]);
	}
}

// print page
$parser->output( 'backend_addons_index.lte', $data_dwoo );

// Print admin footer
$admin->print_footer();

?>