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
 *   @copyright       2015, Black Cat Development
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

$backend     = CAT_Backend::getInstance('Addons', 'modules_install', false, false);
$module_name = CAT_Helper_Validate::sanitizeGet('directory');
$action      = CAT_Helper_Validate::sanitizeGet('action');

if($action=='install' && CAT_Helper_Addons::isModuleInstalled($module_name))
{
    echo json_encode(array(
        'success' => false,
        'message' => $backend->lang()->translate('Already installed')
    ));
    exit();
}

include_once dirname(__FILE__).'/functions.inc.php';
$catalog = get_catalog();

foreach($catalog['modules'] as $module)
{
    if($module['directory'] == $module_name)
    {
        // Check requirements
        if(isset($module['require']) && isset($module['require']['core']) && isset($module['require']['core']['release']))
        {
            if(!CAT_Helper_Addons::versionCompare(CAT_VERSION,$module['require']['core']['release']))
            {
                echo json_encode(array(
                    'success' => false,
                    'message' => $backend->lang()->translate(
                        'You need to have BlackCat CMS Version {{ version }} installed for this addon. You have {{ version2 }}.',
                        array('version'=>$module['require']['core']['release'],'version2'=>CAT_VERSION)
                    )
                ));
                exit();
            }
        }
        // check for download location
        if(!isset($module['github']) || !isset($module['github']['organization']) || !isset($module['github']['repository']))
        {
            echo json_encode(array(
                'success' => false,
                'message' => $backend->lang()->translate(
                    'Unable to download the module. No download location set.'
                )
            ));
            exit();
        }
        // try download
        $dlurl = sprintf('https://github.com/%s/%s/archive/master.zip',
                         $module['github']['organization'],
                         $module['github']['repository']);
        if(CAT_Helper_GitHub::getZip($dlurl,CAT_PATH.'/temp')!==true)
        {
            echo json_encode(array(
                'success' => false,
                'message' => $backend->lang()->translate(
                    'Unable to download the module. Error: {{ error }}',
                    array('error'=>CAT_Helper_GitHub::getError())
                )
            ));
            exit();
        }
        // try install / update
        switch($action)
        {
            case 'install':
                if(CAT_Helper_Addons::installModule( CAT_PATH.'/temp/master.zip', true, true ))
                {
                    echo json_encode(array(
                        'success' => true,
                        'message' => $backend->lang()->translate(
                            'Installed successfully'
                        )
                    ));
                    exit();
                }
                else
                {
                    // error is already printed by the helper
                    echo json_encode(array(
                        'success' => false,
                        'message' => $backend->lang()->translate(
                            'Unable to install the module!'
                        )
                    ));
                    exit();
                }
                break;
            case 'update':
                break;
            default:
                echo json_encode(array(
                    'success' => false,
                    'message' => $backend->lang()->translate(
                        'Unknown action'
                    )
                ));
                exit();
                break;
        }
    }
}

// not found
echo json_encode(array(
    'success' => false,
    'message' => $backend->lang()->translate(
        'Unable to download the module. {{ error }}',
        array('error'=>'Not found')
    )
));
exit();

exit;