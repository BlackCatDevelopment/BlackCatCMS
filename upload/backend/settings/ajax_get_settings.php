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

$backend = CAT_Backend::getInstance('Settings', 'settings', false);
$users   = CAT_Users::getInstance();

header('Content-type: application/json');

if ( !$users->checkPermission('Settings','settings') )
{
	$ajax	= array(
		'message'	=> $backend->lang()->translate("Sorry, but you don't have the permissions for this action"),
		'success'	=> false
	);
	print json_encode( $ajax );
	exit();
}

require_once dirname(__FILE__).'/../../config.php';
require_once dirname(__FILE__).'/functions.php';

$settings = CAT_Registry::getSettings();
$region   = CAT_Helper_Validate::get('_REQUEST','template');
$tpl      = 'backend_settings_index_'.$region.'.tpl';
$data     = getSettingsTable();
$tpl_data = array( 'values' => $data );

$tpl_data['DISPLAY_ADVANCED'] = $users->checkPermission('Settings','settings_advanced');

switch($region)
{
    case 'frontend':
        $tpl_data['templates'] = getTemplateList('frontend');
        $tpl_data['variants']  = array();
        $info = CAT_Helper_Addons::checkInfo(CAT_PATH.'/templates/'.CAT_Registry::get('DEFAULT_TEMPLATE'));
        if(isset($info['module_variants']) && is_array($info['module_variants']) && count($info['module_variants'])) {
            $tpl_data['variants'] = $info['module_variants'];
        }
        break;
    case 'backend':
        $tpl_data['backends']  = getTemplateList('backend');
        $tpl_data['wysiwyg']   = CAT_Helper_Addons::get_addons( CAT_Registry::get('WYSIWYG_EDITOR'), 'module', 'wysiwyg' );
        $tpl_data['er_levels'] = getErrorLevels();
        $tpl_data['variants']  = array();
        $info = CAT_Helper_Addons::checkInfo(CAT_PATH.'/templates/'.CAT_Registry::get('DEFAULT_THEME'));
        if(isset($info['module_variants']) && is_array($info['module_variants']) && count($info['module_variants'])) {
            $tpl_data['variants'] = $info['module_variants'];
        }
        break;
    case 'system':
        $tpl_data['PAGES_LIST'] = getPagesList('maintenance_page', CAT_Registry::get('MAINTENANCE_PAGE'));
        $tpl_data['ERR_PAGES_LIST'] = getPagesList('err_page_404', CAT_Registry::get('ERR_PAGE_404'));
        break;
    case 'users':
        $tpl_data['groups']     = $users->get_groups(CAT_Registry::get('FRONTEND_SIGNUP'), '', false);
        break;
    case 'datetime':
        $tpl_data['languages']  = getLanguages();
        $tpl_data['timezones']  = getTimezones();
        $tpl_data['charsets']   = getCharsets();
        $tpl_data['dateformats'] = getDateformats();
        $tpl_data['timeformats'] = getTimeformats();
        break;
    case 'searchblock':
        $tpl_data['search']           = getSearchSettings();
        $tpl_data['search_templates'] = CAT_Helper_Addons::get_addons( $tpl_data['search']['template'] , 'template', 'template' );#
        $tpl_data['PAGES_LIST']       = getPagesList('search_cfg_search_use_page_id', $tpl_data['search']['cfg_search_use_page_id'], true);
        break;
    case 'server':
        $tpl_data['WORLD_WRITEABLE_SELECTED'] = (CAT_Registry::get('STRING_FILE_MODE') == '0666' && CAT_Registry::get('STRING_DIR_MODE') == '0777')  ? true : false;
        break;
    case 'mail':
        $tpl_data['CATMAILER_LIBS'] = getMailerLibs();
        break;
    case 'security':
        $admin =& $backend;
        require_once(CAT_PATH .'/framework/CAT/Helper/Captcha/WB/captcha.php');
        $captcha                      = getCaptchaTypes($backend);
        $tpl_data                     = array_merge($tpl_data, $captcha);
        $tpl_data['useable_captchas'] = $useable_captchas;
        $tpl_data['ttf_image']        = CAT_URL.'/framework/CAT/Helper/Captcha/WB/captchas/ttf_image.png';
        $tpl_data['calc_image']       = CAT_URL.'/framework/CAT/Helper/Captcha/WB/captchas/calc_image.png';
        $tpl_data['calc_ttf_image']   = CAT_URL.'/framework/CAT/Helper/Captcha/WB/captchas/calc_ttf_image.png';
        $tpl_data['old_image']        = CAT_URL.'/framework/CAT/Helper/Captcha/WB/captchas/old_image.png';
        $tpl_data['calc_text']        = CAT_URL.'/framework/CAT/Helper/Captcha/WB/captchas/calc_text.png';
        $tpl_data['text']             = CAT_URL.'/framework/CAT/Helper/Captcha/WB/captchas/text.png';
        break;
    case 'sysinfo':
        // format installation date and time
        $tpl_data['values']['installation_time']
            = CAT_Helper_DateTime::getDateTime(INSTALLATION_TIME);

        // get page statistics
        $pg = CAT_Helper_Page::getPagesByVisibility();
        foreach( array_keys($pg) as $key )
        {
            $tpl_data['values']['pages_count'][] = array(
                'visibility' => $key,
                'count'      => count($pg[$key])
            );
        }
        break;
}

$result  = true;
$message = NULL;
$output  = $parser->get($tpl, $tpl_data);
if ( !$output || $output == '' ) {
    $result = false;
    $message = 'Unable to load settings sub page';
}

$ajax = array(
	'message'	=> $message,
	'success'	=> $result,
    'settings'  => $output,
);
print json_encode( $ajax );
exit();
