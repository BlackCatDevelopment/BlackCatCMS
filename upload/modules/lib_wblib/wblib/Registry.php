<?php

/**
 *
 *          _     _  _ _
 *         | |   | |(_) |
 *    _ _ _| |__ | | _| |__
 *   | | | |  _ \| || |  _ \
 *   | | | | |_) ) || | |_) )
 *   \___/|____/ \_)_|____/
 *
 *
 *   @category     wblib
 *   @package      Registry
 *   @author       BlackBird Webprogrammierung
 *   @copyright    (c) 2014 BlackBird Webprogrammierung
 *   @license
 *
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
 **/

namespace wblib;

/**
 * 
 *
 * @category   wblib
 * @package    Registry
 * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
 * @license
 */
if ( ! class_exists( 'Registry', false ) )
{
    class Registry {

        private static $REGISTRY = array();

        /**
         * retrieve globally stored data
         *
         * @access public
         * @param  string  $key
         * @param  mixed   $default - default value to return if the key is not found
         * @return mixed
         **/
        public static function get( $key, $default=NULL )
        {
            $return_value = NULL;
            if(isset(self::$REGISTRY[$key]))
                $return_value = self::$REGISTRY[$key];
            if(!$return_value && $default)
                return $default;
            return $return_value;
        }   // end function get()

        /**
         * register (set) globally stored data
         *
         * @access public
         * @param  string  $key
         * @param  mixed   $value
         *
         **/
        public static function set( $key, $value=NULL )
        {
            if ( ! is_array($key) )
                $key = array( $key => $value );
            foreach ( $key as $name => $value )
                self::$REGISTRY[$name] = $value;
        }   // end function set()

    }
}