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

$backend = CAT_Backend::getInstance('admintools');
$user    = CAT_Users::getInstance();
$lang    = CAT_Helper_I18n::getInstance();

// this will redirect to the login page if the permission is not set
$user->checkPermission('admintools','admintools',false);

global $parser;

// get tools
// ----- TODO: PERMISSIONS -----
$tools = CAT_Helper_Addons::get_addons(0,'module','tool');

if(count($tools))
{
    foreach($tools as $tool)
	{
        // check if the user is allowed to see this item
        if(!$user->get_permission($tool['directory'],$tool['type']))
            continue;

		// check if a module description exists for the displayed backend language
		$module_description		= false;
        $icon               = false;
		$language_file		= CAT_PATH.'/modules/'.$tool['VALUE'].'/languages/' . $user->lang()->getLang() . '.php';
		if ( true === file_exists($language_file) )
		{
			require( $language_file );
		}
		// Check whether icon is available for the admintool
		if ( file_exists(CAT_PATH.'/modules/'.$tool['VALUE'].'/icon.png') )
		{
			list($width, $height, $type, $attr) = getimagesize(CAT_PATH.'/modules/'.$tool['VALUE'].'/icon.png');
			// Check whether file is 32*32 pixel and is an PNG-Image
			$icon = ($width == 32 && $height == 32 && $type == 3)
                  ? CAT_URL.'/modules/'.$tool['VALUE'].'/icon.png'
                  : false;
		}

        $tpl_data['tools'][] = array(
			'TOOL_NAME'		=> $tool['NAME'],
			'TOOL_DIR'		=> $tool['VALUE'],
            'ICON'          => $icon,
            'TOOL_DESCRIPTION' => (!$module_description?$tool['description']:$module_description),
		);
	}
}
else
{
	$tpl_data['TOOL_LIST']		= false;
}


// print page
$parser->output('backend_admintools_index',$tpl_data);

// Print admin footer
$backend->print_footer();

?>