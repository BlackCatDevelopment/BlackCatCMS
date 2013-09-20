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

$backend = CAT_Backend::getInstance('Addons', 'modules_uninstall');
$val     = CAT_Helper_Validate::getInstance();
$addons  = CAT_Helper_Addons::getInstance();

// Get name and type of add on
$type       = $val->sanitizePost('type',NULL,true);
$addon_name	= $val->sanitizePost('file');
$file		= $type == 'language' ? $addon_name . '.php' : $addon_name;

// Check if user selected a module
if ( trim($file) == '' || trim($type) == '' )
{
	header("Location: index.php");
	exit(0);
}

$js_back	= CAT_ADMIN_URL . '/addons/index.php';

// Check if the module exists
if ( !$addons->isModuleInstalled($addon_name,NULL,preg_replace('~s$~','',$type)))
{
	$backend->print_error( 'Not installed' , $js_back, false );
}

$path = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/'.$type.'s/'.$file);
if ( ! file_exists($path) )
{
    $backend->print_error( 'Not installed' , $js_back, false );
}

// Check if we have permissions on the directory
if ( !is_writable($path) )
{
	$backend->print_error( 'Unable to write to the target directory' , $js_back );
}

$result = CAT_Helper_Addons::uninstallModule($type.'s',$addon_name);
if($result !== true)
    $backend->print_error($result, $js_back, false);
else
    $backend->print_success( 'Uninstalled successfully' );

// Print admin footer
$backend->print_footer();

?>