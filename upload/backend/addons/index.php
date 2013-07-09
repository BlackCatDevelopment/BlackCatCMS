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

global $parser;
$tpl_data = array();

$backend   = CAT_Backend::getInstance('Addons', 'addons');
$users     = CAT_Users::getInstance();
$addons    = CAT_Helper_Addons::get_addons();
$counter   = 0;
$seen_dirs = array();

$tpl_data['addons']               = array();
$tpl_data['not_installed_addons'] = array();
$tpl_data['groups']               = $users->get_groups('' , '', false);

foreach( $addons as $addon )
{
    // check if the user is allowed to see this item
    if(!$users->get_permission($addon['directory'],$addon['type']))
    {
        $seen_dirs[] = $addon['directory'];
        continue;
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
    $tpl_data['addons'][$counter] = $addon;

    $seen_dirs[] = $addon['directory'];
    $counter++;
}

// Insert permissions values
$tpl_data['permissions']['ADVANCED']          = $users->checkPermission('addons', 'admintools')        ? true : false;
$tpl_data['permissions']['MODULES_VIEW']      = $users->checkPermission('addons', 'modules_view')      ? true : false;
$tpl_data['permissions']['MODULES_INSTALL']   = $users->checkPermission('addons', 'modules_install')   ? true : false;
$tpl_data['permissions']['MODULES_UNINSTALL'] = $users->checkPermission('addons', 'modules_uninstall') ? true : false;

// scan modules path for modules not seen yet
if( $users->checkPermission('addons','modules_install') )
{
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
                $tpl_data['not_installed_addons'][$counter] = array(
                    'is_installed' => false,
                    'type'         => 'modules',
                    'INSTALL'      => file_exists(CAT_PATH.'/modules/'.$dir.'/install.php') ? true : false
                );
                foreach( $info as $key => $value )
                {
                    $tpl_data['not_installed_addons'][$counter][str_ireplace('module_','',$key)] = $value;
                }
                $counter++;
            }
        }
        $tpl_data['not_installed_addons'] = CAT_Helper_Array::ArraySort($tpl_data['not_installed_addons'],'name','asc',true);
    }
}

// print page
$parser->output( 'backend_addons_index', $tpl_data );

// Print admin footer
$backend->print_footer();