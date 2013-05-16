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
 *   @package         CAT_Core
 *
 */

if ( ! class_exists( 'CAT_Object', false ) ) {
    @include dirname(__FILE__).'/../Object.php';
}
if ( ! class_exists( 'CAT_Helper_Directory', false ) ) {
    @include dirname(__FILE__).'/Directory.php';
}

if ( ! class_exists( 'CAT_Helper_Zip', false ) )
{

	class CAT_Helper_Zip extends CAT_Object
	{
	
        private static $_drivers = array();
        private static $instances = array();
        private static $zip;
	    
	    /**
	     * constructor
	     **/
		public function __construct( $zipfile = NULL ) {
            // get driver
            self::$zip = self::getDriver('PclZip',$zipfile);
            return self::$zip;
		}   // end function __construct()

        /**
         * forward unknown methods to driver
         *
         */
        public function __call($method,$attr)
        {
                return self::$zip->$method($attr);
        }   // end function __call()

		/**
		 *
         *
         *
		 *
		 **/
        public static function getInstance( $zipfile = NULL )
		{
            if (!isset(self::$instances[$zipfile]) || !is_object(self::$instances[$zipfile]) )
			{
                self::$instances[$zipfile] = new self($zipfile);
			}
            return self::$instances[$zipfile];
        }   // end function getInstance()
		
		/**
         * try to load the driver
		 * 
         * @access private
         * @param  string  $driver  - driver name
         * @param  string  $zipfile - optional zip file name
         * @return object
		 **/
        private static function getDriver($driver,$zipfile=NULL)
		{
            if ( ! preg_match('/driver$/i',$driver) )
			{
                $driver .= 'Driver';
			}
            if ( ! isset(self::$_drivers[$driver]) || ! is_object(self::$_drivers[$driver]) )
			{
                if ( ! file_exists( dirname(__FILE__).'/Zip/'.$driver.'.php' ) )
			{
                    CAT_Object::getInstance()->printFatalError( 'No such Zip driver: ['.$driver.']' );
			}
                require dirname(__FILE__).'/Zip/'.$driver.'.php';
                $driver = 'CAT_Helper_Zip_'.$driver;
                self::$_drivers[$driver] = $driver::getInstance($zipfile);
            }
            return self::$_drivers[$driver];
        }   // end function getDriver()

        public function config($option,$value=NULL) { return self::$zip->config($option,$value); }
        public function add($args)                  { return self::$zip->add($args);       }
        public function create($args)               { return self::$zip->create($args);    }
        public function extract()           { return self::$zip->extract();           }
        public function extractByIndex($args)       { return self::$zip->extractByIndex($args);  }
        public function errorInfo($p_full=false)    { return self::$zip->errorInfo($p_full);     }
			
    }   // end class Cat_Helper_Zip

}   // end class_exists()