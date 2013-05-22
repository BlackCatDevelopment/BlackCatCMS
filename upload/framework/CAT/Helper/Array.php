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

if ( ! class_exists( 'CAT_Helper_Array' ) )
{

    if ( ! class_exists( 'CAT_Object', false ) ) {
	    @include dirname(__FILE__).'/../Object.php';
	}
	
	class CAT_Helper_Array extends CAT_Object
	{

        private static $Needle  = NULL;
        private static $Key     = NULL;
        protected      $_config = array( 'loglevel' => 8 );
        private static $instance;

        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private static function filter_callback($v)
        {
            return !isset($v[self::$Key]) || $v[self::$Key] !== self::$Needle;
        }

        /**
         * removes an element from an array
         *
         * @access public
         * @param  string $Needle
         * @param  array  $Haystack
         * @param  mixed  $NeedleKey
         **/
        public static function ArrayRemove( $Needle, &$Haystack, $NeedleKey="" )
        {
            if( ! is_array( $Haystack ) ) {
                return false;
            }
            reset($Haystack);
            self::$Needle = $Needle;
            self::$Key    = $NeedleKey;
            $Haystack     = array_filter($Haystack, 'self::filter_callback');
        }

        /**
         * sort an array
         *
         * @access public
         * @param  array   $array          - array to sort
         * @param  mixed   $index          -
         * @param  string  $order          - 'asc' (default) || 'desc'
         * @param  boolean $natsort        - default: false
         * @param  boolean $case_sensitive - sort case sensitive; default: false
         *
         **/
        public static function ArraySort ( $array, $index, $order='asc', $natsort=FALSE, $case_sensitive=FALSE )
        {
            if( is_array($array) && count($array)>0 )
        {
                 foreach(array_keys($array) as $key)
                 {
                     $temp[$key]=$array[$key][$index];
                 }
                 if(!$natsort)
                 {
                     ($order=='asc')? asort($temp) : arsort($temp);
                 }
                 else
                 {
                     ($case_sensitive)? natsort($temp) : natcasesort($temp);
                     if($order!='asc')
                     {
                         $temp=array_reverse($temp,TRUE);
                     }
                 }
                 foreach(array_keys($temp) as $key)
                 {
                     (is_numeric($key))? $sorted[]=$array[$key] : $sorted[$key]=$array[$key];
                 }
                 return $sorted;
            }
            return $array;
        }   // end function ArraySort()
        
        /**
         * make multidimensional array unique
         *
         * @access public
         * @param  array
         * @return array
         **/
        public static function ArrayUniqueRecursive($array) {
    		$set = array();
    		$out = array();
    		foreach ( $array as $key => $val ) {
    			  if ( is_array($val) )
            {
    				    $out[$key] = $this->ArrayUniqueRecursive($val);
    			  }
            elseif ( ! isset( $set[$val] ) )
            {
    				    $out[$key] = $val;
    				    $set[$val] = true;
    			  }
            else
            {
                $out[$key] = $val;
            }
    		}
    		return $out;
   		}   // end function ArrayUniqueRecursive()
	}
}

?>