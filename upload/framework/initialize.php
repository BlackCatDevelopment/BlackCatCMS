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

//**************************************************************************
// add framework subdir to include path
//**************************************************************************
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/framework'),
    get_include_path()
)));
//**************************************************************************
// register autoloader
//**************************************************************************
spl_autoload_register(function($class)
{
    if (defined('CAT_PATH'))
    {
        $file = str_replace('_', '/', $class);
        if (file_exists(CAT_PATH . '/framework/' . $file . '.php'))
        {
            @require CAT_PATH . '/framework/' . $file . '.php';
        }
    }
    // next in stack
});

$reg = CAT_Registry::getInstance();
$reg->register('URL_HELP', 'http://blackcat-cms.org/', true);

if (file_exists(dirname(__FILE__) . '/class.database.php'))
{

    require_once(dirname(__FILE__) . '/class.database.php');

    // Create database class
    $database = new database();
    
    //**************************************************************************
    // Get website settings (title, keywords, description, header, and footer)
    //**************************************************************************
    $sql = 'SELECT `name`,`value` FROM `' . CAT_TABLE_PREFIX . 'settings` ORDER BY `name`';
    if (($result = $database->query($sql)) && ($result->numRows() > 0))
    {
        while (false != ($row = $result->fetchRow(MYSQL_ASSOC)))
        {
            if (preg_match('/^[0-7]{1,4}$/', $row['value']) == true)
            {
                $value = $row['value'];
            }
            elseif (preg_match('/^[0-9]+$/S', $row['value']) == true)
            {
                $value = intval($row['value']);
            }
            elseif ($row['value'] == 'false')
            {
                $value = false;
            }
            elseif ($row['value'] == 'true')
            {
                $value = true;
            }
            else
            {
                $value = $row['value'];
            }
            $temp_name = strtoupper($row['name']);
            //if (!defined($temp_name)) define( $temp_name , $value );
            $reg->register($temp_name, $value, true);
        }
        unset($row);
        }
    else
    {
        die("No settings found in the database, please check your installation!");
    }
    
    //**************************************************************************
    // frontend only
    //**************************************************************************
    if (defined('ENABLE_CSRFMAGIC') && true === ENABLE_CSRFMAGIC)
    {
        CAT_Helper_Protect::getInstance()->enableCSRFMagic();
    }
    
    //**************************************************************************
    //**************************************************************************
    $string_file_mode = STRING_FILE_MODE;
    $reg->register('OCTAL_FILE_MODE', (int) octdec($string_file_mode), true);
    $string_dir_mode = STRING_DIR_MODE;
    $reg->register('OCTAL_DIR_MODE', (int) octdec($string_dir_mode), true);

    //**************************************************************************
    // get CAPTCHA and ASP settings
    //**************************************************************************
    if (!defined('CAT_INSTALL_PROCESS'))
    {
        $sql = 'SELECT * FROM `' . CAT_TABLE_PREFIX . 'mod_captcha_control` LIMIT 1';
        if (false !== ($get_settings = $database->query($sql)))
        {
            if ($get_settings->numRows() == 0)
            {
                die("CAPTCHA-Settings not found");
            }
            $setting = $get_settings->fetchRow(MYSQL_ASSOC);
            $reg->register('ENABLED_CAPTCHA', (($setting['enabled_captcha'] == '1') ? true : false), true);
            $reg->register('ENABLED_ASP', (($setting['enabled_asp'] == '1') ? true : false), true);
            $reg->register('CAPTCHA_TYPE', $setting['captcha_type'], true);
            $reg->register('ASP_SESSION_MIN_AGE', (int) $setting['asp_session_min_age'], true);
            $reg->register('ASP_VIEW_MIN_AGE', (int) $setting['asp_view_min_age'], true);
            $reg->register('ASP_INPUT_MIN_AGE', (int) $setting['asp_input_min_age'], true);
            unset($setting);
        }
    }

    //**************************************************************************
    // set error-reporting
    //**************************************************************************
    if (is_numeric(ER_LEVEL))
    {
        error_reporting(ER_LEVEL);
        if (ER_LEVEL > 0)
            ini_set('display_errors', 1);
    }
    
    //**************************************************************************
    // Start a session
    //**************************************************************************
    if (!defined('SESSION_STARTED'))
    {
        session_name(APP_NAME . 'sessionid');
		$cookie_settings = session_get_cookie_params();
		session_set_cookie_params(
            3 * 3600, // three hours
            $cookie_settings["path"], $cookie_settings["domain"], (strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, 5)) === 'https'), // secure-bool
			true    // http only
		);
        unset($cookie_settings);
    	session_start();
        $reg->register('SESSION_STARTED', true, true);
    }
    if (defined('ENABLED_ASP') && ENABLED_ASP && !isset($_SESSION['session_started']))
        $_SESSION['session_started'] = time();
    
    //**************************************************************************
    // Get users language
    //**************************************************************************
    $val = CAT_Helper_Validate::getInstance();
    $user_lang = $val->sanitizeGet('lang');
    if ( $user_lang && $user_lang != '' && !is_numeric($user_lang) && strlen($user_lang) == 2 && file_exists(CAT_PATH . '/languages/' . $user_lang . '.php'))
    {
        $reg->register('LANGUAGE', strtoupper($user_lang), true);
        }
    if ( ! $reg->exists('LANGUAGE') )
        $reg->register('LANGUAGE',DEFAULT_LANGUAGE,true);

    // Load Language file
    if (!defined('LANGUAGE_LOADED'))
    {
        if (!file_exists(CAT_PATH . '/languages/' . LANGUAGE . '.php'))
        {
            exit('Error loading language file ' . LANGUAGE . ', please check configuration');
    }
        else
        {
            require_once(CAT_PATH . '/languages/' . LANGUAGE . '.php');
        }
    }
    
    //**************************************************************************
    // set timezone and date/time formats
    //**************************************************************************
    $timezone_string = (isset($_SESSION['TIMEZONE_STRING']) ? $_SESSION['TIMEZONE_STRING'] : DEFAULT_TIMEZONE_STRING);
	date_default_timezone_set($timezone_string);
    $reg->register('TIME_FORMAT', CAT_Helper_DateTime::getDefaultTimeFormat(), true);
    $reg->register('DATE_FORMAT', CAT_Helper_DateTime::getDefaultDateFormatShort(), true);
    
    //**************************************************************************
    // Disable magic_quotes_runtime
    //**************************************************************************
    if (version_compare(PHP_VERSION, '5.3.0', '<'))
    {
        set_magic_quotes_runtime(0);
	}
	
    //**************************************************************************
    // Set theme
    //**************************************************************************
    $reg->register('CAT_THEME_URL', CAT_URL . '/templates/' . DEFAULT_THEME, true);
    $reg->register('CAT_THEME_PATH', CAT_PATH . '/templates/' . DEFAULT_THEME, true);
    
    $database->prompt_on_error(PROMPT_MYSQL_ERRORS);
    
    //**************************************************************************
    // set the search library
    //**************************************************************************
    if (!defined('CAT_INSTALL_PROCESS'))
    {
        if (false !== ($query = $database->query("SELECT value FROM " . CAT_TABLE_PREFIX . "search WHERE name = 'cfg_search_library' LIMIT 1")))
        {
            ($query->numRows() > 0) ? $res = $query->fetchRow() : $res['value'] = 'lib_search';
            $reg->register('SEARCH_LIBRARY', $res['value'], true);
        }
        else
        {
            $reg->register('SEARCH_LIBRARY', 'lib_search', true);
        }
    }
    else
    {
        $reg->register('SEARCH_LIBRARY', 'lib_search', true);
    }        
}

//**************************************************************************
// get template engine
//**************************************************************************
global $parser;
$parser = CAT_Helper_Template::getInstance('Dwoo');

$reg->register('CAT_INITIALIZED', true, true);
