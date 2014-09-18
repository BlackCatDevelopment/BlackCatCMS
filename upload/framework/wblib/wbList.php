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
 *   @copyright    (c) 2014 BlackBird Webprogrammierung
 *   @license      GNU LESSER GENERAL PUBLIC LICENSE Version 3
 *
 **/

namespace wblib;

/**
 * language (internationalization) handling class
 *
 * @category   wblib
 * @package    wbList
 * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
 */
if ( ! class_exists( 'wbList', false ) )
{
    class wbList {

        /**
         * instance
         **/
        public static  $instance    = NULL;
        /**
         * logger
         **/
        private static $analog      = NULL;
        /**
         * log level
         **/
        public  static $loglevel    = 4;
        /**
         * functions to debug (leave empty for all functions)
         **/
        public  static $debugfunc   = array();
        /**
         * array to store open nodes
         **/
        private static $open_nodes  = array();
        /**
         * counter for unique id; will be reset on getInstance()
         **/
        private static $id          = 0;
        /**
         * ID of last item
         **/
        private static $last_id     = 0;
        /**
         * array of default options
         **/
        public  static $defaults    = array();

        /**
         * constructor
         **/
        function __construct ( $options = array() )
        {
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
            self::reset();
            if(count($options))
            {
                self::set($options);
            }
            self::$id = 0;
        }   // end function __construct()

        // no cloning!
        private function __clone() {}

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
         * get current trail from list
         *
         * @access public
         * @param  array   $tree
         * @param  integer $current
         * @return array
         **/
        public static function getTrail( $tree, $current )
        {

            $trail = array();
            $i     = 0;
            $max   = self::$defaults['max_recursion'];

            self::log('getting trail from tree: '.str_replace(' ', '    ',var_export($tree,1)),7);

            // get the current node
            $path = wbArray::ArraySearchRecursive(
                        $current,
                        $tree,
                        self::$defaults['__id_key']
                    );

            if ( ! empty($path) && ! is_array( $path ) ) {
                self::log(sprintf(
                    'current item [%s] not found in tree, return undef',
                    $current
                ));
                return NULL;
            }

            self::log(sprintf('path for current item [%s]: ',$current).var_export($path,1),7);

            array_pop($path);
            eval( '$node = $tree[\''.implode( '\'][\'', $path ).'\'];' );
            $trail[] = $node;

            if ( $node[ self::$defaults['__parent_key'] ] !== $current )
            {

                $path = wbArray::ArraySearchRecursive(
                            $node[ self::$defaults['__parent_key'] ],
                            $tree,
                            self::$defaults['__id_key']
                        );

                if ( ! empty($path) && is_array( $path ) && count( $path ) > 0 )
                {
                    array_pop( $path );
                    eval( '$parent =& $tree[\''.implode( '\'][\'', $path ).'\'];' );
                    $trail[] =  $parent;
                    // while we have parents...
                    while (
                           ! empty( $parent )
                        && is_array( $parent )
                        && isset( $parent[ self::$defaults['__parent_key'] ] )
                        && $parent[ self::$defaults['__parent_key'] ] > 0
                    ) {
                        $path = wbArray::ArraySearchRecursive(
                                    $parent[ self::$defaults['__parent_key'] ],
                                    $tree,
                                    self::$defaults['__id_key']
                                );
                        array_pop( $path );
                        eval( '$parent =& $tree[\''.implode( '\'][\'', $path ).'\'];' );
                        $trail[] = $parent;
                        // avoid deep recursion
                        if ( $i > $max ) {
                            self::log(
                                sprintf(
                                    'reached [%s] recursions without finding root; break to avoid deep recursion',
                                    $max
                                ),
                                7
                            );
                            break;
                        }
                        $i++;
                    }
                }
            }
            return array_reverse( $trail );
        }   // end function getTrail()

        /**
         * build a breadcrumb ('path'); this will analyze the $tree to get the
         * current trail (using getTrail()) and use buildList() to generate
         * the output
         *
         * @access public
         * @param  array   $tree     - flat array
         * @param  array   $options  - options to be passed to buildList()
         * @param  boolean $as_array - return result as array; default: false
         * @return string  HTML
         **/
        public static function buildBreadcrumb ( $tree, $options, $as_array = NULL )
        {
            self::log('building breadcrumb from tree:'.var_export($tree,1),7);
            if ( ! empty($tree) && ! is_array( $tree ) ) {
                self::log('no breadcrumb items to show',4);
                return;
            }
            $trail = self::getTrail( self::buildRecursion($tree), $options['selected'] );
            if ( $as_array )
                return $trail;
            return self::buildList($trail,$options);
        }   // end function buildBreadcrumb()

        /**
         *
         * @access public
         * @return
         **/
        public static function buildSelect($list, $options = array())
        {
            $options['type']     = 'select';
            $options['as_array'] = true;

            $space  = ( isset($options['space']) ? $options['space'] : self::$defaults['space'] );
            $output = self::buildList($list,$options);

            if ( isset($options['options_only']) && $options['options_only'] )
                return join( "\n\t", $output )."\n";

            $name   = $options['name'];

            return self::selectStart($space,$name)
		         . join( "\n\t", $output )."\n"
                 . self::selectEnd($space)
                 ;

        }   // end function buildSelect()
        

/*******************************************************************************
 * MAIN METHOD
 ******************************************************************************/

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
        public static function buildList( $list, $options = array() )
        {

            // let the caller handle errors
            if ( empty($list) || ! is_array( $list ) )
            {
                self::log('no list items to show',4);
                return;
            }

            $as_array   = ( isset($options['as_array'])            ? $options['as_array']            : false );
            $hidden     = ( isset(self::$defaults['__hidden_key']) ? self::$defaults['__hidden_key'] : ''    );
            $root_id    = ( isset($options['root_id'])             ? $options['root_id']             : 0     );
            $type       = ( isset($options['type'])                ? $options['type']                : 'ul'  );
            $selected   = ( isset($options['selected'])            ? $options['selected']            : ''    );
            $p_key      = self::$defaults['__parent_key'];
            $id_key     = self::$defaults['__id_key'];
            $children   = array();
            $trail      = array();

            if ( count( self::$open_nodes ) )
                self::log( 'open nodes:'.var_export(self::$open_nodes,1)      , 7 );
            self::log( 'list   : '.str_replace(' ', '    ',var_export($list,1)), 7 );
            self::log( 'root_id: '.$root_id                                   , 7 );

            // create a list of children for each page
            foreach ( $list as $item ) {
                if ( isset($item[$hidden]) ) { // sort out hidden elements
                    continue;
                }
                $children[$item[$p_key]][] = $item;
            }
            self::log('children list: '.str_replace(' ', '    ',var_export($children,1)),7);

            // get trail to current page; this allows to add a css class for
            // items that are in the current trail
            if(isset($options['selected']))
            {
                $items = self::getTrail($children,$options['selected']);
                foreach($items as $i => $item)
                {
                    $trail[$item[$id_key]] = 1;
                }
            }

            // loop will be false if the root has no children (i.e., an empty menu!)
            $loop      = !empty( $children[$root_id] );
            self::log(sprintf('loop [%s] (if this is empty there will be no list!)',$loop),7);

            // spare some typing
            $ul_id      = ( isset($options['ul_id'])    ? $options['ul_id']    : NULL                     );
            $space      = ( isset($options['space'])    ? $options['space']    : self::$defaults['space'] );
            $maxlevel   = ( isset($options['maxlevel']) ? $options['maxlevel'] : 999                      );
            $level_key  = self::$defaults['__level_key'];
            $href_key   = self::$defaults['__href_key'];
            $title_key  = self::$defaults['__title_key'];
            $isopen_key = self::$defaults['__is_open_key'];
            $m_key      = '';
            $m_before   = NULL;
            $m_after    = NULL;

            // always open root node
            if ( isset($list[$root_id]) && count(self::$open_nodes) && ! in_array( $list[$root_id], self::$open_nodes ) )
                array_unshift( self::$open_nodes, $list[$root_id][$id_key] );

            // for additional text (if any)
            if ( isset(self::$defaults['__more_info_key']) )
            {
                $m_key = self::$defaults['__more_info_key'];
                list( $m_before, $m_after ) = explode( '%%', self::$defaults['more_info'] );
            }

            // initializing $parent as the root
            $parent       = $root_id;
            $parent_stack = array();
            $out          = array();
            $isfirst      = true;
            $islast       = false;
            $is_selected  = false;

            self::log(sprintf('parent [%s]',$root_id),7);

            while ( $loop && ( ( $option = each( $children[$parent] ) ) || ( $parent > $root_id ) ) )
            {

                self::log('current item: '.var_export($option,1),7);

                // ----- no more children -----
                if ( $option === false )
                {
                    self::log('no more children',7);
                    // move to next parent
                    $parent = array_pop( $parent_stack );
                    self::log(sprintf('next parent [%s]',$parent),7);
                    // close list item
                    if($type!='select')
                    {
                        $out[]  = str_repeat( "\t", ( count( $parent_stack ) + 1 ) * 2 )     . self::listEnd();
                        $out[]  = str_repeat( "\t", ( count( $parent_stack ) + 1 ) * 2 - 1 ) . self::itemEnd();
                    }
                }

                // ----- handle child -----
                elseif ( ! empty( $children[ $option['value'][$id_key] ] ) )
                {
                    self::log('handling children for:',var_export($option['value'],1),7);

                    $text        = $option['value'][$title_key];
                    $is_open     = isset($option['value'][$isopen_key]) ? $option['value'][$isopen_key] : false;
                    $is_selected = ( isset($selected) && $selected == $option['value'][$id_key] );
                    $is_in_trail = ( isset($trail[$option['value'][$id_key]]) ? true : false );

                    $tab     = str_repeat( $space, ( count( $parent_stack ) + 1 ) * 2 - 1 );
//($id,$level,$is_selected=false,$has_children=false,$is_first=false,$is_last=false,$is_open=false,$is_in_trail=false,$for='li')
                    $li_css  = self::getListItemCSS(
                        $option['value'][$id_key],
                        $option['value'][$level_key],
                        $is_selected,
                        true,
                        $isfirst,
                        $islast,
                        $is_open,
                        $is_in_trail
                    );

                    if ( isset( $option['value'][$href_key] ) )
                    {
                        // check if it's already a link
                        if( ! preg_match( '~^\<a href~i', $option['value'][$href_key] ) )
                            $text = '<a href="'.$option['value'][$href_key].'">'.$text.'</a>';
                        else
                            $text = $option['value'][$href_key];
                    }
                    if ( isset( $option['value'][ $m_key ] ) )
                    {
                        $text .= $m_before
                              .  $option['value'][$m_key]
                              .  $m_after;
                    }
                    if($type=='select')
                    {
                        $sel    = NULL;
                        if ( isset($selected) && $selected == $option['value'][$id_key] ) {
                            $sel = ' selected="selected"';
                        }
                        $out[] = self::selectOption($option['value'][$id_key],$sel,$space,$text);
                    }
                    else
                    {
                        // only add children if in open_nodes array and level <= $maxlevel
                        if (
                             ( !isset($option['value'][$level_key]) || $option['value'][$level_key] <= $maxlevel                )
                             &&
                             ( !count(self::$open_nodes)            || in_array( $option['value'][$id_key], self::$open_nodes ) )
                        ) {
                            self::log(sprintf('showing children for element [%s]',$option['value'][$title_key]),7);
                            // are we going to show next level?
                            $first_child = $children[$option['value'][$id_key]][0];
#                            if ( $first_child[$level_key] <= $maxlevel ) {
#                                $li_css .= ' ' . self::$defaults['is_open_li_class'];
#                            }
                            // HTML for menu item containing children (open)
                            $out[] = $tab.self::itemStart( $li_css, $space )
                                   . $text;
                            // open sub list
                            $out[] = $tab . "\t" . self::listStart( $space, $ul_id, $option['value'][$level_key] );
                            array_push( $parent_stack, $option['value'][$p_key] );
                            $parent = $option['value'][$id_key];
                        }
                        else {
                            self::log(sprintf('skipping children for element [%s]',$option['value'][$title_key]),7);
                            //self::log( $option['value'][$level_key] .' <= ' . $maxlevel . ' || ' . $option['value'][$id_key] . ' ! in_array ', $this->open_nodes );
                            // HTML for menu item containing children (open)
                            $out[] = sprintf(
                                '%1$s'.self::itemStart( $li_css, $space ).'%2$s',
                                $tab,   // %1$s = tabulation
                                $text   // %2$s = title
                            );
                        }
                    }
                    // get id for last child
                    end($children[ $option['value'][$id_key] ]);
                    $key = key($children[ $option['value'][$id_key] ]);
                    reset($children[ $option['value'][$id_key] ]);
                    self::$last_id = $children[ $option['value'][$id_key] ][$key][$id_key];
                    self::log( 'last child id: '.self::$last_id, 7 );
                }

                // ----- handle leaf -----
                else {
                    self::log(sprintf('handling leaf [%s]',$option['value'][$id_key]),7);
                    // only add leaf if level <= maxlevel
                    if ( !isset($option['value'][$level_key]) || $option['value'][$level_key] <= $maxlevel )
                    {
                        $is_selected = ( isset($selected) && $selected == $option['value'][$id_key] );
                        $li_css  = self::getListItemCSS($option['value'][$id_key],$option['value'][$level_key],$is_selected,false,$isfirst,$islast,false);
                        $text    = ( isset($option['value'][$title_key]) ? $option['value'][$title_key] : '' );
                        if ( isset( $option['value'][$href_key] ) )
                        {
                            // check if it's already a link
                            if( ! preg_match( '~^\<a href~i', $option['value'][$href_key] ) )
                                $text = '<a href="'.$option['value'][$href_key].'">'.$text.'</a>';
                            else
                                $text = $option['value'][$href_key];
                        }
                        if ( isset( $option['value'][ $m_key ] ) )
                        {
                            $text .= $m_before
                                  .  $option['value'][$m_key]
                                  .  $m_after;
                        }
                        if($type=='select')
                        {
                            // mark selected
                            $sel    = NULL;
                            $is_current = ( ( isset($selected) && $selected == $option['value'][$id_key] ) ? true : false );
                            if ( $is_current ) {
                                $sel = ' selected="selected"';
                            }
                            $out[] = self::selectOption($option['value'][ $id_key ],$sel,$space,$text);
                        }
                        else
                        {
                            // HTML for menu item with no children (aka "leaf")
                            $out[] = sprintf(
                                '%1$s'.self::itemStart( $li_css, $space ).'%2$s'.self::itemEnd(),
                                str_repeat( $space, ( count( $parent_stack ) + 1 ) * 2 - 1 ),   // %1$s = tabulation
                                $text   // %2$s = title
                            );
                        }
                    }
                    else {
                        self::log(sprintf('leaf not shown because level [%s] <= [%s]',$option['value'][$level_key],$maxlevel), 7 );
                    }
                }
                $isfirst = 0;
            }

            if ( count($out) && isset( self::$defaults['last_li_class'] ) && ! empty(self::$defaults['last_li_class']) )
            {
                // get the very last element
                $last   = array_splice( $out, -1, 1 );
                // add last item css
                $last   = str_ireplace( '{{lastcss}}', ' '.self::$defaults['last_li_class'], $last );
                $out[]  = $last[0];
            }

            if ( $as_array )
                return $out;

            $output = self::listStart( $space, $ul_id )
                    . implode( "\r\n", $out )
                    . self::listEnd();
            $output = str_ireplace( '{{lastcss}}', '', $output );

            self::log($output,7);

            return $output;
        }   // end function buildList()


        /**
         * build multilevel (recursive) array from flat one; will add the
         * children of an item to __children_key array key
         *
         * @access  public
         * @param   array   $items - flat array (reference!)
         * @param
         * @return  array
         **/
        public static function buildRecursion ( &$items, $min = -9 )
        {
            if ( ! empty( $items ) && ! is_array( $items ) )
            {
                self::log( 'no items to build recursion', 4 );
                return NULL;
            }
            if ( isset($items['__is_recursive']) )
            {
                self::log('array seems to be already recursive (key __is_recursive is set), nothing to do',7);
                return $items;
            }
            // if there's only one item, no recursion to do
            if ( ! ( count( $items ) > 1 ) )
            {
                self::log('array has only one entry, nothing to do',7);
                return $items;
            }

            self::log(sprintf('building recursion from [%s] items',count($items)),7);
            self::log(str_replace(" ","    ",var_export($items,true)),7);

            $tree    = array();
            $root_id = -1;

            // spare some typing...
            $ik      = self::$defaults['__id_key'];
            $pk      = self::$defaults['__parent_key'];
            $ck      = self::$defaults['__children_key'];
            $lk      = self::$defaults['__level_key'];

            self::log(sprintf('id key [%s], parent key [%s], children key [%s], level key [%s]',$ik,$pk,$ck,$lk),7);

            // make sure that the $items array is indexed by the __id_key
            $arr     = array();
            foreach ( $items as $index => $item ){
                $arr[$item[$ik]] = $item;
            }
            $items = $arr;
            self::log('reindexed array by __id_key: '.str_replace(" ","    ",var_export($items,true)),7);

            //
            // this creates an array of parents with their associated children
            //
            // -----------------------------------------------
            // REQUIRES that the array index is the parent ID!
            // -----------------------------------------------
            //
            // http://www.tommylacroix.com/2008/09/10/php-design-pattern-building-a-tree/
            //
            foreach ( $items as $id => &$node )
            {
                // skip nodes with depth < min level
                if ( isset( $node[$lk] ) && $node[$lk] <= $min )
                {
                    self::log(sprintf('skipping node (level <= min level [%s]',$min),7);
                    continue;
                }

                // avoid error messages on missing parent key
                if ( ! isset( $node[$pk] ) )
                {
                    self::log( 'adding missing parent key to node', 7 );
                    $node[$pk] = null;
                }

                // root node
                if ( $node[$pk] === null && $root_id < 0 )
                {
                    self::log( 'found root node', 7 );
                    $tree[$id] = &$node;
                    $root_id   = $id;
                }
                // sub node
                else
                {
                    // avoid warnings on missing children key
                    if ( ! isset($items[$node[$pk]][$ck]) || ! is_array($items[$node[$pk]][$ck]) )
                    {
                        $items[$node[$pk]][$ck] = array();
                    }
                    $items[$node[$pk]][$ck][] = &$node;
                }

            }
            if ( ! empty($tree) && is_array($tree) && count( $tree ) > 0 )
            {
                // mark tree as already seen
                $tree[$root_id][$ck]['__is_recursive'] = 1;
                $tree = $tree[$root_id][$ck];
            }
            else {
                self::log( '---ERROR!--- $tree is empty!', 7);
            }

            self::log( 'returning tree: '.var_export($tree,1),7 );

            return $tree;

        }   // end function buildRecursion ()

        public static function getListItemCSS($id,$level,$is_selected=false,$has_children=false,$is_first=false,$is_last=false,$is_open=false,$is_in_trail=false,$for='li')
        {
            self::log(sprintf('id [%s], level [%s], current [%s], children [%s], first [%s], last [%s], open [%s], trail [%s], for [%s]',
                $id, $level, $is_selected, $has_children, $is_first, $is_last, $is_open, $is_in_trail, $for ),7);
            $class =  self::$defaults['css_prefix']
                   .  self::$defaults[$for.'_class'];
            // first element
            $class .= ( $is_first )
                   ?  ' '.self::$defaults['css_prefix'].self::$defaults['first_li_class']
                   :  '';
            // last element
            $class .= ( $is_last )
                   ?  ' '.self::$defaults['css_prefix'].self::$defaults['last_'.$for.'_class']
                   :  '';
            // element is in trail
            $class .= ( $is_in_trail )
                   ?  ' '.self::$defaults['css_prefix'].self::$defaults['trail_'.$for.'_class']
                   :  '';
            // element is open
            $class .= ( $is_open )
                   ?  ' '.self::$defaults['css_prefix'].self::$defaults['is_open_'.$for.'_class']
                   :  ' '.self::$defaults['css_prefix'].self::$defaults['is_closed_'.$for.'_class'];
            // element has children
            $class .= ( $has_children )
                   ?  ' '.self::$defaults['css_prefix'].self::$defaults['has_child_'.$for.'_class']
                   :  '';
            // markup for current element
            $class .= ( $is_selected )
                   ?  ' '.self::$defaults['css_prefix'].self::$defaults['current_'.$for.'_class']
                   :  '';
            // special CSS class for each level?
            if (
                   (
                       isset( self::$defaults['create_level_css'] )
                       &&
                       self::$defaults['create_level_css'] == 1
                   )
                   &&
                   (
                       isset( self::$defaults[$for.'_class'] )
                       &&
                       ! empty ( self::$defaults[$for.'_class'] )
                   )
            ) {
                $class  .= ' '
                        .  self::$defaults['css_prefix']
                        .  self::$defaults[$for.'_class']
                        .  $level;
            }
            self::log(sprintf('returning css classes [%s]',$class),7);
            return $class;
        }   // end function getListItemCSS()

        /**
         *
         *
         *
         *
         **/
        public static function itemStart( $css, $space = NULL )
        {
            $id = NULL;
            if ( self::$defaults['unique_id'] !== false ) {
                $id = self::$defaults['li_id_prefix'].self::getID();
            }
            $start = str_replace(
                array( '%%id%%', '%%' ),
                array( $id     , $css ),
                self::$defaults['item_open']
            );
            // remove empty id-attribute
            $start = str_replace( ' id=""', '', $start );
            return $space
                 . $start
                 . "\n";
        }   // end function itemStart()

        /**
         * close item
         **/
        public static function itemEnd( $space = NULL ) {
            return $space . self::$defaults['item_close'];
        }   // end function itemEnd()

        /**
         *
         *
         *
         *
         **/
        public static function listStart($space=NULL, $ul_id=NULL, $level=NULL)
        {
            $class = self::$defaults['ul_class'];

            // special CSS class for each level?
            if (
                   isset( self::$defaults['create_level_css'] )
                   &&
                   self::$defaults['create_level_css'] === true
            ) {
                $suffix  = empty($level)
                         ? intval( ( strlen($space) / 4 ) )
                         : $level;
                $class  .= ' '
                        .  self::$defaults['css_prefix']
                        .  self::$defaults['ul_class']
                        .  '_'
                        .  $suffix;
            }

            $id      = $ul_id;
            if ( self::$defaults['unique_id'] !== false ) {
                if ( empty($id) ) {
                    $id  = self::$defaults['ul_id_prefix'].self::getID();
                }
                else {
                    $id .= '_' . self::getID();
                }
            }

            //$this->last_ul_id = $id;

            $output = $space
                    . str_replace(
                          array(
                              '%%id%%',
                              '%%',
                          ),
                          array(
                              $id,
                              $class
                          ),
                          self::$defaults['list_open']
                      );

            // remove empty id-attribute
            $output = str_replace( ' id=""', '', $output );

            return $output;

        }   // end function listStart()

        /**
         * close list
         **/
        public static function listEnd( $space = NULL ) {
            return $space . self::$defaults['list_close'];
        }   // end function listEnd()

        /**
         * opens a <select> box with given $name
         *
         * @access private
         * @param  string  $space - indentation
         * @param  string  $name  - name for the select field
         * @param  int     $level - ignored
         * @return string
         **/
        private static function selectStart($space=NULL, $name=NULL, $level=NULL)
        {
            return $space
                 . str_replace(
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
        private static function selectEnd($space = NULL)
        {
            return $space . self::$defaults['select_close'];
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
        private static function selectOption($value,$sel,$space,$text)
        {
            $content = $space . ' ' . $text;
            return str_replace(
                array( '%%value%%', '%%selected%%', '%%text%%', '%%' ),
                array( $value     , $sel          , $text     , ''   ),
                self::$defaults['select_option']
            );
        }   // end function selectOption()

        /**
         * sort array by children
         *
         * @access public
         * @param  array  $list
         * @param  int    $root_id
         * @return array
         **/
        public static function sort ( $list, $root_id )
        {

            if ( empty($list) || ! is_array( $list ) || count($list) == 0 )
            {
                self::log('no list items to sort',4);
                return NULL;
            }

            // if there's only one item, nothing to do
            if ( ! ( count($list) > 1 ) )
            {
                self::log('array has only one entry, nothing to do',7);
                return $list;
            }

            $return    = array();
            $children  = array();
            $p_key     = self::$defaults['__parent_key'];
            $id_key    = self::$defaults['__id_key'];

            // create a list of children for each item
            foreach ( $list as $item )
            {
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
        }   // end function sort()

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
            if(count(self::$debugfunc))
            {
                $caller = debug_backtrace();
                if(!in_array($caller[1]['function'],self::$debugfunc)) return;
            }
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
                    self::$analog = -1;
                }
            }
            if ( self::$analog )
                \Analog::log($message,$level);
        }   // end function log()

        /**
         *
         * @access public
         * @return
         **/
        public static function reset() {
            self::$defaults = array(
                // keys in the input array
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
                // global options
                'create_level_css'      => true,
                'unique_id'             => true,
                'space'                 => '    ',
                'max_recursion'         => 15,
                // css options
                'css_prefix'            => '',
                'current_li_class'      => 'current_item',
                'first_li_class'        => 'first_item',
                'has_child_li_class'    => 'has_child',
                'is_closed_li_class'    => 'is_closed',
                'is_open_li_class'      => 'is_open',
                'item_close'            => '</li>',
                'item_open'             => '<li id="%%id%%" class="%%{{lastcss}}">',
                'last_li_class'         => 'last_item',
                'li_class'              => 'item',
                'li_id_prefix'          => 'li_',
                'list_close'            => '</ul>',
                'list_open'             => '<ul id="%%id%%" class="%%">',
                'more_info'             => '<span class="more_info">%%</span>',
                'select_open'           => '<select name="%%id%%" id="%%id%%" class="%%">',
                'select_close'          => '</select>',
                'select_option'         => '<option class="%%" value="%%value%%"%%selected%%>%%text%%</option>',
                'select_class'          => '',
                'trail_li_class'        => 'trail_item',
                'ul_class'              => 'list',
                'ul_id_prefix'          => 'ul_',
            );
        }   // end function reset()

        /**
         * change options
         *
         * @access public
         * @param  array  $options
         * @return void
         **/
        public static function set($options)
        {
            if(count($options))
            {
                foreach($options as $key => $value)
                {
                    self::$defaults[$key] = $value;
                }
            }
        }   // end function set()

        /**
         * get id for list start;
         * returns NULL if 'unique_id' is set to false;
         * returns a unique id if 'unique_id' is set to true;
         * returns the value of 'unique_id' in any other case
         *
         * @access private
         * @return string
         *
         **/
        private static function getID() {
            if ( self::$defaults['unique_id'] === false ) {
                return NULL;
            }
            if ( self::$defaults['unique_id'] !== true ) {
                return self::$defaults['unique_id'];
            }
            self::$id++;
            return self::$id;
        }   // end function getID()

    }

    class wbListException extends \Exception {}

    /**
     * array helper methods
     *
     * @category   wblib
     * @package    wbArray
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    if ( ! class_exists( 'wbArray', false ) )
    {
        class wbArray
        {
            /**
             * Recursive array search
             *
             * @param  string  $Needle     value to search for
             * @param  array   $Haystack   array to search
             * @param  string  $NeedleKey  array key to retrieve
             * @param  boolean $Strict
             * @param  array   $Path
             *
             * @return mixed   $Path       array - path to array value if $Needle is found
             *                 false       if $Needle is not found
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
        }
    }

}
