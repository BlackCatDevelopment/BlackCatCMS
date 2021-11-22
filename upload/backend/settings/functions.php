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
 *   @copyright       2016, Black Cat Development
 *   @link            https://blackcat-cms.org
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
    while (($level < 10) && (!file_exists($root.'framework/class.secure.php'))) {
        $root .= "../";
        $level += 1;
    }
    if (file_exists($root.'framework/class.secure.php')) {
        include($root.'framework/class.secure.php');
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}

require_once(CAT_PATH.'/framework/functions-utf8.php');

global $groups, $allow_tags_in_fields, $allow_empty_values, $boolean, $numeric;
$groups = array(
    'seo' => array('website_title','website_description','website_keywords','use_short_urls'),
    'frontend' => array('default_template','default_template_variant','website_header','website_footer'),
    'backend' => array('default_theme','default_theme_variant','wysiwyg_editor','redirect_timer','token_lifetime','max_attempts','session_lifetime','session_save_path'),
    'system' => array('er_level','maintenance_mode','maintenance_page','err_page_404','page_level_limit','page_trash','manage_sections','section_blocks','multiple_menus','page_languages','intro_page','homepage_redirection'),
    'users' => array('frontend_signup','frontend_login','home_folders','auth_min_login_length','auth_max_login_length','auth_min_pass_length','auth_max_pass_length','users_allow_mailaddress','initial_page'),
    'server' => array('operating_system','pages_directory','page_extension','media_directory','page_spacer','upload_allowed','app_name','sec_anchor'),
    'mail' => array('server_email','catmailer_lib','catmailer_default_sendername','catmailer_routine','catmailer_smtp_host','catmailer_smtp_timeout','catmailer_smtp_auth','catmailer_smtp_username','catmailer_smtp_password','catmailer_smtp_ssl','catmailer_smtp_ssl_port','catmailer_smtp_starttls'),
    'security' => array('auto_disable_users','cookie_samesite','enable_htmlpurifier','upload_enable_mimecheck','upload_mime_default_type','upload_allowed','captcha_type','text_qa','enabled_captcha','enabled_asp'),
);
$allow_tags_in_fields = array('website_header', 'website_footer');
$allow_empty_values   = array('website_header', 'website_footer', 'sec_anchor', 'pages_directory','catmailer_smtp_host','catmailer_smtp_timeout','catmailer_smtp_username','catmailer_smtp_password');
$boolean              = array('auto_disable_users','frontend_login','home_folders','manage_sections','multiple_menus','page_trash','prompt_mysql_errors','section_blocks','maintenance_mode','homepage_redirection','intro_page','page_languages','users_allow_mailaddress','upload_enable_mimecheck','catmailer_smtp_ssl','catmailer_smtp_starttls','use_short_urls','initial_page');
$numeric              = array('redirect_timer','maintenance_page','err_page_404','page_level_limit','token_lifetime','session_lifetime','max_attempts','catmailer_smtp_ssl_port','catmailer_smtp_timeout');

/**
 * get data from settings table
 **/
function getSettingsTable()
{
    $settings = CAT_Registry::getSettings();
    $data     = array();
    foreach ($settings as $key => $value) {
        $data[strtolower($key)] = $value;
    }
    return $data;
}   // end function getSettingsTable()

/**
 * get settings from search table
 **/
function getSearchSettings()
{
    $backend = CAT_Backend::getInstance('Settings', 'settings_advanced');
    $data = array();
    $res_search = $backend->db()->query('SELECT * FROM `:prefix:search` WHERE `extra`="" OR `extra` IS NULL');
    if (!$backend->db()->isError() && is_object($res_search)) {
        foreach ($res_search->fetchAll() as $row) {
            $data[$row['name']]
                = htmlspecialchars(($row['value']));
        }
    }
    $r = $backend->db()->query('SELECT `value` FROM `:prefix:settings` WHERE `name`=:name', array('name'=>'search'));
    if ($r->rowCount()) {
        $row = $r->fetch();
        $data['search'] = $row['value'];
    }
    return $data;
}   // end function getSearchSettings()

/**
 * get a list of installed templates
 **/
function getTemplateList($for='frontend')
{
    if ($for=='backend') {
        return CAT_Helper_Addons::get_addons(CAT_Registry::get('DEFAULT_THEME'), 'template', 'theme');
    } else {
        return CAT_Helper_Addons::get_addons(CAT_Registry::get('DEFAULT_TEMPLATE'), 'template', 'template');
    }
}   // end function getTemplateList()

/**
 * get a list of defined error levels
 **/
function getErrorLevels()
{
    $ER_LEVELS = CAT_Registry::get('ER_LEVELS', 'array');
    $counter   = 0;
    $data      = array();
    foreach ($ER_LEVELS as $value => $title) {
        $data[$counter] = array(
            'NAME'     => $title,
            'VALUE'    => $value,
            'SELECTED' => (ER_LEVEL == $value) ? true : false
        );
        $counter++;
    }
    return $data;
}   // end function getErrorLevels()

/**
 *
 **/
function getPagesList($fieldname, $selected, $add_empty=false)
{
    $pages_list = CAT_Helper_Page::getPages(CAT_Backend::isBackend());
    $deleted    = CAT_Helper_Page::getPagesByVisibility('deleted');
    if (count($deleted)) {
        $arrh = CAT_Helper_Array::getInstance();
        foreach ($deleted as $page) {
            $arrh->ArrayRemove($page, $pages_list, 'page_id');
        }
    }
    if ($add_empty) {
        array_unshift($pages_list, array('page_id'=>0,'parent'=>0,'level'=>0,'is_open'=>false,'menu_title'=>CAT_Helper_Page::getInstance()->lang()->translate('[none (use internal)]')));
    }
    return CAT_Helper_ListBuilder::getInstance(true)
           ->config(array('space' => '|-- '))
           ->dropdown($fieldname, $pages_list, 0, $selected);
}   // end function getPagesList()

/**
 *
 **/
function getLanguages()
{
    $langs = CAT_Helper_Addons::get_addons(0, 'language');
    $data  = array();
    foreach ($langs as $addon) {
        $l_codes[$addon['NAME']] = $addon['VALUE'];
        $l_names[$addon['NAME']] = entities_to_7bit($addon['NAME']); // sorting-problem workaround
    }
    asort($l_names);
    $counter=0;
    foreach ($l_names as $l_name=>$v) {
        $data[$counter]['CODE']    = $l_codes[$l_name];
        $data[$counter]['NAME']    = $l_name;
        $data[$counter]['SELECTED'] = ( defined('DEFAULT_LANGUAGE') && DEFAULT_LANGUAGE == $l_codes[$l_name]) ? true : false;
        $counter++;
    }
    return $data;
}   // end function getLanguages()

/**
 *
 **/
function getTimezones()
{
    $timezone_table = CAT_Helper_DateTime::getTimezones();
    $counter        = 0;
    $data           = array();
    foreach ($timezone_table as $title) {
        $data[$counter] = array(
            'NAME'            => $title,
            'SELECTED'        => ( defined('DEFAULT_TIMEZONE_STRING') && DEFAULT_TIMEZONE_STRING == $title ) ? true : false
        );
        $counter++;
    }
    return $data;
}

/**
 *
 **/
function getCharsets()
{
    $CHARSETS = CAT_Helper_I18n::getInstance()->getCharsets();
    $counter  = 0;
    $data     = array();
    foreach ($CHARSETS as $code => $title) {
        $data[$counter] = array(
            'NAME'            => $title,
            'VALUE'           => $code,
            'SELECTED'        => ( defined('DEFAULT_CHARSET') && DEFAULT_CHARSET == $code ) ? true : false
        );
        $counter++;
    }
    return $data;
}

/**
 *
 **/
function getDateformats()
{
    $DATE_FORMATS = CAT_Helper_DateTime::getDateFormats();
    $counter      = 0;
    $data         = array();
    foreach ($DATE_FORMATS as $format => $title) {
        $data[$counter] = array(
            'NAME'     => $title,
            'VALUE'    => ( $format != 'system_default' )    ? $format : '',
            'SELECTED' => ( defined('CAT_DEFAULT_DATE_FORMAT') && CAT_DEFAULT_DATE_FORMAT == $format ) ? true    : false
        );
        $counter++;
    }
    return $data;
}

/**
 *
 **/
function getTimeformats()
{
    $TIME_FORMATS = CAT_Helper_DateTime::getTimeFormats();
    $counter      = 0;
    $data         = array();
    foreach ($TIME_FORMATS as $format => $title) {
        $format = str_replace('|', ' ', $format); // Add's white-spaces (not able to be stored in array key)
        $data[$counter] = array(
            'NAME'     => $title,
            'VALUE'    => ( $format != 'system_default' ) ? $format : '',
            'SELECTED' => ( defined('CAT_DEFAULT_TIME_FORMAT') && CAT_DEFAULT_TIME_FORMAT == $format ) ? true : false
        );
        $counter++;
    }
    return $data;
}

/**
 *
 **/
function getMailerLibs()
{
    $data = array();
    $mailer_libs = CAT_Helper_Addons::getInstance()->getLibraries('mail');
    if (count($mailer_libs)) {
        foreach ($mailer_libs as $item) {
            $data[] = $item;
        }
    }
    return $data;
}

/**
 *
 **/
function getCaptchaTypes($backend)
{
    $text_qa         = '';
    $enabled_captcha = '1';
    $enabled_asp     = '1';
    $captcha_type    = 'calc_text';
    // load text-captchas
    if ($query = $backend->db()->query(sprintf("SELECT ct_text FROM `%smod_captcha_control`", CAT_TABLE_PREFIX))) {
        $data    = $query->fetch(PDO::FETCH_ASSOC);
        $text_qa = $data['ct_text'];
    }
    if ($text_qa == '') {
        $text_qa = $backend->lang()->translate('Delete this all to add your own entries'."\n".'or your changes won\'t be saved!'."\n".'### example ###'."\n".'Here you can enter Questions and Answers.'."\n".'Use:'."\n".'?What\'s Claudia Schiffer\'s first name?'."\n".'!Claudia'."\n".'?Question 2'."\n".'!Answer 2'."\n".''."\n".'if language doesn\'t matter.'."\n".' ... '."\n".'Or, if language do matter, use:'."\n".'?EN:What\'s Claudia Schiffer\'s first name?'."\n".'!Claudia'."\n".'?EN:Question 2'."\n".'!Answer 2'."\n".'?DE:Wie ist der Vorname von Claudia Schiffer?'."\n".'!Claudia'."\n".' ... '."\n".'### example ###'."\n".'');
    }

    // connect to database and read out captcha settings
    if ($query = $backend->db()->query(sprintf("SELECT * FROM `%smod_captcha_control`", CAT_TABLE_PREFIX))) {
        $data            = $query->fetch(PDO::FETCH_ASSOC);
        $enabled_captcha = $data['enabled_captcha'];
        $enabled_asp     = $data['enabled_asp'];
        $captcha_type    = $data['captcha_type'];
    }
    return array(
        'captcha_type'    => $captcha_type,
        'enabled_captcha' => $enabled_captcha,
        'enabled_asp'     => $enabled_asp,
        'text_qa'         => $text_qa
    );
}

/**
 *
 **/
function saveServer($backend)
{
    saveGroup($backend, 'server');
    if (CAT_Helper_Validate::sanitizePost('world_writeable') == 'true') {
        $string_file_mode = '0666';
        $string_dir_mode  = '0777';
    } else {
        $string_file_mode = '0644';
        $string_dir_mode = '0755';
    }
    $backend->db()->query(sprintf(
        'UPDATE `%ssettings` SET `value`=\'%s\' WHERE `name`=\'%s\'',
        CAT_TABLE_PREFIX,
        $string_file_mode,
        'string_file_mode'
    ));
    $backend->db()->query(sprintf(
        'UPDATE `%ssettings` SET `value`=\'%s\' WHERE `name`=\'%s\'',
        CAT_TABLE_PREFIX,
        $string_dir_mode,
        'string_dir_mode'
    ));
}

/**
 *
 **/
function saveDatetime($backend)
{
    $settings     = array();
    $old_settings = getSettingsTable();
    $val          = CAT_Helper_Validate::getInstance();
    // language must be 2 upercase letters only
    $default_language = strtoupper($val->sanitizePost('default_language'));
    $settings['default_language']
       = $backend->lang()->checkLang($default_language)
       ? $default_language
       : $old_settings['default_language'];
    // check date format
    $settings['cat_default_date_format']
       = CAT_Helper_DateTime::checkDateformat($val->sanitizePost('default_date_format'))
       ? $val->sanitizePost('default_date_format')
       : $old_settings['cat_default_date_format'];
    // check time format
    $settings['cat_default_time_format']
       = CAT_Helper_DateTime::checkTimeformat($val->sanitizePost('default_time_format'))
       ? $val->sanitizePost('default_time_format')
       : $old_settings['cat_default_time_format'];
    // check timezone string
    $settings['default_timezone_string']
       = CAT_Helper_DateTime::checkTZ($val->sanitizePost('default_timezone_string'))
       ? $val->sanitizePost('default_timezone_string')
       : $old_settings['default_timezone_string'];
    // check charset
    $CHARSETS = $backend->lang()->getCharsets();
    $char_set = $val->sanitizePost('default_charset');
    $settings['default_charset']
       = (array_key_exists($char_set, $CHARSETS)
       ? $char_set
       : $old_settings['default_charset']);
    saveSettings($settings);
}

/**
 * this only saves the jQuery core and UI settings, as the plugins are saved
 * at once via AJAX
 **/
function saveHeaderfiles($backend)
{
    $data = CAT_Helper_Page::getExtraHeaderFiles(0);
    if (CAT_Helper_Validate::sanitizePost('use_core')=='on') {
        $use_core = 'Y';
    } else {
        $use_core = 'N';
    }
    if (CAT_Helper_Validate::sanitizePost('use_ui')=='on') {
        $use_ui = 'Y';
    } else {
        $use_ui = 'N';
    }
    if (count($data)) {
        $query  = 'UPDATE `:prefix:pages_headers` SET `use_core`=:value1, `use_ui`=:value2 WHERE `page_id`=:page_id';
    } else {
        $query  = 'INSERT INTO `:prefix:pages_headers` ( `page_id`, `use_core`, `use_ui` ) VALUES ( :page_id, :value1, :value2 )';
    }
    CAT_Helper_Page::getInstance(1)->db()->query($query, array('page_id'=>0,'value1'=>$use_core,'value2'=>$use_ui));
}   // end function saveHeaderfiles()

/**
 *
 **/
function saveMail($backend)
{
    $settings     = array();
    $old_settings = getSettingsTable();
    $val          = CAT_Helper_Validate::getInstance();

    global $groups, $err_msg;

    foreach ($groups['mail'] as $key) {
        $settings[$key] = $val->sanitizePost($key);
    }

    // email should be validated by core
    // Work-out which mailer routine should be checked
    if ((isset($settings['server_email'])) && (!$val->validate_email($settings['server_email']))) {
        $err_msg[] = $backend->lang()->translate('Invalid default sender eMail address!');
    }

    $catmailer_default_sendername = (isset($settings['catmailer_default_sendername'])) ? $settings['catmailer_default_sendername'] : $old_settings['catmailer_default_sendername'];
    if (($catmailer_default_sendername <> '')) {
        $settings['catmailer_default_sendername'] = $catmailer_default_sendername;
    } else {
        $err_msg[] = $backend->lang()->translate('This field is required').': '.$backend->lang()->translate('Default Sender Name');
    }

    $catmailer_routine = isset($settings['catmailer_routine']) ? $settings['catmailer_routine'] : $old_settings['catmailer_routine'];
    if (($catmailer_routine == 'smtp')) {
        $pattern = '#https?://([A-Z0-9][^:][A-Z.0-9_-]+[a-z]{2,6})#ix';
        $catmailer_smtp_host = (isset($settings['catmailer_smtp_host'])) ? $settings['catmailer_smtp_host'] : $old_settings['catmailer_smtp_host'];
        if (preg_match($pattern, $catmailer_smtp_host, $array)) {
            $catmailer_smtp_host = $array [0];
        }
        if ((isset($catmailer_smtp_host))) {
            if ((isset($catmailer_smtp_host)) && ($catmailer_smtp_host != '')) {
                $settings['catmailer_smtp_host'] = $catmailer_smtp_host;
            } else {
                $err_msg[] = $backend->lang()->translate('You must enter details for the following fields').': '.$backend->lang()->translate('SMTP Host');
            }
        }
        // timeout
        if (isset($settings['catmailer_smtp_timeout'])) {
            // silently catch invalid values
            if (!$settings['catmailer_smtp_timeout']>10 && !$settings['catmailer_smtp_timeout']<120) {
                $settings['catmailer_smtp_timeout'] = 30;
            }
        }
        // Work-out if SMTP authentification should be checked
        $settings['catmailer_smtp_auth']
            = (! isset($settings['catmailer_smtp_auth']) || $settings['catmailer_smtp_auth']=='')
            ? 'false'
            : 'true'
            ;
        if ($settings['catmailer_smtp_auth'] == 'true') {
            $pattern = '/^[a-zA-Z0-9_]{4,30}$/';
            $catmailer_smtp_username = (isset($settings['catmailer_smtp_username'])) ? $settings['catmailer_smtp_username'] : $old_settings['catmailer_smtp_username'];
            if (($catmailer_smtp_username == '') && !preg_match($pattern, $catmailer_smtp_username)) {
                $err_msg[] = $backend->lang()->translate('SMTP').': '.$backend->lang()->translate('Username or password incorrect');
            } else {
                $settings['catmailer_smtp_username'] = $catmailer_smtp_username;
            }

            $current_password = $val->sanitizePost('catmailer_smtp_password');
            $current_password = ($current_password == null ? '' : $current_password);
            if (($current_password == '')) {
                $err_msg[] = $backend->lang()->translate('SMTP').': '.$backend->lang()->translate('Username or password incorrect');
            } elseif (!CAT_Users::validatePassword($current_password)) {
                $err_msg[] = $backend->lang()->translate('Invalid password')
                            . ': ' . CAT_Users::getPasswordError();
            }
        }
        // If SMTP-Authentification is disabled delete USER and PASSWORD for security reasons
        else {
            $settings['catmailer_smtp_username'] = '';
            $settings['catmailer_smtp_password'] = '';
        }
    }
    if (!count($err_msg)) {
        saveSettings($settings);
    }
}

function saveSearch($backend)
{
    $olddata = getSearchSettings();
    $val     = CAT_Helper_Validate::getInstance();
    foreach ($olddata as $key => $value) {
        $new = $val->sanitizePost('search_'.$key);
        if ($new && $new != '' && $new != $value) {
            $backend->db()->query(sprintf(
                "UPDATE `%ssearch` SET `value`='%s' WHERE `name`='%s'",
                CAT_TABLE_PREFIX,
                $new,
                $key
            ));
        }
    }
    // 'search' is a global setting
    $search_set = $val->sanitizePost('search');
    if (!CAT_Registry::defined('SEARCH') || CAT_Registry::get('SEARCH') != $search_set) {
        $backend->db()->query(sprintf(
            "UPDATE `%ssettings` SET `value`='%s' WHERE `name`='%s'",
            CAT_TABLE_PREFIX,
            $search_set,
            'search'
        ));
    }
}

function saveSecurity($backend)
{
    $val             = CAT_Helper_Validate::getInstance();
    $enabled_captcha = ($val->sanitizePost('enabled_captcha') == '1') ? '1' : '0';
    $enabled_asp     = ($val->sanitizePost('enabled_asp')     == '1') ? '1' : '0';
    $captcha_type    = $val->sanitizePost('captcha_type', null, true);

    // update database settings
    $backend->db()->query(sprintf(
        "UPDATE `%smod_captcha_control` SET
		enabled_captcha = '%s', enabled_asp = '%s', captcha_type = '%s' ",
        CAT_TABLE_PREFIX,
        $enabled_captcha,
        $enabled_asp,
        $captcha_type
    ));

    // save text-captchas
    if ($captcha_type == 'text') { // ct_text
        $text_qa = $val->sanitizePost('text_qa', null, true);
        if (!preg_match('/### .*? ###/', $text_qa)) {
            $backend->db()->query(sprintf(
                "UPDATE `%smod_captcha_control` SET ct_text = '%s'",
                CAT_TABLE_PREFIX,
                $text_qa
            ));
        }
    }

    // save the others
    saveGroup($backend, 'security');
}

/**
 *
 **/
function saveGroup($backend, $group)
{
    global $groups;
    $settings     = array();
    $val          = CAT_Helper_Validate::getInstance();
    foreach ($groups[$group] as $key) {
        $settings[$key] = $val->sanitizePost($key);
    }
    saveSettings($settings);
}   // end function saveGroup()

/**
 *
 **/
function saveSettings($settings)
{
    global $database, $err_msg;
    global $groups, $allow_tags_in_fields, $allow_empty_values, $boolean, $numeric;
    $old_settings = getSettingsTable();
    foreach ($settings as $key => $value) {
        $value = trim($value);
        // allow HTML?
        if (!in_array($key, $allow_tags_in_fields)) {
            $value = strip_tags($value);
        }
        // check boolean
        if (in_array($key, $boolean)) {
            $value = (! $value || $value == 'false' || $value == '')
                   ? 'false'
                   : 'true'
                   ;
        }
        // check numeric
        if (in_array($key, $numeric)) {
            if (!is_numeric($value)) {
                continue;
            }
        }
        // suppress errors if no old setting
        if (!isset($old_settings[$key])) {
            $old_settings[$key] = '';
        }
        // function for this special item?
        if (function_exists('check_'.$key)) {
            $func  = 'check_'.$key;
            $value = $func($value, $old_settings[$key]);
        }
        if ($value !== '' || in_array($key, $allow_empty_values)) {
            $check  = $database->query(
                'SELECT `value` FROM `:prefix:settings` WHERE `name`=:name',
                array('name'=>$key)
            );
            if (!$check->numRows()) {
                $database->query(
                    'INSERT INTO `:prefix:settings` VALUES ( NULL, :name, :value )',
                    array('value'=>$value,'name'=>$key)
                );
            } else {
                $database->query(
                    'UPDATE `:prefix:settings` SET `value`=:value WHERE `name`=:name',
                    array('value'=>$value,'name'=>$key)
                );
            }
            if ($database->is_error()) {
                $err_msg[] =  CAT_Users::getInstance()->lang()->translate(
                    'Unable to save setting [{{ setting }}] - error {{ error }}',
                    array( 'setting' => $key, 'error' => $database->get_error() )
                );
            }
        }
    }
}   // end function saveSettings()

/**
 * if auth_min_login_length is changed, there must not be any users that have
 * shorter names
 **/
function check_auth_min_login_length($value, $oldvalue)
{
    global $database, $err_msg;
    $result = $database->query(sprintf(
        'select count(*) as cnt from `%susers` where char_length(username)<%d',
        CAT_TABLE_PREFIX,
        $value
    ));
    if ($result->numRows()) {
        $row = $result->fetch(PDO::FETCH_ASSOC);
        if ($row['cnt']>0) {
            $err_msg[] = CAT_Users::getInstance()->lang()->translate(
                'The min. Login name length could not be saved. There is/are {{ count }} user/s that have shorter names.',
                array( 'count' => $row['cnt'] )
            );
            return $oldvalue;
        }
    }
    return $value;
}   // end function check_auth_min_login_length()

/**
 * if auth_max_login_length is changed, there must not be any users that have
 * longer names
 **/
function check_auth_max_login_length($value, $oldvalue)
{
    global $database, $err_msg;
    $result = $database->query(sprintf(
        'select count(*) as cnt from `%susers` where char_length(username)>%d',
        CAT_TABLE_PREFIX,
        $value
    ));
    if ($result->numRows()) {
        $row = $result->fetch(PDO::FETCH_ASSOC);
        if ($row['cnt']>0) {
            $err_msg[] = CAT_Users::getInstance()->lang()->translate(
                'The max. Login name length could not be saved. There is/are {{ count }} user/s that have longer names.',
                array( 'count' => $row['cnt'] )
            );
            return $oldvalue;
        }
    }
    return $value;
}

/**
 * special function to sanitize token lifetime (-1<value<10000)
 **/
function check_token_lifetime($value, $oldvalue)
{
    return ($value > -1 && $value < 10000)
        ? $value
        : $oldvalue;
}

/**
 * special function to sanitize redirect timer (-1<value<10000)
 **/
function check_redirect_timer($value, $oldvalue)
{
    return ($value > -1 && $value <= 10000)
        ? $value
        : $oldvalue;
}

/**
 * special function to sanitize page_level_limit (0<value<10)
 **/
function check_page_level_limit($value, $oldvalue)
{
    return ($value >= 1 && $value < 10)
        ? $value
        : $oldvalue;
}

/**
 * special function to sanitize max login attempts (0<value<10)
 **/
function check_max_attempts($value, $oldvalue)
{
    return ($value >= 1 && $value < 10)
        ? $value
        : $oldvalue;
}

/**
 *
 **/
function check_pages_directory($value, $oldvalue)
{
    $bad     = array('"','`','!','@','#','$','%','^','&','*','=','+','|',';',':',',','?');
    $value   = str_replace($bad, '', $value);
    $value   = str_replace('\\', '/', $value);
    $pattern = '#[/][a-z,0-9_-]+#';
    preg_match($pattern, $value, $array);
    $value   = (isset($array['0']) ? $array['0'] : $oldvalue);
    return $value;
}

/**
 *
 **/
function check_sec_anchor($value, $oldvalue)
{
    if (!empty($value)) {
        // must begin with a letter
        $pattern = '/^[a-z][a-z_0-9]*$/i';
        if (!preg_match($pattern, $value, $array)) {
            return $oldvalue;
        }
        return $value;
    }
    return $oldvalue;
}

/**
 * remove home folders for all users if the option is changed to "false"
 **/
function check_home_folders($value, $oldvalue)
{
    if (!$value && $oldvalue) {
        global $database;
        $database->query(sprintf(
            'UPDATE `%susers` SET `home_folder` = \'\';',
            CAT_TABLE_PREFIX
        ));
    }
    return $value;
}

/**
 *
 **/
function check_er_level($value, $oldvalue)
{
    $ER_LEVELS = CAT_Registry::get('ER_LEVELS', 'array');
    return
         (isset($value) && (array_key_exists($value, $ER_LEVELS)))
       ? intval($value)
       : $oldvalue;
}

/**
 *
 **/
function check_frontend_signup($value, $oldvalue)
{
    if (is_bool($value) || $value == 'false') {
        return 'false';
    }
    global $database;
    if (($result = $database->query(sprintf('SELECT count(*) AS `tcount` FROM `%sgroups`', CAT_TABLE_PREFIX))) && ($result->numRows() > 0)) {
        $row = $result->fetchRow();
        return
             ($value > 1) && ($value <= $row['tcount'])
           ? intval($value)
           : $oldvalue
           ;
    }
}

function check_use_short_urls($value, $oldvalue)
{
    if (($value && $value=='true')) {
        create_htaccess();
        return true;
    }
    if ((!$value || $value=='false') && file_exists(CAT_PATH.'/.htaccess')) {
        // check if the contents are changed
        $content = get_htaccess_content();
        $hash    = sha1($content);
        $check   = sha1_file(CAT_PATH.'/.htaccess');
        if ($hash == $check) {
            unlink(CAT_PATH.'/.htaccess');
        }
    }
    return false;
}

/**
 * create .htaccess in root dir
 **/
function create_htaccess()
{
    $file = '.htaccess';
    if (file_exists(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/.htaccess'))) {
        $file = 'htaccess_BlackCatCMS.txt';
    }
    $content = get_htaccess_content();
    $fh = fopen(CAT_PATH.'/'.$file, 'w');
    if (is_resource($fh) && $fh) {
        fwrite($fh, $content);
        fclose($fh);
    }
}

function get_htaccess_content()
{
    // filter document root from CAT_PATH
    $server_path = CAT_Helper_Directory::sanitizePath(CAT_PATH);
    $server_path = str_replace(CAT_Helper_Directory::sanitizePath($_SERVER['DOCUMENT_ROOT']), '', $server_path);

    return '    RewriteEngine On
    # If called directly - redirect to short url version
    RewriteCond %{REQUEST_URI} !'.PAGES_DIRECTORY.'/intro.php
    RewriteCond %{REQUEST_URI} '.PAGES_DIRECTORY.'
    RewriteRule ^'.PAGES_DIRECTORY.'/(.*)'.PAGE_EXTENSION.'$ /$1/ [R=301,L]

# Send the request to the index.php for processing
    RewriteCond %{REQUEST_URI} !^/('.str_replace('/', '', PAGES_DIRECTORY).'|'.CAT_BACKEND_FOLDER.'|framework|include|languages|media|account|search|temp|templates/.*)$
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^([\/\sa-zA-Z0-9._-]+)$ '.$server_path.'/index.php?$1 [QSA,L]

# !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
# Important note!
# !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
# The path above should be relative to your DOCUMENT_ROOT. If it\'s not, the
# redirection will not work! So please check the path!
#
# Example:
#    URL  - http://www.yourdomain.com/blackcatcms/index.php
#    path - /blackcatcms/index.php
# !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    ';
}
