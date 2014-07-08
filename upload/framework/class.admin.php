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

require_once(CAT_PATH.'/framework/class.wb.php');

if(!class_exists('admin'))
{
class admin extends wb
{

    function __construct($section_name, $section_permission = 'start', $auto_header = true, $auto_auth = true)
    {
        return CAT_Backend::getInstance($section_name,$section_permission,$auto_header,$auto_auth);
    }

    function get_email()                             { return CAT_Users::get_email();                       }
    function get_page_details($page_id)              { return CAT_Helper_Page::properties($page_id);        }
    function get_permission($name, $type = 'system') { return CAT_Users::get_permission($name,$type); }
    function get_user_details($user_id)              { return CAT_Users::get_user_details($user_id);  }
    function print_banner()                          { return CAT_Backend::getInstance('')->print_banner(); }
    function print_header()                          { return CAT_Backend::getInstance('')->print_header(); }
    function print_footer()                          { return CAT_Backend::getInstance('')->print_footer(); }

        // the following functions are originally located in SecureForm.php (WB 2.8.3)
        public function checkIDKEY( $fieldname, $default = 0, $request = 'POST' ) {
            $val = CAT_Helper_Validate::get('_'.$request, $fieldname);
            return $val ? $val : $default;
        }
        public function getIDKEY($value)                 { return $value; }

}

/*
	get_link_permission($title)
	get_page_permission($page,$action='admin')
*/
}