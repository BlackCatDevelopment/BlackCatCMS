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
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

$backend = CAT_Backend::getInstance('Addons', 'addons', false);
$users   = CAT_Users::getInstance();

header('Content-type: application/json');

if ( !$users->checkPermission('Addons','addons') )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate("Sorry, but you don't have the permissions for this action"),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$module = CAT_Helper_Validate::sanitizePost('module');
$type   = CAT_Helper_Validate::sanitizePost('type');

if(CAT_Helper_Addons::isModuleInstalled($module,NULL,$type))
{
    $info = CAT_Helper_Addons::checkInfo(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.$type.'s/'.$module));
}
else
{
    $path = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.$type.'/'.$module.(($type=='languages')?'.php':''));
    $info = CAT_Helper_Addons::checkInfo($path);
}

if ( ! is_array($info) || ! count($info) )
{
    $ajax	= array(
		'message'	=> $backend->lang()->translate("No Addon info available, seems to be an invalid addon!"),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}
$addon = array(
    'type' => $info['addon_function'],
    'installed' => NULL,
    'upgraded' => NULL,
    'removable' => 'Y',
);
foreach($info as $key => $value)
{
    $key = preg_replace('/^(module_|addon_)/i','',$key);
    $addon[$key] = $value;
}


// check if the user is allowed to see this item
if(!$users->get_permission($addon['directory'],$addon['type']))
{
    $ajax	= array(
		'message'	=> $backend->lang()->translate("Sorry, but you don't have the permissions for this action"),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

$tpl_data = array( 'permissions' => array() );

// Insert permissions values
$tpl_data['permissions']['ADVANCED']          = $users->checkPermission('addons', 'admintools')        ? true : false;
$tpl_data['permissions']['MODULES_VIEW']      = $users->checkPermission('addons', 'modules_view')      ? true : false;
$tpl_data['permissions']['MODULES_INSTALL']   = $users->checkPermission('addons', 'modules_install')   ? true : false;
$tpl_data['permissions']['MODULES_UNINSTALL'] = $users->checkPermission('addons', 'modules_uninstall') ? true : false;

// get header info
$info = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.$addon['type'].'s/'.$addon['directory'].'/info.php');
$link = NULL;
if( file_exists($info) )
{
    ini_set('auto_detect_line_endings',true);
    $file = fopen($info,'r');
    if ($file) {
        while ($line = fgets($file)) {
            if (preg_match('/\@link\s+([^\*].+?)$/mis', $line, $matches)) {
                $link = trim($matches[1]);
                break;
            }
        }
        fclose($file);
    }
}

// ==================================================
// ! Check whether icon is available for the module
// ==================================================
$icon = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.$addon['type'].'s/'.$addon['directory'].'/icon.png');
if(file_exists($icon)){
    list($width, $height, $type_of, $attr) = getimagesize($icon);
    // Check whether file is 32*32 pixel and is an PNG-Image
    $addon['icon']
        = ($width == 32 && $height == 32 && $type_of == 3)
        ? CAT_URL.'/'.$addon['type'].'s/'.$addon['directory'].'/icon.png'
        : false;
}

if($addon['type'] == 'language')
{
    // Clear all variables
    $vars = get_defined_vars();
    foreach( array_keys($vars) as $var )
    {
        if ( preg_match( '~^language_~i', $var ) )
        {
            ${$var} = '';
        }
    }
    // for language files, the column 'directory' contains the lang code
    $langfile = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/languages/'.$addon['directory'].'.php');
    if ( file_exists($langfile))
    {
        // use require as we just need the info vars, not the lang strings
        require $langfile;
        $addon['name']        = $language_name;
        $addon['author']      = $addon['author'] != '' ? $addon['author'] : $language_author;
        $addon['version']     = $language_version;
        $addon['platform']    = $language_platform;
        $addon['license']     = $language_license;
        $addon['description'] = $language_name;
    }
}
else
{
    // check if a module description exists for the displayed backend language
    $langfile            = false;
    // for modules, look for a language file for current language
    $langfile = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.$addon['type'].'s/'.$addon['directory'].'/languages/'.LANGUAGE.'.php');
    if ( function_exists('file_get_contents') && file_exists($langfile) )
    {
        // read contents of the module language file into string
        $description          = @file_get_contents($langfile);
        // use regular expressions to fetch the content of the variable from the string
        $tool_description     = get_variable_content('module_description', $description, false, false);
        // replace optional placeholder {CAT_URL} with value stored in config.php
        if ($tool_description !== false && strlen(trim($tool_description)) != 0)
            $addon['description'] = str_replace('{CAT_URL}', CAT_URL, $tool_description);
    }
}

// long text for type
switch ($addon['function'])
{
    case NULL:
        $type_name    = $backend->lang()->translate( 'Unknown' );
        break;
    case 'page':
        $type_name    = $backend->lang()->translate( 'Page' );
        break;
    case 'wysiwyg':
        $type_name    = $backend->lang()->translate( 'WYSIWYG Editor' );
        break;
    case 'tool':
        $type_name    = $backend->lang()->translate( 'Administration tool' );
        break;
    case 'admin':
        $type_name    = $backend->lang()->translate( 'Admin' );
        break;
    case 'administration':
        $type_name    = $backend->lang()->translate( 'Administration' );
        break;
    case 'snippet':
        $type_name    = $backend->lang()->translate( 'Code-Snippet' );
        break;
    case 'library':
        $type_name    = $backend->lang()->translate( 'Library' );
        break;
    default:
        $type_name    = $backend->lang()->translate( 'Unknown' );
}

$addon['function'] = $type_name;

// Check if the module is installable or upgradeable
$addon['INSTALL'] = file_exists(CAT_PATH . '/' . $addon['type'] . 's/' . $addon['directory'] . '/install.php') ? true : false;
$addon['UPGRADE'] = file_exists(CAT_PATH . '/' . $addon['type'] . 's/' . $addon['directory'] . '/upgrade.php') ? true : false;

// add some more details
$addon = array_merge(
    $addon,
    array(
        'installed'    => ( ($addon['installed']!='' )  ? CAT_Helper_DateTime::getDate($addon['installed']) : NULL ),
        'upgraded'     => ( ($addon['upgraded'] !='' )  ? CAT_Helper_DateTime::getDate($addon['upgraded'])  : NULL ),
        'is_installed' => CAT_Helper_Addons::isModuleInstalled($addon['directory'],NULL,$addon['type']),
        'is_removable' => ( ($addon['removable']=='N') ? false : true ),
        'link'         => $link,
));

// create token
$tpl_data['csrftoken'] = csrf_get_tokens();
$tpl_data['csrfname']  = $GLOBALS['csrf']['input-name'];

require_once dirname(__FILE__).'/../../config.php';
require_once dirname(__FILE__).'/../../framework/functions.php';

$result  = true;
$message = NULL;
$output  = $parser->get('backend_addons_index_details', array_merge($tpl_data,array('addon'=>$addon)));
if ( !$output || $output == '' ) {
    $result = false;
    $message = 'Unable to load settings sub page';
}

$ajax = array(
	'message' => $message,
	'success' => $result,
    'content' => $output,
);

print json_encode( $ajax );
exit();