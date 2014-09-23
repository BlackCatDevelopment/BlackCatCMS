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
 * @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 * @copyright       2013, Black Cat Development
 * @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
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

require_once(CAT_PATH . '/framework/class.database.php');

class wb
{
    public  $password_chars      = 'a-zA-Z0-9\_\-\!\#\*\+';
    private $_handles            = NULL;
    public  $lang                = NULL;
    public  $page                = array(); // keep SM2 happy
    
    private static $depre_func   = array(
        'bind_jquery' => '<a href="https://github.com/webbird/BlackCatCMS/wiki/get_page_headers%28%29">get_page_headers()</a>',
        'register_backend_modfiles' => '<a href="https://github.com/webbird/BlackCatCMS/wiki/get_page_headers%28%29">get_page_headers("backend", true, "$section_name")</a>',
        'register_backend_modfiles_body' => '<a href="https://github.com/webbird/BlackCatCMS/wiki/get_page_footers()">get_page_footers("backend")</a>',
        'register_frontend_modfiles' => '<a href="https://github.com/webbird/BlackCatCMS/wiki/get_page_headers%28%29">get_page_headers()</a>',
        'register_frontend_modfiles_body' => '<a href="https://github.com/webbird/BlackCatCMS/wiki/get_page_footers()">get_page_footers()</a>',
        'page_menu' => 'show_menu2()',
        'show_menu' => 'show_menu2()',
        'show_breadcrumbs' => 'show_menu2()',
        'menu' => 'show_menu2()',
        'search_highlight' => 'nothing',
    );

    // General initialization public function
    // performed when frontend or backend is loaded.

    public function __construct()
    {
  		$this->lang  = CAT_Helper_I18n::getInstance(LANGUAGE);
    }   // end constructor

    public function __call($method, $arguments)
        {
        if (array_key_exists($method,self::$depre_func)) {
            trigger_error('Method ## '.$method.'() ## is deprecated, use ## '.self::$depre_func[$method].' ## instead!',E_USER_ERROR);
        } else {
            trigger_error('Unknown method '.$method, E_USER_ERROR);
        }
    }   // end function __call()

    public function lang() {
        if(!is_object($this->lang))
            $this->lang  = CAT_Helper_I18n::getInstance(LANGUAGE);
        return $this->lang;
        }

    // Escape backslashes for use with mySQL LIKE strings
    public function escape_backslashes($input)
    {
        return str_replace("\\", "\\\\", $input);
    }

    /* ****************
     * set one or more bit in a integer value
     *
     * @access public
     * @param int $value: reference to the integer, containing the value
     * @param int $bits2set: the bitmask witch shall be added to value
     * @return void
     */
    public function bit_set(&$value, $bits2set)
    {
        $value |= $bits2set;
    }

    /* ****************
     * reset one or more bit from a integer value
     *
     * @access public
     * @param int $value: reference to the integer, containing the value
     * @param int $bits2reset: the bitmask witch shall be removed from value
     * @return void
     */
    public function bit_reset(&$value, $bits2reset)
    {
        $value &= ~$bits2reset;
    }

    /* ****************
     * check if one or more bit in a integer value are set
     *
     * @access public
     * @param int $value: reference to the integer, containing the value
     * @param int $bits2set: the bitmask witch shall be added to value
     * @return void
     */
    public function bit_isset($value, $bits2test)
    {
        return(($value & $bits2test) == $bits2test);
    }

    /***************************************************************************
     * DEPRECATED FUNCTIONS
     * These functions are moved to other classes
     **************************************************************************/

    /* moved to CAT_Object */
    public function print_error($message, $link = 'index.php', $auto_footer = true)
    {
        CAT_Object::printError($message,$link);
        exit();
    }
	public function print_success($message, $redirect = 'index.php', $auto_footer = true)
	{
        // this sets auto_exit to false, as some WB modules (like Bakery)
        // have additional code after calling print_success()
		CAT_Object::printMsg($message,$redirect,$auto_footer,false);
    }

    /* moved to CAT_Helper_Mail */
    public function mail($fromaddress, $toaddress, $subject, $message, $fromname = '')
    {
        // check if mailer lib is installed
        if(count(CAT_Helper_Addons::getLibraries('mail')))
        return CAT_Helper_Mail::getInstance()->sendMail($fromaddress, $toaddress, $subject, $message, $fromname);
        else
            return false;
    }

    /* moved to CAT_Helper_Page */
    public function page_is_visible($page) { return CAT_Helper_Page::isVisible($page['page_id']); }
    public function page_is_active($page)  { return CAT_Helper_Page::isActive($page['page_id']);  }
    public function page_link($link)       { return CAT_Helper_Page::getLink($link);   }
    public function show_page($page)       { return CAT_Helper_Page::isVisible($page['page_id']); }

    /* moved to CAT_Sections */
    public function section_is_active($section_id) { return CAT_Sections::section_is_active($section_id); }

    /* moved to CAT_Users */
    public function get_user_id()          { return CAT_Users::get_user_id();      }
    public function get_group_id()         { return CAT_Users::get_group_id();     }
    public function get_groups_id()        { return CAT_Users::get_groups_id();    }
    public function get_group_name()       { return CAT_Users::get_group_name();   }
    public function get_groups_name()      { return CAT_Users::get_groups_name();  }
    public function get_username()         { return CAT_Users::get_username();     }
    public function get_display_name()     { return CAT_Users::get_display_name(); }
    public function get_email()            { return CAT_Users::get_email();        }
    public function get_home_folder()      { return CAT_Users::get_home_folder();  }
    public function is_authenticated()     { return CAT_Users::is_authenticated(); }

    public function is_group_match($groups_list1 = '', $groups_list2 = '')
    {
        return CAT_Users::is_group_match($groups_list1,$groups_list2);
    }
    public function get_groups($viewing_groups = array() , $admin_groups = array(), $insert_admin = true)
    {
         return CAT_Users::get_groups($viewing_groups,$admin_groups,$insert_admin);
    }
    // Get the current users timezone
    public function get_timezone_string()
    {
        return CAT_Helper_DateTime::getTimezone();
    }   // end function get_timezone_string()

    /* moved to CAT_Helper_Validate */
    public function add_slashes($input)     { return CAT_Helper_Validate::add_slashes($input);    }
    public function strip_slashes($input)   { return CAT_Helper_Validate::strip_slashes($input);  }
    public function get_post($field)        { return CAT_Helper_Validate::sanitizePost($field);   }
    public function get_get($field)         { return CAT_Helper_Validate::sanitizeGet($field);    }
    public function get_session($field)     { return CAT_Helper_Validate::fromSession($field);    }
    public function get_server($field)      { return CAT_Helper_Validate::sanitizeServer($field); }
    public function get_post_escaped($field){ return CAT_Helper_Validate::sanitizePost($field,NULL,true); }
    public function validate_email($email)  { return CAT_Helper_Validate::validate_email($email); }

    /* methods for compatibility with WB 2.8.3 and above (originally located in SecureForm.php) */
   	public function createFTAN() {}
	public function getFTAN( $mode = 'POST')   { return CAT_Helper_Protect::createToken($mode); }
	public function checkFTAN( $mode = 'POST') { return CAT_Helper_Protect::checkToken($mode);  }
	public function getIDKEY($value)           { return $value; }
	public function checkIDKEY( $fieldname, $default = 0, $request = 'POST' ) {
        $val = CAT_Helper_Validate::get('_'.$request, $fieldname);
        return $val ? $val : $default;
    }
	public function clearIDKEY() {}
}

?>