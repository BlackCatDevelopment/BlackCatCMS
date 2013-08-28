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

if (!class_exists('CAT_Helper_Widget'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Widget extends CAT_Object
    {
        private static $instance;

        public static function getInstance()
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }

        /**
         *
         * @access public
         * @return
         **/
        public static function getWidgets()
        {
            global $parser;
            $_chw_data    = array();
            $widgets      = self::findWidgets();
            $widget_name  = NULL;
            $addonh       = CAT_Helper_Addons::getInstance();
            $base         = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules');
            foreach( $widgets as $widget )
            {
                $path = pathinfo(CAT_Helper_Directory::sanitizePath($widget),PATHINFO_DIRNAME);
                $info = $content = NULL;
                // check if path is deeper than CAT_PATH/modules/<module>
                if ( count(explode('/',str_ireplace( $base.'/', '', $path ))) > 1 )
                {
                    $temp = explode('/',str_ireplace( $base.'/', '', $path ));
                    $path = $base.'/'.$temp[0];
                }
                if ( file_exists($path.'/info.php') )
                {
                    $info = $addonh->checkInfo($path);
                }
                if ( file_exists($path.'/languages/'.LANGUAGE.'.php') )
                {
                    $addonh->lang()->addFile(LANGUAGE.'.php', $path.'/languages/');
                }
                ob_start();
                    $widget_name  = NULL;
                    include($widget);
                    $content = ob_get_contents();
                ob_clean();
                $_chw_data[$widget] = array_merge( ( is_array($info) ? $info : array() ), array('content'=>$content) );
                if($widget_name)
                    $_chw_data[$widget]['module_name'] .= ' - '.$widget_name;
            }

            return $_chw_data;
        }   // end function getWidgets()
        
        /**
         * scans modules for widgets
         *
         * @access public
         * @return array
         **/
        public static function findWidgets()
        {
            // find files called widget.php
            $widgets     = CAT_Helper_Directory::getInstance()->maxRecursionDepth(2)->setSkipFiles(array('index.php'))->findFiles( 'widget.php', CAT_PATH.'/modules' );
            // find files in directory called widgets
            $directories = CAT_Helper_Directory::getInstance()->maxRecursionDepth(2)->findDirectories( 'widgets', CAT_PATH.'/modules' );
            if(count($directories))
            {
                if(!is_array($widgets)) $widgets = array();
                foreach($directories as $dir)
                {
                    $widgets = array_merge($widgets, CAT_Helper_Directory::getInstance()->setSkipFiles(array('index.php'))->getPHPFiles($dir));
                }
            }
            return $widgets;
        }   // end function findWidgets()

    }

}