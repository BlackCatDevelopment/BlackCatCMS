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
 *   @copyright       2014, 2016, Black Cat Development
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *   @review          05.03.2015 14:59:28
 *
 */

if (defined("CAT_PATH")) {
    include CAT_PATH . "/framework/class.secure.php";
} else {
    $root = "../";
    $level = 1;
    while ($level < 10 && !file_exists($root . "/framework/class.secure.php")) {
        $root .= "../";
        $level += 1;
    }
    if (file_exists($root . "/framework/class.secure.php")) {
        include $root . "/framework/class.secure.php";
    } else {
        trigger_error(
            sprintf(
                "[ <b>%s</b> ] Can't include class.secure.php!",
                $_SERVER["SCRIPT_NAME"]
            ),
            E_USER_ERROR
        );
    }
}

require_once dirname(__FILE__) . "/../../config.php";
require_once dirname(__FILE__) . "/../../framework/functions.php";

$backend = CAT_Backend::getInstance("Addons", "addons", false);
$users = CAT_Users::getInstance();

/**
 * -------------------------------------------------
 * Ensure template parser is initialized (AJAX-safe)
 * -------------------------------------------------
 */
global $parser;
if (!isset($parser) || !is_object($parser) || !method_exists($parser, "get")) {
    $parser = CAT_Helper_Template::getInstance("Dwoo");
    $parser->setPath(CAT_THEME_PATH . "/templates/default", "backend");
}

header("Content-type: application/json");

if (!$users->checkPermission("Addons", "addons")) {
    $ajax = [
        "message" => $backend
            ->lang()
            ->translate(
                "Sorry, but you don't have the permissions for this action"
            ),
        "success" => false,
    ];
    print json_encode($ajax);
    exit();
}

$module = CAT_Helper_Validate::get("_REQUEST", "module");
$type = CAT_Helper_Validate::get("_REQUEST", "type");

if (CAT_Helper_Addons::isModuleInstalled($module, null, $type)) {
    $info = CAT_Helper_Addons::checkInfo(
        CAT_Helper_Directory::sanitizePath(
            CAT_PATH . "/" . $type . "s/" . $module
        )
    );
} else {
    $path = CAT_Helper_Directory::sanitizePath(
        CAT_PATH .
            "/" .
            $type .
            "/" .
            $module .
            ($type == "languages" ? ".php" : "")
    );
    $info = CAT_Helper_Addons::checkInfo($path);
}

if (!is_array($info) || !count($info)) {
    $ajax = [
        "message" => $backend
            ->lang()
            ->translate(
                "No Addon info available, seems to be an invalid addon!"
            ),
        "success" => false,
    ];
    print json_encode($ajax);
    exit();
}

$addon = [
    "type" => $info["addon_function"],
    "installed" => null,
    "upgraded" => null,
    "removable" =>
        CAT_Helper_Addons::isRemovable($module, $info["addon_function"]) ===
        true
            ? "Y"
            : "N",
];

foreach ($info as $key => $value) {
    $key = preg_replace("/^(module_|addon_)/i", "", $key);
    $addon[$key] = $value;
}

if (!$users->get_permission($addon["directory"], $addon["type"])) {
    $ajax = [
        "message" => $backend
            ->lang()
            ->translate(
                "Sorry, but you don't have the permissions for this action"
            ),
        "success" => false,
    ];
    print json_encode($ajax);
    exit();
}

$tpl_data = ["permissions" => []];

$tpl_data["permissions"]["ADVANCED"] = $users->checkPermission(
    "addons",
    "admintools"
)
    ? true
    : false;
$tpl_data["permissions"]["MODULES_VIEW"] = $users->checkPermission(
    "addons",
    "modules_view"
)
    ? true
    : false;
$tpl_data["permissions"]["MODULES_INSTALL"] = $users->checkPermission(
    "addons",
    "modules_install"
)
    ? true
    : false;
$tpl_data["permissions"]["MODULES_UNINSTALL"] = $users->checkPermission(
    "addons",
    "modules_uninstall"
)
    ? true
    : false;

/**
 * FTAN defaults (PHP 8 safe)
 */
$tpl_data["FTAN"] = [
    "token_name" => "",
    "token" => "",
];

$ftan = null;
if (method_exists($backend, "getFTAN")) {
    $ftan = $backend->getFTAN();
} elseif (function_exists("getFTAN")) {
    $ftan = getFTAN();
}

if (is_array($ftan)) {
    if (isset($ftan["name"]) || isset($ftan["value"])) {
        $tpl_data["FTAN"]["token_name"] = $ftan["name"] ?? "";
        $tpl_data["FTAN"]["token"] = $ftan["value"] ?? "";
    } elseif (isset($ftan["token_name"]) || isset($ftan["token"])) {
        $tpl_data["FTAN"]["token_name"] = $ftan["token_name"] ?? "";
        $tpl_data["FTAN"]["token"] = $ftan["token"] ?? "";
    }
}

$tpl_data["usage"] = CAT_Helper_Addons::getModuleUsage($addon["directory"]);

$result = true;
$message = null;
$output = $parser->get(
    "backend_addons_index_details",
    array_merge($tpl_data, ["addon" => $addon])
);

if (!$output || $output === "") {
    $result = false;
    $message = "Unable to load settings sub page";
}

$ajax = [
    "message" => $message,
    "success" => $result,
    "content" => $output,
];

print json_encode($ajax);
exit();
