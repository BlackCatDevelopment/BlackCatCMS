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
 *   @copyright       2014, 2015, Black Cat Development
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

include_once dirname(__FILE__).'/functions.inc.php';

// read catalog
if(!file_exists(CAT_PATH."/temp/catalog.json"))
{
    update_catalog();
}

if(!file_exists(CAT_PATH."/temp/catalog.json"))
{
    echo json_encode(array(
        'success' => false,
        'content' => 'Unable to get the catalog!',
    ));
}

$catalog = get_catalog();

// get installed
$modules = CAT_Helper_Addons::get_addons('module');

// map installed
$installed = array();
foreach($modules as $i => $m)
    $installed[$m['directory']] = $i;

$catalog['modules'] = CAT_Helper_Array::ArraySort($catalog['modules'],'name','asc',true,false);

// mark installed in catalog
foreach( $catalog['modules'] as $i => $m)
{
    if(isset($installed[$m['directory']]))
    {
        $catalog['modules'][$i]['is_installed']   = true;
        $catalog['modules'][$i]['installed_data'] = array_merge(
            $modules[$installed[$m['directory']]],
            array(
                'install_date' => CAT_Helper_DateTime::getDate($modules[$installed[$m['directory']]]['installed']),
                'update'       => CAT_Helper_Addons::versionCompare($m['version'],$modules[$installed[$m['directory']]]['version'],'>'),
            )
        );
        $catalog['modules'][$i]['is_removable']   = ( ($catalog['modules'][$i]['installed_data']['removable']=='N') ? false : true );
    }
}

$users       = CAT_Users::getInstance();
$permissions = array();
$permissions['ADVANCED']          = $users->checkPermission('addons', 'admintools')        ? true : false;
$permissions['MODULES_VIEW']      = $users->checkPermission('addons', 'modules_view')      ? true : false;
$permissions['MODULES_INSTALL']   = $users->checkPermission('addons', 'modules_install')   ? true : false;
$permissions['MODULES_UNINSTALL'] = $users->checkPermission('addons', 'modules_uninstall') ? true : false;


echo json_encode(array(
    'success' => true,
    'content' => $parser->get(
        'backend_addons_index_catalog',
        array(
            'addons'=>$catalog['modules'],
            'permissions'=>$permissions,
            'catalog_version'=>(isset($catalog['version']) ? $catalog['version'] : NULL),
        )
    ),
));