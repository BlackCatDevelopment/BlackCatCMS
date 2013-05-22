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
 * @copyright       2013, Black Cat Development
 * @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 *   This file is for backward compatibility with Website Baker 2.8.x and
 *   LEPTON 1.x. All methods are moved to CAT_* classes.
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

require_once(CAT_PATH.'/framework/class.wb.php');

class admin extends wb
{

    function __construct($section_name, $section_permission = 'start', $auto_header = true, $auto_auth = true)
    {
        if(defined('WB2COMPAT'))
        {
            global $admin;
            require_once CAT_PATH.'/framework/class.admin.php';
            $admin = CAT_Backend::getInstance($section_name);
            return $admin;
        }
    return CAT_Backend::getInstance($section_name);
    }

    function get_permission($name, $type = 'system') { return CAT_Users::get_permission($name,$type); }
    function get_user_details($user_id)              { return CAT_Users::get_user_details($user_id);  }
    function print_banner()                          { return CAT_Backend::getInstance('')->print_banner(); }
    function print_header()                          { return CAT_Backend::getInstance('')->print_header(); }
    function print_footer()                          { return CAT_Backend::getInstance('')->print_footer(); }

}

/*
	get_link_permission($title)
	__admin_register_backend_modfiles()
	__admin_build_link( $aPath, $aType="css")
	get_page_permission($page,$action='admin')
*/