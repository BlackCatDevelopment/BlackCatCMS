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
 *   @link            http://www.blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (!class_exists('CAT_Helper_Template'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }
    class CAT_Helper_Template extends CAT_Object
    {

        protected $debuglevel      = CAT_Helper_KLogger::CRIT;
        protected $logger          = NULL;
        private   static $_drivers = array();

        public function __construct($compileDir = null, $cacheDir = null)
        {
            parent::__construct( $compileDir, $cacheDir );

            // get current working directory
            $callstack = debug_backtrace();
            $this->workdir
                = ( isset( $callstack[0] ) && isset( $callstack[0]['file'] ) )
                ? realpath( dirname( $callstack[0]['file'] ) )
                : realpath( dirname(__FILE__) );

            if (
                 file_exists( $this->workdir.'/templates' )
            ) {
                $this->setPath( $this->workdir.'/templates' );
            }

            if ( ! class_exists('CAT_Helper_KLogger',false) ) {
                include dirname(__FILE__).'/../../../framework/CAT/Helper/KLogger.php';
    		}
            $this->logger = new CAT_Helper_KLogger( CAT_PATH.'/temp', $this->debuglevel );

        }   // end function __construct()

        /**
         *
         *
         *
         *
         **/
        public static function getInstance( $driver )
        {
            if ( ! file_exists( dirname(__FILE__).'/Template/'.$driver.'.php' ) )
            {
                $this->printError( $this->lang->translate( 'No such template driver: ['.$driver.']' ) );
                die;
            }
            if ( ! isset(self::$_drivers[$driver]) || ! is_object(self::$_drivers[$driver]) )
            {
                require dirname(__FILE__).'/Template/DriverDecorator.php';
                require dirname(__FILE__).'/Template/'.$driver.'.php';
                $driver = 'CAT_Helper_Template_'.$driver;
                self::$_drivers[$driver] = new CAT_Helper_Template_DriverDecorator( new $driver() );
                self::$_drivers[$driver]->setGlobals(
                      array(
                              'ADMIN_URL' => ADMIN_URL,
                              'WB_URL' => LEPTON_URL,
                              'WB_PATH' => LEPTON_PATH,
                              'LEPTON_URL' => LEPTON_URL,
                        	  'LEPTON_PATH' => LEPTON_PATH,
                        	  'THEME_URL' => THEME_URL,
                        	  'URL_HELP' => URL_HELP,
                      )
                  );
            }
            return self::$_drivers[$driver];
        }   // end function getInstance()


    }
}

