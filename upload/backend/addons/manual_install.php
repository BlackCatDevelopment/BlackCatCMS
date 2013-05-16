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

$backend = CAT_Backend::getInstance('Addons', 'modules_install');
$user    = CAT_Users::getInstance();
$val = CAT_Helper_Validate::getInstance();

$action		= $val->sanitizePost('action');
$file		= $val->sanitizePost('file');

if ( !in_array( $action, array('install', 'upgrade') ) )
{
	die(header('Location: ' . CAT_ADMIN_URL));
}
if ( $file == '' || !( strpos($file, '..') === false ) )
{
	die(header('Location: ' . CAT_ADMIN_URL));
}

$js_back	= CAT_ADMIN_URL . '/addons/index.php';

// check if specified module folder exists
$mod_path = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/'.basename(CAT_PATH.'/'.$file));

// set the old variablename in case the module uses it
$module_dir		= $mod_path;
$mod_file   = $mod_path.'/'.$action.'.php';

if ( !file_exists($mod_file) )
{
	$backend->print_error(
          $backend->lang()->translate('Not found')
        . ': <tt>"'
        . htmlentities(basename($mod_path)).'/'.$action.'.php"</tt> ',
        $js_back
    );
}

// this prints an error page if prerequisites are not met
$precheck_errors = CAT_Helper_Addons::preCheckAddon( NULL, $mod_path, false );
if ( $precheck_errors != '' && ! is_bool($precheck_errors) )
{
    $backend->print_error($backend->lang()->translate(
        'Invalid installation file. {{error}}',
        array('error'=>$precheck_errors)
    ));
    return false;
}

// include modules install.php/upgrade.php script
require $mod_file;

// load module info into database and output status message
if ( !CAT_Helper_Addons::loadModuleIntoDB($mod_path,$action,CAT_Helper_Addons::checkInfo($mod_path)))
{
    $backend->print_error(
        $backend->lang()->translate(
            'Unable to add the addon to the database!<br />{{error}}',
            array('error'=>$backend->db()->get_error())
        ), $js_back );
}

switch ($action)
{
	case 'install':
	case 'upgrade':
		$backend->print_success( 'Done', $js_back);
		break;
	default:
		$backend->print_error( 'Action not supported', $js_back );
}

?>