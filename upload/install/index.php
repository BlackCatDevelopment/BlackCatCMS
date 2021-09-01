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
 *   @copyright       @copyright       2016, Black Cat Development
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Installer
 *
 */

define("CAT_DEBUG", false);
define("CAT_PATH", dirname(__FILE__) . "/../");

global $depth, $current_build;
$depth = 0;

// check wether to call update.php or start installation
if (
    file_exists("../config.php") &&
    file_exists(dirname(__FILE__) . "/update/update.php") &&
    !file_exists(dirname(__FILE__) . "/steps.tmp")
) {
    $url = pathinfo($_SERVER["SCRIPT_NAME"], PATHINFO_DIRNAME);
    header("Location: " . $url . "/update/update.php");
    exit();
}

define("CAT_INSTALL", true);
define("CAT_LOGFILE", dirname(__FILE__) . "/../temp/inst.log");
define("CAT_INST_EXEC_TIME", 600);

// Start a session
if (!defined("SESSION_STARTED")) {
    session_name("cat_session_id");
    session_start();
    define("SESSION_STARTED", true);
}
//unset($_SESSION);
error_reporting(E_ALL ^ E_NOTICE);

// set global default to avoid warnings
date_default_timezone_set("Europe/Berlin");

set_include_path(
    implode(PATH_SEPARATOR, [
        realpath(dirname(__FILE__) . "/../framework"),
        realpath(dirname(__FILE__) . "/../modules/lib_dwoo/dwoo"),
        realpath(dirname(__FILE__) . "/../modules/lib_doctrine"),
        get_include_path(),
    ])
);
function catcmsinstall_autoload($class)
{
    $file = str_replace("_", "/", $class) . ".php";
    if (file_exists($file)) {
        @include $file;
    }
}
spl_autoload_register("catcmsinstall_autoload", false, false);

// Try to guess installer URL
$installer_uri =
    (isset($_SERVER["HTTPS"]) ? "https://" : "http://") .
    $_SERVER["SERVER_NAME"] .
    ($_SERVER["SERVER_PORT"] != 80 ? ":" . $_SERVER["SERVER_PORT"] : "") .
    $_SERVER["SCRIPT_NAME"];
$installer_uri = dirname($installer_uri);

// *****************************************************************************
// pre installation check: global file system permissions
$dirs = ["temp", "install"];
$pre_inst_err = [];
// check root folder; needed for config.php
if (!is_writable(dirname(__FILE__) . "/..")) {
    $pre_inst_err[] =
        "The CMS base directory must be writable during installation!<br />Das CMS Basisverzeichnis muss während der Installation schreibbar sein!";
}
foreach ($dirs as $i => $dir) {
    $path = dirname(__FILE__) . "/../" . $dir;
    if (!is_writable($path)) {
        $pre_inst_err[] =
            "The [" .
            $dir .
            "] subfolder must be writable!<br />Das Verzeichnis [" .
            $dir .
            "] muss schreibbar sein!";
    }
}
if (!version_compare(phpversion(), "7.3.1", ">=")) {
    $pre_inst_err[] =
        "BlackCat CMS requires PHP >= 7.3.1. You have " .
        phpversion() .
        ". Installation not possible!";
}
if (count($pre_inst_err)) {
    pre_installation_error(implode("<br /><br />", $pre_inst_err));
    exit();
}
// *****************************************************************************

// language helper
include dirname(__FILE__) . "/../framework/CAT/Helper/I18n.php";
$lang = CAT_Helper_I18n::getInstance();
$lang->addFile($lang->getLang() . ".php", dirname(__FILE__) . "/languages");

// the admin dummy defines some methods needed for module installation and error handling
include dirname(__FILE__) . "/admin_dummy.inc.php";
$admin = new admin_dummy();

// user class for checking password
include dirname(__FILE__) . "/../framework/CAT/Users.php";
$users = new CAT_Users();

// directory helper
include dirname(__FILE__) . "/../framework/CAT/Helper/Directory.php";
$dirh = new CAT_Helper_Directory();

// bundled modules
$bundled = [
    // ----- widgets -----
    "blackcat",
    // ----- modules -----
    "blackcatFilter",
    "droplets",
    "lib_getid3",
    "lib_dwoo",
    "lib_images",
    "lib_jquery",
    "lib_pclzip",
    "lib_search",
    "lib_zendlite",
    "lib_doctrine",
    "menu_link",
    "show_menu2",
    "wrapper",
    "wysiwyg",
    "wysiwyg_admin",
    // ----- templates -----
    "blank",
    "freshcat",
    // ----- languages -----
    "DE",
    "EN",
];
$mandatory = [
    "droplets",
    "lib_doctrine",
    "lib_dwoo",
    "lib_jquery",
    "lib_pclzip",
    "show_menu2",
    "wysiwyg",
    "wysiwyg_admin",
];

// *****************************************************************************
// define the steps we are going through
$steps = [
    [
        "id" => "intro",
        "text" => $lang->translate("Welcome"),
        "done" => false,
        "success" => true,
        "current" => true,
        "errors" => null,
    ],
    [
        "id" => "precheck",
        "text" => $lang->translate("Precheck"),
        "done" => false,
        "success" => false,
        "current" => false,
        "errors" => null,
    ],
    [
        "id" => "globals",
        "text" => $lang->translate("Global settings"),
        "done" => false,
        "success" => false,
        "current" => false,
        "errors" => null,
    ],
    [
        "id" => "db",
        "text" => $lang->translate("Database settings"),
        "done" => false,
        "success" => false,
        "current" => false,
        "errors" => null,
    ],
    [
        "id" => "site",
        "text" => $lang->translate("Site settings"),
        "done" => false,
        "success" => false,
        "current" => false,
        "errors" => null,
    ],
    [
        "id" => "postcheck",
        "text" => $lang->translate("Postcheck"),
        "done" => false,
        "success" => false,
        "current" => false,
        "errors" => null,
    ],
    [
        "id" => "optional",
        "text" => $lang->translate("Optional"),
        "done" => false,
        "success" => false,
        "current" => false,
        "errors" => null,
    ],
    [
        "id" => "finish",
        "text" => $lang->translate("Finish"),
        "done" => false,
        "success" => false,
        "current" => false,
        "errors" => null,
    ],
];
// *****************************************************************************

// current state is saved to a temp. file
if (file_exists(dirname(__FILE__) . "/steps.tmp")) {
    $file = implode("\n", file(dirname(__FILE__) . "/steps.tmp"));
    $steps = unserialize($file);
}

// this is a helper for easy mapping of the current step to the steps array
$id_to_step_index = [];
foreach ($steps as $i => $step) {
    $id_to_step_index[$step["id"]] = $i;
}

// this will suppress some errors
$cat_path = $dirh->sanitizePath(dirname(__FILE__) . "/..");
init_constants($cat_path);
if (!defined("CAT_THEME_URL")) {
    define("CAT_THEME_URL", "");
}
if (!defined("URL_HELP")) {
    define("URL_HELP", "");
}
if (!defined("DEFAULT_TEMPLATE")) {
    define("DEFAULT_TEMPLATE", "");
}

// template engine; creates a global var $parser
global $parser;
$parser = CAT_Helper_Template::getInstance("Dwoo");
$parser->setPath(dirname(__FILE__) . "/templates/default");

// set some globals
$parser->setGlobals([
    "installer_uri" => $installer_uri,
]);

// get the current step
$this_step = "intro";
if (isset($_REQUEST["btn_back"])) {
    $this_step = $_REQUEST["prevstep"];
} elseif (isset($_REQUEST["btn_next"])) {
    $this_step = $_REQUEST["nextstep"];
} elseif (isset($_REQUEST["goto"])) {
    $this_step = $_REQUEST["goto"];
}
$parser->setGlobals([
    "this_step" => $this_step,
]);

write2log(sprintf("current step: [%s]", $this_step));

if ($this_step == "intro") {
    // remove old inst.log
    if (file_exists(CAT_LOGFILE)) {
        @unlink(CAT_LOGFILE);
    }
    if (file_exists(dirname(__FILE__) . "/optional")) {
        // check for optional modules
        $zip_files = $dirh->scanDirectory(
            dirname(__FILE__) . "/optional",
            true,
            true,
            dirname(__FILE__) . "/optional/",
            ["zip"]
        );
        if (!count($zip_files) && $steps[6]["id"] == "optional") {
            // remove step 'optional'
            array_splice($steps, 6, 1);
        }
    }
}

// let's see if we have some stored data from previous steps or installations
if (file_exists(dirname(__FILE__) . "/instdata.tmp")) {
    $file = implode("\n", file(dirname(__FILE__) . "/instdata.tmp"));
    $config = unserialize($file);
} else {
    $config = ["ssl_available" => sslCheck()];
}

// set timezone default
if (!isset($config["default_timezone_string"])) {
    if (date_default_timezone_get()) {
        $config["default_timezone_string"] = date_default_timezone_get();
    } elseif (ini_get("date.timezone")) {
        $config["default_timezone_string"] = ini_get("date.timezone");
    } else {
        $config["default_timezone_string"] = "Europe/Berlin";
    }
}

date_default_timezone_set($config["default_timezone_string"]);

if (isset($config["cat_url"]) && $config["cat_url"] != "") {
    $parser->setGlobals([
        "cat_url" => $config["cat_url"],
    ]);
} else {
    $config['cat_url'] = '';
}

if (!isset($config["installed_version"])) {
    // get current version
    if (file_exists(dirname(__FILE__) . "/tag.txt")) {
        $tag = fopen(dirname(__FILE__) . "/tag.txt", "r");
        list($current_version, $current_build) = explode("#", fgets($tag));
        fclose($tag);
    } else {
        $current_version = "0.0.0";
        $current_build = "unknown";
    }
    $config["installed_version"] = $current_version;
}

// call the check-method for last step (if any)
if (isset($_REQUEST["laststep"])) {
    // save the form data into temp file
    foreach ($_REQUEST as $key => $value) {
        if (preg_match('~^installer_(.*)$~i', $key, $match)) {
            $_SESSION[$key] = $value;
            $key = $match[1];
            $config[$key] = $value;
        }
    }
    if (function_exists("check_step_" . $_REQUEST["laststep"])) {
        $callback = "check_step_" . $_REQUEST["laststep"];
        list($ok, $errors) = $callback();
        if (!$ok) {
            $this_step = $_REQUEST["laststep"];
            $steps[$id_to_step_index[$this_step]]["errors"] = $errors;
        }
    }
    if (false !== ($fh = fopen(dirname(__FILE__) . "/instdata.tmp", "w"))) {
        fwrite($fh, serialize($config));
        fclose($fh);
    }
}

list($result, $output) = do_step($this_step);

// print the page
if (!$output) {
    // default page = step 0
    $tpl = "welcome.tpl";
    if (
        file_exists(
            dirname(__FILE__) .
                "/templates/default/welcome_" .
                $lang->getLang() .
                ".tpl"
        )
    ) {
        $tpl = "welcome_" . $lang->getLang() . ".tpl";
    }
    $parser->setPath(dirname(__FILE__) . "/templates/default");
    $output = $parser->get($tpl, []);
}

$parser->output("index.tpl", [
    "debug" => CAT_DEBUG,
    "steps" => $steps,
    "nextstep" => isset($nextstep["id"]) ? $nextstep["id"] : '',
    "prevstep" => isset($prevstep["id"]) ? $prevstep["id"] : '',
    "status" => $currentstep["success"] ? true : false,
    "output" => $output,
    "this_step" => $this_step,
    "dump" => print_r(
        [$this_step, $_REQUEST, $prevstep, $nextstep, $currentstep, $steps],
        1
    ),
]);

/**
 * check the basic prerequisites for the CMS installation; uses
 * precheck.php to do this. Returns the result of preCheckAddon() method
 **/
function show_step_precheck()
{
    global $lang, $parser, $installer_uri;
    $ok = true;

    write2log("> [show_step_precheck()]");

    // precheck.php
    include dirname(__FILE__) . "/../framework/CAT/Helper/Addons.php";
    $addons = CAT_Helper_Addons::getInstance();
    $result = $addons->preCheckAddon(null, dirname(__FILE__), false, true);
    $parser->setPath(dirname(__FILE__) . "/templates/default");
    $result = $parser->get("precheck.tpl", ["output" => $result]);

    // scan the HTML for errors; this is easier than to extend the methods in
    // the Addons helper
    if (preg_match('~class=\"fail~i', $result, $match)) {
        $ok = false;
    }

    $install_dir = pathinfo(dirname(__FILE__), PATHINFO_BASENAME);

    // file permissions check
    $dirs = [
        ["name" => "", "ok" => false],
        ["name" => "page", "ok" => false],
        ["name" => "media", "ok" => false],
        ["name" => "templates", "ok" => false],
        ["name" => "modules", "ok" => false],
        ["name" => "languages", "ok" => false],
        ["name" => "temp", "ok" => false],
    ];
    foreach ($dirs as $i => $dir) {
        $path = dirname(__FILE__) . "/../" . $dir["name"];
        $dirs[$i]["ok"] = is_writable($path);
        if ($dir["name"] == "") {
            $dirs[$i]["name"] = $lang->translate("CMS root directory");
        } else {
            $dirs[$i]["name"] = "/" . $dirs[$i]["name"] . "/";
        }
        if ($dirs[$i]["ok"] === false) {
            $ok = false;
        }
    }

    // special check for install dir (must be world writable)
    $inst_is_writable = is_writable(dirname(__FILE__)); //( substr(sprintf('%o', fileperms(dirname(__FILE__))), -1) == 7 ? true : false );
    if (!$inst_is_writable) {
        $ok = false;
    }
    $dirs[] = [
        "name" =>
            $lang->translate("CMS installation directory") .
            " (<tt>" .
            $install_dir .
            "</tt>)",
        "ok" => $inst_is_writable,
    ];

    $output = $parser->get("fperms.tpl", [
        "dirs" => $dirs,
        "ok" => $ok,
        "result" => $ok
            ? $lang->translate("All checks succeeded!")
            : $lang->translate(
                "Sorry, we encountered some issue(s) that will inhibit the installation. Please check the results above and fix the issue(s) listed there."
            ),
    ]);

    write2log("< [show_step_precheck()]");

    return [$ok, $result . $output];
} // end function show_step_precheck()

/**
 * global settings
 **/
function show_step_globals($step)
{
    global $lang, $parser, $installer_uri, $config, $dirh;
    global $timezone_table;

    write2log("> [show_step_globals()]");

    // get timezones
    include dirname(__FILE__) . "/../framework/CAT/Helper/DateTime.php";
    $timezone_table = CAT_Helper_DateTime::getInstance()->getTimezones();

    $lang_dir = dirname(__FILE__) . "/../languages/";
    $lang_files = $dirh
        ->setRecursion(false)
        ->setSkipFiles(["index"])
        ->getPHPFiles($lang_dir, $lang_dir);
    $dirh->setRecursion(true); // reset

    // get language name
    foreach ($lang_files as $temp_file) {
        $str = file($lang_dir . $temp_file);
        $language_name = "";
        foreach ($str as $line) {
            if (strpos($line, "language_name") != false) {
                eval($line);
                break;
            }
        }
        $lang_short = pathinfo($temp_file, PATHINFO_FILENAME);
        $langs[$lang_short] = $language_name;
    }

    ksort($langs);

    if (!isset($config["default_language"])) {
        $config["default_language"] = $lang->getLang();
    }

    // operating system
    // --> FrankH: Detect OS
    $ctrue = " checked='checked'";
    $cfalse = "";
    if (substr(php_uname("s"), 0, 7) == "Windows") {
        $osw = $ctrue;
        $osl = $cfalse;
        $startstyle = "none";
    } else {
        $osw = $cfalse;
        $osl = $ctrue;
        $startstyle = "block";
    }
    // <-- FrankH: Detect OS

    $output = $parser->get("globals.tpl", [
        "installer_cat_url" => dirname($installer_uri) . "/",
        "installer_session_save_path" => isset($config["session_save_path"])
            ? $config["session_save_path"]
            : "temp/session",
        "timezones" => $timezone_table,
        "installer_default_timezone_string" =>
            $config["default_timezone_string"],
        "languages" => $langs,
        "installer_default_language" => $config["default_language"],
        "editors" => findWYSIWYG(),
        "installer_default_wysiwyg" => isset($config["default_wysiwyg"]) ? $config["default_wysiwyg"] : '',
        "installer_ssl" => isset($config["ssl"]) ? $config["ssl"] : sslCheck(),
        "is_linux" => $osl,
        "is_windows" => $osw,
        "errors" => $step["errors"],
        "ssl_available" => isset($config["ssl_available"])
            ? $config["ssl_available"]
            : sslCheck(),
    ]);

    write2log("< [show_step_globals()]");

    return [true, $output];
} // end function show_step_globals()

/**
 *
 **/
function check_step_globals()
{
    global $config, $lang;
    write2log("> [check_step_globals()]");
    $errors = [];
    if (!isset($config["cat_url"]) || $config["cat_url"] == "") {
        $errors["installer_cat_url"] = $lang->translate(
            "Please insert the base URL!"
        );
    }
    if (
        !isset($config["session_save_path"]) ||
        $config["session_save_path"] == ""
    ) {
        $errors["installer_session_save_path"] = $lang->translate(
            "Enter a relative path where the sessions are saved."
        );
    }
    if (!isset($config["ssl"]) || $config["ssl"] == "") {
        $config["ssl"] = false;
    }
    write2log("< [check_step_globals()]");
    return [count($errors) ? false : true, $errors];
} // end function check_step_globals()

/**
 * database settings
 **/
function show_step_db($step)
{
    global $parser, $config;
    write2log("> [show_step_db()]");
    $output = $parser->get("db.tpl", [
        "installer_database_host" => isset($config["database_host"])
            ? $config["database_host"]
            : "localhost",
        "installer_database_port" => isset($config["database_port"])
            ? $config["database_port"]
            : "3306",
        "installer_database_username" => isset($config["database_username"])
            ? $config["database_username"]
            : "my-user-name",
        "installer_database_password" => isset($config["database_password"])
            ? $config["database_password"]
            : "",
        "installer_database_name" => isset($config["database_name"])
            ? $config["database_name"]
            : "my-db-name",
        "installer_table_prefix" => isset($config["table_prefix"])
            ? $config["table_prefix"]
            : "cat_",
        "installer_install_tables" => isset($config["install_tables"])
            ? $config["install_tables"]
            : "y",
        "installer_no_validate_db_password" => isset(
            $config["no_validate_db_password"]
        )
            ? $config["no_validate_db_password"]
            : "",
        "errors" => $step["errors"],
    ]);
    write2log("< [show_step_db()]");
    return [true, $output];
} // end function show_step_db()

/**
 * check the db connection
 **/
function check_step_db()
{
    global $admin, $dirh, $config;
    write2log("> [check_step_db()]");
    // do not check if back button was clicked
    if (isset($_REQUEST["btn_back"])) {
        return [true, []];
    }
    $errors = __cat_check_db_config();
    if (!count($errors)) {
        $cat_path = $dirh->sanitizePath(dirname(__FILE__) . "/..");
        $db_config_content =
            "
;<?php
;die(); // For further security
;/*

[CAT_DB]
TYPE=mysql
HOST=" .
            $config["database_host"] .
            "
PORT=" .
            $config["database_port"] .
            "
USERNAME=" .
            $config["database_username"] .
            "
PASSWORD=\"" .
            $config["database_password"] .
            "\"
NAME=" .
            $config["database_name"] .
            "

;*/
;?>
";
        // save database settings; we generate a file name here
        $db_settings_file =
            $cat_path .
            "/framework/CAT/Helper/DB/" .
            $admin->createGUID("") .
            ".bc.php";
        write2log("trying to create " . $db_settings_file);
        if (($handle = @fopen($db_settings_file, "w")) === false) {
            write2log(
                "< [check_step_db()] (cannot create database settings file " .
                    $db_settings_file .
                    ")"
            );
            return [
                false,
                $lang->translate(
                    "Cannot open the configuration file ({{ file }})",
                    ["file" => $db_settings_file]
                ),
            ];
        } else {
            if (
                fwrite(
                    $handle,
                    $db_config_content,
                    strlen($db_config_content)
                ) === false
            ) {
                write2log(
                    "< [check_step_db()] (cannot write to database settings file)"
                );
                fclose($handle);
                return [
                    false,
                    $lang->translate(
                        "Cannot write to the configuration file ({{ file }})",
                        ["file" => $db_config_content]
                    ),
                ];
            }
            write2log(
                "created database settings file [" .
                    pathinfo($db_settings_file, PATHINFO_BASENAME) .
                    "]"
            );
            // Close file
            fclose($handle);
        }
    }
    write2log("< [check_step_db()]");
    return [count($errors) ? false : true, $errors];
} // end function check_step_db()

/**
 *
 **/
function show_step_site($step)
{
    global $lang, $config, $parser;
    write2log("> [show_step_site()]");
    $output = $parser->get("site.tpl", [
        "installer_website_title" => isset($config["website_title"])
            ? $config["website_title"]
            : "BlackCat CMS",
        "installer_admin_username" => isset($config["admin_username"])
            ? $config["admin_username"]
            : "",
        "installer_admin_password" => isset($config["admin_password"])
            ? $config["admin_password"]
            : "",
        "installer_admin_repassword" => isset($config["admin_repassword"])
            ? $config["admin_repassword"]
            : "",
        "installer_admin_email" => isset($config["admin_email"])
            ? $config["admin_email"]
            : "",
        "errors" => $step["errors"],
    ]);
    write2log("< [show_step_site()]");
    return [true, $output];
} // end function show_step_site()

/**
 *
 **/
function check_step_site()
{
    global $lang, $config, $users, $parser;
    write2log("> [check_step_site()]");
    // do not check if back button was clicked
    if (isset($_REQUEST["btn_back"])) {
        return [true, []];
    }
    $errors = [];
    if (!isset($config["website_title"]) || $config["website_title"] == "") {
        $errors["installer_website_title"] = $lang->translate(
            "Please enter a website title!"
        );
    }

    // check admin user name
    if (!isset($config["admin_username"]) || $config["admin_username"] == "") {
        $errors["installer_admin_username"] = $lang->translate(
            'Please enter an admin username (choose "admin", for example)!'
        );
    } else {
        if (strlen($config["admin_username"]) < 5) {
            $errors["installer_admin_username"] = $lang->translate(
                "Name too short! The admin username should be at least 5 chars long."
            );
        } elseif (
            !preg_match('/^[a-z0-9][a-z0-9_-]+$/i', $config["admin_username"])
        ) {
            $errors["installer_admin_username"] = $lang->translate(
                "Only characters a-z, A-Z, 0-9 and _ allowed in admin username"
            );
        }
    }

    // check admin password
    if (!isset($config["no_validate_admin_password"])) {
        if (
            !isset($config["admin_password"]) ||
            $config["admin_password"] == ""
        ) {
            $errors["installer_admin_password"] = $lang->translate(
                "Please enter an admin password!"
            );
        }
        if (
            !isset($config["admin_repassword"]) ||
            $config["admin_repassword"] == ""
        ) {
            $errors["installer_admin_repassword"] = $lang->translate(
                "Please retype the admin password!"
            );
        }
        if (
            isset($config["admin_password"]) &&
            isset($config["admin_repassword"]) &&
            $config["admin_password"] != "" &&
            $config["admin_repassword"] != "" &&
            strcmp($config["admin_password"], $config["admin_repassword"])
        ) {
            $errors["installer_admin_password"] = $lang->translate(
                "The admin passwords you have given do not match!"
            );
            $errors["installer_admin_repassword"] = $lang->translate(
                "The admin passwords you have given do not match!"
            );
        }
        if (!$users->validatePassword($config["admin_password"], false, true)) {
            $errors["installer_admin_password"] =
                $lang->translate("Invalid password!") .
                " (" .
                $users->getPasswordError() .
                ")";
        }
    }

    // check admin email address
    if (!isset($config["admin_email"]) || $config["admin_email"] == "") {
        $errors["installer_admin_email"] = $lang->translate(
            "Please enter an email address!"
        );
    } else {
        if (
            !preg_match(
                '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$/i',
                $config["admin_email"]
            )
        ) {
            $errors["installer_admin_email"] = $lang->translate(
                "Please enter a valid email address for the Administrator account"
            );
        }
    }

    write2log("< [check_step_site()]");

    return [count($errors) ? false : true, $errors];
} // end function check_step_site()

/**
 *
 **/
function show_step_postcheck()
{
    global $lang, $config, $parser;
    write2log("> [show_step_postcheck()]");
    foreach ($config as $key => $value) {
        if (preg_match("~password~i", $key)) {
            $config[$key] = "********";
        }
        if (preg_match("~repassword~i", $key)) {
            unset($config[$key]);
        }
        if (preg_match("~no_validate_admin_password~i", $key)) {
            unset($config[$key]);
        }
        if (preg_match("~installed_version~i", $key)) {
            unset($config[$key]);
        }
        if (preg_match("~optional_addon~i", $key)) {
            $config[$key] = count($config[$key]);
        }
        if ($key == "ssl_available") {
            $config[$key] = $config[$key] == 1 ? "true" : "false";
        }
    }
    $output = $parser->get("postcheck.tpl", ["config" => $config]);
    write2log("< [show_step_postcheck()]");
    return [true, $output];
} // end function show_step_postcheck()

/**
 * install optional addons (located in ./optional subfolder)
 **/
function show_step_optional()
{
    global $dirh, $parser, $config, $installer_uri, $lang;
    write2log("> [show_step_optional()]");
    // do base installation first
    list($result, $output) = __do_install();
    if (!$result) {
        return [true, $output];
    }
    // list of optional modules
    // no check for 'exists' here, because this is done in intro step!
    $zip_files = $dirh->scanDirectory(
        dirname(__FILE__) . "/optional",
        true,
        true,
        dirname(__FILE__) . "/optional/",
        ["zip"]
    );
    if (count($zip_files)) {
        // try to set max_execution_time
        $info = null;
        // test only
        if (false === ini_set("max_execution_time", CAT_INST_EXEC_TIME)) {
            $info = $lang->translate(
                "Unable to set max_execution_time; there may be problems installation big optional modules!"
            );
        } else {
            $info = $lang->translate(
                "Set max_execution_time to {{ sec }} seconds",
                ["sec" => ini_get("max_execution_time")]
            );
        }
        // fix path (some modules may change it)
        $parser->setPath(dirname(__FILE__) . "/templates/default");
        $output = $parser->get("optional.tpl", [
            "backend_path" => "backend",
            "cat_url" => CAT_URL,
            "installer_uri" => $installer_uri,
            "zip_files" => $zip_files,
            "config" => $config,
            "info" => $info,
        ]);
        write2log("< [show_step_optional()]");
        return [true, $output];
    }
    write2log("> [show_step_optional()] (no optional modules found)");
} // end function show_step_optional()

/**
 * install optional addons (located in ./optional subfolder)
 **/
function check_step_optional()
{
    write2log("> [check_step_optional()]");
    if (!isset($_REQUEST["installer_optional_addon"])) {
        return [true, []];
    }
    list($ok, $errors) = install_optional_modules();
    write2log("< [check_step_optional()]");
    return [count($errors) ? false : true, $errors];
} // end function check_step_optional()

/**
 *
 **/
function show_step_finish()
{
    global $lang, $parser, $installer_uri, $config, $dirh;
    write2log("> [show_step_finish()]");
    // check if installation is done
    $cat_path = $dirh->sanitizePath(dirname(__FILE__) . "/..");
    init_constants($cat_path);
    include $cat_path . "/framework/class.database.php";
    $database = CAT_Helper_DB::getInstance();

    // check if pages table exists
    $table_prefix = $config["table_prefix"];
    try {
        $result = $database->query(
            'SHOW TABLES LIKE ":prefix:cat_mod_wysiwyg_admin_v2"'
        );
        if (!is_object($result) || !$result->numRows()) {
            // do base installation first
            list($result, $output) = __do_install();
            if (!$result) {
                write2log("< [show_step_finish()]");
                return [true, $output];
            }
        }
    } catch (Exception $e) {
        write2log(
            "Unable to retrieve the database tables! [" .
                $database->getError() .
                "]"
        );
        return [false, null];
    }

    $tpl = "finish.tpl";
    if (
        file_exists(
            dirname(__FILE__) .
                "/templates/default/finish_" .
                $lang->getLang() .
                ".tpl"
        )
    ) {
        $tpl = "finish_" . $lang->getLang() . ".tpl";
    }
    // fix globals
    $parser->setGlobals([
        "installer_uri" => $installer_uri,
        "prevstep" => null,
    ]);
    // fix path (some modules may change it)
    $parser->setPath(dirname(__FILE__) . "/templates/default");
    $output = $parser->get($tpl, [
        "backend_path" => "backend",
        "installer_uri" => $installer_uri,
    ]);
    write2log("< [show_step_finish()]");
    return [true, $output];
} // function show_step_finish()

/*******************************************************************************
 *                 HELPER FUNCTIONS
 ******************************************************************************/

/**
 * find the default permissions for new files
 **/
function default_file_mode()
{
    // we've already created some new files, so just check the perms they've got
    if (file_exists(dirname(__FILE__) . "/steps.tmp")) {
        $filename = dirname(__FILE__) . "/steps.tmp";
        $default_file_mode =
            "0" . substr(sprintf("%o", fileperms($filename)), -3);
    } else {
        $default_file_mode = "0777";
    }
    return $default_file_mode;
} // end function default_file_mode()

/**
 * find the default permissions for new directories by creating a test dir
 **/
function default_dir_mode($temp_dir)
{
    if (is_writable($temp_dir)) {
        $dirname = $temp_dir . "/test_permissions/";
        mkdir($dirname);
        //exit;
        $default_dir_mode =
            "0" . substr(sprintf("%o", fileperms($dirname)), -3);
        rmdir($dirname);
    } else {
        $default_dir_mode = "0777";
    }
    return $default_dir_mode;
} // end function default_dir_mode()

/**
 * install tables
 **/
function install_tables($database)
{
    global $config;
    write2log("> [install_tables()]");
    if (!defined("CAT_INSTALL_PROCESS")) {
        define("CAT_INSTALL_PROCESS", true);
    }
    // import structure
    $errors["install tables"] = __cat_installer_import_sql(
        dirname(__FILE__) . "/db/structure.sql",
        $database
    );
    write2log("< [install_tables()]");
    return [
        count($errors["install tables"]) ? false : true,
        count($errors["install tables"]) ? $errors : [],
    ];
} // end function install_tables()

/**
 * fills the tables created by install_tables()
 **/
function fill_tables($database)
{
    global $config, $admin, $current_build;

    write2log("> [fill_tables()]");

    $errors = [];

    // create a random session name
    list($usec, $sec) = explode(" ", microtime());
    srand((float) $sec + (float) $usec * 100000);
    $session_rand = rand(1000, 9999);

    // Work-out file permissions
    if ($config["operating_system"] == "windows") {
        $file_mode = "0644";
        $dir_mode = "0755";
    } elseif (
        isset($config["world_writeable"]) &&
        $config["world_writeable"] == "true"
    ) {
        $file_mode = "0666";
        $dir_mode = "0777";
    } else {
        $file_mode = default_file_mode();
        $dir_mode = default_dir_mode("../temp");
    }

    // fill 'hardcoded' settings and class.secure config
    __cat_installer_import_sql(dirname(__FILE__) . "/db/data.sql", $database);

    $current_version = $config["installed_version"];

    // for optional wysiwyg editors; requires name to be something like
    // <editorname>_xxx.zip, which will be prefixed with 'opt_' by the wizard,
    // so second part is the name of the editor
    if (isset($config["default_wysiwyg"]) && $config["default_wysiwyg"] != "" && $config["default_wysiwyg"] !== "edit_area") {
        list($ignore, $config["default_wysiwyg"], $ignore) = explode(
            "_",
            $config["default_wysiwyg"],
            3
        );
    }

    // fill settings configured by installer
    $settings_rows =
        "INSERT INTO `" .
        CAT_TABLE_PREFIX .
        "settings` " .
        " (name, value) VALUES " .
        " ('app_name', 'cat$session_rand')," .
        " ('cat_build', '$current_build')," .
        " ('cat_version', '$current_version')," .
        " ('default_language', '" .
        $config["default_language"] .
        "')," .
        " ('default_timezone_string', '" .
        $config["default_timezone_string"] .
        "')," .
        " ('installation_time', '" .
        time() .
        "')," .
        " ('operating_system', '" .
        $config["operating_system"] .
        "')," .
        " ('session_save_path', '" .
        $config["session_save_path"] .
        "')," .
        " ('string_dir_mode', '$dir_mode')," .
        " ('string_file_mode', '$file_mode')," .
        " ('website_title', '" .
        $config["website_title"] .
        "')," .
        " ('wysiwyg_editor', '" .
        $config["default_wysiwyg"] .
        "')";

    $database->query($settings_rows);
    if ($database->is_error()) {
        trigger_error(
            sprintf("[%s - %s] %s", __FILE__, __LINE__, $database->get_error()),
            E_USER_ERROR
        );
        $errors["settings"] = $database->get_error();
    } else {
        CAT_Helper_Directory::getInstance()->createDirectory(
            CAT_PATH . "/" . $config["session_save_path"]
        );
        $f = fopen(
            CAT_PATH . "/" . $config["session_save_path"] . "/.htaccess",
            "a+"
        );
        fwrite($f, "deny from all");
        fclose($f);
        chmod(CAT_PATH . "/" . $config["session_save_path"], 0700);
        write2log(
            sprintf(
                "created session save path [%s]",
                CAT_PATH . "/" . $config["session_save_path"]
            )
        );
    }

    // Admin group
    $full_system_permissions =
        "pages,pages_view,pages_add,pages_add_l0,pages_settings,pages_modify,pages_intro,pages_delete,media,media_view,media_upload,media_rename,media_delete,media_create,addons,modules,modules_view,modules_install,modules_uninstall,templates,templates_view,templates_install,templates_uninstall,languages,languages_view,languages_install,languages_uninstall,settings,settings_basic,settings_advanced,access,users,users_view,users_add,users_modify,users_delete,groups,groups_view,groups_add,groups_modify,groups_delete,admintools,service";
    $insert_admin_group =
        "INSERT INTO `" .
        CAT_TABLE_PREFIX .
        "groups` VALUES ('1', 'Administrators', '$full_system_permissions', '', '')";
    $database->query($insert_admin_group);
    if ($database->is_error()) {
        trigger_error(
            sprintf("[%s - %s] %s", __FILE__, __LINE__, $database->get_error()),
            E_USER_ERROR
        );
        $errors["groups"] = $database->get_error();
    } else {
        write2log("filled table [group]");
    }

    // Admin user
    $insert_admin_user =
        "INSERT INTO `" .
        CAT_TABLE_PREFIX .
        "users` (user_id,group_id,groups_id,active,username,password,email,display_name) VALUES ('1','1','1','1','" .
        $config["admin_username"] .
        "','" .
        password_hash($config["admin_password"], PASSWORD_DEFAULT) .
        "','" .
        $config["admin_email"] .
        "','Administrator')";
    $database->query($insert_admin_user);
    if ($database->is_error()) {
        trigger_error(
            sprintf("[%s - %s] %s", __FILE__, __LINE__, $database->get_error()),
            E_USER_ERROR
        );
        $errors["users"] = $database->get_error();
    } else {
        write2log("filled table [users]");
    }

    write2log("< [fill_tables()]");

    return [count($errors) ? false : true, $errors];
} // end function fill_tables()

/**
 * installs all modules, templates, and languages
 **/
function install_modules($cat_path, $database)
{
    global $admin, $bundled, $mandatory;

    write2log("> [install_modules()]");

    $errors = [];

    require $cat_path . "/framework/initialize.php";

    // Load addons into DB
    $dirs = [
        "modules" => $cat_path . "/modules/",
        "templates" => $cat_path . "/templates/",
        "languages" => $cat_path . "/languages/",
    ];
    $ignore_files = ["admin.php", "index.php", "edit_module_files.php"];

    write2log("------------------------------------");
    write2log("-----    installing addons     -----");
    write2log("------------------------------------");

    foreach ($dirs as $type => $dir) {
        $subs =
            $type == "languages"
                ? CAT_Helper_Directory::getInstance()
                    ->setRecursion(false)
                    ->getPHPFiles($dir, $dir . "/")
                : CAT_Helper_Directory::getInstance()
                    ->setRecursion(false)
                    ->getDirectories($dir, $dir . "/");
        natsort($subs);
        foreach ($subs as $item) {
            // for now: do not install lib_search here, as it lets the installer break!
            if ($item == "lib_search") {
                continue;
            }
            if (in_array($item, $ignore_files)) {
                continue;
            }
            if ($type == "languages") {
                write2log("installing language [" . $item . "]");
                $info = CAT_Helper_Addons::checkInfo($dir . "/" . $item);
                if (
                    !CAT_Helper_Addons::loadModuleIntoDB(
                        $dir . "/" . $item,
                        "install",
                        $info
                    )
                ) {
                    $errors[$dir] = sprintf(
                        "Unable to add language [%s] to database!",
                        $item
                    );
                    write2log(
                        sprintf(
                            "Unable to add language [%s] to database!",
                            $item
                        )
                    );
                } else {
                    write2log(
                        sprintf(
                            "%s [%s] sucessfully installed",
                            ucfirst(substr($type, 0, -1)),
                            $item
                        )
                    );
                }
            } else {
                write2log("installing module/template [" . $item . "]");
                $addon_info = CAT_Helper_Addons::checkInfo($dir . "/" . $item);
                // load the module info into the database
                if (
                    !CAT_Helper_Addons::loadModuleIntoDB(
                        $dir . "/" . $item,
                        "install",
                        $addon_info
                    )
                ) {
                    $errors[$dir] = sprintf(
                        "Unable to add %s [%s] to database!",
                        $type,
                        $item
                    );
                    write2log(
                        sprintf(
                            "Unable to add %s [%s] to database!",
                            $type,
                            $item
                        )
                    );
                } else {
                    write2log("running " . $item . "/install.php");
                    // Run the install script if there is one
                    if (file_exists($dir . "/" . $item . "/install.php")) {
                        require $dir . "/" . $item . "/install.php";
                    }
                    write2log(
                        sprintf(
                            "%s [%s] sucessfully installed",
                            ucfirst(substr($type, 0, -1)),
                            $item
                        )
                    );
                }
            }
        }
    }

    // mark bundled modules
    foreach ($bundled as $module) {
        $database->query(
            sprintf(
                'UPDATE `%saddons` SET bundled="Y" WHERE directory="%s"',
                CAT_TABLE_PREFIX,
                $module
            )
        );
    }
    // mark mandatory modules
    foreach ($mandatory as $module) {
        $database->query(
            sprintf(
                'UPDATE `%saddons` SET removable="N" WHERE directory="%s"',
                CAT_TABLE_PREFIX,
                $module
            )
        );
    }

    write2log("< [install_modules()]");

    return [count($errors) ? false : true, $errors];
} // end function install_modules ()

/**
 * installs additional modules (located in ./optional subfolder)
 **/
function install_optional_modules()
{
    global $admin, $bundled, $config, $lang, $dirh;

    write2log("> [install_optional_modules()]");

    if (
        !isset($_REQUEST["installer_optional_addon"]) ||
        !is_array($_REQUEST["installer_optional_addon"]) ||
        !count($_REQUEST["installer_optional_addon"])
    ) {
        fwrite($logh, "no additional addons to install");
        fclose($logh);
        return [true, []];
    } else {
        $config["optional_addon"] == $_REQUEST["installer_optional_addon"];
    }

    write2log("------------------------------------");
    write2log("-----installing optional addons-----");
    write2log("------------------------------------");
    write2log(print_r($config["optional_addon"], 1));

    $cat_path = $dirh->sanitizePath(dirname(__FILE__) . "/..");
    $errors = [];

    // try to set max_execution_time
    ini_set("max_execution_time", CAT_INST_EXEC_TIME);

    // set installed CMS version for precheck.php
    CAT_Registry::set("CAT_VERSION", $config["installed_version"], true);
    // set other constants
    init_constants($cat_path);

    include $cat_path . "/framework/class.database.php";
    $database = new database();
    foreach ($config["optional_addon"] as $file) {
        if (
            !file_exists(
                $dirh->sanitizePath(dirname(__FILE__) . "/optional/" . $file)
            )
        ) {
            write2log(
                "file not found: " .
                    $dirh->sanitizePath(
                        dirname(__FILE__) . "/optional/" . $file
                    )
            );
            $errors[] = $lang->translate("No such file: [{{file}}]", [
                "file" => $file,
            ]);
        } else {
            write2log("installing optional addon [" . $file . "]");
            if (
                !CAT_Helper_Addons::installModule(
                    $dirh->sanitizePath(
                        dirname(__FILE__) . "/optional/" . $file
                    ),
                    true // silent
                )
            ) {
                write2log(
                    "-> installation failed! " . CAT_Helper_Addons::getError()
                );
                if (CAT_Helper_Addons::getError() != "already installed") {
                    $errors[] = $lang->translate(
                        "-> Unable to install {{module}}! {{error}}",
                        [
                            "module" => $file,
                            "error" => CAT_Helper_Addons::getError(),
                        ]
                    );
                }
            } else {
                write2log("-> installation succeeded");
            }
        }
    }

    write2log("< [install_optional_modules()]");

    return [count($errors) ? false : true, $errors];
}

/**
 * checks important tables for existance
 **/
function check_tables($database)
{
    global $config;
    $errors = [];
    $all_tables = [];
    $missing_tables = [];

    write2log("> [check_tables()]");

    $table_prefix = $config["table_prefix"];

    $requested_tables = [
        "class_secure",
        "pages",
        "page_langs",
        "sections",
        "settings",
        "users",
        "groups",
        "addons",
        "search",
        "mod_droplets",
        "mod_droplets_settings",
        "mod_droplets_permissions",
        "mod_wysiwyg",
        "mod_wysiwyg_admin_v2",
    ];
    for ($i = 0; $i < count($requested_tables); $i++) {
        $requested_tables[$i] = $table_prefix . $requested_tables[$i];
    }

    $result = $database->query("SHOW TABLES FROM `" . _CAT_DB_NAME . "`");
    if (!is_object($result)) {
        $errors["tables"] =
            "Unable to check tables - no result from SHOW TABLES!";
    } else {
        $temp = $result->fetchAll(PDO::FETCH_NUM);
        foreach ($temp as $t) {
            $all_tables[] = $t[0];
        }
        foreach ($requested_tables as $temp_table) {
            if (!in_array($temp_table, $all_tables)) {
                $missing_tables[] = $temp_table;
            }
        }
    }

    /**
     *    If one or more needed tables are missing, so
     *    we can't go on and have to display an error
     */
    if (count($missing_tables) > 0) {
        $errors["missing"] = $missing_tables;
    }

    /**
     *    Try to get some default settings ...
     *    Keep in Mind, that the values are only used as default, if an entry isn't found.
     */
    $vars = [
        "DEFAULT_THEME" => "freshcat",
        "CAT_THEME_URL" => CAT_URL . "/templates/freshcat",
        "CAT_THEME_PATH" => CAT_PATH . "/templates/freshcat",
        "LANGUAGE" => $config["default_language"],
        "SERVER_EMAIL" => "admin@yourdomain.tld",
        "PAGES_DIRECTORY" => "/page",
        "ENABLE_OLD_LANGUAGE_DEFINITIONS" => true,
    ];
    foreach ($vars as $k => $v) {
        if (!defined($k)) {
            $temp_val = $database->get_one(
                "SELECT `value` from `" .
                    $table_prefix .
                    "settings` where `name`='" .
                    strtolower($k) .
                    "'"
            );
            if ($temp_val) {
                $v = $temp_val;
            }
            define($k, $v);
        }
    }

    if (!isset($MESSAGE)) {
        include CAT_PATH . "/languages/" . LANGUAGE . ".php";
    }

    write2log("< [check_tables()]");

    return [count($errors) ? false : true, $errors];
} // end function check_tables()

function pre_installation_error($msg)
{
    global $installer_uri, $lang;
    echo '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
  <head>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
    <title>BlackCat CMS Installation Prerequistes Error</title>
     <link rel="stylesheet" href="' .
        $installer_uri .
        '/templates/default/index.css" type="text/css" />
   </head>
  <body>
  <div style="width:800px;min-width:500px;margin-left:auto;margin-right:auto;margin-top:100px;text-align:center;">
    <div style="float:left">
      <img src="templates/default/images/fail.png" alt="Fail" title="Fail" />
    </div>
    <div style="float:left">
        <h1>BlackCat CMS Installation Prerequistes Error</h1>
        <h2>Sorry, the BlackCat CMS Installation prerequisites check failed</h2>
        <span style="display:inline-block;background-color:#343434;color:#ff3030;font-size:1.5em;border:1px solid #ff3030;padding:15px;width:100%;margin:15px auto;-webkit-border-radius: 8px;-moz-border-radius: 8px;-khtml-border-radius: 8px;border-radius: 8px;">' .
        $msg .
        '</span><br /><br />
        <h2>You will need to fix the errors quoted above to start the installation</h2>
        <h2>Entschuldigung, die Prüfung der BlackCat CMS Installationsvoraussetzungen ist fehlgeschlagen</h2>
        <span style="display:inline-block;background-color:#343434;color:#ff3030;font-size:1.5em;border:1px solid #ff3030;padding:15px;width:100%;margin:15px auto;-webkit-border-radius: 8px;-moz-border-radius: 8px;-khtml-border-radius: 8px;border-radius: 8px;">' .
        $msg .
        '</span><br /><br />
        <h2>Sie müssen die o.g. Probleme beheben, um BlackCat CMS zu installieren</h2>
    </div>
  </div>
  <div id="header">
    <div>Installation Wizard</div>
  </div>
  <div id="footer">
    <div style="float:left;margin:0;padding:0;padding-left:50px;"><h3>enjoy the difference!</h3></div>
    <div>
      <!-- Please note: the below reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
      <a href="https://blackcat-cms.org" title="BlackCat CMS" target="_blank">BlackCat CMS Core</a> is released under the
      <a href="http://www.gnu.org/licenses/gpl.html" title="BlackCat CMS Core is GPL" target="_blank">GNU General Public License</a>.<br />
      <!-- Please note: the above reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
      <a href="https://blackcat-cms.org" title="BlackCat CMS Bundle" target="_blank">BlackCat CMS Bundle</a> is released under several different licenses.
    </div>
  </div>
  </body>
</html>
';
} // end function pre_installation_error()

/**
 * init constants needed for module installations etc.
 **/
function init_constants($cat_path)
{
    global $config;

    // avoid to load config.php here
    if (!CAT_Registry::exists("CAT_PATH")) {
        CAT_Registry::define("CAT_PATH", $cat_path);
    }
    if (!CAT_Registry::exists("CAT_ADMINS_FOLDER")) {
        CAT_Registry::define("CAT_ADMINS_FOLDER", "/admins");
    }
    if (!CAT_Registry::exists("CAT_BACKEND_FOLDER")) {
        CAT_Registry::define("CAT_BACKEND_FOLDER", "/backend");
    }
    if (!CAT_Registry::exists("CAT_BACKEND_PATH")) {
        CAT_Registry::define("CAT_BACKEND_PATH", CAT_BACKEND_FOLDER);
    }
    if (!CAT_Registry::exists("CAT_ADMIN_PATH")) {
        CAT_Registry::define("CAT_ADMIN_PATH", CAT_PATH . CAT_BACKEND_PATH);
    }

    if (!empty($config)) {
        foreach ($config as $key => $value) {
            if (!CAT_Registry::exists(strtoupper($key))) {
                if (!is_scalar($value)) {
                    continue;
                }
                CAT_Registry::define(
                    str_replace("DATABASE_", "_CAT_DB_", strtoupper($key)),
                    $value
                );
            }
        }
        if (!CAT_Registry::exists("CAT_TABLE_PREFIX")) {
            CAT_Registry::define("CAT_TABLE_PREFIX", TABLE_PREFIX);
        }

        // WB compatibility
        if (!CAT_Registry::exists("WB_URL")) {
            CAT_Registry::define("WB_URL", $config["cat_url"]);
        }
        if (!CAT_Registry::exists("WB_PATH")) {
            CAT_Registry::define("WB_PATH", $cat_path);
        }
        // LEPTON compatibility
        if (!CAT_Registry::exists("LEPTON_URL")) {
            CAT_Registry::define("LEPTON_URL", $config["cat_url"]);
        }
        if (!CAT_Registry::exists("LEPTON_PATH")) {
            CAT_Registry::define("LEPTON_PATH", $cat_path);
        }
    } else {
        if (!CAT_Registry::exists("CAT_URL")) {
            CAT_Registry::define("CAT_URL", '');
        }
        if (!CAT_Registry::exists("CAT_ADMIN_URL")) {
            CAT_Registry::define("CAT_ADMIN_URL", '');
        }
    }

    // user id
    $_SESSION["USER_ID"] = 1;
    $_SESSION["GROUP_ID"] = 1;
} // end function init_constants()

/**
 * scan for WYSIWYG-Editors
 **/
function findWYSIWYG()
{
    global $dirh, $lang;

    $info_files = $dirh->findFiles("info.php", CAT_PATH . "/modules", CAT_PATH);
    $editors = [];
    foreach ($info_files as $file) {
        $module_function = "";
        require $dirh->sanitizePath(CAT_PATH . "/modules/" . $file);
        if ($module_function == "WYSIWYG") {
            $editors[
                str_replace("/", "", pathinfo($file, PATHINFO_DIRNAME))
            ] = $module_name;
        }
    }
    // optional
    $zip_files = $dirh->scanDirectory(
        dirname(__FILE__) . "/optional",
        true,
        true,
        true,
        ["zip"]
    );
    if (count($zip_files)) {
        foreach ($zip_files as $file) {
            // not very elegant, but good enough for now...
            if (preg_match("/ckeditor/i", $file)) {
                $editors[
                    "opt_" .
                        str_replace("/", "", pathinfo($file, PATHINFO_FILENAME))
                ] =
                    pathinfo($file, PATHINFO_FILENAME) .
                    " (" .
                    $lang->translate("optional Add-On!") .
                    ")";
            }
        }
    }
    return $editors;
} // end function findWYSIWYG()

function create_default_page($database)
{
    write2log("> [create_default_page()]");

    $errors = __cat_installer_import_sql(
        dirname(__FILE__) . "/db/default_page.sql",
        $database
    );

    $pg_content =
        "<" .
        "?" .
        "php
/**
 *    This file is autogenerated by the BlackCat CMS Installer
 *    Do not modify this file!
 */
" .
        '$page_id = %%id%%;' .
        "
    require('../index.php');
?>
";

    $fh = fopen(CAT_PATH . "/page/welcome.php", "w");
    fwrite($fh, str_replace("%%id%%", 1, $pg_content));
    fclose($fh);

    $fh = fopen(CAT_PATH . "/page/willkommen.php", "w");
    fwrite($fh, str_replace("%%id%%", 2, $pg_content));
    fclose($fh);

    $fh = fopen(CAT_PATH . "/page/maintenance.php", "w");
    fwrite($fh, str_replace("%%id%%", 3, $pg_content));
    fclose($fh);

    $fh = fopen(CAT_PATH . "/page/404.php", "w");
    fwrite($fh, str_replace("%%id%%", 4, $pg_content));
    fclose($fh);

    $database->query(
        sprintf(
            'UPDATE `%spages` SET `modified_when`="%s"',
            CAT_TABLE_PREFIX,
            time()
        )
    );

    write2log("< [create_default_page()]");
} // end function create_default_page()

/**
 *
 **/
function do_step($this_step, $skip = false)
{
    global $steps, $nextstep, $prevstep, $currentstep;

    // reset the 'current' marker for all steps
    foreach ($steps as $i => $step) {
        $steps[$i]["current"] = false;
    }

    $result = $output = null;

    foreach ($steps as $i => $step) {
        // set the 'done' marker for all steps < current
        $steps[$i]["done"] = true;
        // for current step...
        if ($step["id"] == $this_step) {
            // reset errors for this step
            $steps[$i]["errors"] = null;
            // do we have a presentation method for this step?
            $callback = "show_step_" . $step["id"];
            if (function_exists($callback)) {
                list($result, $output) = $callback($step);
                $steps[$i]["success"] = $result;
            }
            // set 'current' marker for this step
            $steps[$i]["current"] = true;
            // reset 'done' marker for this step
            $steps[$i]["done"] = false;
            // find next and previous steps
            if ($i < count($steps) - 1) {
                $nextstep = $steps[$i + 1];
            }
            if ($i > 0) {
                $prevstep = $steps[$i - 1];
            }
            $currentstep = $steps[$i];
            if (!$skip) {
                // leave the rest as-is
                break;
            }
        }
    }

    // save the current state to temp. file
    if (false !== ($fh = fopen(dirname(__FILE__) . "/steps.tmp", "w"))) {
        fwrite($fh, serialize($steps));
        fclose($fh);
    }

    return [$result, $output];
} // end function do_step()

/**
 * parse SQL file and execute the statements
 * $file     is the name of the file
 * $database is the db handle
 **/
function __cat_installer_import_sql($file, $database)
{
    write2log($file);
    $errors = [];
    $import = file_get_contents($file);
    $import = preg_replace("%/\*(.*)\*/%Us", "", $import);
    $import = preg_replace("%^--(.*)\n%mU", "", $import);
    $import = preg_replace("%^$\n%mU", "", $import);
    $import = preg_replace("%cat_%", CAT_TABLE_PREFIX, $import);
    foreach (split_sql_file($import, ";") as $imp) {
        if ($imp != "" && $imp != " ") {
            write2log($imp);
            $ret = $database->query($imp);
            if ($database->isError()) {
                $errors[] = $database->getError();
            }
        }
    }
    write2log(var_export($errors, 1));
    return $errors;
} // end function __cat_installer_import_sql()

/**
 * INSTALLATION GOES HERE!!!
 **/
function __do_install()
{
    global $config, $parser, $dirh;

    write2log("> [__do_install()]");

    include dirname(__FILE__) . "/../framework/functions.php";
    $cat_path = sanitize_path(dirname(__FILE__) . "/..");
    $inst_path = sanitize_path(
        $cat_path . "/" . pathinfo(dirname(__FILE__), PATHINFO_BASENAME)
    );

    if (
        isset($config["install_tables"]) &&
        $config["install_tables"] == "true"
    ) {
        $install_tables = true;
    } else {
        $install_tables = false;
    }

    // get server IP
    if (array_key_exists("SERVER_ADDR", $_SERVER)) {
        $server_addr = $_SERVER["SERVER_ADDR"];
    } else {
        $server_addr = "127.0.0.1";
    }

    // get server path
    $server_path = pathinfo(
        CAT_Helper_Directory::sanitizePath($_SERVER["SCRIPT_FILENAME"] . "/.."),
        PATHINFO_DIRNAME
    );
    $local_path = CAT_Helper_Directory::sanitizePath(dirname(__FILE__) . "/..");
    if ($server_path != $local_path) {
        $server_config_path = "'" . $server_path . "'";
    } else {
        $server_config_path = "dirname(__FILE__)";
    }

    // Patch robots.txt _before_ changing the $config_cat_url
    if (($handle = @fopen($cat_path . "/robots.txt", "r+")) !== false) {
        $robots = fread($handle, filesize($cat_path . "/robots.txt"));
        rewind($handle);
        $robots = str_replace("{CAT_URL}", $config["cat_url"], $robots);
        fwrite($handle, $robots);
        fclose($handle);
    }

    // remove trailing /
    $config_cat_url = rtrim($config["cat_url"], "/");

    // remove scheme
    $config_cat_url = preg_replace("~^https?:~i", "", $config_cat_url);

    $config_content =
        "" .
        "<?php\n" .
        "\n" .
        "if(defined('CAT_PATH')) {\n" .
        "    die('By security reasons it is not permitted to load \'config.php\' twice!! " .
        "Forbidden call from \''.\$_SERVER['SCRIPT_NAME'].'\'!');\n}\n\n" .
        "// *****************************************************************************\n" .
        "// please set the path names for the backend subfolders here; that is,\n" .
        "// if you rename 'backend' to 'myadmin', for example, set 'CAT_BACKEND_FOLDER'\n" .
        "// to 'myadmin'.\n" .
        "// *****************************************************************************\n" .
        "// path to backend subfolder; default name is 'backend'\n" .
        "define('CAT_BACKEND_FOLDER', 'backend');\n" .
        "// *****************************************************************************\n" .
        "define('CAT_BACKEND_PATH', CAT_BACKEND_FOLDER );\n" .
        "\n" .
        "define('CAT_TABLE_PREFIX', '" .
        $config["table_prefix"] .
        "');\n" .
        "\n" .
        "define('CAT_SERVER_ADDR', '" .
        $server_addr .
        "');\n" .
        "define('CAT_PATH', " .
        $server_config_path .
        ");\n" .
        "define('CAT_URL', '" .
        $config_cat_url .
        "');\n" .
        "define('CAT_ADMIN_PATH', CAT_PATH.'/'.CAT_BACKEND_PATH);\n" .
        "define('CAT_ADMIN_URL', CAT_URL.'/'.CAT_BACKEND_PATH);\n" .
        "\n" .
        "// if you have problems with SSL, set this to 'false' or delete the following line\n" .
        "define('CAT_BACKEND_REQ_SSL', " .
        (isset($config["ssl"]) && $config["ssl"] ? "true" : "false") .
        ");\n\n" .
        (isset($config["no_validate_admin_password"]) &&
        $config["no_validate_admin_password"] == "true"
            ? "define('ALLOW_SHORT_PASSWORDS',true);\n\n"
            : "") .
        "if (!defined('CAT_INSTALL')) require_once(CAT_PATH.'/framework/initialize.php');\n" .
        "\n" .
        "// WB2/Lepton backward compatibility\n" .
        "include_once CAT_PATH.'/framework/wb2compat.php';\n" .
        "\n";

    $config_filename = $cat_path . "/config.php";
    write2log("trying to create " . $config_filename);

    // Check if the file exists and is writable first.
    if (($handle = @fopen($config_filename, "w")) === false) {
        write2log("< [__do_install()] (cannot create config file)");
        return [
            false,
            $lang->translate(
                "Cannot open the configuration file ({{ file }})",
                ["file" => $config_filename]
            ),
        ];
    } else {
        if (
            fwrite($handle, $config_content, strlen($config_content)) === false
        ) {
            write2log("< [__do_install()] (cannot write to config file)");
            fclose($handle);
            return [
                false,
                $lang->translate(
                    "Cannot write to the configuration file ({{ file }})",
                    ["file" => $config_filename]
                ),
            ];
        }
        // Close file
        fclose($handle);
    }

    init_constants($cat_path);

    include $cat_path . "/framework/class.database.php";
    $database = new database();

    // ---- install tables -----
    if ($install_tables) {
        list($result, $errors) = install_tables($database);
        // only try to fill tables if the creation succeeded
        if ($result && !count($errors)) {
            // ----- fill tables -----
            list($result, $fillerrors) = fill_tables($database);
            if (!$result || count($fillerrors)) {
                $errors["populate tables"] = $fillerrors;
            }
            // only try to install modules if fill tables succeeded
            else {
                // ----- install addons -----
                list($result, $insterrors) = install_modules(
                    $cat_path,
                    $database
                );
                if (!$result || count($insterrors)) {
                    $errors["install modules"] = $insterrors;
                }
                // only check if all above succeeded
                else {
                    // ----- check tables ----
                    list($result, $checkerrors) = check_tables($database);
                    if (!$result || count($checkerrors)) {
                        $errors["check tables"] = $checkerrors;
                    } else {
                        create_default_page($database);
                    }
                }
            }
            $config["install_tables_done"] = true;
        }
    }

    // ---- set index.php to read only ----
    $dirh->setReadOnly($cat_path . "/index.php");

    // ---- make sure we have an index.php everywhere ----
    $dirh->recursiveCreateIndex($cat_path);

    write2log("< [__do_install()]");

    if (count($errors)) {
        $parser->setPath(dirname(__FILE__) . "/templates/default");
        $output = $parser->get("install_errors.tpl", ["errors" => $errors]);
        return [count($errors) ? false : true, $output];
    } else {
        return [true, ""];
    }
} // end function __do_install()

function __cat_check_db_config()
{
    global $lang, $users, $config;

    $errors = [];
    $regexp = '/^[^\x-\x1F]+$/D';

    // Check if user has entered a database host
    if (!isset($config["database_host"]) || $config["database_host"] == "") {
        $errors["installer_database_host"] = $lang->translate(
            "Please enter a database host name"
        );
    } else {
        if (
            preg_match(
                "~(?:(?:(?:(?:[a-zA-Z0-9][-a-zA-Z0-9]{0,61})?[a-zA-Z0-9])[.])*(?:[a-zA-Z][-a-zA-Z0-9]{0,61}[a-zA-Z0-9]|[a-zA-Z])[.]?)~",
                $config["database_host"],
                $match
            )
        ) {
            $database_host = $match[0];
        } else {
            $errors["installer_database_host"] = $lang->translate(
                "Invalid database hostname!"
            );
        }
    }

    // check for valid port number
    if (!isset($config["database_port"]) || $config["database_port"] == "") {
        $errors["installer_database_port"] = $lang->translate(
            "Please enter a database port"
        );
    } else {
        if (is_numeric($config["database_port"])) {
            $database_port = $config["database_port"];
        } else {
            $errors["installer_database_port"] = $lang->translate(
                "Invalid port number!"
            );
        }
    }

    // Check if user has entered a database username
    if (
        !isset($config["database_username"]) ||
        $config["database_username"] == ""
    ) {
        $errors["installer_database_username"] = $lang->translate(
            "Please enter a database username"
        );
    } else {
        if (preg_match($regexp, $config["database_username"], $match)) {
            $database_username = $match[0];
        } else {
            $errors["installer_database_username"] = $lang->translate(
                "Invalid database username!"
            );
        }
    }

    // Check if user has entered a database password
    if (
        !isset($config["database_password"]) ||
        $config["database_password"] == ""
    ) {
        $database_password = "";
        if (!isset($config["no_validate_db_password"])) {
            $errors["installer_database_password_empty"] = true;
        }
    } else {
        if (!isset($config["no_validate_db_password"])) {
            if (
                !$users->validatePassword(
                    $config["database_password"],
                    false,
                    true
                )
            ) {
                $errors["installer_database_password"] =
                    $lang->translate("Invalid database password!") .
                    " " .
                    $users->getPasswordError();
            } else {
                $database_password = $users->getLastValidatedPassword();
            }
        } else {
            $database_password = $config["database_password"];
        }
    }

    // Check if user has entered a database name
    if (!isset($config["database_name"]) || $config["database_name"] == "") {
        $errors["installer_database_name"] = $lang->translate(
            "Please enter a database name"
        );
    } else {
        // make sure only allowed characters are specified; it is not allowed to
        // have a DB name with digits only!
        if (
            preg_match('/^[a-z0-9][a-z0-9_-]+$/i', $config["database_name"]) &&
            !is_numeric($config["database_name"])
        ) {
            $database_name = $config["database_name"];
        } else {
            // contains invalid characters (only a-z, A-Z, 0-9 and _ allowed to avoid problems with table/field names)
            $errors["installer_database_name"] = $lang->translate(
                "Only characters a-z, A-Z, 0-9, - and _ allowed in database name. Please note that a database name must not be composed of digits only."
            );
        }
    }

    // table prefix
    if (
        isset($config["table_prefix"]) &&
        $config["table_prefix"] != "" &&
        !preg_match('/^[a-z0-9_]+$/i', $config["table_prefix"])
    ) {
        $errors["installer_table_prefix"] = $lang->translate(
            "Only characters a-z, A-Z, 0-9 and _ allowed in table_prefix."
        );
    }

    if (!count($errors)) {
        // check database connection
        $connectionParams = [
            "DB_NAME" => $database_name,
            "DB_USERNAME" => $database_username,
            "DB_PASSWORD" => $database_password,
            "DB_HOST" => $database_host,
            "DB_PORT" => $database_port,
        ];
        $conn = CAT_Helper_DB::getInstance($connectionParams);
        if (!$conn->check()) {
            $errors["global"] = $lang->translate(
                "Unable to connect to the database! Please check your settings!"
            );
        }
    }

    return $errors;
} // end function __cat_check_db_config()

/**
 * Credits: http://stackoverflow.com/questions/147821/loading-sql-files-from-within-php
 **/
function split_sql_file($sql, $delimiter)
{
    // Split up our string into "possible" SQL statements.
    $tokens = explode($delimiter, $sql);

    // try to save mem.
    $sql = "";
    $output = [];

    // we don't actually care about the matches preg gives us.
    $matches = [];

    // this is faster than calling count($oktens) every time thru the loop.
    $token_count = count($tokens);
    for ($i = 0; $i < $token_count; $i++) {
        // Don't wanna add an empty string as the last thing in the array.
        if ($i != $token_count - 1 || strlen($tokens[$i] > 0)) {
            // This is the total number of single quotes in the token.
            $total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
            // Counts single quotes that are preceded by an odd number of backslashes,
            // which means they're escaped quotes.
            $escaped_quotes = preg_match_all(
                "/(?<!\\\\)(\\\\\\\\)*\\\\'/",
                $tokens[$i],
                $matches
            );

            $unescaped_quotes = $total_quotes - $escaped_quotes;

            // If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
            if ($unescaped_quotes % 2 == 0) {
                // It's a complete sql statement.
                $output[] = $tokens[$i];
                // save memory.
                $tokens[$i] = "";
            } else {
                // incomplete sql statement. keep adding tokens until we have a complete one.
                // $temp will hold what we have so far.
                $temp = $tokens[$i] . $delimiter;
                // save memory..
                $tokens[$i] = "";

                // Do we have a complete statement yet?
                $complete_stmt = false;

                for ($j = $i + 1; !$complete_stmt && $j < $token_count; $j++) {
                    // This is the total number of single quotes in the token.
                    $total_quotes = preg_match_all(
                        "/'/",
                        $tokens[$j],
                        $matches
                    );
                    // Counts single quotes that are preceded by an odd number of backslashes,
                    // which means they're escaped quotes.
                    $escaped_quotes = preg_match_all(
                        "/(?<!\\\\)(\\\\\\\\)*\\\\'/",
                        $tokens[$j],
                        $matches
                    );

                    $unescaped_quotes = $total_quotes - $escaped_quotes;

                    if ($unescaped_quotes % 2 == 1) {
                        // odd number of unescaped quotes. In combination with the previous incomplete
                        // statement(s), we now have a complete statement. (2 odds always make an even)
                        $output[] = $temp . $tokens[$j];

                        // save memory.
                        $tokens[$j] = "";
                        $temp = "";

                        // exit the loop.
                        $complete_stmt = true;
                        // make sure the outer loop continues at the right point.
                        $i = $j;
                    } else {
                        // even number of unescaped quotes. We still don't have a complete statement.
                        // (1 odd and 1 even always make an odd)
                        $temp .= $tokens[$j] . $delimiter;
                        // save memory.
                        $tokens[$j] = "";
                    }
                } // for..
            } // else
        }
    }

    // remove empty
    for ($i = count($output) + 1; $i >= 0; $i--) {
        if (isset($output[$i]) && trim($output[$i]) == "") {
            array_splice($output, $i, 1);
        }
    }

    return $output;
}

function sslCheck()
{
    if (
        isset($_SERVER["OPENSSL_CONF"]) &&
        preg_match("~SSL~", $_SERVER["SERVER_SOFTWARE"])
    ) {
        write2log("Seems SSL is available, try to open a socket");
        try {
            $SSL_Check = @fsockopen(
                "ssl://" . $_SERVER["HTTP_HOST"],
                443,
                $errno,
                $errstr,
                30
            );
            if (!$SSL_Check) {
                write2log(
                    sprintf(
                        "fsockopen failed, SSL not available for [%s]",
                        $_SERVER["HTTP_HOST"]
                    )
                );
                return false;
            } else {
                write2log(
                    sprintf(
                        "fsockopen succeeded, SSL seems to be available for [%s]",
                        $_SERVER["HTTP_HOST"]
                    )
                );
                fclose($SSL_Check);
                return true;
            }
        } catch (Exception $e) {
            write2log(sprintf("exception caught: %s", $e->getMessage()));
            return false;
        }
    } else {
        write2log('No SSL in $_SERVER array');
        return false;
    }
}

function write2log($msg)
{
    global $depth;
    if (substr($msg, 0, 1) == "<") {
        $depth--;
    }
    $logh = fopen(CAT_LOGFILE, "a");
    fwrite($logh, str_repeat("  ", $depth) . $msg . "\n");
    fclose($logh);
    if (substr($msg, 0, 1) == ">") {
        $depth++;
    }
}
