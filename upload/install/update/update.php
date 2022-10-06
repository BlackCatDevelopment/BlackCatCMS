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

define("CAT_INSTALL", true);
#define('CAT_INSTALL_PROCESS',true);
define("CAT_LOGFILE", dirname(__FILE__) . "/../../temp/update.log");

//**************************************************************************
// add framework subdir to include path
//**************************************************************************
set_include_path(
    implode(PATH_SEPARATOR, [
        realpath(dirname(__FILE__) . "/framework"),
        get_include_path(),
    ])
);
//**************************************************************************
// register autoloader
//**************************************************************************
spl_autoload_register(function ($class) {
    $file = str_replace("_", "/", $class);
    if (file_exists(dirname(__FILE__) . "/../../framework/" . $file . ".php")) {
        @require dirname(__FILE__) . "/../../framework/" . $file . ".php";
    }
    // next in stack
});

$lang = CAT_Helper_I18n::getInstance();
$lang->addFile($lang->getLang() . ".php", dirname(__FILE__) . "/../languages");

// allow upgrade from v1.2, too
if (
    !isset($_GET["do"]) &&
    CAT_Helper_Addons::versionCompare(CAT_VERSION, "1.2", "<")
) {
    update11to12pre();
}

// keep wb2compat.php happy
foreach (
    array_values([
        "DEFAULT_THEME",
        "CATMAILER_DEFAULT_SENDERNAME",
        "DEFAULT_TIMEZONE_STRING",
        "SERVER_EMAIL",
    ])
    as $const
) {
    define($const, "");
}
define("LANGUAGE", "EN");

@require_once dirname(__FILE__) . "/../../config.php";

if (!version_compare(PHP_VERSION, "7.3", ">=")) {
    pre_update_error(
        $lang->translate(
            "You need to have PHP version >= v7.3 to run BlackCat CMS v1.4 and above. You have strong>{{version}}</strong> installed.",
            ["version" => PHP_VERSION]
        )
    );
}

$result = $database->query(
    sprintf(
        "SELECT `value` FROM `%ssettings` WHERE `name`='%s'",
        CAT_TABLE_PREFIX,
        "cat_version"
    )
);
if ($result->rowCount() > 0) {
    $row = $result->fetch();
    define("CAT_VERSION", $row["value"]);
}

// Try to guess installer URL
$installer_uri =
    (isset($_SERVER["HTTPS"]) ? "https://" : "http://") .
    $_SERVER["SERVER_NAME"] .
    ($_SERVER["SERVER_PORT"] != 80 && !isset($_SERVER["HTTPS"])
        ? ":" . $_SERVER["SERVER_PORT"]
        : "") .
    $_SERVER["SCRIPT_NAME"];
$installer_uri = dirname($installer_uri);
$installer_uri = str_ireplace("update", "", $installer_uri);

if (!CAT_Helper_Addons::versionCompare(CAT_VERSION, "1.1")) {
    pre_update_error(
        $lang->translate(
            "You need to have <strong>BlackCat CMS v1.1</strong> installed to use the Update.<br />You have <strong>{{version}}</strong> installed.",
            ["version" => CAT_VERSION]
        )
    );
}

// get new version from tag.txt
if (file_exists(dirname(__FILE__) . "/../tag.txt")) {
    $tag = fopen(dirname(__FILE__) . "/../tag.txt", "r");
    list($current_version, $current_build, $current_build) = explode(
        "#",
        fgets($tag)
    );
    fclose($tag);
} else {
    pre_update_error(
        $lang->translate(
            "The file <pre>tag.txt</pre> is missing! Unable to upgrade!"
        )
    );
    exit();
}

if (!CAT_Helper_Validate::getInstance()->sanitizeGet("do")) {
    update_wizard_header();
    echo '
        <h1>BlackCat CMS Update Wizard</h1>
        <h2>' .
        $lang->translate("Welcome!") .
        '</h2>
		' .
        $lang->translate(
            "This wizard will help you to upgrade your current BlackCat CMS Version"
        ) .
        '<br />
		<span style="font-weight:bold;color:#f00;">' .
        CAT_VERSION .
        '</span><br />
		' .
        $lang->translate("to Version") .
        '<br />
		<span style="font-weight:bold;color:#f00;">' .
        $current_version .
        " Build " .
        $current_build .
        '</span>
        <form method="get" action="' .
        $installer_uri .
        '/update/update.php">
          <input type="hidden" name="do" value="1" />
          <input type="submit" value="' .
        $lang->translate("To start the update, please click here") .
        '" />
        </form>
    ';
    update_wizard_footer();
}

/*******************************************************************************
 * DO THE UPDATE
 ******************************************************************************/
ob_start();

/*******************************************************************************
 *  1.1 TO 1.2
 ******************************************************************************/
$database->query(
    "CREATE TABLE IF NOT EXISTS `:prefix:dashboard` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL DEFAULT '0',
      `module` varchar(50) DEFAULT '0',
      `layout` varchar(10) NOT NULL,
      `widgets` text NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `id_user_id_module` (`user_id`,`module`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);

/*******************************************************************************
    1.2 TO 1.2.1
*******************************************************************************/
$sql =
    "UPDATE `:prefix:system_permissions` SET `perm_bit`=:val WHERE `perm_name`=:perm";
$database->query($sql, ["val" => 1, "perm" => "settings"]);
$database->query($sql, ["val" => 2, "perm" => "settings_basic"]);
$database->query($sql, ["val" => 4, "perm" => "settings_advanced"]);

// update module versions
$sql =
    "UPDATE `:prefix:addons` SET `upgraded`=:time, `version`=:ver WHERE `directory`=:dir";
foreach (array_values(["lib_getid3", "lib_wblib", "wysiwyg"]) as $module) {
    $addon_dir = CAT_PATH . "/modules/" . $module;
    $addon_info = CAT_Helper_Addons::checkInfo($addon_dir);
    $database->query($sql, [
        "time" => time(),
        "ver" => $addon_info["module_version"],
        "dir" => $addon_info["module_directory"],
    ]);
}

/*******************************************************************************
    add missing database entries for addons catalog
*******************************************************************************/
$database->query(
    "INSERT IGNORE INTO `:prefix:class_secure` (`module`, `filepath`) VALUES (0, '/backend/addons/ajax_get_template.php');"
);
$database->query(
    "INSERT IGNORE INTO `:prefix:class_secure` (`module`, `filepath`) VALUES (0, '/backend/addons/ajax_update_catalog.php');"
);
$database->query(
    "INSERT IGNORE INTO `:prefix:class_secure` (`module`, `filepath`) VALUES (0, '/backend/addons/ajax_install.php');"
);

/*******************************************************************************
    1.3 TO 1.4
*******************************************************************************/
// remove csrf setting
$database->query(
    'DELETE FROM `:prefix:settings` WHERE `name`="enable_csrfmagic";'
);
// remove token lifetime
$database->query(
    'DELETE FROM `:prefix:settings` WHERE `name`="token_lifetime";'
);
// add new settings
$database->query(
    'INSERT IGNORE INTO `:prefix:settings` (`name`,`value`) VALUES ( "session_lifetime", "7200" );'
);
$database->query(
    'INSERT IGNORE INTO `:prefix:settings` (`name`,`value`) VALUES ( "cookie_samesite", "Strict" );'
);
// create sessions subfolder
$database->query(
    "INSERT IGNORE INTO `:prefix:settings` (`name`, `value`) VALUES ('session_save_path', 'temp/sessions');"
);
$session_save_path = CAT_PATH . "/temp/sessions";
CAT_Helper_Directory::getInstance()->createDirectory($session_save_path);
// protect sessions subfolder
$fh = fopen($session_save_path . "/.htaccess", "w");
fwrite($fh, "Order deny,allow\n");
fwrite($fh, "Deny from all\n");
fwrite($fh, "ErrorDocument 403 " . CAT_URL . "\n");
fwrite($fh, "ErrorDocument 404 " . CAT_URL . "\n");
fclose($fh);
chmod($session_save_path, 0700);

if (
    false !==
    ($query = $database->query(
        "SELECT value FROM `:prefix:search` WHERE name='cfg_search_library' LIMIT 1"
    ))
) {
    $query->rowCount() > 0
        ? ($res = $query->fetch())
        : ($res["value"] = "lib_search");
    CAT_Registry::register("SEARCH_LIBRARY", $res["value"], true);
} else {
    CAT_Registry::register("SEARCH_LIBRARY", "lib_search", true);
}

// update LoginBox Droplet
CAT_Helper_Droplet::installDroplet(
    CAT_PATH . "/modules/droplets/install/droplet_LoginBox.zip"
);

/*******************************************************************************
    1.3 TO 1.4
	Add attribute otp (one-time password) to table users
*******************************************************************************/
$getDB = CAT_Helper_DB::getInstance()->getConfig();
$checkForField = $database
    ->query(
        "SELECT * FROM INFORMATION_SCHEMA.COLUMNS " .
            "WHERE TABLE_NAME = ':prefix:users' " .
            "AND COLUMN_NAME = 'otp' " .
            "AND TABLE_SCHEMA = '" .
            $getDB["DB_NAME"] .
            "'"
    )
    ->fetchColumn();
if (!$checkForField) {
    CAT_Helper_DB::getInstance()->query(
        "ALTER TABLE `:prefix:users` ADD `otp` tinyint(1) NOT NULL DEFAULT 0"
    );
}
unset($getDB);

/*******************************************************************************
1.3 TO 1.4
Increase login_ip for IPv6 from 15 to 39 chars
*******************************************************************************/
CAT_Helper_DB::getInstance()->query(
    "ALTER TABLE `:prefix:users` MODIFY COLUMN `login_ip` varchar(39) NOT NULL DEFAULT ''"
);

/*******************************************************************************
1.x TO 2.0
*******************************************************************************/
$database->query(
    "INSERT INTO `:prefix:class_secure` ('module','filepath') VALUES (0,'/backend/users/add.php')"
);

/*******************************************************************************
    ALL VERSIONS
*******************************************************************************/

// delete templates cache (the folder will be re-created by the DwooDriver)
$temp_path = CAT_Helper_Directory::sanitizePath(
    dirname(__FILE__) . "/../../temp/"
);
CAT_Helper_Directory::removeDirectory($temp_path . "/compiled");

/*******************************************************************************
    ALL VERSIONS: update version info
*******************************************************************************/
$database->query(
    sprintf(
        'UPDATE `%ssettings` SET `value`="%s" WHERE `name`="%s"',
        CAT_TABLE_PREFIX,
        $current_version,
        "cat_version"
    )
);
$database->query(
    sprintf(
        'UPDATE `%ssettings` SET `value`="%s" WHERE `name`="%s"',
        CAT_TABLE_PREFIX,
        $current_build,
        "cat_build"
    )
);

ob_end_clean();

/*******************************************************************************

*******************************************************************************/
$installer_uri = str_replace("/update", "", $installer_uri);
update_wizard_header();
echo '
        <h2>' .
    $lang->translate("Update done") .
    '</h2>
        <form method="get" action="' .
    CAT_ADMIN_URL .
    '">
          <input type="submit" value="' .
    $lang->translate("Click here to enter the backend") .
    '" />
        </form>
    ';
update_wizard_footer();
exit();

function pre_update_error($msg)
{
    global $installer_uri, $lang;
    update_wizard_header(true);
    echo '
        <div style="float:left">
          <img src="templates/default/images/fail.png" alt="Fail" title="Fail" />
        </div>
  <h1>BlackCat CMS Update Prerequistes Error</h1>
        <h2>' .
        $lang->translate(
            "Sorry, the BlackCat CMS Update prerequisites check failed."
        ) .
        '</h2>
        <span style="display:inline-block;background-color:#343434;color:#ff3030;font-size:1.5em;border:1px solid #ff3030;padding:15px;width:100%;margin:15px auto;-webkit-border-radius: 8px;-moz-border-radius: 8px;-khtml-border-radius: 8px;border-radius: 8px;">' .
        $msg .
        '</span><br /><br />
        <h2>' .
        $lang->translate(
            "You will need to fix the errors quoted above to start the installation."
        ) .
        "</h2>";
    update_wizard_footer();
} // end function pre_update_error()

function update_wizard_header($is_error = false)
{
    global $installer_uri, $lang;
    $header = $is_error
        ? "BlackCat CMS Update Prerequistes Error"
        : "BlackCat CMS Update Wizard";
    echo '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
  <head>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
    <title>' .
        $header .
        '</title>
    <link rel="stylesheet" href="' .
        $installer_uri .
        'templates/default/index.css" type="text/css" />
   </head>
  <body>
  <div style="width:800px;min-width:800px;margin:auto;margin-top:20%;text-align:center;color:#5AA2DA;">
    <div style="float:left;width:100%;">';
}

function update_wizard_footer()
{
    echo '
    </div>
  </div>
  <div id="header">
    <div>Update Wizard</div>
  </div>
  <div id="footer">
    <div style="float:left;margin:0;padding:0;padding-left:50px;"><h3>enjoy the difference!</h3></div>
    <div>
      <!-- Please note: the below reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
      <a href="http://blackcat-cms.org" title="BlackCat CMS" target="_blank">BlackCat CMS Core</a> is released under the
      <a href="http://www.gnu.org/licenses/gpl.html" title="BlackCat CMS Core is GPL" target="_blank">GNU General Public License</a>.<br />
      <!-- Please note: the above reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
      <a href="http://blackcat-cms.org" title="BlackCat CMS Bundle" target="_blank">BlackCat CMS Bundle</a> is released under several different licenses.
    </div>
  </div>
  </body>
</html>
';
    exit();
}

/*******************************************************************************
    1.1 TO 1.2: We must create the new database settings file first!
    Note: We cannot include / require the original config.php as it will
    cause lots of errors
*******************************************************************************/
function update11to12pre()
{
    $db_config_file_path = CAT_Helper_Directory::sanitizePath(
        dirname(__FILE__) . "/../../framework/CAT/Helper/DB"
    );
    if (is_dir($db_config_file_path)) {
        // find file
        // note: .bc.php as suffix filter does not work!
        $configfiles = CAT_Helper_Directory::scanDirectory(
            dirname(__FILE__) . "/../../framework/CAT/Helper/DB",
            true,
            true,
            null,
            ["php"],
            null,
            ["index.php"]
        );
    } else {
        mkdir($db_config_file_path, "0755");
    }
    if (!is_array($configfiles) || !count($configfiles)) {
        include dirname(__FILE__) . "/../admin_dummy.inc.php";
        $admin = new admin_dummy();
        // get the DB config from config.php
        $config = file_get_contents(dirname(__FILE__) . "/../../config.php");
        preg_match_all(
            "~define\(\'CAT_(DB_\w+)[^,].+?\'([^\'].+?)\'~i",
            $config,
            $m
        );
        if (is_array($m) && count($m)) {
            $db = [];
            for ($i = 0; $i < count($m[0]); $i++) {
                $db[$m[1][$i]] = $m[2][$i];
            }
            $db_config_content =
                "
;<?php
;die(); // For further security
;/*

[CAT_DB]
TYPE=mysql
HOST=" .
                $db["DB_HOST"] .
                "
PORT=" .
                $db["DB_PORT"] .
                "
USERNAME=" .
                $db["DB_USERNAME"] .
                "
PASSWORD=\"" .
                $db["DB_PASSWORD"] .
                "\"
NAME=" .
                $db["DB_NAME"] .
                "

;*/
;?>
";

            // save database settings; we generate a file name here
            $db_settings_file =
                $db_config_file_path . "/" . $admin->createGUID("") . ".bc.php";
            write2log("trying to create " . $db_settings_file);
            if (($handle = @fopen($db_settings_file, "w")) === false) {
                write2log(
                    "!!!ERROR!!! Cannot create database settings file [" .
                        $db_settings_file .
                        "]"
                );
                pre_update_error(
                    "!!!ERROR!!! Cannot create database settings file [" .
                        $db_settings_file .
                        "]"
                );
                exit();
            } else {
                if (
                    fwrite(
                        $handle,
                        $db_config_content,
                        strlen($db_config_content)
                    ) === false
                ) {
                    write2log(
                        "!!!ERROR!!! Cannot write to database settings file [" .
                            $db_settings_file .
                            "]"
                    );
                    fclose($handle);
                    pre_update_error(
                        "!!!ERROR!!! Cannot write to database settings file [" .
                            $db_settings_file .
                            "]"
                    );
                    exit();
                }
            }
            write2log(">>> ok");

            // remove DB config from config.php
            write2log("removing db settings from config.php");
            $config = preg_replace("~define\(\'CAT_(DB_\w+).*~i", "", $config);
            $config = preg_replace("~\n\n+~", "\n\n", $config);
            $fh = fopen(dirname(__FILE__) . "/../../config.php", "w");
            fwrite($fh, $config);
            ftruncate($fh, ftell($fh));
            fclose($fh);

            // remove index.php
            if (file_exists($db_config_file_path . "/index.php")) {
                unlink($db_config_file_path . "/index.php");
            }
        }
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
