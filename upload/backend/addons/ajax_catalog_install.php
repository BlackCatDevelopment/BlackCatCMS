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
    CAT_Object::json_error('Already installed');
}

include_once dirname(__FILE__).'/functions.inc.php';
include_once CAT_PATH.'/framework/functions.php';

$catalog = get_catalog();

foreach($catalog['modules'] as $module)
{
    if($module['directory'] == $module_name)
    {
        // Check requirements
        if(isset($module['require']))
        {
            if(isset($module['require']['core']) && isset($module['require']['core']['release']))
            {
                if(!CAT_Helper_Addons::versionCompare(CAT_VERSION,$module['require']['core']['release']))
                {
                    CAT_Object::json_error($backend->lang()->translate(
                            'You need to have BlackCat CMS Version {{ version }} installed for this addon. You have {{ version2 }}.',
                            array('version'=>$module['require']['core']['release'],'version2'=>CAT_VERSION)
                        )
                    );
                }
            }
            if(isset($module['require']['modules']))
            {
                $req_modules = $module['require']['modules']; // shorter
                foreach($req_modules as $mod => $req)
                {
                    if(!CAT_Helper_Addons::isModuleInstalled($mod))
                    {
                        CAT_Object::json_error($backend->lang()->translate(
                            'You need to have addon {{ addon }} version {{ version }} installed for this addon.',
                            array('addon'=>$mod,'version'=>$req['release'])
                        ));
                    }
                }
            }
        }
        // try install / update
        switch($action)
        {
            case 'install':
            case 'update':
        // check for download location
        if(!isset($module['github']) || !isset($module['github']['organization']) || !isset($module['github']['repository']))
        {
            CAT_Object::json_error('Unable to download the module. No download location set.');
        }
        // get latest release
        $release_info = CAT_Helper_GitHub::getRelease($module['github']['organization'],$module['github']['repository']);
        if(!is_array($release_info) || !count($release_info))
        {
                    // no release found, search for tags
                    $tags = CAT_Helper_GitHub::getTags($module['github']['organization'],$module['github']['repository']);
                    if(!is_array($tags) || !count($release_info))
                    {
                        // no release and no tag, use master.zip
                        $dlurl = sprintf('https://github.com/%s/%s/archive/master.zip',$module['github']['organization'],$module['github']['repository']);
                    }
                    else
                    {
                        $dlurl = $tags['zipball_url'];
                    }
                    //CAT_Object::json_error('Unable to download the module. No release found.');
                }
                else
                {
                    $dlurl = $release_info['zipball_url'];
        }

        // try download
        if(CAT_Helper_GitHub::getZip($dlurl,CAT_PATH.'/temp',$module_name)!==true)
        {
            CAT_Object::json_error($backend->lang()->translate(
                'Unable to download the module. Error: {{ error }}',
                array('error'=>CAT_Helper_GitHub::getError())
            ));
        }
                if(CAT_Helper_Addons::installModule( CAT_PATH.'/temp/'.$module_name.'.zip', true, false ))
                {
                    CAT_Object::json_success('Installed successfully');
                }
                else
                {
                    // error is already printed by the helper
                    CAT_Object::json_error('Unable to install the module: ' . CAT_Helper_Addons::getError() );
                }
                break;
            case 'uninstall':
                $result = CAT_Helper_Addons::uninstallModule('modules',$module_name);
                if($result !== true)
                {
                    CAT_Object::json_error(
                        CAT_Helper_Addons::lang()->translate(
                            'Unable to uninstall the module! {{message}}',
                            array('message'=>$result)
                        )
                    );
                }
                else
                {
                    CAT_Object::json_success('Uninstalled successfully');
                }
                break;
            default:
                CAT_Object::json_error('Unknown action');
                break;
        }
    }
}

// not found
CAT_Object::json_error($backend->lang()->translate(
    'Unable to download the module. Error: {{ error }}',
    array('error'=>'Not found')
));
