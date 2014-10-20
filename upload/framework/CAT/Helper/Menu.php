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

include dirname(__FILE__).'/../../wblib/wbList.php';

if ( ! class_exists( 'CAT_Helper_Menu', false ) )
{
	class CAT_Helper_Menu extends CAT_Object
	{
        /**
         * enable/disable logging/debugging
         * 8 = off
         * 7 = debug
         **/
	    protected $_config
			= array(
                 'loglevel'             => 8,
			);

        /**
         * map menu options to wbList keys
         **/
        private static $_lbmap = array(
            'prefix'     => 'css_prefix',
            'first'      => 'first_li_class',
            'last'       => 'last_li_class',
            'child'      => 'has_child_li_class',
            'current'    => 'current_li_class',
            'open'       => 'is_open_li_class',
            'closed'     => 'is_closed_li_class',
            'list-class' => 'ul_class',
        );

        /**
         * wbList accessor
         **/
        private static $list = NULL;

        /**
         * holds local instance
         **/
        private static $instance;

        /**
         * menu number (if a template has several menus)
         **/
        private static $menu_no = NULL;

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
         * initialize wbList, so we don't need to do this more than once
         *
         * @access public
         * @return object
         **/
        public static function init_list()
        {
            if(!is_object(self::$list))
                self::$list = \wblib\wbList::getInstance(
                    array(
                        '__id_key'    => 'page_id',
                        '__title_key' => 'menu_title',
                        'create_level_css' => 'false',
                    )
                );
            return self::$list;
        }    // end function init_list()

        /**
         * Create a breadcrumb (path to current list item)
         *
         * @access public
         * @param  integer $id           - page ID
         * @param  integer $max_level    - max. level to show, default 999
         * @param  boolean $show_current - wether to include current page, default false
         * @param  array   $options      - optional array of additional options
         * @return string  HTML
         **/
        public static function breadcrumbMenu($id=NULL,$max_level=999,$show_current=false,array &$options = array())
        {
            global $page_id;
            if($id===NULL) $id = $page_id;
            if($id===0)    $id = CAT_Helper_Page::getRootParent($page_id);
            self::analyzeOptions($options);
            $menu       = array();
            $level      = CAT_Helper_Page::properties($id,'level');
            $level_diff = self::analyzeLevel($id,$max_level);
            $subpages   = array_reverse(CAT_Helper_Page::getPageTrail($id,false,true));

            foreach($subpages as $id)
            {
                $pg = CAT_Helper_Page::properties($id);
                if ( $max_level !== 999 && $pg['level'] < $max_level )
                    break;
                $menu[] = $pg;
            }

            $root_id = ( $pg['level'] > 0 ? $pg['page_id'] : 0 );
            // use wbList to create the menu
            //return CAT_Helper_ListBuilder::getInstance()->config('__auto_link',true)->tree($menu,$root_id);
            return self::$list->buildList($menu,array('root_id'=>$root_id));
        }   // end function breadcrumbMenu()
        
        /**
         * creates a full menu with all visible pages (like a sitemap)
         *
         * @access public
         * @return
         **/
        public static function fullMenu(array &$options = array())
        {
            global $page_id;
            self::analyzeOptions($options);
            $self       = self::getInstance();
            $pages      = self::$menu_no
                        ? CAT_Helper_Page::getPagesForMenu(self::$menu_no)
                        : CAT_Helper_Page::getPages()
                        ;
            return self::$list->buildList($pages,array('selected'=>$page_id));
        }   // end function fullMenu()

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
         * @param  integer $id           - parent page; default: current page
         * @param  mixed   $max_level    - see above; default: 999 (unlimited)
         * @param  boolean $show_current - show current page in the menu; default: false
         * @return string  HTML
         **/
        public static function subMenu($id=NULL,$max_level=999,$show_current=false,array &$options = array())
        {
            global $page_id;
            if($id===NULL) $id = $page_id;
            if($id===0)    $id = CAT_Helper_Page::getRootParent($page_id);
            self::analyzeOptions($options);

            $self       = self::getInstance();
            $menu       = array();
            $level_diff = self::analyzeLevel($id,$max_level);

            $self->log()->LogDebug(sprintf('levels to show [%d]',$level_diff));

            if($level_diff==1)
                $subpages = CAT_Helper_Page::getPagesByParent($id);
            else
                $subpages = CAT_Helper_Page::getSubPages($id);

            foreach($subpages as $sid)
                $menu[] = CAT_Helper_Page::properties($sid);

            // use ListBuilder to create the menu
            //return CAT_Helper_ListBuilder::getInstance()->config('__auto_link',true)->tree($menu,$id);
            return self::$list->buildList($menu,array('root_id'=>$id));
        }   // end function subMenu()

        /**
         * creates a menu of siblings for given page_id
         *
         * @access public
         * @param  integer $page_id
         * @return string
         **/
        public static function siblingsMenu($id=NULL,$max_level=999,$show_current=false,array &$options = array())
        {
            global $page_id;
            if($id===NULL) $id = $page_id;
            if($id===0)    $id = CAT_Helper_Page::getRootParent($page_id);
            self::analyzeOptions($options);
            $level    = CAT_Helper_Page::properties($id,'level');
            $menu     = CAT_Helper_Page::getPagesForLevel($level,self::$menu_no);
            $selected = $id;
            // if current page is not in the menu...
            if(!self::isInMenu($id,$menu))
            {
                $trail = CAT_Helper_Page::getPageTrail($page_id,false,true);
                foreach($trail as $id)
                {
                    if(false!==($i=self::isInMenu($id,$menu)))
                    {
                        $menu[$i]['is_open']    = true;
                        $menu[$i]['is_current'] = true;
                        $selected = $menu[$i]['page_id'];
                        break;
                    }
                }
            }
            //return CAT_Helper_ListBuilder::getInstance(false)->config(array('__auto_link' => true))->tree($menu,0,$selected);
            return self::$list->buildList($menu,array('root_id'=>0,'selected'=>$selected));
        }   // end function siblingsMenu()

        /**
         *
         * @access private
         * @return
         **/
        private static function isInMenu($id,&$menu)
        {
            $found   = false;
            foreach($menu as $i => $item)
            {
                if($item['page_id']===$id)
                {
                    $found = $i;
                    break;
                }
            }
            return $found;
        }   // end function isInMenu()
        

        /**
         * analyzes the given options and configures the ListBuilder
         *
         * @access private
         * @param  array   $options
         * @return object
         **/
        private static function analyzeOptions(array &$options = array())
        {
            $fixed         = array(); // reset temp array
            $lbopt         = array(); // reset list builder options
            self::$menu_no = NULL;    // reset current menu number
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
                if($key=='menu')
                {
                    self::$menu_no = $value;
                    continue;
                }
                if(isset(self::$_lbmap[$key]))
                {
                    $lbopt[self::$_lbmap[$key]] = $value;
                }
            }
            // pass options to Listbuilder
            //CAT_Helper_ListBuilder::getInstance()->config($lbopt);
            return self::init_list()->set($lbopt);
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
