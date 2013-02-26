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
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) {
			include($dir.'/framework/class.secure.php'); $inc = true;	break;
	}
	}
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

require_once(CAT_PATH . '/framework/class.admin.php');
$admin = new admin('Addons', 'addons');
$user  = CAT_Users::getInstance();
$date  = CAT_Helper_DateTime::getInstance();

global $parser;
$tpl_data = array();

$tpl_data['URL'] = array(
	'addons'		=> CAT_ADMIN_URL . '/modules/index.php',
	'TEMPLATES'	=> $user->get_permission('templates') ? CAT_ADMIN_URL . '/templates/index.php' : false,
	'LANGUAGES'	=> $user->get_permission('languages') ? CAT_ADMIN_URL . '/languages/index.php' : false,
);

// Insert permissions values
$tpl_data['permissions']['ADVANCED']		  = $user->get_permission('admintools')        ? true : false;
$tpl_data['permissions']['MODULES_VIEW']	  = $user->get_permission('modules_view')      ? true : false;
$tpl_data['permissions']['MODULES_INSTALL']	  = $user->get_permission('modules_install')   ? true : false;
$tpl_data['permissions']['MODULES_UNINSTALL'] = $user->get_permission('modules_uninstall') ? true : false;


$counter	= 0;
$seen_dirs  = array();
$result		= $database->query("SELECT * FROM " . CAT_TABLE_PREFIX . "addons ORDER BY name");
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
                // for later use
                $seen_dirs[] = $addon['directory'];
				break;
			case 'language':
				$type				= 'languages';
				// Clear all variables
                $vars = get_defined_vars();
                foreach( array_keys($vars) as $var )
                {
                    if ( preg_match( '~^language_~i', $var ) )
                    {
                        ${$var} = '';
                    }
                }
				// Insert values
				if ( file_exists(CAT_PATH.'/languages/'.$addon['directory'].'.php'))
				{
					require( CAT_PATH . '/languages/' . $addon['directory'] . '.php');
					$addon['name']			= $language_name;
					$addon['author']		= $addon['author'] != '' ? $addon['author'] : $language_author;
					$addon['version']		= $language_version;
					$addon['platform']		= $language_platform;
					$addon['license']		= $language_license;
				}
				require( CAT_PATH . '/languages/' . LANGUAGE . '.php');
				break;
			case 'template':
				$type	= 'templates';
				break;
			default:
				$type	= 'modules';
		}

        $langfile = CAT_Helper_Directory::getInstance()->sanitizePath(CAT_PATH.'/'.$type.'/'.$addon['directory'].'/languages/'.LANGUAGE.'.php');
		if ( $type != 'languages' && ( function_exists('file_get_contents') && file_exists($langfile) ) )
		{
			// read contents of the module language file into string
			$description			= @file_get_contents($langfile);
			// use regular expressions to fetch the content of the variable from the string
			$tool_description		= get_variable_content('module_description', $description, false, false);
			// replace optional placeholder {CAT_URL} with value stored in config.php
			if ($tool_description !== false && strlen(trim($tool_description)) != 0)
			{
				$tool_description	= str_replace('{CAT_URL}', CAT_URL, $tool_description);
			}
			else
			{
				$tool_description = false;
			}
		}		

		// Set a number to dimension $addon[directory] to see
		$modules_count[$addon['directory']] = $addon['directory'];
		
		$tpl_data['addons'][$counter] = array(
			'name'			=> $addon['name'],
			'author'		=> $addon['author'],
			'description'	=> $addon['description'],
			'version'		=> $addon['version'],
			'platform'		=> $addon['platform'],
			'license'		=> $addon['license'],
			'directory'		=> $addon['directory'],
			'function'		=> $addon['function'],
            'installed'     => ( ($addon['installed']!='') ? $date->getDate($addon['installed']) : NULL ),
            'upgraded'      => ( ($addon['upgraded']!='') ? $date->getDate($addon['upgraded']) : NULL ),
            'is_installed'  => true,
			'type'			=> $type
		);

		if ($tool_description !== false)
		{
			// Override the module-description with correct desription in users language
			$tpl_data['addons'][$counter]['description']	= $tool_description;
		}

		// ================================================== 
		// ! Check whether icon is available for the module   
		// ================================================== 
        $icon = CAT_Helper_Directory::getInstance()->sanitizePath(CAT_PATH . '/' . $type . '/' . $addon['directory'] . '/icon.png');
		if(file_exists($icon)){
			list($width, $height, $type_of, $attr) = getimagesize($icon);
			// Check whether file is 32*32 pixel and is an PNG-Image
			$tpl_data['addons'][$counter]['icon']
                = ($width == 32 && $height == 32 && $type_of == 3)
                ? CAT_URL . '/' . $type . '/' . $addon['directory'] . '/icon.png'
                : false;
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

		$tpl_data['addons'][$counter]['function'] = $type_name;

		// Check if the module is installable or upgradeable
		$tpl_data['addons'][$counter]['INSTALL'] = file_exists(CAT_PATH . '/' . $type . '/' . $addon['directory'] . '/install.php') ? true : false;
		$tpl_data['addons'][$counter]['UPGRADE'] = file_exists(CAT_PATH . '/' . $type . '/' . $addon['directory'] . '/upgrade.php') ? true : false;

		$counter++;
	}
}

$tpl_data['groups']	= $user->get_groups('' , '', false);

// scan modules path for modules not seen yet
$new = CAT_Helper_Directory::getInstance()
           ->maxRecursionDepth(0)
           ->setSkipDirs($seen_dirs)
           ->getDirectories( CAT_PATH.'/modules', CAT_PATH.'/modules/' );

if ( count($new) )
{
    $addon = CAT_Helper_Addons::getInstance();
    foreach( $new as $dir )
    {
        $info = $addon->checkInfo(CAT_PATH.'/modules/'.$dir);
        if ( $info )
	{
            $tpl_data['addons'][$counter] = array(
                'is_installed'  => false,
    			'type'			=> 'modules',
                'INSTALL'       => file_exists(CAT_PATH.'/modules/'.$dir.'/install.php') ? true : false
            );
            foreach( $info as $key => $value )
		{
                $tpl_data['addons'][$counter][str_ireplace('module_','',$key)] = $value;
		}
		$counter++;

	}
	}
}

// print page
$parser->output( 'backend_addons_index.lte', $tpl_data );

// Print admin footer
$admin->print_footer();

?>