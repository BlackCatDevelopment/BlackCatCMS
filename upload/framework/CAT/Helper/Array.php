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
                self::$instance = new self();
            return self::$instance;
        }   // end function getInstance()

        private static function filter_callback($v)
        {
            return !isset($v[self::$Key]) || $v[self::$Key] !== self::$Needle;
        }   // end function filter_callback()

        /**
         * allows to reorder the $_FILES array if the 'multiple' attribute
         * was set on the file upload field; see
         * http://de1.php.net/manual/de/reserved.variables.files.php#109958
         * for details
         *
         * @access public
         * @param  array  $vector
         * @return array
         **/
        public function ArrayDiverse($vector) {
            $result = array();
            foreach($vector as $key1 => $value1)
                foreach($value1 as $key2 => $value2)
                    $result[$key2][$key1] = $value2;
            return $result;
        }   // end function ArrayDiverse()

        /**
         * encode all entries of an multidimensional array into utf8
         * http://de1.php.net/manual/de/function.json-encode.php#100492
         *
         * @access public
         * @param  array  $dat
         * @return array
         **/
        public static function ArrayEncodeUTF8($dat) // -- It returns $dat encoded to UTF8
        {
            if (is_string($dat)) return utf8_encode($dat);
            if (!is_array($dat)) return $dat;
            $ret = array();
            foreach($dat as $i=>$d) $ret[$i] = self::ArrayEncodeUTF8($d);
            return $ret;
        }   // end function ArrayEncodeUTF8()

        /**
         * filters an multidimensional array by given key, returns the filtered
         * elements
         *
         * This means, all elements that have a key $key with value $value will
         * be removed from &$array and returned as result
         *
         * @access public
         * @param  array  $array (reference!)
         * @param  string $key
         * @param  string $value
         * @return array
         **/
        public static function ArrayFilterByKey(&$array, $key, $value)
        {
            $result = array();
            foreach ($array as $k => $elem) {
                if (isset($elem[$key]) && $elem[$key] == $value) {
                    $result[] = $array[$k];
                    unset($array[$k]);
                }
            }
            return $result;
        }   // end function ArrayFilterByKey()
        

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
        }   // end function ArrayRemove()

        /**
         * sort an array
         *
         * @access public
         * @param  array   $array          - array to sort
         * @param  mixed   $index          - key to sort by
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
                     $temp[$key]=$array[$key][$index];
                 if(!$natsort)
                 {
                     ($order=='asc')? asort($temp) : arsort($temp);
                 }
                 else
                 {
                     ($case_sensitive)? natsort($temp) : natcasesort($temp);
                     if($order!='asc')
                         $temp=array_reverse($temp,TRUE);
                 }
                 foreach(array_keys($temp) as $key)
                     (is_numeric($key))? $sorted[]=$array[$key] : $sorted[$key]=$array[$key];
                 return $sorted;
            }
            return $array;
        }   // end function ArraySort()

        /**
         * search multidimensional array for $Needle
         *
         * @access public
         * @param  string  $Needle
         * @param  array   $Haystack
         * @param  string  $NeedleKey - optional
         * @param  boolean $Strict    - optional, default: false
         * @param  array   $Path      - needed for recursion
         * @return mixed   array (path) or false (not found)
         **/
        public static function ArraySearchRecursive( $Needle, $Haystack, $NeedleKey="", $Strict=false, $Path=array() )
        {

            if( ! is_array( $Haystack ) ) {
                return false;
            }
            reset($Haystack);
            foreach ( $Haystack as $Key => $Val ) {
                if (
                    is_array( $Val )
                    &&
                    $SubPath = self::ArraySearchRecursive($Needle,$Val,$NeedleKey,$Strict,$Path)
                ) {
                    $Path = array_merge($Path,Array($Key),$SubPath);
                    return $Path;
                }
                elseif (
                    ( ! $Strict && $Val  == $Needle && $Key == ( strlen($NeedleKey) > 0 ? $NeedleKey : $Key ) )
                    ||
                    (   $Strict && $Val === $Needle && $Key == ( strlen($NeedleKey) > 0 ? $NeedleKey : $Key ) )
                ) {
                    $Path[]=$Key;
                    return $Path;
                }
            }
            return false;
        }   // end function ArraySearchRecursive()
        
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
    		foreach ( $array as $key => $val )
            {
                if ( is_array($val) )
                {
                    $out[$key] = self::ArrayUniqueRecursive($val);
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
	}   // ----- end class CAT_Helper_Array -----
}