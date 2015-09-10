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
 *   @copyright       2015, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (!class_exists('CAT_Helper_CodeEditor'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_CodeEditor extends CAT_Object
    {
        // array to store config options
        protected $_config         = array( 'loglevel' => 8 );
        protected static $_editor  = NULL;

        /**
         * get an editor instance
         *
         * @access public
         * @return void
         **/
        public static function loadEditor()
        {
            // find installed editors
            $editors = CAT_Helper_Directory::getInstance()
                     ->maxRecursionDepth(2)
                     ->setSuffixFilter(array('php'))
                     ->findFiles('c_code_editor.php',CAT_PATH.'/modules',CAT_PATH.'/modules/');

            if(count($editors))
            {
                // for now, we use the first one
                @require CAT_PATH.'/modules/'.$editors[0];
                self::$_editor = new c_code_editor();
            }
        }   // end function loadEditor()
        

        /**
         * checks for installed code editors and returns the preferred one
         *
         * @access public
         * @param  string  $name    - name for the code area
         * @param  string  $id      - id
         * @param  string  $content - initial content
         * @param  string  $width   - area width, default: 100%
         * @param  string  $height  - area height, default: 350px
         * @param  boolean $pring   - wether to echo or not, default: true
         * @return void
         **/
        public static function getEditor($name, $id, $content, $width=NULL, $height=NULL, $print=true)
        {
            if(!self::$_editor) self::loadEditor();
            if(!self::$_editor)
            {
                $editor = sprintf(
                    '<textarea id="%s" name="%s" style="width:%s;height:%s;">%s</textarea>',
                    $id,$name,$width,$height,$content
                );
                if($print) echo $editor;
                else       return $editor;
            }
            else {
                $e =& self::$_editor;
                $e::init($name, $id, $content, $width, $height);
                $e::show($print);
            }
        }   // end function getEditor()

        /**
         * set the highlight mode (for example 'php')
         *
         * @access public
         * @param  string  $mode
         * @return void
         **/
        public static function setMode($mode)
        {
            if(method_exists(self::$_editor,'setMode'))
            {
                $e =& self::$_editor;
                $e::setMode($mode);
            }
        }   // end function setMode()

        /**
         * set the editor theme (skin)
         *
         * @access public
         * @param  string  $skin
         * @return void
         **/
        public static function setTheme($skin)
        {
            if(method_exists(self::$_editor,'setTheme'))
            {
                $e =& self::$_editor;
                $e::setTheme($skin);
            }
        }   // end function setTheme()

    } // class CAT_Helper_CodeEditor

} // if class_exists()