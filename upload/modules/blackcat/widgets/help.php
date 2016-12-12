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
 *   @category        CAT_Modules
 *   @package         blackcat
 *
 */

if (defined('CAT_PATH')) {
    if (defined('CAT_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
    include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));        $dir = $_SERVER['DOCUMENT_ROOT'];
    $inc = false;
    foreach ($subs as $sub) {
            if (empty($sub)) continue; $dir .= '/'.$sub;
            if (file_exists($dir.'/framework/class.secure.php')) {
                    include($dir.'/framework/class.secure.php'); $inc = true;        break;
            }
    }
    if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

$widget_settings = array(
    'allow_global_dashboard' => true,
    'widget_title'           => CAT_Helper_I18n::getInstance()->translate('Help links'),
    'preferred_column'       => 3
);

if(!function_exists('render_widget_blackcat_help'))
{
    function render_widget_blackcat_help()
    {
        return "
    <span style=\"width:80px;display:inline-block;\">Wiki:</span> <a href=\"https://wiki.blackcat-cms.org\" target=\"_blank\">https://wiki.blackcat-cms.org</a><br />
    <span style=\"width:80px;display:inline-block;\">Forum:</span> <a href=\"https://forum.blackcat-cms.org\" target=\"_blank\">https://forum.blackcat-cms.org</a><br />
    <span style=\"width:80px;display:inline-block;\">Homepage:</span> <a href=\"https://blackcat-cms.org\" target=\"_blank\">https://blackcat-cms.org</a><br />
    ";
    }
}