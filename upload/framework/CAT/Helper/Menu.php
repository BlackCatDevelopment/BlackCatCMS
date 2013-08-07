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

if ( ! class_exists( 'CAT_Helper_Menu', false ) ) {
	class CAT_Helper_Menu extends CAT_Object
	{
	    protected $_config
			= array(
                 'loglevel'             => 7,
			);

        private static $_lbmap = array(
            'first'   => '__li_first_item_class',
            'last'    => '__li_last_item_class',
            'child'   => '__li_has_child_class',
            'current' => '__li_is_open_class',
            'closed'  => '__li_is_closed_class',
        );

        private static $instance;

        public static function getInstance($reset=false)
        {
            if (!self::$instance)
            {
                self::$instance = new self();
                $reset = true;
            }
            if($reset) self::$instance->reset();
            return self::$instance;
        }   // end function getInstance()

        public function __call($method, $args)
            {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }   // end function __call()

        /**
         *
         * @access public
         * @return
         **/
        public static function breadcrumbMenu($page_id=0,$max_level=999,array &$options = array())
        {
            self::analyzeOptions($options);
            $menu       = array();
            $level      = CAT_Helper_Page::properties($page_id,'level');
            $level_diff = self::analyzeLevel($page_id,$max_level);
            $subpages   = array_reverse(CAT_Helper_Page::getPageTrail($page_id,false,true));
            foreach($subpages as $id)
            {
                $pg = CAT_Helper_Page::properties($id);
// ---- ACHTUNG DAS IST NOCH NICHT RICHTIG HIER! FUNZT NICHT MIT $max_level = +1! -----
                if ( $max_level !== 999 && $pg['level'] < $max_level )
                    break;
                $menu[] = $pg;
            }
echo "last page level: ", $pg['level'], "<br />";
echo "<textarea cols=\"100\" rows=\"20\" style=\"width: 100%;\">";
print_r( $pg );
echo "</textarea>";
            $root_id = ( $pg['level'] > 0 ? $pg['page_id'] : 0 );
            // use ListBuilder to create the menu
            return CAT_Helper_ListBuilder::getInstance()->config('__auto_link',true)->tree($menu,$root_id);
        }   // end function breadcrumbMenu()
        

        /**
         * creates a sub menu for given page_id (children of that page)
         *
         * $max_level may be:
         *   + absolute maximal level to be shown (i.e. '7')
         *     example: level for $page_id is 5, $max_level is 7 = show 2 levels
         *   + relative level to be shown (i.e. '+2')
         *     example: level for $page_id is 5, $max_level is '+3' = show 3 levels
         *
         * @access public
         * @param  integer $page_id   - parent page
         * @param  mixed   $max_level - see above; default: 999 (unlimited)
         * @return string
         **/
        public static function subMenu($page_id=0,$max_level=999,array &$options = array())
        {
            self::analyzeOptions($options);
            $menu       = array();
            $level_diff = self::analyzeLevel($page_id,$max_level);
            // one level only is easy, we have a function for this...
            if($level_diff==1)
            {
                $subpages = CAT_Helper_Page::getPagesByParent($page_id);
            }
            else
            {
                $subpages = CAT_Helper_Page::getSubPages($page_id);
            }
            foreach($subpages as $id)
                $menu[] = CAT_Helper_Page::properties($id);
            // use ListBuilder to create the menu
            return CAT_Helper_ListBuilder::getInstance()->config('__auto_link',true)->tree($menu,$page_id);
        }   // end function subMenu()

        /**
         * creates a menu of siblings for given page_id
         *
         * @access public
         * @param  integer $page_id
         * @return string
         **/
        public static function siblingsMenu($page_id=0,array &$options = array())
        {
            self::analyzeOptions($options);
            $level   = CAT_Helper_Page::properties($page_id,'level');
            $menu    = CAT_Helper_Page::getPagesForLevel($level);
            return CAT_Helper_ListBuilder::getInstance()->config(
                    array(
                        '__auto_link' => true,
                    )
                )->tree($menu,0,$page_id);
        }   // end function siblingsMenu()

        /**
         *
         * @access private
         * @return
         **/
        private static function analyzeOptions(array &$options = array())
        {
            $fixed = array();
            $lbopt = array();
            while ( $opt = array_shift($options) )
            {
                if(preg_match('~^(.+?)\:$~',$opt,$m))
                {
                    $value = array_shift($options);
                    $fixed[$m[1]] = $value;
                    continue;
                }
                list($key,$value) = explode( ':', $opt );
                $fixed[$key] = $value;
            }
            foreach($fixed as $key => $value)
            {
                if(isset(self::$_lbmap[$key]))
                    $lbopt[self::$_lbmap[$key]] = $value;
            }
            // pass options to Listbuilder
            CAT_Helper_ListBuilder::getInstance()->config($lbopt);
        }   // end function analyzeOptions()
        
        /**
         *
         * @access private
         * @return
         **/
        private static function analyzeLevel($page_id,$max_level=999)
        {
            $level = CAT_Helper_Page::properties($page_id,'level');
            // figure out max depth to show
            if( $max_level!==999 )
            {
                // handle '+X' $max_level value
                if( preg_match('~^\+(\d+)$~',$max_level,$m) )
                    $max_level  = $level + $m[1];
                    return ( $max_level - $level );
            }
            return 999;
        }   // end function analyzeLevel()
        
        
    }
}
