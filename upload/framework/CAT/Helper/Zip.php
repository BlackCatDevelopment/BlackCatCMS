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
        private static $instance;
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
            if ( method_exists( self::$zip, $method ) )
            {
                return self::$zip->$method($attr);
            }
        }   // end function __call()

		/**
		 *
         *
         *
		 *
		 **/
        public static function getInstance( $zipfile )
		{
            if ( ! is_object(self::$instance) )
			{
                self::$instance = new self($zipfile);
			}
            return self::$instance;
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
        public function add($p_filelist)    { return self::$zip->add($p_filelist);    }
        public function create($p_filelist) { return self::$zip->create($p_filelist); }
        public function extract()           { return self::$zip->extract();           }
        public function errorInfo()         { return self::$zip->errorInfo();         }
			
    }   // end class Cat_Helper_Zip

}   // end class_exists()