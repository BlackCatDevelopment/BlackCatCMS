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

if (!defined('CAT_PATH') && !defined('CAT_INSTALL'))
{

    //**************************************************************************
    // try to find config.php
    //**************************************************************************
    if (strpos(__FILE__, '/framework/class.secure.php') !== false)
        $config_path = str_replace('/framework/class.secure.php', '', __FILE__);
    else
        $config_path = str_replace('\framework\class.secure.php', '', __FILE__);

    if (!file_exists($config_path . '/config.php'))
    {
        if (file_exists($config_path . '/install/index.php'))
        {
            header("Location: ../install/index.php");
            exit();
        }
        else
        {
            // Problem: no config.php nor installation files...
            exit('<p><strong>Sorry, but this installation seems to be damaged! Please contact your webmaster!</strong></p>');
        }
    }

    //**************************************************************************
    // include config.php
    //**************************************************************************
    require_once($config_path . '/config.php');

    //**************************************************************************
    // analyze path to auto-protect backend
    //**************************************************************************
    if (!defined('CAT_LOGIN_PHASE'))
    {
        $path = (isset($_SERVER['SCRIPT_FILENAME']) ? CAT_Helper_Directory::getInstance()->sanitizePath($_SERVER['SCRIPT_FILENAME']) : NULL);
        if ($path)
        {
            $check = str_replace('/', '\/', CAT_Helper_Directory::getInstance()->sanitizePath(CAT_ADMIN_PATH));
            if (preg_match('~^' . $check . '~i', $path))
            {
                define('CAT_REQUIRE_ADMIN', true);
                if (!CAT_Users::getInstance()->is_authenticated())
                {
                    CAT_Users::getInstance()->handleLogin();
                    exit(0);
                }
                // always enable CSRF protection in backend; does not work with
                // AJAX so scripts called via AJAX should set this constant
                if (!defined('CAT_AJAX_CALL'))
                {
                    CAT_Helper_Protect::getInstance()->enableCSRFMagic();
                }
                global $parser;
                if (!is_object($parser))
                    $parser = CAT_Helper_Template::getInstance('Dwoo');
                // initialize template search path
                $parser->setPath(CAT_THEME_PATH . '/templates');
                $parser->setFallbackPath(CAT_THEME_PATH . '/templates');
            }
        }
        else
        {
            define('CAT_REQUIRE_ADMIN', false);
        }
    }

    if (!defined('CAT_INITIALIZED'))
        require dirname(__FILE__) . '/initialize.php';

    $admin_dir             = str_replace(CAT_PATH, '', CAT_ADMIN_PATH);
    $db                    = new database();
    $direct_access_allowed = array();

    //**************************************************************************
    // some core files must be allowed to load the config.php directly. We
    // get the list of allowed files from the DB
    //**************************************************************************
    $q = $db->query('SELECT * FROM ' . CAT_TABLE_PREFIX . 'class_secure');
    if ($q->numRows() > 0)
    {
        while (false !== ($row = $q->fetchRow(MYSQL_ASSOC)))
        {
            $direct_access_allowed[] = $row['filepath'];
        }
    }

    $allowed = false;
    foreach ($direct_access_allowed as $allowed_file)
    {
        if (strpos($_SERVER['SCRIPT_NAME'], $allowed_file) !== false)
        {
            $allowed = true;
            break;
        }
    }

    if (!$allowed)
    {
        if (((strpos($_SERVER['SCRIPT_NAME'], $admin_dir . '/media/index.php')) !== false) || ((strpos($_SERVER['SCRIPT_NAME'], $admin_dir . '/preferences/index.php')) !== false) || ((strpos($_SERVER['SCRIPT_NAME'], $admin_dir . '/support/index.php')) !== false))
        {
            // special: do absolute nothing!
        }
        elseif ((strpos($_SERVER['SCRIPT_NAME'], $admin_dir . '/index.php') !== false) || (strpos($_SERVER['SCRIPT_NAME'], $admin_dir . '/interface/index.php') !== false))
        {
            // special: call start page of admins directory
            header("Location: " . CAT_ADMIN_URL . '/start/index.php');
            exit();
        }
        elseif (strpos($_SERVER['SCRIPT_NAME'], '/index.php') !== false)
        {

            // call the main page
            header("Location: ../index.php");
            exit();
        }
        else
        {
            if (!headers_sent())
            {
                // set header to 403
                header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
            }
            // stop program execution
            exit('<p><strong style="color:#f00;">ACCESS DENIED!</strong> - Invalid call of <i>' . $_SERVER['SCRIPT_NAME'] . '</i></p>');
        }
    }

    //echo "done secure<br />";
    //exit;
}

/**
 * this is used to configure csrf-magic
 **/
if (!function_exists('csrf_startup'))
{
    function csrf_startup()
    {
        // AJAX requests are allowed via POST only and must identify themselves
        // by adding a '_cat_ajax' param to the request
        if (isset($_POST['_cat_ajax']))
            csrf_conf('rewrite', false);
        // This enables JavaScript rewriting and will ensure your AJAX calls
        // don't stop working.
        csrf_conf('rewrite-js', CAT_URL . '/modules/lib_csrfmagic/csrf-magic.js');
        // This makes csrf-magic call my_csrf_callback() before exiting when
        // there is a bad csrf token. This lets me customize the error page.
        csrf_conf('callback', 'cat_csrf_callback');
        // While this is enabled by default to boost backwards compatibility,
        // for security purposes it should ideally be off. Some users can be
        // NATted or have dialup addresses which rotate frequently. Cookies
        // are much more reliable.
        csrf_conf('allow-ip', false);
        // Token lifetime
        if (defined('TOKEN_LIFETIME') && TOKEN_LIFETIME > 0)
        {
            csrf_conf('expires', TOKEN_LIFETIME);
        }
    }
}

if (!function_exists('cat_csrf_callback'))
{
    function cat_csrf_callback($tokens)
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
        echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html>
      <head>
      <meta http-equiv="content-type" content="text/html; charset=windows-1250">
      <title>Black Cat CMS Error Message</title>
      </head>
      <body>
      <strong>Black Cat CMS NOTICE</strong><br />
      CSRF check failed. Please enable cookies.<br /><br />
      Debug: '.$tokens.'</body></html>';
    }
}

/**
 * strip droplets
 **/
if (!function_exists('cat_secure_formdata'))
{
    function cat_secure_formdata(&$arr)
    {
        foreach ($arr as $key => $value)
        {
            if (is_array($value))
            {
                cat_secure_formdata($value);
            }
            else
            {
                // remove <script> tags
                $value     = str_replace(array(
                    '<script',
                    '</script'
                ), array(
                    '&lt;script',
                    '&lt;/script'
                ), $value);
                $value     = preg_replace('#(\&lt;script.+?)>#i', '$1&gt;', $value);
                $value     = preg_replace('#(\&lt;\/script)>#i', '$1&gt;', $value);
                //$arr[$key] = preg_replace( '#\[\[.+?\]\]#', '', __strip($value) );
                $arr[$key] = str_replace(array(
                    '[',
                    ']'
                ), array(
                    '&#91;',
                    '&#93;'
                ), $value);
            }
        }
    }
}

// secure form input
if (isset($_SESSION) && !defined('CAT_SEC_FORMDATA') && !isset($_SESSION['USER_ID']))
{
    if (count($_GET))
    {
        cat_secure_formdata($_GET);
    }
    if (count($_POST))
    {
        cat_secure_formdata($_POST);
    }
    if (count($_REQUEST))
    {
        cat_secure_formdata($_REQUEST);
    }
    define('CAT_SEC_FORMDATA', true);
}

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
