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

if (defined("CAT_PATH")) {
    include CAT_PATH . "/framework/class.secure.php";
} else {
    $root = "../";
    $level = 1;
    while ($level < 10 && !file_exists($root . "framework/class.secure.php")) {
        $root .= "../";
        $level += 1;
    }
    if (file_exists($root . "framework/class.secure.php")) {
        include $root . "framework/class.secure.php";
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

$val = CAT_Helper_Validate::getInstance();
$perm = "users_modify";

if ($val->sanitizePost("addUser")) {
    $perm = "users_add";
}

$backend = CAT_Backend::getInstance("access", $perm, false);
$users = CAT_Users::getInstance();

header("Content-type: application/json");

if (!$users->checkPermission("access", $perm)) {
    echo CAT_Object::json_error(
        $backend
            ->lang()
            ->translate(
                "You do not have the permission to {{action}} a user.",
                ["action" => str_replace("users", "", $perm)]
            )
    );
    exit();
}

$addUser = trim($val->sanitizePost("addUser", null, true));
$saveUser = trim($val->sanitizePost("saveUser", null, true));

include_once CAT_PATH . "/framework/functions.php";

// Gather details entered
$username_fieldname = str_replace(
    ["[[", "]]"],
    "",
    htmlspecialchars($val->sanitizePost("username_fieldname"), ENT_QUOTES)
);
$username = trim($val->sanitizePost($username_fieldname, null, true));
$display_name = trim(
    str_replace(
        ["[[", "]]"],
        "",
        htmlspecialchars($val->sanitizePost("display_name"), ENT_QUOTES)
    )
);
$user_id = $val->sanitizePost("user_id", null, true);
$password = $val->sanitizePost("password");
$password2 = $val->sanitizePost("password2");
$email = $val->sanitizePost("email", null, true);
$home_folder = $val->sanitizePost("home_folder", null, true);
$active = $val->sanitizePost("active") != "" ? 1 : 0;
$otp = $val->sanitizePost("otp") != "" ? true : false;
$groups = null;

if ($val->sanitizePost("groups", null, true)) {
    $groups = implode(",", $val->sanitizePost("groups", null, true));
}

/**
 *    Check user_id
 */
if (
    ($saveUser && (!is_numeric($user_id) || $user_id == 1 || $user_id == "")) ||
    ($addUser == "" && $saveUser == "") ||
    ($addUser != "" && $saveUser != "") ||
    $user_id == "admin"
) {
    echo CAT_Object::json_error(
        $backend->lang()->translate("You sent an invalid value")
    );
    exit();
}
if ($groups == "") {
    echo CAT_Object::json_error(
        $backend->lang()->translate("No group was selected")
    );
    exit();
}
if (!$users->validateUsername($username)) {
    echo CAT_Object::json_error(
        $backend->lang()->translate($users->getError())
    );
    exit();
}
if (
    ($password != "" && strlen($password) < AUTH_MIN_PASS_LENGTH) ||
    ($addUser != "" && strlen($password) < AUTH_MIN_PASS_LENGTH)
) {
    echo CAT_Object::json_error(
        $backend
            ->lang()
            ->translate(
                "The password you entered was too short (Please use at least {{AUTH_MIN_PASS_LENGTH}} chars)",
                ["AUTH_MIN_PASS_LENGTH" => AUTH_MIN_PASS_LENGTH]
            )
    );
    exit();
}
if ($password != $password2) {
    echo CAT_Object::json_error(
        $backend->lang()->translate("The passwords you entered do not match")
    );
    exit();
}
if ($email != "") {
    if ($val->validate_email($email) == false) {
        echo CAT_Object::json_error(
            $backend
                ->lang()
                ->translate("The email address you entered is invalid")
        );
        exit();
    }
} else {
    echo CAT_Object::json_error(
        $backend->lang()->translate("You must enter an email address")
    );
    exit();
}

if ($addUser && $users->checkUsernameExists($username)) {
    echo CAT_Object::json_error(
        $backend
            ->lang()
            ->translate("The username you entered is already in use")
    );
    exit();
}

if ($addUser && $users->checkEmailExists($email)) {
    echo CAT_Object::json_error(
        $backend->lang()->translate("The email you entered is already in use")
    );
    exit();
}

$group_id = $val->sanitizePost("groups", null, true);
$group_id =
    is_array($group_id) && in_array("1", $group_id) && $addUser != ""
        ? ($group_id = "1")
        : $group_id[0];

// create new user
if ($addUser) {
    $users->createUser(
        $group_id,
        $active,
        $username,
        $password,
        $display_name,
        $email,
        $home_folder,
        $otp
    );
    unset($password);
} else {
    $options = [
        "group_id" => $group_id,
        "groups_id" => $group_id,
        "active" => $active,
        "username" => $username,
        "display_name" => $display_name,
        "email" => $email,
        "home_folder" => $home_folder,
        "otp" => $otp,
    ];

    if ($password != "") {
        $options["password"] = $password;
    }

    // extended
    $available = $users->getExtendedOptions();
    foreach ($available as $key => $method) {
        $value = $val->sanitizePost($key);
        if ($value) {
            if ($method) {
                // not implemented yet
            }
        }
        $options[$key] = $value;
    }

    $errors = $users->setUserOptions($user_id, $options);
    unset($options["password"]);
    if (count($errors)) {
        echo CAT_Object::json_error(
            "Errors:<br />" . implode("<br />", $errors)
        );
        exit();
    }
}

if ($backend->db()->isError()) {
    echo CAT_Object::json_error($backend->db()->getError());
    exit();
} else {
    // ================================
    // ! Generate username field name
    // ================================
    $username_fieldname = "username_";
    $salt = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEZ_+-";
    $salt_len = strlen($salt) - 1;
    $i = 0;
    while (++$i <= 7) {
        $num = mt_rand(0, $salt_len);
        $username_fieldname .= $salt[$num];
    }

    $action = $addUser != "" ? "added" : "saved";
    $ajax = [
        "message" => $backend
            ->lang()
            ->translate("User {{action}} successfully", [
                "action" => $backend->lang()->translate($action),
            ]),
        "action" => $action,
        "username" => $username,
        "display_name" => $display_name,
        "username_fieldname" => $username_fieldname,
        "id" => $action == "added" ? $backend->db()->lastInsertId() : $user_id,
        "success" => true,
    ];
    print json_encode($ajax);
    exit();
}

exit();
