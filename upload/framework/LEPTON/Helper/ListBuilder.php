<?php

/**
 *
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON v2.0 Black Cat Edition Development
 * @copyright       2013, LEPTON v2.0 Black Cat Edition Development
 * @link            http://www.lepton2.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */

if ( ! class_exists( 'LEPTON_Object', false ) ) {
    @include dirname(__FILE__).'/../Object.php';
}

if ( ! class_exists( 'LEPTON_Helper_ListBuilder', false ) ) {
	class LEPTON_Helper_ListBuilder extends LEPTON_Object
	{
	    protected $_config
			= array(
			// ----- used globally -----
	            // array key that contains the id of the parent item
	            '__parent_key'          => 'parent',
	            // array key that contains the item id
	            '__id_key'              => 'id',
	            // array key that contains the name (text) of the element
	            '__title_key'           => 'title',
	            // array key that contains the item level (=depth)
	            '__level_key'           => 'level',
			    // array key to store child references
	            '__children_key'        => 'children',
	            // array key to mark current item
	            '__current_key'         => 'current',
	            // array key to mark items as hidden
	            '__hidden_key'          => 'hidden',
                // default CSS class for <select>
                '__select_class'        => '',
                // suppress html creation
                '__no_html'             => false,
			// ----- used for dropdown -----
			    'space'                 => '    ',
			);

        /**
         * sort array by children
         **/
        public function sort ( $list, $root_id ) {

            if ( empty($list) || ! is_array( $list ) || count($list) == 0 ) {
                return NULL;
            }

            $return    = array();
            $children  = array();
            $p_key     = $this->_config['__parent_key'];
            $id_key    = $this->_config['__id_key'];

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
                    $return[] = $option['value'];
                    array_push( $parent_stack, $option['value'][$p_key] );
                    $parent = $option['value'][$id_key];
                }
                else {
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
         *     $list = new LEPTON_Helper_ListBuilder();
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
        public function dropdown ( $name, $list, $root_id, $selected = NULL ) {

            if ( empty($list) || ! is_array( $list ) || count($list) == 0 ) {
                return NULL;
            }

            $output    = array();
            $hidden    = ( isset($this->_config['__hidden_key']) ? $this->_config['__hidden_key'] : '' );
            $p_key     = $this->_config['__parent_key'];
            $id_key    = $this->_config['__id_key'];
            $title_key = $this->_config['__title_key'];
            $level_key = $this->_config['__level_key'];
            $space     = $this->_config['space'];

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
                    $parent = array_pop( $parent_stack );
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
                    $sel    = NULL;
                    if ( isset($selected) && $selected == $option['value'][$id_key] ) {
                        $sel = ' selected="selected"';
                    }
                    $output[] = $this->getOption($option['value'][ $id_key ],$sel,$tab,$text);

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
                    // mark selected
                    $sel    = NULL;
                    if ( isset($selected) && $selected == $option['value'][$id_key] ) {
                        $sel = ' selected="selected"';
                    }
                    $output[] = $this->getOption($option['value'][ $id_key ],$sel,$tab,$text);
                }

            }

            return $this->startSelect($name)
				  . join( "\n\t", $output )."\n"
				  . $this->closeSelect();

        }   // end function dropdown ()

        /**
         *
         **/
        private function startSelect($name) {
            return
                  $this->_config['__no_html']
                ? NULL
                : '<select name="'.$name.'" id="'.$name.'" class="'. $this->_config['__select_class'].'">'."\n\t";
        }

        /**
         *
         **/
        private function closeSelect() {
            return
                $this->_config['__no_html']
                ? NULL
                : '</select>';
        }

        /**
         *
         **/
        private function getOption($value,$sel,$tab,$text) {
            $content = $tab . ' ' . $text;
            return
                $this->_config['__no_html']
                ? $content
                : '<option value="'.$value.'"'.$sel.'>'.$content.'</option>';
        }
        
	}
}

?>