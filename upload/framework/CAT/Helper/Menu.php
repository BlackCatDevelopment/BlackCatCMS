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

require_once CAT_PATH.'/modules/lib_wblib/wblib/wbList.php';

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
         * holds local instance
         **/
        private static $instance;
        /**
         * wbList accessor
         **/
        private static $list            = NULL;
        /**
         * this maps SM2 classes to wbList settings
         **/
        private static $sm2_classes     = array(
            'menu-current' => 'current_li_class',
            'menu_current' => 'current_li_class',
        );
        /**
         * this maps some settings to shorter aliases
         **/
        private static $alias_map       = array(
            'prefix'       => 'css_prefix',
            'first'        => 'first_li_class',
            'last'         => 'last_li_class',
            'child'        => 'has_child_li_class',
            'current'      => 'current_li_class',
            'open'         => 'is_open_li_class',
            'closed'       => 'is_closed_li_class',
        );

        /**
         * create a singular instance (for object oriented use)
         **/
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

        /**
         * for object oriented use
         **/
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
            if(!is_object(self::$list)) {
                self::$list = \wblib\wbList::getInstance();
            }
            // reset list to defaults
            self::$list->reset();
            self::$list->set(
                array(
                    '__id_key'         => 'page_id',
                    '__title_key'      => 'menu_title',
                    '__current_key'    => 'is_current',
                    'create_level_css' => false,
                )
            );
            return self::$list;
        }    // end function init_list()

        /**
         * creates a breadcrumb menu (path to current page)
         *
         * @access public
         * @param  integer  $pid     - page_id
         * @param  array    $options - optional
         * @return string
         **/
        public static function breadcrumbMenu(array &$options = array())
        {
            self::checkPageId($pid);
            self::checkOptions($options);
            $self = self::getInstance();
            $self->log()->LogDebug(sprintf('create a breadcrumbMenu for page with id [%s]',$pid));
            $self->log()->LogDebug('options:',$options);
            $menu       = array();
            // get the level of the current page
            $level    = CAT_Helper_Page::properties($pid,'level');
            // get the path
            $subpages = array_reverse(CAT_Helper_Page::getPageTrail($pid,false,true));
            // add the pages to the menu
            foreach($subpages as $id)
            {
                $pg = CAT_Helper_Page::properties($id);
                $menu[] = $pg;
            }
            // check if the current page should be shown
            if(!isset($options['show_current']) || !$options['show_current'])
                array_shift($menu); // remove last item = current page
            $self->log()->LogDebug('pages:',$menu);
            // set root id to the root parent to make the listbuilder work
            $options['root_id'] = CAT_Helper_Page::getRootParent($pid);
            // return the menu
            return self::$list->buildList($menu,$options);
        }   // end function breadcrumbMenu()
        
        /**
         * creates a full menu with all visible pages (like a sitemap)
         *
         * @access public
         * @param  integer  $menu_number - default NULL means all pages
         * @param  array    $options     - optional
         * @return string
         **/
        public static function fullMenu($menu_number=NULL,array &$options = array())
        {
            $pid = NULL;
            self::checkPageId($pid);
            self::checkOptions($options);
            $menu = $menu_number
                  ? CAT_Helper_Page::getPagesForMenu($menu_number)
                  : CAT_Helper_Page::getPages()
                  ;
// -----------------------------------------------------------------------------
// ----- !!!FIX ME!!! ----------------------------------------------------------
            //$options['root_id'] = CAT_Helper_Page::getRootParent($pid);
            $options['root_id'] = 0;
// -----------------------------------------------------------------------------
            return self::$list->buildList($menu,$options);
        }   // end function fullMenu()

        /**
         * creates a siblings menu for given page_id (pages on same level)
         *
         * the menu number is derived from the given page
         *
         * @access public
         * @param  integer  $pid     - page id
         * @param  array    $options - optional
         * @return string
         **/
        public static function siblingsMenu($pid=NULL,array &$options = array())
        {
            self::checkPageId($pid);
            self::checkOptions($options);
            $self = self::getInstance();
            $self->log()->LogDebug(sprintf('create a siblingsmenu for page with id [%s]',$pid));
            $self->log()->LogDebug('options:',$options);
            // get the menu number
            $menu_no  = CAT_Helper_Page::properties($pid,'menu');
            // get the level of the current/given page
            $level    = CAT_Helper_Page::properties($pid,'level');
            // pages
            $menu     = CAT_Helper_Page::getPagesForLevel($level,$menu_no);
            $self->log()->LogDebug('pages:',$menu);
            // set root id to the parent page to make the listbuilder work
            $options['root_id'] = CAT_Helper_Page::properties($pid,'parent');
            // return the menu
            return self::$list->buildList($menu,$options);
        }   // end function siblingsMenu()
        
        /**
         * creates a sub menu for given page_id (children of that page)
         *
         * @access public
         * @param  integer  $pid     - page id
         * @param  array    $options - optional
         * @return string
         **/
        public static function subMenu($pid=NULL,array &$options = array())
        {
            self::checkPageId($pid);
            self::checkOptions($options);
            // get the pages
            $pages = CAT_Helper_Page::getSubPages($pid);
            // add current page to menu
            $menu  = array(CAT_Helper_Page::properties($pid));
            if(isset($options['levels']))
            {
                $maxlevel = $menu[0]['level'] + $options['levels'];
                if(!$maxlevel) $maxlevel = 1;
                self::$list->set('maxlevel',$maxlevel);
            }
            // add the pages
            foreach($pages as $sid)
                $menu[] = CAT_Helper_Page::properties($sid);
            // set the root id to the current page
            $options['root_id'] = $pid;
            // return the menu
            return self::$list->buildList($menu,$options);
        }   // end function subMenu()

        /**
         * analyzes the passed options and converts them for wbList
         * initializes wbList with the given options
         *
         * @access protected
         * @param  array     $options
         * @return void
         **/
        protected static function checkOptions(array &$options = array())
        {
            $lbopt = array();
            while ( $opt = array_shift($options) )
            {
                if(preg_match('~^(.+?)\:$~',$opt,$m))
                {
                    $key   = $m[1];
                    $value = array_shift($options);
                    if(array_key_exists($key,self::$sm2_classes))
                        $key = self::$sm2_classes[$key];
                    if(array_key_exists($key,self::$alias_map))
                        $key = self::$alias_map[$key];
                    $lbopt[str_replace('-','_',$key)] = $value;
                    continue;
                }
                list($key,$value) = explode( ':', $opt );
                if(array_key_exists($key,self::$sm2_classes))
                    $key = self::$sm2_classes[$key];
                if(array_key_exists($key,self::$alias_map))
                    $key = self::$alias_map[$key];
                $lbopt[str_replace('-','_',$key)] = $value;
            }
            self::init_list()->set($lbopt);
            $options = $lbopt;
        }   // end function checkOptions()

        /**
         * makes sure that we have a valid page id; the visibility does not
         * matter here
         *
         * @access protected
         * @param  integer   $id (reference!)
         * @return void
         **/
        protected static function checkPageId(&$pid=NULL)
        {
            global $page_id;
            if($pid===NULL) $pid = $page_id;
            if($pid===0)    $pid = CAT_Helper_Page::getRootParent($page_id);
        }   // end function checkPageId()
    }
}
