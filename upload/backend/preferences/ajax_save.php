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

header("Content-type: application/json");

$backend = CAT_Backend::getInstance("user", "preferences", false, false);
$user = CAT_Users::getInstance();
$val = CAT_Helper_Validate::getInstance();

$extended = $user->getExtendedOptions();
$err_msg = [];

// =================================================
// ! remove any dangerouse chars from display_name
// =================================================
$display_name = $val->add_slashes(
    strip_tags(trim($val->sanitizePost("display_name")))
);
$display_name = $display_name == "" ? $user->get_display_name() : $display_name;

// ==================================================================================
// ! check that display_name is unique in whole system (prevents from User-faking)
// ==================================================================================
$sql =
    "SELECT COUNT(*) FROM `:prefix:users` WHERE `user_id` <> :id AND `display_name` LIKE :name";

if (
    $backend
        ->db()
        ->query($sql, [
            "id" => (int) $user->get_user_id(),
            "name" => $display_name,
        ])
        ->fetchColumn() > 0
) {
    $err_msg[] = $backend->lang->translate(
        "The username you entered is already taken"
    );
}
// ============================================
// ! language must be 2 uppercase letters only
// ============================================
$language = strtoupper($val->sanitizePost("language"));
$language = $backend->lang()->checkLang($language)
    ? $language
    : CAT_Registry::get("DEFAULT_LANGUAGE");

// ================
// ! validate email
// ================
$email =
    $val->sanitizePost("email") == null ? "x" : $val->sanitizePost("email");
if (!$val->validate_email($email)) {
    $email = "";
    $err_msg[] = $backend
        ->lang()
        ->translate("The email address you entered is invalid");
} else {
    // check that email is unique
    $email = $val->add_slashes($email);
    $sql =
        "SELECT COUNT(*) FROM `:prefix:users` WHERE `user_id` <> :id AND `email` LIKE :mail";
    if (
        $backend
            ->db()
            ->query($sql, [
                "id" => (int) $user->get_user_id(),
                "mail" => $email,
            ])
            ->fetchColumn() > 0
    ) {
        $err_msg[] = $backend
            ->lang()
            ->translate("The email you entered is already in use");
    }
}

// =====================================================
// ! receive password vars and calculate needed action
// =====================================================
$current_password = $val->sanitizePost("current_password");
$new_password_1 = $val->sanitizePost("new_password_1");
$new_password_2 = $val->sanitizePost("new_password_2");
$current_password = $current_password == null ? "" : $current_password;
$new_password_1 =
    $new_password_1 == null || $new_password_1 == "" ? "" : $new_password_1;
$new_password_2 =
    $new_password_2 == null || $new_password_2 == "" ? "" : $new_password_2;

if ($current_password == "") {
    $err_msg[] = $backend
        ->lang()
        ->translate(
            "You must enter your current password to save your changes"
        );
} elseif ($new_password_1 != $new_password_2) {
    $err_msg[] = $backend->lang()->translate("The passwords do not match.");
} else {
    // if new_password is empty, still let current one
    if ($new_password_1 == "") {
        $new_password_1 = $current_password;
        $new_password_2 = $current_password;
    } else {
        if (!$user->validatePassword($new_password_1)) {
            $err_msg[] = $user->getPasswordError();
        }
    }
}

// =======================================================================================
// ! if no validation errors, try to update the database, otherwise return errormessages
// =======================================================================================
if (!count($err_msg)) {
    $user_id = $user->get_user_id();

    // check pw
    if (!CAT_Users::checkUserLogin($user->get_username(), $current_password)) {
        print json_encode([
            "success" => false,
            "message" => $backend
                ->lang()
                ->translate("The (current) password you entered is incorrect"),
        ]);
        exit();
    }

    // --- save basics ---
    $sql =
        "UPDATE `:prefix:users` SET `display_name` = :display, " .
        "`password` = :pw, " .
        "`email` = :email, " .
        "`language` = :lang " .
        "WHERE `user_id` = :uid";

    $arr = [
        "display" => $display_name,
        "pw" => CAT_Users::getHash($new_password_1),
        "email" => $email,
        "lang" => $language,
        "uid" => $user_id,
    ];

    if (($stmt = $backend->db()->query($sql, $arr)) !== false) {
        // update successful
        // --- save additional settings ---
        $backend
            ->db()
            ->query(
                "DELETE FROM `:prefix:users_options` WHERE `user_id` = :uid",
                ["uid" => $user_id]
            );
        foreach ($extended as $opt => $check) {
            $value = $val->sanitizePost($opt);
            //echo "OPT -$opt- VAL -$value- CHECK -$check- VALID -" . call_user_func($check,$value) . "-\n<br />";
            if ($check && !call_user_func($check, $value)) {
                continue;
            }
            $sql =
                "INSERT INTO `:prefix:users_options` " .
                "VALUES ( :uid, :opt, :val )";
            $backend
                ->db()
                ->query($sql, [
                    "uid" => $user_id,
                    "opt" => $opt,
                    "val" => $value,
                ]);
        }

        $_SESSION["DISPLAY_NAME"] = $display_name;
        $_SESSION["LANGUAGE"] = $language;
        $_SESSION["EMAIL"] = $email;
        $_SESSION["TIMEZONE_STRING"] = $timezone_string;

        date_default_timezone_set($timezone_string);

        // ======================
        // ! Update date format
        // ======================
        $date_format = $val->sanitizePost("date_format");
        if ($date_format != "") {
            $_SESSION["CAT_DATE_FORMAT"] = $date_format;
            if (isset($_SESSION["USE_DEFAULT_DATE_FORMAT"])) {
                unset($_SESSION["USE_DEFAULT_DATE_FORMAT"]);
            }
        } else {
            $_SESSION["USE_DEFAULT_DATE_FORMAT"] = true;
            if (isset($_SESSION["CAT_DATE_FORMAT"])) {
                unset($_SESSION["CAT_DATE_FORMAT"]);
            }
        }
        // ======================
        // ! Update time format
        // ======================
        $time_format = $val->sanitizePost("time_format");
        if ($time_format != "") {
            $_SESSION["CAT_TIME_FORMAT"] = $time_format;
            if (isset($_SESSION["USE_DEFAULT_TIME_FORMAT"])) {
                unset($_SESSION["USE_DEFAULT_TIME_FORMAT"]);
            }
        } else {
            $_SESSION["USE_DEFAULT_TIME_FORMAT"] = true;
            if (isset($_SESSION["CAT_TIME_FORMAT"])) {
                unset($_SESSION["CAT_TIME_FORMAT"]);
            }
        }

        if (defined("WB2COMPAT") && WB2COMPAT === true) {
            $wb2compat_format_map = CAT_Registry::get("WB2COMPAT_FORMAT_MAP");
            $_SESSION["DATE_FORMAT"] =
                $wb2compat_format_map[$_SESSION["CAT_DATE_FORMAT"]];
            $_SESSION["TIME_FORMAT"] =
                $wb2compat_format_map[$_SESSION["CAT_TIME_FORMAT"]];
        }

        // ====================
        // ! Set initial page
        // ====================
        $new_init_page = $val->sanitizePost("init_page_select");
        if ($new_init_page) {
            require_once CAT_PATH .
                "/modules/initial_page/classes/c_init_page.php";
            $ref = new c_init_page($backend->db());
            $ref->update_user($_SESSION["USER_ID"], $new_init_page);
            unset($ref);
        }
    } else {
        $err_msg =
            $backend->lang()->translate("invalid database UPDATE call in ") .
            __FILE__ .
            "::" .
            __FUNCTION__ .
            $backend->lang()->translate("before line ") .
            __LINE__;
    }
}

$ajax = [
    "message" => count($err_msg)
        ? implode("\n", $err_msg)
        : $backend->lang()->translate("Details saved successfully"),
    "success" => count($err_msg) ? false : true,
];
print json_encode($ajax);
exit();
