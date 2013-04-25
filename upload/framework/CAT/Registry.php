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

if (!class_exists('CAT_Object', false))
{
    @include dirname(__FILE__) . '/Object.php';
}

if (!class_exists('CAT_Registry', false))
{
    class CAT_Registry extends CAT_Object
    {

        protected      $_config         = array( 'loglevel' => 8 );

        // singleton
        private static $instance        = NULL;

        private static $REGISTRY;

        /**
         * get singleton
         *
         * @access public
         * @return object
         **/
        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }   // end function getInstance()

        /**
         * check if $key is defined; same as exists() but similar to defined(CONSTANT)
         *
         * @access public
         * @param  string  $key
         * @return boolean
         **/
        public static function defined($key)
        {
            return self::exists($key);
        }   // end function defined()

        /**
         * dump all; this is for debugging only as it uses var_dump()
         *
         * @access public
         * @return void
         **/
        public static function dump()
        {
            var_dump(self::$REGISTRY);
            }

        /**
         * check if a global var exists; same as defined()
         *
         * @access public
         * @param  string  $key
         * @return boolean
         *
         **/
        public static function exists($key)
            {
            if(isset(self::$REGISTRY[$key]))
                {
                return true;
                }
            return false;
        }   // end function exists()

        /**
         * get globally stored data
         *
         * @access public
         * @param  string  $key
         * @param  string  $require - function to check value with
         *                            i.e. 'array' => is_array()
         * @param  mixed   $default - default value to return if the key is not found
         **/
        public static function get( $key, $require=NULL, $default=NULL )
        {
            $return_value = NULL;
            if(isset(self::$REGISTRY[$key]))
            {
                if($require)
                {
                    $return_value = CAT_Helper_Validate::check(self::$REGISTRY[$key],$require);
                }
                else
                {
                    $return_value = self::$REGISTRY[$key];
                }
            }
            if(!$return_value)
            {
                if($require && $require == 'array')
                    if($default && is_array($default))
                        return $default;
            else
                        return array();
                return NULL;
            }
            return $return_value;
        }   // end function get()

        /**
         * register globally stored data
         *
         * @access public
         * @param  string  $key
         * @param  mixed   $value
         * @param  boolean $as_const - use define() to set as constant; this is for backward compatibility as WB works with global constants very much
         *                             default: false
         **/
        public static function register( $key, $value=NULL, $as_const=false )
        {
            if ( ! is_array($key) )
            {
                self::$REGISTRY[$key] = $value;
                // we deliberately do not catch errors here!
                if($as_const) define($key,$value);
            }
            else
        {
                foreach ( $key as $name => $value )
            {
                    self::$REGISTRY[$name] = $value;
                    if($as_const) define($name,$value);
                }
            }
        }   // end function register()

        /**
         * same as register(), just shorter
         **/
        public static function set($key,$value=NULL,$as_const=false)
        {
            return self::register($key,$value,$as_const);
        }   // end function set()
    }
}