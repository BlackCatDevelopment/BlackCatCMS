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
 *   @copyright       2014, Black Cat Development
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

// -----------------------------------------------------------------------------
// Methods moved to CAT_Helper_Addons; this file is for backward compatibility
// with Website Baker and LEPTON
// -----------------------------------------------------------------------------

function check_module_dir($mod_dir) {
    return file_exists(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/'.$mod_dir.'/info.php'));
}
function edit_module_css($mod_dir)  {
    return CAT_Helper_Addons::getEditModuleCSSForm($mod_dir,true);
}
function mod_file_exists($mod_dir, $mod_file='frontend.css') {
    return file_exists(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/'.$mod_dir.'/'.$mod_file));
}
function toggle_css_file($mod_dir, $base_css_file = 'frontend.css') {
    return NULL;
}

// -----------------------------------------------------------------------------
//                        DEPRECATED!
// -----------------------------------------------------------------------------
function get_module_language_file($mymod_dir)          {}
function include_module_css($mymod_dir, $css_file)     {}
function requires_module_js($mymod_dir, $js_file)      {}
function requires_module_body_js($mymod_dir, $js_file) {}