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

if (!class_exists('CAT_Helper_CSV'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_CSV extends CAT_Object
    {
        // array to store config options
        protected $_config         = array( 'loglevel' => 7 );

        private static $instance;

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * allow to use methods in OO context
         **/
        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }   // end function __call()

        /**
          * Formats a line (passed as a fields  array) as CSV and returns the CSV as a string.
          * Adapted from http://us3.php.net/manual/en/function.fputcsv.php#87120
          */
        public static function arrayToCsv( array $line, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false )
        {
            $delimiter_esc = preg_quote($delimiter, '/');
            $enclosure_esc = preg_quote($enclosure, '/');
            $output        = array();
            foreach ( $line as $field ) {
                if ($field === null && $nullToMysqlNull) {
                    $output[] = 'NULL';
                    continue;
                }
                // Enclose fields containing $delimiter, $enclosure or whitespace
                if ( $encloseAll || preg_match( "/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field ) ) {
                    $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
                }
                else {
                    $output[] = $field;
                }
            }
            return implode($delimiter,$output);
        }   // end function arrayToCsv()
        
    } // class CAT_Helper_CSV

} // if class_exists()