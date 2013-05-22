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

$user    = CAT_Users::getInstance();
$backend = CAT_Backend::getInstance('Settings', 'settings_advanced');

global $parser;
$tpl_data = array();

// Include the WB functions file
require_once(CAT_PATH.'/framework/functions-utf8.php');

// ===========================================================================
// ! Query current settings in the db
// =========================================================================== 
if ( $res_settings = $backend->db()->query(sprintf(
    'SELECT `name`, `value` FROM `%ssettings` ORDER BY `name`',
    CAT_TABLE_PREFIX
))
) {
    while ( $row = $res_settings->fetchRow(MYSQL_ASSOC) )
    {
        $tpl_data['values'][$row['name']]
            = ($row['name'] != 'catmailer_smtp_password')
            ? htmlspecialchars($row['value'])
            : $row['value'];
    }
}

// =========================================================================== 
// ! Query current search settings in the db
// =========================================================================== 
if (
    (
         ($res_search = $backend->db()->query(sprintf('SELECT * FROM `%ssearch` WHERE `extra` = \'\' ',CAT_TABLE_PREFIX)))
      && ($res_search->numRows() > 0)
    )
) {
    while ( $row = $res_search->fetchRow() )
    {
        $tpl_data['search'][$row['name']]
            = htmlspecialchars(($row['value']));
    }
}
else
{
    $tpl_data['search'] = array();
}

// ============================= 
// ! Add setting to $tpl_data   
// ============================= 
$tpl_data['DISPLAY_ADVANCED']                  = $user->checkPermission('Settings','settings_advanced');

$tpl_data['values']['DATABASE_TYPE']           = '';
$tpl_data['values']['DATABASE_HOST']           = '';
$tpl_data['values']['DATABASE_USERNAME']       = '';
$tpl_data['values']['DATABASE_NAME']           = '';

$tpl_data['values']['pages_directory']         = trim(CAT_Registry::get('PAGES_DIRECTORY'));
$tpl_data['values']['media_directory']         = trim(CAT_Registry::get('MEDIA_DIRECTORY'));
$tpl_data['values']['page_extension']          = CAT_Registry::get('PAGE_EXTENSION');
$tpl_data['values']['page_spacer']             = CAT_Registry::get('PAGE_SPACER');
$tpl_data['values']['sec_anchor']              = CAT_Registry::get('SEC_ANCHOR');
$tpl_data['values']['CAT_TABLE_PREFIX']        = CAT_Registry::get('CAT_TABLE_PREFIX');
$tpl_data['values']['catmailer_smtp_host']     = CAT_Registry::get('CATMAILER_SMTP_HOST');
$tpl_data['values']['catmailer_smtp_username'] = CAT_Registry::get('CATMAILER_SMTP_USERNAME');
$tpl_data['values']['catmailer_smtp_password'] = CAT_Registry::get('CATMAILER_SMTP_PASSWORD');
$tpl_data['values']['server_email']            = CAT_Registry::get('SERVER_EMAIL');
$tpl_data['values']['wb_default_sendername']   = CAT_Registry::get('CATMAILER_DEFAULT_SENDERNAME');

$tpl_data['MULTIPLE_MENUS']                    = (CAT_Registry::defined('MULTIPLE_MENUS')       && CAT_Registry::get('MULTIPLE_MENUS')       == true) ? true : false;
$tpl_data['PAGE_LANGUAGES']                    = (CAT_Registry::defined('PAGE_LANGUAGES')       && CAT_Registry::get('PAGE_LANGUAGES')       == true) ? true : false;
$tpl_data['SMART_LOGIN']                       = (CAT_Registry::defined('SMART_LOGIN')          && CAT_Registry::get('SMART_LOGIN')          == true) ? true : false;
$tpl_data['SECTION_BLOCKS']                    = (CAT_Registry::defined('SECTION_BLOCKS')       && CAT_Registry::get('SECTION_BLOCKS')       == true) ? true : false;
$tpl_data['HOMEPAGE_REDIRECTION']              = (CAT_Registry::defined('HOMEPAGE_REDIRECTION') && CAT_Registry::get('HOMEPAGE_REDIRECTION') == true) ? true : false;

$tpl_data['WORLD_WRITEABLE_SELECTED']          = (CAT_Registry::get('STRING_FILE_MODE') == '0666' && CAT_Registry::get('STRING_DIR_MODE') == '0777')  ? true : false;

$tpl_data['OPERATING_SYSTEM']                  = CAT_Registry::get('OPERATING_SYSTEM');
$tpl_data['CATMAILER_ROUTINE']                 = CAT_Registry::get('CATMAILER_ROUTINE');
$tpl_data['CATMAILER_SMTP_AUTH']               = CAT_Registry::get('CATMAILER_SMTP_AUTH');
$tpl_data['HOME_FOLDERS']                      = CAT_Registry::get('HOME_FOLDERS');
$tpl_data['SEARCH']                            = CAT_Registry::get('SEARCH');
$tpl_data['PAGE_LEVEL_LIMIT']                  = CAT_Registry::get('PAGE_LEVEL_LIMIT');
$tpl_data['PAGE_TRASH']                        = CAT_Registry::get('PAGE_TRASH');
$tpl_data['ER_LEVEL']                          = CAT_Registry::get('ER_LEVEL');
$tpl_data['DEFAULT_CHARSET']                   = CAT_Registry::get('DEFAULT_CHARSET');
$tpl_data['MANAGE_SECTIONS']                   = CAT_Registry::get('MANAGE_SECTIONS') ? true : false;
$tpl_data['INTRO_PAGE']                        = CAT_Registry::get('INTRO_PAGE')      ? true : false;
$tpl_data['FRONTEND_LOGIN']                    = CAT_Registry::get('FRONTEND_LOGIN')  ? true : false;

$tpl_data['MAINTENANCE_MODE']                  = CAT_Registry::get('MAINTENANCE_MODE');

$tpl_data['GD_EXTENSION']                      = (extension_loaded('gd') && function_exists('imageCreateFromJpeg')) ? true : false;


// ==========================
// ! Specials
// ==========================

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

// get installed mailer libs
$tpl_data['CATMAILER_LIBS'] = array();
$mailer_libs = CAT_Helper_Addons::getInstance()->getLibraries('mail');
if ( count($mailer_libs) )
{
    foreach ( $mailer_libs as $item )
    {
        $tpl_data['CATMAILER_LIBS'][] = $item;
    }
}

// ========================== 
// ! Insert language values   
// ========================== 
$langs = CAT_Helper_Addons::getInstance()->get_addons(0,'language');
foreach($langs as $addon)
{
    $l_codes[$addon['NAME']]    = $addon['VALUE'];
    $l_names[$addon['NAME']]    = entities_to_7bit($addon['NAME']); // sorting-problem workaround
}
asort($l_names);
$counter=0;
foreach($l_names as $l_name=>$v)
{
    // ======================== 
    // ! Insert code and name   
    // ======================== 
    $tpl_data['languages'][$counter]['CODE']    = $l_codes[$l_name];
    $tpl_data['languages'][$counter]['NAME']    = $l_name;
    // $tpl_data['languages']['CODE'] = true;
    // =========================== 
    // ! Check if it is selected   
    // =========================== 
    $tpl_data['languages'][$counter]['SELECTED'] = (DEFAULT_LANGUAGE == $l_codes[$l_name]) ? true : false;
    $counter++;
}

// ================================== 
// ! Insert default timezone values   
// ================================== 
$timezone_table = CAT_Helper_DateTime::getTimezones();
$counter=0;
foreach( $timezone_table as $title )
{
    $tpl_data['timezones'][$counter] = array(
        'NAME'            => $title,
        'SELECTED'        => ( DEFAULT_TIMEZONE_STRING == $title ) ? true : false
    );
    $counter++;
}

// ================================= 
// ! Insert default charset values   
// ================================= 
$CHARSETS = $backend->lang()->getCharsets();
$counter=0;
foreach ( $CHARSETS AS $code => $title )
{
    $tpl_data['charsets'][$counter] = array(
        'NAME'            => $title,
        'VALUE'            => $code,
        'SELECTED'        => ( DEFAULT_CHARSET == $code ) ? true : false
    );
    $counter++;
}

// ==================================== 
// ! set TZ to current system default   
// ==================================== 
$old_tz = date_default_timezone_get();
date_default_timezone_set(DEFAULT_TIMEZONE_STRING);

// =========================== 
// ! Insert date format list   
// =========================== 
$DATE_FORMATS = CAT_Helper_DateTime::getDateFormats();
$counter=0;
foreach ( $DATE_FORMATS AS $format => $title )
{
    #$format = str_replace('|', ' ', $format); // Add's white-spaces (not able to be stored in array key)
    $tpl_data['dateformats'][$counter] = array(
        'NAME'     => $title,
        'VALUE'    => ( $format != 'system_default' )    ? $format : '',
        'SELECTED' => ( DEFAULT_DATE_FORMAT == $format ) ? true    : false
    );
    $counter++;
}

// =========================== 
// ! Insert time format list   
// =========================== 
$TIME_FORMATS = CAT_Helper_DateTime::getTimeFormats();
$counter=0;
foreach ( $TIME_FORMATS AS $format => $title )
{
    $format = str_replace('|', ' ', $format); // Add's white-spaces (not able to be stored in array key)
    $tpl_data['timeformats'][$counter] = array(
        'NAME'     => $title,
        'VALUE'    => ( $format != 'system_default' ) ? $format : '',
        'SELECTED' => ( DEFAULT_TIME_FORMAT == $format ) ? true : false
    );
    $counter++;
}

// ========================================= 
// ! Insert default error reporting values   
// ========================================= 
$ER_LEVELS = CAT_Registry::get('ER_LEVELS','array');
$counter = 0;
foreach ( $ER_LEVELS AS $value => $title )
{
    $tpl_data['er_levels'][$counter] = array(
        'NAME'     => $title,
        'VALUE'    => $value,
        'SELECTED' => (ER_LEVEL == $value) ? true : false
    );
    $counter++;
}

// =============================== 
// ! set TZ back to user default   
// =============================== 
date_default_timezone_set($old_tz);

$addons = CAT_Helper_Addons::getInstance();

// ============================ 
// ! Insert groups and addons   
// ============================ 
$tpl_data['groups']           = $user->get_groups(CAT_Registry::get('FRONTEND_SIGNUP'), '', false);
$tpl_data['templates']        = $addons->get_addons( CAT_Registry::get('DEFAULT_TEMPLATE'), 'template', 'template' );
$tpl_data['backends']         = $addons->get_addons( CAT_Registry::get('DEFAULT_THEME'), 'template', 'theme' );
$tpl_data['wysiwyg']          = $addons->get_addons( CAT_Registry::get('WYSIWYG_EDITOR'), 'module', 'wysiwyg' );
$tpl_data['search_templates'] = $addons->get_addons( $tpl_data['search']['template'] , 'template', 'template' );

array_unshift (
    $tpl_data['wysiwyg'],
    array(
        'NAME'     => $backend->lang()->translate('None'),
        'VALUE'    => false,
        'SELECTED' => ( !CAT_Registry::defined('WYSIWYG_EDITOR') || CAT_Registry::get('WYSIWYG_EDITOR') == 'none' ) ? true : false
    )
);

array_unshift (
    $tpl_data['search_templates'],
    array(
        'NAME'     => $backend->lang()->translate('System default'),
        'VALUE'    => false,
        'SELECTED' => ( ($tpl_data['search']['template'] == '') || $tpl_data['search']['template'] == CAT_Registry::get('DEFAULT_TEMPLATE') ) ? true : false
    )
);

// ====================
// ! Pages list
// ====================
$pages_list = CAT_Helper_Page::getPages();
$deleted    = CAT_Helper_Page::getPagesByVisibility('deleted');
if(count($deleted))
{
    $arrh = CAT_Helper_Array::getInstance();
    foreach($deleted as $page)
    {
        $arrh->ArrayRemove( $page['page_id'], $pages_list, 'page_id' );
    }
}
$tpl_data['PAGES_LIST'] = CAT_Helper_ListBuilder::getInstance(true)
                          ->config(array('space' => '|-- '))
                          ->dropdown( 'fc_maintenance_page', $pages_list, 0, CAT_Registry::get('MAINTENANCE_PAGE') );

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_settings_index', $tpl_data);

// ====================== 
// ! Print admin footer   
// ====================== 
$backend->print_footer();

?>