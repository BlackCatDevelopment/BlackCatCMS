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

$backend  = CAT_Backend::getInstance('admintools', 'admintools');
$admin    =& $backend;
$val      = CAT_Helper_Validate::getInstance();
$get_tool = $val->sanitizeGet('tool',NULL,true);

if ( $get_tool == '' )
{
	header("Location: index.php");
	exit(0);
}

// check tool permission
if(!CAT_Users::get_permission($get_tool,'module'))
{
	header("Location: index.php");
	exit(0);
}

global $parser;
$parser->setGlobals('CAT_ADMIN_URL',CAT_ADMIN_URL);

// ============================== 
// ! Check if tool is installed   
// ============================== 
if ( !CAT_Helper_Addons::isModuleInstalled($get_tool) )
{
	header("Location: index.php");
	exit(0);
}
$tool = CAT_Helper_Addons::getAddonDetails($get_tool);

// Set toolname
$tpl_data['TOOL_NAME']		= $tool['name'];
$parser->setGlobals('TOOL_URL',CAT_ADMIN_URL.'/admintools/tool.php?tool='.$tool['directory']);

// Check if folder of tool exists
if ( file_exists(CAT_PATH.'/modules/'.$tool['directory'].'/tool.php') )
{

        // load language file (if any)
    $langfile = sanitize_path(CAT_PATH.'/modules/'.$tool['directory'].'/languages/'.LANGUAGE.'.php');
    if ( file_exists($langfile) )
    {
        if ( ! $backend->lang()->checkFile($langfile, 'LANG', true ))
            // old fashioned language file
            require $langfile;
        else
            // modern language file
            $backend->lang()->addFile(LANGUAGE . '.php', sanitize_path(CAT_PATH . '/modules/' . $tool['directory'] . '/languages'));
	}
	// Cache the tool and add it to dwoo
	ob_start();
	require(CAT_PATH.'/modules/'.$tool['directory'].'/tool.php');
	$tpl_data['TOOL']	= ob_get_contents();
	//ob_end_clean();
    ob_clean(); // allow multiple buffering for csrf-magic
}
else
{
	$admin->print_error('Error opening file.');
}

// print page
$parser->output( 'backend_admintools_tool', $tpl_data );

// Print admin footer
$backend->print_footer();

?>