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
         * retrieves widgets found in the file system
         *
         * result array format:
         *     [] = array(
         *              'module_name'      => <Name>,
         *              'module_directory' => <Path>,
         *              'widget_path'      => <Path>,
         *              'widget_file'      => <File>
         *          )
         *
         * @access public
         * @return array
         **/
        public static function getWidgets($module=NULL)
        {
            global $parser;

            if($module == 'backend') $module = NULL;

            $_chw_data    = array();
            $widgets      = self::findWidgets($module);
            $widget_name  = NULL;
            $addonh       = CAT_Helper_Addons::getInstance();
            $base         = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules');

            foreach( $widgets as $w_path )
            {
                $path     = pathinfo(CAT_Helper_Directory::sanitizePath($w_path),PATHINFO_DIRNAME);
                $infopath = $path;

                // check if path is deeper than CAT_PATH/modules/<module>
                if ( count(explode('/',str_ireplace( $base.'/', '', $path ))) > 1 )
                {
                    $temp     = explode('/',str_ireplace( $base.'/', '', $path ));
                    $infopath = $base.'/'.$temp[0];
                }
                if ( file_exists($infopath.'/info.php') )
                {
                    $info = $addonh->checkInfo($infopath);
                }
                if ( file_exists($infopath.'/languages/'.LANGUAGE.'.php') )
                {
                    $addonh->lang()->addFile(LANGUAGE.'.php', $infopath.'/languages/');
                }

                $widget = array(
                    'module_name'      => $info['module_name'],
                    'module_directory' => $info['module_directory'],
                    'widget_fullpath'  => $path,
                    'widget_path'      => str_replace(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules'),'',$path),
                    'widget_file'      => pathinfo(CAT_Helper_Directory::sanitizePath($w_path),PATHINFO_BASENAME)
                );
                $_chw_data[] = $widget;
            }
            return $_chw_data;
        }   // end function getWidgets()

        /**
         * reads the widgets.config.php (if available) and returns the
         * $widget_config array
         *
         * note: only the first widgets.config.php will be loaded!
         *
         * @access public
         * @param  string  $module
         * @return mixed   array or NULL
         **/
        public static function getWidgetConfig($module=NULL)
        {
            $widget_path = CAT_PATH.'/modules';
            if($module && $module!='backend') $widget_path .= '/'.$module;

            $directories = CAT_Helper_Directory::getInstance()
                           ->maxRecursionDepth(2)
                           ->findDirectories('widgets', $widget_path);

            if(count($directories))
            {
                sort($directories);
                foreach($directories as $dir)
                {
                    if(file_exists($dir.'/widgets.config.php'))
                    {
                        $widget_config = array();
                        require $dir.'/widgets.config.php';
                        return $widget_config;
                    }
                }
            }

            return NULL;
        }   // end function getWidgetConfig()

        /**
         * scans modules (=paths) for widgets
         *
         * @access public
         * @return array
         **/
        public static function findWidgets($module=NULL,$list=NULL)
        {
            $widget_path = CAT_PATH.'/modules';
            if(!$module)           $module       = 'backend';
            if($module!='backend') $widget_path .= '/'.$module;

            // find files called 'widget.php'
            $widgets     = CAT_Helper_Directory::getInstance()
                           ->maxRecursionDepth(2)
                           ->setSkipFiles(array('index.php'))
                           ->findFiles('widget.php', $widget_path);

            if(count($widgets)) sort($widgets);

            // find files in directory called 'widgets'
            $directories = CAT_Helper_Directory::getInstance()
                           ->maxRecursionDepth(2)
                           ->findDirectories('widgets', $widget_path);

            if(count($directories))
            {
                sort($directories);
                if(!is_array($widgets)) $widgets = array();
                foreach($directories as $dir)
                {
// *****************************************************************************
// TODO: Es wäre eleganter, das mit getWidgetConfig() zusammen zu legen
// *****************************************************************************
                    if(file_exists($dir.'/widgets.config.php'))
                    {
                        $widget_config = array();
                        require $dir.'/widgets.config.php';
                        if($module=='backend' && isset($widget_config['allow_global_dashboard']) && $widget_config['allow_global_dashboard'] === false)
                        {
                            continue;
                        }
                    }
                    $files = CAT_Helper_Directory::getInstance()
                             ->setSkipFiles(array('index.php','widgets.config.php'))
                             ->getPHPFiles($dir);
                    sort($files);
                    $widgets = array_merge(
                        $widgets,
                        $files
                    );
                }
            }

            // remove widgets that are already visible
            if($list && is_array($list))
            {
                $basepath = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules');
                for($i=count($widgets)-1;$i>=0;$i--)
                {
                     $path = str_ireplace($basepath,'',$widgets[$i]);
                     $item = CAT_Helper_Array::ArrayFilterByKey($list,'widget_path',$path);
                     if($item) unset($widgets[$i]);
                }
            }

            return $widgets;
        }   // end function findWidgets()

        /**
         * executes the given widget and adds it's output to 'content' key
         *
         * @access public
         * @param  array  $widget
         * @return array
         **/
        public static function render($widget)
        {
            if(!isset($widget['isHidden']) || !$widget['isHidden'])
            {
                // scan for info.php
                $root = explode('/',$widget['widget_path']);
                $info = CAT_Helper_Addons::checkInfo(CAT_PATH.'/modules/'.$root[1]);
                if(is_array($info) && count($info) && isset($info['module_name']))
                {
                    $widget['module_name'] = $info['module_name'];
                }
                if ( file_exists(CAT_PATH.'/modules/'.$root[1].'/languages/'.LANGUAGE.'.php') )
                {
                    self::getInstance()->lang()->addFile(LANGUAGE.'.php', CAT_PATH.'/modules/'.$root[1].'/languages/');
                }
                ob_start();
                    $widget_settings = array();
                    include(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/'.$widget['widget_path']));
                    $content = ob_get_contents();
                ob_clean();

                $widget['content'] = $content;

                if(isset($widget_settings) && is_array($widget_settings))
                {
                    $widget['settings'] = $widget_settings;
                }
            }
            return $widget;
        }   // end function render()
        

    }

}