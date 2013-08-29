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

if ( ! class_exists( 'CAT_Helper_ListBuilder', false ) ) {
	class CAT_Helper_ListBuilder extends CAT_Object
	{
	    protected $_config
			= array(
                 'loglevel'             => 7,
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
         * sort array by children
         **/
        public static function sort ( $list, $root_id ) {

            if ( empty($list) || ! is_array( $list ) || count($list) == 0 )
                return NULL;

            $self      = self::getInstance(false);
            $return    = array();
            $children  = array();
            $p_key     = $self->_config['__parent_key'];
            $id_key    = $self->_config['__id_key'];

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
         *
         * This function creates an (optionally indented) dropdown from a flat
         * array using an iterative loop
         *
         * By default, it uses 4 blanks for indentation; use the 'space' config
         * parameter to set a different value.
         *
         * Usage example:
         *
         *     $list = new CAT_Helper_ListBuilder();
         *     $list->config(
         *         array(
         *             'space' => '|-> '
         *         )
         *     );
         *     echo $list->dropdown( 'myselect', $array, 0 );
         *
         * @access public
         * @param  string $name     - name of the select field
         * @param  array  $list     - flat array
         * @param  int    $root_id  - id of the root element
         * @param  array  $selected - (optional) id of selected element
         * @return string
         *
         * Based on code found here:
         * http://codjng.blogspot.com/2010/10/how-to-build-unlimited-level-of-menu.html
         *
         **/
        public static function dropdown ( $name, $list, $root_id, $selected = NULL, $options_only = false )
        {

            $output = self::listbuilder($list,$root_id,'select',$selected);

            if ( $options_only )
                return join( "\n\t", $output )."\n";
            $self   = self::getInstance(false);
            return $self->startSelect($name)
		         . join( "\n\t", $output )."\n"
                 . $self->closeSelect();

        }   // end function dropdown ()

        public static function tree( $list, $root_id, $selected=NULL )
        {
            $self   = self::getInstance(false);
            $output = self::listbuilder($list,$root_id,'ul',$selected);
            if(is_array($output) && count($output))
            return $self->startUL()
		         . join( "\n\t", $output )."\n"
                 . $self->closeUL();
            else
                return '';
        }   // end function tree()

        /**
         *
         * @access public
         * @return
         **/
        private static function listbuilder($list,$root_id=0,$type='ul',$selected=NULL)
        {
            if (empty($list) || !is_array($list) || !count($list))
            {
                return NULL;
            }

            // initialize
            $self       = self::getInstance(false);
            $output     = array();
            $hidden     = ( isset($self->_config['__hidden_key'])
                        ? $self->_config['__hidden_key']
                        : ''
                        );
            $p_key      = $self->_config['__parent_key'];
            $id_key     = $self->_config['__id_key'];
            $title_key  = $self->_config['__title_key'];
            $level_key  = $self->_config['__level_key'];
            $isopen_key = $self->_config['__is_open_key'];
            $link_key   = $self->_config['__link_key'];
            $auto_link  = $self->_config['__auto_link'];
            $space      = $self->_config['space'];
            $is_first   = true;
            $is_last    = false;
            $is_open    = false;

            // create a list of children for each item
            foreach ( $list as $item ) {
                // sort out hidden items
                if ( isset($item[$hidden]) ) {
                    continue;
                }
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
                    $parent = array_pop($parent_stack);
                    if($type!='select')
                    {
                        // close list item
                        $output[]  = str_repeat( "\t", ( count( $parent_stack ) + 1 ) * 2 )     . $self->closeUL();
                        $output[]  = str_repeat( "\t", ( count( $parent_stack ) + 1 ) * 2 - 1 ) . $self->closeLI();
                    }
                }
                // current item has children
                elseif ( ! empty( $children[ $option['value'][$id_key] ] ) )
                {
                    $level  = isset( $option['value'][ $level_key ] )
                            ? $option['value'][ $level_key ]
                            : 0;
                    $tab    = str_repeat( $space, $level );
                    $text   = $option['value'][$title_key];
                    $is_open = ( $selected ? $selected : $option['value'][$isopen_key] );
                    // mark selected
                    if($type=='select')
                    {
                    $sel    = NULL;
                    if ( isset($selected) && $selected == $option['value'][$id_key] ) {
                        $sel = ' selected="selected"';
                    }
                        $output[] = $self->getOption($option['value'][$id_key],$sel,$tab,$text);
                    }
                    else
                    {
                        // HTML for menu item containing children (open)
                        $output[] = $tab.$self->startLI($option['value'][$id_key],$level,true,$is_first,$is_last,$is_open)
                               //. "<span>$text</span>";
                                  . ( ($auto_link&&$link_key) ? '<a href="'.CAT_Helper_Page::getLink($option['value'][$id_key]).'">' : '' )
                                  . $text
                                  . ( ($auto_link&&$link_key) ? '</a>' : '' )
                                  ;
                        // open sub list
                        $output[] = $tab . "\t" . $self->startUL( $space, '', $option['value'][$level_key] );
                        #$output[] = '-'.$option['value'][$id_key].'-';
                    }
                    array_push( $parent_stack, $option['value'][$p_key] );
                    $parent = $option['value'][$id_key];
                }
                // handle leaf
                else {
                    $level  = isset( $option['value'][ $level_key ] )
                            ? $option['value'][ $level_key ]
                            : 0;
                    $tab    = str_repeat( $space, $level );
                    $text   = $option['value'][$title_key];
                    $is_current = ( ( isset($selected) && $selected == $option['value'][$id_key] ) ? true : false );
                    if($type=='select')
                    {
                    // mark selected
                    $sel    = NULL;
                        if ( $is_current ) {
                        $sel = ' selected="selected"';
                    }
                        $output[] = $self->getOption($option['value'][ $id_key ],$sel,$tab,$text);
                    }
                    else
                    {
                        $output[] = $tab.$self->startLI($option['value'][$id_key],$level,false,$is_first,$is_last,$is_current)
                                  . ( ($auto_link&&$link_key) ? '<a href="'.CAT_Helper_Page::getLink($option['value'][$id_key]).'">' : '' )
                                  . $text
                                  . ( ($auto_link&&$link_key) ? '</a>' : '' )
                                  . $self->closeLI();
                    }
                }
                $is_first = false;
            }   // end while

            if ( isset( $self->_config['__li_last_item_class'] ) && ! empty($self->_config['__li_last_item_class']) ) {
                // get the very last element
                $last   = array_splice( $output, -1, 1 );
                // add last item css
                $last   = str_ireplace( 'class="', 'class="'.$self->_config['__li_last_item_class'].' ', $last );
                $output[] = ( is_array($last) && count($last) ) ? $last[0] : '';
            }

            return $output;
        }   // end function list()

        /**
         * opens a <select> box with given $name
         *
         * @access private
         * @param  string  $name
         * @return string
         **/
        private static function startSelect($name)
        {
            $self      = self::getInstance(false);
            return
                  $self->_config['__no_html']
                ? NULL
                : '<select name="'.$name.'" id="'.$name.'" class="'. $self->_config['__select_class'].'">'."\n\t";
        }   // end function startSelect()

        /**
         * closes a <select>
         *
         * @access private
         * @return string
         **/
        private static function closeSelect()
        {
            $self      = self::getInstance(false);
            return
                $self->_config['__no_html']
                ? NULL
                : '</select>';
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
        private static function getOption($value,$sel,$tab,$text)
        {
            $self    = self::getInstance(false);
            $content = $tab . ' ' . $text;
            return
                $self->_config['__no_html']
                ? $content
                : '<option value="'.$value.'"'.$sel.'>'.$content.'</option>';
        }   // end function getOption()

        /**
         *
         *
         *
         *
         **/
        private static function startUL($space=NULL, $ul_id=NULL, $level=NULL )
        {

            $self  = self::getInstance(false);

            $class = $self->_config['__ul_css_prefix']
                   . $self->_config['__ul_class'];

            // special CSS class for each level?
            if (
                   isset( $self->_config['__ul_level_css'] )
                   &&
                   $self->_config['__ul_level_css'] === true
            ) {
                $suffix  = empty($level)
                         ? intval( ( strlen($space) / 4 ) )
                         : $level;

                $class  .= ' '
                        .  $self->_config['__ul_css_prefix']
                        .  $self->_config['__ul_class']
                        .  '_'
                        .  $suffix;
            }

            $id     = $ul_id;
            $output = $space
                    . str_replace(
                          array(
                              '%%id%%',
                              '%%class%%',
                          ),
                          array(
                              $ul_id,
                              $class
                          ),
                          $self->_config['__list_open']
                      );

            // remove empty id-attribute
            $output = str_replace( ' id=""', '', $output );

            return $output."\n";

        }   // end function startUL()
        
        /**
         *
         *
         *
         *
         **/
        function closeUL( $space = NULL ) {
            $self = self::getInstance(false);
            return $space . $self->_config['__list_close'];
        }   // end function closeUL()

        /**
         *
         *
         *
         *
         **/
        function startLI($id,$level,$has_children=false,$is_first=false,$is_last=false,$is_open=false)
        {
            $self  = self::getInstance(false);
            $id    = ( isset($self->_config['__li_id_prefix']) )
                   ? $self->_config['__li_id_prefix'].$id
                   : $id;
            $class = $self->_config['__li_css_prefix']
                   . $self->_config['__li_class'];
            $class .= ( $has_children )
                   ?  ' '.$self->_config['__li_has_child_class']
                   : '';
            $class .= ( $is_first )
                   ?  ' '.$self->_config['__li_first_item_class']
                   : '';
            $class .= ( $is_open )
                   ?  ' '.$self->_config['__li_is_open_class']
                   :  ' '.$self->_config['__li_is_closed_class'];
            $start = str_replace(
                array( '%%id%%', '%%class%%' ),
                array( $id     , $class ),
                $self->_config['__list_item_open']
            );
            // remove empty id-attribute
            $start = str_replace( ' id=""', '', $start );
            return $self->_config['space']
                 . $start
                 . "\n";
        }   // end function startLI()

        /**
         *
         *
         *
         *
         **/
        function closeLI( $space = NULL )
        {
            return $space . self::getInstance(false)->_config['__list_item_close'];
        }   // end function closeLI()
        
        /**
         *
         * @access public
         * @return
         **/
        public function reset() {
            $this->_config = array(
	            '__parent_key'          => 'parent',
	            '__id_key'              => 'page_id',
	            '__title_key'           => 'menu_title',
	            '__level_key'           => 'level',
                '__link_key'            => 'link',
	            '__children_key'        => 'children',
	            '__current_key'         => 'current',
	            '__hidden_key'          => 'hidden',
                '__editable_key'        => 'editable',
                '__is_open_key'         => 'is_open',
                '__select_class'        => '',
                '__list_open'           => '<ul id="%%id%%" class="%%class%%">',
                '__list_close'          => '</ul>',
                '__list_item_open'      => '<li id="%%id%%" class="%%class%%">',
                '__list_item_close'     => '</li>',
                '__ul_css_prefix'       => NULL,
                '__ul_class'            => 'ui-sortable',
                '__ul_level_css'        => false,
                '__li_class'            => 'tree_item',
                '__li_level_css'        => false,
                '__li_css_prefix'       => NULL,
                '__li_id_prefix'        => NULL,
                '__li_first_item_class' => 'first_item',
                '__li_last_item_class'  => 'last_item',
                '__li_has_child_class'  => 'has_child',
                '__li_is_open_class'    => 'item_open',
                '__li_is_closed_class'  => 'item_closed',
                '__no_html'             => false,
                '__auto_link'           => false,
			    'space'                 => '    ',
			);
            return $this; // make chainable
        }   // end function reset()
        

	}
}

?>