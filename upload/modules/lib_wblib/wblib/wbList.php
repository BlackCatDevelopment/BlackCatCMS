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
 *   @package      wbList
 *   @author       BlackBird Webprogrammierung
 *   @copyright    (c) 2014, 2015 BlackBird Webprogrammierung
 *   @license      GNU LESSER GENERAL PUBLIC LICENSE Version 3
 *
 **/

namespace wblib;

/**
 * language (internationalization) handling class
 *
 * @category   wblib
 * @package    wbList
 * @copyright  Copyright (c) 2014, 2015 BlackBird Webprogrammierung
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
 */
if(!class_exists('wblib\wbList',false))
{

    class wbList {

        /**
         * instance
         **/
        public static  $instance     = NULL;
        /**
         * logger
         **/
        private static $analog       = NULL;
        /**
         * log level
         **/
        public  static $loglevel     = 8;
        /**
         * array to store open nodes
         **/
        private static $open_nodes   = array();
        /**
         * array of default options
         **/
        public  static $defaults     = array();
        /**
         * counter for open lists; will be reset when reset() is called!
         **/
        private static $open_lists   = 0;
        /**
         * this will be set to false after the first item was added
         **/
        private static $isfirst      = true;
        /**
         * allows to create li ids
         **/
        private static $_id          = 0;

        /**
         * constructor
         **/
        function __construct ( $options = array() )
        {
/*
            if ( isset( self::$defaults['__nodes_cookie_name'] ) )
            {
                // get open nodes
                self::$open_nodes
                    = isset( $_COOKIE[self::$defaults['__nodes_cookie_name']] )
                    ? explode( ',', $_COOKIE[self::$defaults['__nodes_cookie_name']] )
                    : array();
                self::log(sprintf(
                    'open nodes from cookie key [%s]:',self::$defaults['__nodes_cookie_name'],var_export(self::$open_nodes,1)
                ));
            }
*/
            self::reset();
            if(count($options))
            {
                self::set($options);
            }
        }   // end function __construct()

        // no cloning!
        private function __clone() {}

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
         * Create an instance; this will also reset the defaults
         *
         * @access public
         * @param  array   $options    - OPTIONAL
         * @return object
         **/
        public static function getInstance(array $options=array())
        {
            if ( !is_object(self::$instance) )
            {
                self::$instance = new self($options);
            }
            return self::$instance;
        }   // end function getInstance()

        /**
         *
         * This function creates a nested list from a flat array using an
         * iterative loop
         *
         * @access public
         * @param  array  $list    - flat array
         * @param  int    $root_id - id of the root element
         * @param  array  $options - additional options
         * @return string HTML
         *
         * Based on code found here:
         * http://codjng.blogspot.com/2010/10/how-to-build-unlimited-level-of-menu.html
         *
         **/
        public static function buildList($list,$options=array())
        {
            // let the caller handle errors
            if ( empty($list) || ! is_array($list) )
            {
                self::log('no list items to show!',4);
                return false;
            }

            $root_id    = ( isset($options['root_id'])             ? $options['root_id']             : 0     );
            $hidden     = ( isset(self::$defaults['__hidden_key']) ? self::$defaults['__hidden_key'] : ''    );
            $type       = ( isset($options['type'])                ? $options['type']                : ''    );
            $maxlevel   = ( isset($options['maxlevel'])            ? $options['maxlevel']            : self::$defaults['maxlevel'] );
            $startlevel = ( isset($options['startlevel'])          ? $options['startlevel']          : 0     );

            $p_key      = self::$defaults['__parent_key'];
            $id_key     = self::$defaults['__id_key'];
            $l_key      = self::$defaults['__level_key'];

            // create a list of children for each page; skip hidden items and
            // items that are outside given maxlevel
            $children   = array();
            foreach ( $list as $item )
            {
                if(isset($item[$hidden])) continue; // skip hidden items
                if(isset($item[$l_key]) && $item[$l_key] > $maxlevel ) continue; // skip items outside max level
                $children[$item[$p_key]][] = $item; // add to children array
            }
            // mark the last child
            foreach ( $list as $item )
            {
                if(isset($children[$item[$p_key]]))
                {
                    end($children[$item[$p_key]]);
                    $key = key($children[$item[$p_key]]);
                    $children[$item[$p_key]][$key]['is_last'] = true;
                    reset($children[$item[$p_key]]);
                }
            }

            self::log(sprintf('start level [%s], max. level [%s], total item count [%s]',$startlevel, $maxlevel,count($list)),7);
            self::log('children list: '.str_replace(' ', '    ',var_export($children,1)),7);

            // loop will be false if the root has no children (i.e., an empty menu!)
            $loop      = !empty( $children[$root_id] );
            self::log(sprintf('loop      [%s] (if this is empty there will be no list!)',$loop),7);

            if(!$loop) return false;

            // some more options
            #$type         = ( isset($options['type'])                ? $options['type']                : 'ul'  );
            $ul_id        = ( isset($options['ul_id'])    ? $options['ul_id']    : NULL                        );

            $parent       = $root_id; // initializing $parent as the root
            $parent_stack = array();
            $html         = array();  // output array
            $items        = array();  // for type select
            $lastchild    = false;

            // open the root list
            $html[] = self::listStart($ul_id);

            while ( $loop && ( ( count($children[$parent]) > 0 || ( $parent <> $root_id ) ) ) )
            {
                if(!strlen(key($children[$parent]))) break;
	            $option	= array(
		        	'key'	=> key($children[$parent]),
		        	'value'	=> current($children[$parent])
	            );
				array_shift($children[$parent]);
                // ----- no more items -----
                if ( $option === false )
                {
                    $parent = array_pop($parent_stack); // move to next parent
                    if($type!='select')
                    {
                        $html[]  = self::listEnd();
                        $html[]  = str_repeat(self::$defaults['space'],self::$open_lists).self::itemEnd();
                    }
                }
                // ----- handle item with children -----
                elseif(!empty($children[$option['value'][$id_key]]))
                {
                    $item   = $option['value'];
                    if(!isset($item[$l_key]) || $item[$l_key]>=$startlevel)
                    {
                        if($type!='select')
                        {
                            if(!isset($item[self::$defaults['__children_key']]))
                            {
                                $item[self::$defaults['__children_key']] = true;
                            }
                            // get the css classes
                            $css    = self::getListItemCSS($item,$lastchild);
                            // add the item first
                            $html[] = self::itemStart($css) . self::getListItemText($item);
                            // open sub list for the children; create unique ID
                            $html[] = self::listStart($ul_id.'_'.$item[$l_key].'_'.self::getID());
                        }
                        else
                        {
                            $items[] = $item;
                        }
                    }
                    // push parent to parent stack
                    array_push( $parent_stack, $item[$p_key] );
                    $parent = $item[$id_key];
                }
                // ----- handle leaf (item with no children) -----
                else {
                    $item   = $option['value'];
                    if(!isset($item[$l_key]) || $item[$l_key]>=$startlevel)
                    {
                        if($type!='select')
                        {
                            // get the css classes
                            $css    = self::getListItemCSS($item);
                            // add the item first
                            $html[] = self::itemStart($css)
                                    . self::getListItemText($item)
                                    . self::itemEnd();
                        }
                        else
                        {
                            $items[] = $item;
                        }
                    }
                }
            }   // end while()

            // HTML wrapper for the menu (close)
            if($type!='select')
            {
                $html[] = self::listEnd();
                $output = str_ireplace( '{{lastcss}}', '', implode("\r\n",$html) );
                return $output;
            }

            return $items;
        }   // end function buildList()

        /**
         * this is a convenience method; creates a <select> for the given list
         *
         * you can use buildList() with option 'type' => 'select' instead
         *
         * @access public
         * @param  array  $list
         * @param  array  $options
         * @return string
         **/
        public static function buildSelect($list, $options = array())
        {
            if(!count(self::$defaults)) self::reset();
            $options['type']     = 'select';
            $options['as_array'] = true;

            $space  = ( isset($options['space']) ? $options['space'] : self::$defaults['space'] );
            $output = self::buildList($list,$options);
            $items  = array();

            if ( isset($options['options_only']) && $options['options_only'] )
            {
                $return = array();
                foreach(array_values($output) as $item)
                {
                    $return[$item[self::$defaults['__id_key']]]
                        = str_repeat($space,$item[self::$defaults['__level_key']])
                        . $item[self::$defaults['__title_key']];
                }
                return $return;
            }
            else
            {
                $items   = array();
                $sel     = isset($options['selected'])
                         ? $options['selected']
                         : NULL
                         ;
                foreach(array_values($output) as $item)
                {
                    $items[] = self::selectOption(
                        $item[self::$defaults['__id_key']],
                        (($item[self::$defaults['__id_key']]==$sel)?'selected="selected" ':''),
                        (isset($item[self::$defaults['__level_key']]) ? (str_repeat($space,$item[self::$defaults['__level_key']])) : ''),
                        $item[self::$defaults['__title_key']]
                    );
                }
            }

            $name   = isset($options['name'])
                    ? $options['name']
                    : self::generateRandomString();

            return self::selectStart($name)
		         . join( "\n\t", $items )."\n"
                 . self::selectEnd()
                 ;

        }   // end function buildSelect()

        /**
         * opens a new list
         *
         * @access public
         * @param  string  $ul_id   - defaults to empty
         * @param  array   $options - optional
         * @return string
         **/
        public static function listStart($ul_id=NULL,array $options = array())
        {
            self::log(sprintf('listStart() ID [%s] open list count [%s]',$ul_id,self::$open_lists),7);
            if(self::$open_lists == 0)
            {
                $tpl     = self::$defaults['top_list_open'];
                $classes = array(self::$defaults['top_ul_class']);
            }
            else
            {
                $tpl     = self::$defaults['list_open'];
                $classes = array(self::$defaults['ul_class']);
            }

            self::log(sprintf('using tpl [%s], css class [%s]',$tpl,$classes[0]),7);

            // create a CSS class for each level?
            if (isset(self::$defaults['create_level_css']) && self::$defaults['create_level_css'] === true )
            {
                self::log('adding level css');
                $classes[] = self::$defaults['css_prefix']
                           . self::$defaults['ul_class']
                           . '_' . (self::$open_lists + 1);
            }

            $output = str_repeat(self::$defaults['space'],self::$open_lists)
                    . str_replace(
                          array(
                              '%%id%%',
                              '%%class%%',
                          ),
                          array(
                              $ul_id,
                              implode(' ',$classes)
                          ),
                          $tpl
                      );
            $output = str_replace( ' id=""', '', $output ); // remove empty id-attribute

            self::$open_lists++;

            self::log('returning [ '.$output.' ]',7);

            return $output;
        }   // end function listStart()

        /**
         * close list
         *
         * @access public
         * @return string
         **/
        public static function listEnd()
        {
            if(!self::$open_lists > 0)
                return;
            self::$open_lists--;
            $output = self::$defaults['list_close'];
            return str_repeat(self::$defaults['space'],self::$open_lists).$output;
        }   // end function listEnd()

        /**
         * open item
         *
         * @access public
         * @param  string  $css   - css classes
         * @return string
         **/
        public static function itemStart($css=NULL)
        {
            $li_id = ( self::$defaults['create_li_id'] ? self::$defaults['li_id_prefix'].self::getID() : NULL );
            self::log(sprintf('itemStart() li id [%s] css classes [%s]',$li_id,$css),7);
            $start = str_replace(
                array( '%%id%%', '%%class%%' ),
                array( $li_id  , $css        ),
                self::$defaults['item_open']
            );
            // remove empty id-attribute
            $start = str_replace( ' id=""', '', $start );
            self::log('setting isfirst to false',7);
            self::$isfirst = false;
            return str_repeat(self::$defaults['space'],self::$open_lists).$start;
        }   // end function itemStart()

        /**
         * close item
         *
         * @access public
         * @return string
         **/
        public static function itemEnd()
        {
            return self::$defaults['item_close'];
        }   // end function itemEnd()

        /**
         * figure out the css classes for the current list item
         *
         * @access public
         * @param  array  $item - current item
         * @return string
         **/
        public static function getListItemCSS(array $item = array())
        {
            self::log('getListItemCSS() item:'.var_export($item,1),7);
            self::log(sprintf('self::$isfirst [%s]',self::$isfirst),7);
            $classes = array();

            // check the current item for options
            $options = array(
                'level'        => ( isset($item[self::$defaults['__level_key']])    ? $item[self::$defaults['__level_key']]    : 0     ),
                'in_trail'     => ( isset($item[self::$defaults['__trail_key']])    ? $item[self::$defaults['__trail_key']]    : false ),
                'is_selected'  => ( isset($item[self::$defaults['__current_key']])  ? $item[self::$defaults['__current_key']]  : false ),
                'is_open'      => ( isset($item[self::$defaults['__is_open_key']])  ? $item[self::$defaults['__is_open_key']]  : false ),
                'has_children' => ( isset($item[self::$defaults['__children_key']]) ? $item[self::$defaults['__children_key']] : false ),
                'is_last'      => ( isset($item['is_last'])                         ? true                                     : false ),
            );
            self::log('getListItemCSS() options:'.var_export($options,1),7);

            // default for all items
            if(isset(self::$defaults['li_class']) && strlen(self::$defaults['li_class']))
                $classes[] = self::$defaults['css_prefix'] . self::$defaults['li_class'];
            // special CSS class for each level?
            if ( isset(self::$defaults['create_level_css']) && self::$defaults['create_level_css'] && isset(self::$defaults['li_class']) && strlen(self::$defaults['li_class']))
                $classes[] = self::$defaults['css_prefix'] . self::$defaults['li_class'] . '_level_' . $options['level'];
            // first element
            if( self::$isfirst                                             && isset(self::$defaults['first_li_class'])     && strlen(self::$defaults['first_li_class']))
                $classes[] = self::$defaults['css_prefix'] . self::$defaults['first_li_class'];
            // element is in trail
            if(isset($options['in_trail'])     && $options['in_trail']     && isset(self::$defaults['trail_li_class'])     && strlen(self::$defaults['trail_li_class']))
                $classes[] = self::$defaults['css_prefix'] . self::$defaults['trail_li_class'];
            // element has children
            if(isset($options['has_children']) && $options['has_children'] && isset(self::$defaults['has_child_li_class']) && strlen(self::$defaults['has_child_li_class']))
                $classes[] = self::$defaults['css_prefix'] . self::$defaults['has_child_li_class'];
            // element is open
            if(isset($options['is_open'])      && $options['is_open']      && isset(self::$defaults['is_open_li_class'])   && strlen(self::$defaults['is_open_li_class']))
                $classes[] = self::$defaults['css_prefix'] . self::$defaults['is_open_li_class'];
            // markup for current element
            if(isset($options['is_selected'])  && $options['is_selected']  && isset(self::$defaults['current_li_class'])   && strlen(self::$defaults['current_li_class']))
                $classes[] = self::$defaults['css_prefix'] . self::$defaults['current_li_class'];
            // last element
            if(isset($options['is_last'])      && $options['is_last']      && isset(self::$defaults['last_li_class'])      && strlen(self::$defaults['last_li_class']))
                $classes[] = self::$defaults['css_prefix'] . self::$defaults['last_li_class'];
            $result = implode(' ',$classes);
            self::log(sprintf('returning list item css [%s]',$result),7);
            return $result;
        }   // end function getListItemCSS()

        /**
         *
         * @access public
         * @return
         **/
        public static function getListItemText(array $item = array())
        {
            $text = ( isset($item[self::$defaults['__title_key']]) ? $item[self::$defaults['__title_key']] : NULL );
            if(isset($item[self::$defaults['__href_key']]))
            {
                // check if it's already a link
                if( ! preg_match( '~^\<a href~i', $item[self::$defaults['__href_key']] ) )
                {
                    $text = str_replace(
                        array(
                            '%%href%%',
                            '%%class%%',
                            '%%text%%'
                        ),
                        array(
                            $item[self::$defaults['__href_key']],
                            ( strlen(self::$defaults['link_class']) ? 'class='.self::$defaults['link_class'] : '' ),
                            $text
                        ),
                        self::$defaults['href']
                    );
                }
                else
                {
                    $text = $item[self::$defaults['__href_key']];
                }
            }
            return str_replace('%%text%%',$text,self::$defaults['item']);
        }   // end function getListItemText()

        /**
         * opens a <select> box with given $name
         *
         * @access private
         * @param  string  $name  - name for the select field
         * @param  int     $level - ignored
         * @return string
         **/
        private static function selectStart($name=NULL, $level=NULL)
        {
            return str_replace(
                       array( '%%id%%', '%%'                            ),
                       array( $name   , self::$defaults['select_class'] ),
                       self::$defaults['select_open']
                   );
        }   // end function selectStart()

        /**
         * closes a <select>
         *
         * @access private
         * @return string
         **/
        private static function selectEnd()
        {
            return self::$defaults['select_close'];
        }   // end function closeSelect()

        /**
         * creates an <option> element
         *
         * @access private
         * @param  string  $value
         * @param  string  $sel
         * @param  string  $tab
         * @param  string  $text
         * @return string
         **/
        private static function selectOption($value,$sel,$space,$text,$class=NULL)
        {
            $text    = $space . ' ' . $text;
            $option  = str_replace(
                array( '%%value%%', '%%selected%%', '%%text%%', '%%'   ),
                array( $value     , $sel          , $text     , $class ),
                self::$defaults['select_option']
            );
            return str_replace('class="" ','',$option);
        }   // end function selectOption()


        /**
         * accessor to Analog (if installed)
         *
         * Note: Log messages are ignored if no Analog is available!
         *
         * @access private
         * @param  string   $message
         * @param  integer  $level
         * @return
         **/
        private static function log($message, $level = 3)
        {
            if($level<>self::$loglevel) return;
            if( !self::$analog && !self::$analog == -1)
            {
                if(file_exists(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php'))
                {
                    include_once(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php');
                    wblib_init_3rdparty(dirname(__FILE__).'/debug/','wbList',self::$loglevel);
                    self::$analog = true;
                }
                else
                {
                    self::$analog = false;
                }
            }
            if ( self::$analog )
                \Analog::log($message,$level);
        }   // end function log()

        /**
         * build multilevel (recursive) array from flat one; will add the
         * children of an item to __children_key array key
         *
         * @access  public
         * @param   array   $items - flat array (reference!)
         * @param   number  $min   - min level to show
         * @return  array
         **/
        public static function buildRecursion ( &$items, $min = -9 )
        {
            // check if $items is an array
            if(!empty($items) && !is_array($items))
            {
                return NULL;
            }
            // if there's only one item, no recursion to do
            if ( !(count($items) > 1))
            {
                return $items;
            }

            $tree    = array();
            $root_id = -1;
            $self    = self::getInstance();

            // check if the $items array is already multi-dimensional
            if(array_key_exists(self::$defaults['__children_key'],$items))
                return $items;

            // spare some typing...
            $ik      = self::$defaults['__id_key'];
            $pk      = self::$defaults['__parent_key'];
            $ck      = self::$defaults['__children_key'];
            $lk      = self::$defaults['__level_key'];

            // make sure that the $items array is indexed by the __id_key
            $arr     = array();
            foreach ( $items as $index => $item ){
                $arr[$item[$ik]] = $item;
            }
            $items = $arr;

            //
            // this creates an array of parents with their associated children
            //
            // -----------------------------------------------
            // REQUIRES that the array index is the parent ID!
            // -----------------------------------------------
            //
            // http://www.tommylacroix.com/2008/09/10/php-design-pattern-building-a-tree/
            //
            foreach($items as $id => &$node)
            {
                // skip nodes with depth < min level
                if(isset($node[$lk]) && $node[$lk] <= $min)
                {
                    continue;
                }

                // avoid error messages on missing parent key
                if(!isset($node[$pk]))
                {
                    $node[$pk] = null;
                }

                // root node
                if($node[$pk] === null && $root_id < 0)
                {
                    $tree[$id] = &$node;
                    $root_id   = $id;
                }
                // sub node
                else
                {
                    // avoid warnings on missing children key
                    if(!isset($items[$node[$pk]][$ck]) || !is_array($items[$node[$pk]][$ck]))
                    {
                        $items[$node[$pk]][$ck] = array();
                    }
                    $items[$node[$pk]][$ck][] = &$node;
                }
            }

            if(!empty($tree) && is_array($tree) && count($tree) > 0)
            {
                if(isset($tree[$root_id]))
                    $tree = $tree[$root_id][$ck];
            }

            return $tree;

        }   // end function buildRecursion ()

        public static function generateRandomString($length=10)
        {
            for(
                   $code_length = $length, $newcode = '';
                   strlen($newcode) < $code_length;
                   $newcode .= chr(!rand(0, 2) ? rand(48, 57) : (!rand(0, 1) ? rand(65, 90) : rand(97, 122)))
            );
            return $newcode;
        }

        /**
         *
         * @access public
         * @return
         **/
        public static function getID()
        {
            self::$_id++;
            return self::$_id;
        }   // end function getID()

        /**
         * change options
         *
         * @access public
         * @param  array  $options
         * @return void
         **/
        public static function set($options, $val = NULL)
        {
            if(isset($val) && !is_null($val) && is_string($options))
            {
                if(isset(self::$defaults[$options]))
                {
                    self::$defaults[$options] = $val;
                }
                return;
            }
            if(is_array($options) || count($options))
            {
                foreach($options as $key => $value)
                {
                    if(isset(self::$defaults[$key]))
                    {
                        self::$defaults[$key] = $value;
                    }
                }
            }
        }   // end function set()

        /**
         * sort array by children
         **/
        public static function sort($list, $root_id)
        {

            if ( empty($list) || ! is_array( $list ) || count($list) == 0 )
                return NULL;

            $self      = self::getInstance();
            $return    = array();
            $children  = array();
            $p_key     = self::$defaults['__parent_key'];
            $id_key    = self::$defaults['__id_key'];

            // create a list of children for each item
            foreach ( $list as $item ) {
                $children[$item[$p_key]][] = $item;
            }

            // loop will be false if the root has no children
            $loop         = !empty( $children[$root_id] );

            // initializing $parent as the root
            $parent       = $root_id;
            $parent_stack = array();

            while ( $loop && ( ( $option = each( $children[$parent] ) ) || ( $parent > $root_id ) ) )
            {
                if ( $option === false ) // no more children
                {
                    $parent = array_pop( $parent_stack );
                }
                // current item has children
                elseif ( ! empty( $children[ $option['value'][$id_key] ] ) )
                {
                    if(!isset($option['value']['__children']))
                        $option['value']['__children'] = count($children[ $option['value'][$id_key] ]);
                    $return[] = $option['value'];
                    array_push( $parent_stack, $option['value'][$p_key] );
                    $parent = $option['value'][$id_key];
                }
                else {
                    if(!isset($option['value']['__children']))
                        $option['value']['__children'] = 0;
                    $return[] = $option['value'];
                }
            }
            return $return;
        }

        /**
         * reset to the defaults
         *
         * @access public
         * @return void
         **/
        public static function reset()
        {
            self::$open_lists = 0;
            self::$defaults = array(
                // ***** keys in the input array *****
                '__children_key'        => 'children',
                '__current_key'         => 'current',
                '__hidden_key'          => 'hidden',
                '__href_key'            => 'href',
                '__id_key'              => 'id',
                '__is_open_key'         => 'is_open',
                '__level_key'           => 'level',
                '__more_info_key'       => '',
                '__nodes_cookie_name'   => NULL,
                '__parent_key'          => 'parent',
                '__title_key'           => 'title',
                '__trail_key'           => 'is_in_trail',
                // ***** output templates *****
                'top_list_open'         => '<ul id="%%id%%" class="%%class%%">',
                'top_list_close'        => '</ul>',
                'list_open'             => '<ul id="%%id%%" class="%%class%%">',
                'list_close'            => '</ul>',
                'item_open'             => '<li id="%%id%%" class="%%class%%{{lastcss}}">',
                'item_close'            => '</li>',
                'item'                  => '%%text%%',
                'select_open'           => '<select name="%%id%%" id="%%id%%" class="%%">',
                'select_close'          => '</select>',
                'select_option'         => '<option class="%%" value="%%value%%"%%selected%%>%%text%%</option>',
                'more_info'             => '<span class="more_info">%%</span>',
                'href'                  => '<a href="%%href%%"%%class%%>%%text%%</a>',
                // ***** global options *****
                'create_level_css'      => true,
                'create_li_id'          => false,
                'space'                 => '    ',
                'max_recursion'         => 15,
                'maxlevel'              => 999,
                // ***** css options *****
                'css_prefix'            => '',

                'ul_id_prefix'          => 'ul_',
                'top_ul_class'          => 'list',
                'ul_class'              => 'sublist',

                'li_class'              => 'item',
                'first_li_class'        => 'first_item',
                'last_li_class'         => 'last_item',
                'current_li_class'      => 'current_item',
                'has_child_li_class'    => 'has_child',
                'is_open_li_class'      => 'is_open',
                'is_closed_li_class'    => 'is_closed',
                'trail_li_class'        => 'trail_item',
                'select_class'          => '',
                'li_id_prefix'          => 'li_',

                'link_class'            => '',

            );
        }   // end function reset()

    }   // ----- end class wbList -----

}       // ----- end if(!class_exists('wblib\wbList',false)) -----
