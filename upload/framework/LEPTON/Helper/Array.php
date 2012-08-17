<?php

/**
 *
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */

if ( ! class_exists( 'LEPTON_Helper_Array' ) )
{

    if ( ! class_exists( 'LEPTON_Object', false ) ) {
	    @include dirname(__FILE__).'/../Object.php';
	}
	
	class LEPTON_Helper_Array extends LEPTON_Object
	{

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
        public function ArraySort ( $array, $index, $order='asc', $natsort=FALSE, $case_sensitive=FALSE )
        {
            if( is_array($array) && count($array)>0 ) {
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
        }   // function ArraySort
        
        /**
         *
         *
         *
         *
         **/
        public function ArrayUniqueRecursive($array) {
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