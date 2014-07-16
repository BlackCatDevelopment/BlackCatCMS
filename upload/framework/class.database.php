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
 *   This class is for backward compatibility with Website Baker and LEPTON CMS.
 *   Please use CAT_Helper_DB instead!
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

global $database;

if (!class_exists('database', false))
{
    class database
    {
        private static $obj = NULL;
        public function __construct()
        {
            self::$obj = CAT_Helper_DB::getInstance();
            return self::$obj;
        }
        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( self::$obj, $method ) )
                return call_user_func_array(array(self::$obj, $method), $args);
        }

        /**
         * Execute query and return the first column of the first row of
         * the result; returns NULL if no result was fetched
         *
         * @access public
         * @param  string  $sql
         * @param  flag    $type
         * @return mixed
         **/
    	public function get_one($sql,$type=PDO::FETCH_ASSOC)
        {
            $q = $this->query($sql);
            if($q && $q->rowCount())
            {
                $row = $q->fetch($type);
    			if($type==2 || preg_match('~_assoc$~i',$type))
                {
    				$temp = array_values($row);
    				return $temp[0];
    			} else {
    				return $row[0];
    			}
            }
            return NULL;
        }   // end function get_one()

        /**
         * old function names wrap new ones
         **/
        public function is_error()  { return self::$obj->isError();      }
        public function get_error() { return self::$obj->getError();     }
        public function insert_id() { return self::$obj->lastInsertId(); }
        public function prompt_on_error($switch=true) { /* no longer supported */ }

    }   // ----- end class database -----
}

/*
        public function get_one($sql,$type=PDO::FETCH_ASSOC)
        public function insert_id()
        public function prompt_on_error($switch=true)
*/