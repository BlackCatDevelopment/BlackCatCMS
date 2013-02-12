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
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Installer
 *
 */

/**
 *	Dummy class to allow modules' install scripts to call $admin->print_error
 */
class admin_dummy
{
	/**
	 *	Public var that holds the message
	 *
	 */
	public $error='';
	public $lang='';
	
	public function __construct() {
	    include dirname(__FILE__).'/../framework/CAT/Helper/I18n.php';
	    $this->lang = new CAT_Helper_I18n();
	}

	/**
	 *	Public function to "setup" the message.
	 *
	 *	@param	string	Any message-string.
	 *	@return	nothing
	 */
	public function print_error($message)
	{
		$this->error=$message;
	}

	/**
	 *	Need for e.g. installing dropleps.
	 *
	 */
	public function get_user_id()
	{
		return 1;
	}

    /**
     * Fake this for installer
     **/
    public function get_permission()
    {
        return true;
    }

    /**
     * create a guid
     **/
    function createGUID($prefix)
    {
        if(!$prefix||$prefix='') $prefix=rand();
        $s = strtoupper(md5(uniqid($prefix,true)));
        $guidText =
            substr($s,0,8) . '-' .
            substr($s,8,4) . '-' .
            substr($s,12,4). '-' .
            substr($s,16,4). '-' .
            substr($s,20);
        return $guidText;
    }
}
