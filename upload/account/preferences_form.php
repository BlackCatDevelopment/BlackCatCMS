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
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
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

// get current user instance
$user      = CAT_Users::getInstance();

// get available languages
$languages = CAT_Helper_Addons::getInstance()->get_addons(LANGUAGE,'language');

global $parser;
$parser->setPath(CAT_PATH.'/templates/'.DEFAULT_TEMPLATE.'/'); // if there's a template for this in the current frontend template
$parser->setFallbackPath(dirname(__FILE__).'/templates/default'); // fallback to default dir
$parser->output('account_preferences_form',
    array(
        'languages' => $languages,
        'timezones' => CAT_Helper_DateTime::getTimezones(),
        'current_tz' => CAT_Helper_DateTime::getTimezone(),
        'date_formats' => CAT_Helper_DateTime::getDateFormats(),
        'current_df' => CAT_Helper_DateTime::getDefaultDateFormatShort(),
        'time_formats' => CAT_Helper_DateTime::getTimeFormats(),
        'current_tf' => CAT_Helper_DateTime::getDefaultTimeFormat(),
        'PREFERENCES_URL' => PREFERENCES_URL,
        'USER_ID' => $user->get_user_id(),
        'DISPLAY_NAME' => $user->get_display_name(),
        'GET_EMAIL' => $user->get_email(),
        'RESULT_MESSAGE' => ( isset( $_SESSION[ 'result_message' ] ) ) ? $_SESSION[ 'result_message' ] : "",
        'AUTH_MIN_LOGIN_LENGTH' => AUTH_MIN_LOGIN_LENGTH,
    )
);

unset( $_SESSION['result_message'] );
