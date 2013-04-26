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
	            // array key that contains the id of the parent item
	            '__parent_key'          => 'parent',
	            // array key that contains the item id
	            '__id_key'              => 'page_id',
	            // array key that contains the name (text) of the element
	            '__title_key'           => 'menu_title',
	            // array key that contains the item level (=depth)
	            '__level_key'           => 'level',
			    // array key to store child references
	            '__children_key'        => 'children',
	            // array key to mark current item
	            '__current_key'         => 'current',
	            // array key to mark items as hidden
	            '__hidden_key'          => 'hidden',
                //
                '__editable_key'        => 'editable',
                // default CSS class for <select>
                '__select_class'        => '',
                // template for <ul>
                '__list_open'           => '<ul id="%%id%%" class="%%class%%">',
                // template for </ul>
                '__list_close'          => '</ul>',
                // template for <li>
                '__list_item_open'      => '<li id="%%id%%" class="%%class%%">',
                // template for </li>
                '__list_item_close'     => '</li>',
                // prefix to be used for CSS classes
                '__ul_css_prefix'       => NULL,
                // default CSS class for <ul>
                '__ul_class'            => 'ui-sortable',
                // create CSS classes per sublevel
                '__ul_level_css'        => false,
                // default CSS class for <li>
                '__li_class'            => 'tree_item',
                // create CSS classes per sublevel
                '__li_level_css'        => false,
                // prefix to be used for CSS classes for <li>
                '__li_css_prefix'       => NULL,
                //
                '__li_id_prefix'        => NULL,
                '__li_first_item_class' => 'first_item',
                '__li_last_item_class'  => 'last_item',
                '__li_has_child_class'  => 'has_child',
                '__li_is_open_class'    => 'is_open',

                // suppress html creation
                '__no_html'             => false,
			// ----- used for dropdown -----
			    'space'                 => '    ',
			);

        private static $instance;

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
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

            $output = self::listbuilder($list,$root_id,'select');

            if ( $options_only )
                return join( "\n\t", $output )."\n";

            $self   = self::getInstance();

            return $self->startSelect($name)
		         . join( "\n\t", $output )."\n"
                 . $self->closeSelect();

        }   // end function dropdown ()

        public static function tree( $list, $root_id )
        {
            $self   = self::getInstance();
            $output = self::listbuilder($list,$root_id);
            return $self->startUL()
		         . join( "\n\t", $output )."\n"
                 . $self->closeUL();
        }   // end function tree()

        /**
         *
         * @access public
         * @return
         **/
        private static function listbuilder($list,$root_id=0,$type='ul')
        {
            if (empty($list) || !is_array($list) || !count($list))
            {
                return NULL;
            }

            // initialize
            $self      = self::getInstance();
            $output    = array();
            $hidden    = ( isset($self->_config['__hidden_key'])
                       ? $self->_config['__hidden_key']
                       : ''
                       );
            $p_key     = $self->_config['__parent_key'];
            $id_key    = $self->_config['__id_key'];
            $title_key = $self->_config['__title_key'];
            $level_key = $self->_config['__level_key'];
            $space     = $self->_config['space'];
            $is_first  = true;
            $is_last   = false;

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
                        $output[] = $tab.$self->startLI($option['value'][$id_key],$level,true,$is_first,$is_last)
                               . "<span>$text</span>";
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
                    if($type=='select')
                    {
                    // mark selected
                    $sel    = NULL;
                    if ( isset($selected) && $selected == $option['value'][$id_key] ) {
                        $sel = ' selected="selected"';
                    }
                        $output[] = $self->getOption($option['value'][ $id_key ],$sel,$tab,$text);
                    }
                    else
                    {
                        $output[] = $tab.$self->startLI($option['value'][$id_key],$level,false,$is_first,$is_last)
                                  . $text
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
                $output[]  = $last[0];
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
            $self      = self::getInstance();
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
            $self      = self::getInstance();
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
            $self    = self::getInstance();
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

            $self  = self::getInstance();

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
            $self = self::getInstance();
            return $space . $self->_config['__list_close'];
        }   // end function closeUL()

        /**
         *
         *
         *
         *
         **/
        function startLI($id,$level,$has_children=false,$is_first=false,$is_last=false)
        {
            $self  = self::getInstance();
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
            return $space . self::getInstance()->_config['__list_item_close'];
        }   // end function closeLI()
        
	}
}

?>