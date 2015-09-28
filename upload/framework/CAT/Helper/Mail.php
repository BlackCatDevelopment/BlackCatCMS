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

if ( ! class_exists( 'CAT_Helper_Mail', false ) ) {

	class CAT_Helper_Mail extends CAT_Object
	{
        protected        $debugLevel = 8; // 8 = OFF
        private   static $error      = NULL;
        private   static $_drivers   = array();
        private   static $init       = false;
        private   static $instance;
        private   static $settings = array(
            'routine'            => 'phpmail',
            'smtp_auth'          => '',
            'smtp_host'          => '',
            'smtp_password'      => '',
            'smtp_username'      => '',
            'default_sendername' => 'Black Cat CMS Mailer',
        );
        private   static $routine_driver_map = array(
            'lib_swift'     => 'Swift',
            'lib_phpmailer' => 'PHPMailer',
        );

        /**
         *
         *
         *
         *
         **/
        public static function getInstance( $driver = NULL )
        {

            if ( ! self::$init ) self::init();
            if ( ! $driver && isset(self::$routine_driver_map[CATMAILER_LIB]) )
                $driver = self::$routine_driver_map[CATMAILER_LIB];
            if ( ! $driver )
                $driver = 'PHPMailer';
            if ( ! preg_match('/driver$/i',$driver) )
            {
                $driver .= 'Driver';
            }
            // check if the lib is available
            if ( ! file_exists(dirname(__FILE__).'/../../../modules/'.CATMAILER_LIB) )
                return false;
            if ( ! isset(self::$_drivers[$driver]) || ! is_object(self::$_drivers[$driver]) )
            {
                if ( ! file_exists( dirname(__FILE__).'/Mail/'.$driver.'.php' ) )
                {
                    CAT_Object::getInstance()->printFatalError( 'No such mail driver: ['.$driver.']' );
                }
                require dirname(__FILE__).'/Mail/'.$driver.'.php';
                $driver = 'CAT_Helper_Mail_'.$driver;
                self::$_drivers[$driver] = $driver::getInstance(self::$settings);
            }
            return self::$_drivers[$driver];
        }   // end function getInstance()

        /**
         * initialize
         *
         * @access private
         * @return void
         *
         **/
        private static function init()
        {
            global $database;
            if ( ! is_object($database) )
            {
                @require dirname(__FILE__).'/../../class.database.php';
    		    $database = new database();
            }
    		$query    = "SELECT * FROM " .CAT_TABLE_PREFIX. "settings";
    		$results  = $database->query($query);
    		while($setting = $results->fetch(PDO::FETCH_ASSOC))
            {
                if ( preg_match('/^wbmailer_(.*)$/i',$setting['name'],$match) )
                {
    			    self::$settings[$match[1]] = $setting['value'];
                    continue;
                }
                if ( preg_match('/^catmailer_(.*)$/i',$setting['name'],$match) )
                {
    			    self::$settings[$match[1]] = $setting['value'];
                    continue;
                }
    			if ($setting['name'] == "server_email")
                {
                    self::$settings['server_email'] = $setting['value'];
                    continue;
                }
    		}
        }   // end function init()

        /**
         *
         *
         *
         *
         **/
        public function sendMail($fromaddress, $toaddress, $subject, $message, $fromname='')
        {
            // format
            $fromaddress = preg_replace('/[\r\n]/'  , ''      , $fromaddress);
            $toaddress   = preg_replace('/[\r\n]/'  , ''      , $toaddress  );
            $subject     = preg_replace('/[\r\n]/'  , ''      , $subject    );
            $message     = preg_replace('/\r\n?|\n/', '<br \>', $message    );
        }

        /**
         *
         *
         *
         *
         **/
        public static function setError($msg)
        {
            self::$error = $msg;
        }
            
        /**
         *
         *
         *
         *
         **/
        public static function getError()
        {
            return self::$error;
        }
    }
}