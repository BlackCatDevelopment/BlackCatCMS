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
        protected      $_config = array(
                           'loglevel' => 4
                       );
        protected      $debugLevel = 4;

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

            if($module == 'global') $module = NULL;

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
                    'widget_path'      => str_replace($base,'',$path),
                    'widget_file'      => pathinfo(CAT_Helper_Directory::sanitizePath($w_path),PATHINFO_BASENAME)
                );
                $_chw_data[] = $widget;
            }
            return $_chw_data;
        }   // end function getWidgets()

        /**
         *
         * @access public
         * @return
         **/
        public static function getWidgetConfig($item)
        {
            $widget_settings = array();
            try {
                ob_start();
                    include(CAT_Helper_Directory::sanitizePath($item));
                ob_clean();
            } catch ( Exception $e ) {}
            return $widget_settings;
        }   // end function getWidgetConfig()
        
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
        public static function getGlobalWidgetConfig($module=NULL)
        {
            $widget_path = CAT_PATH.'/modules';
            if($module && $module!='global') $widget_path .= '/'.$module;

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
        }   // end function getGlobalWidgetConfig()

        /**
         * scans modules (=paths) for widgets
         *
         * @access public
         * @param  string  $module - 'global' or module name
         * @param  array   $list   - optional list of widgets to filter out
         * @return array
         **/
        public static function findWidgets($module=NULL,$list=NULL)
        {
            $widget_path = CAT_PATH.'/modules';
            $self        = self::getInstance(); // for logger

            if(!$module)          $module       = 'global';
            if($module!='global') $widget_path .= '/'.$module;

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

            $self->log()->logDebug(sprintf('directories called [widgets] in path [%s]:',$widget_path),$directories);

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
                        $self->log()->logDebug(sprintf('global widget config for dir [%s]',$dir),$widget_config);
                        // check global setting
                        if($module=='global' && isset($widget_config['allow_global_dashboard']) && $widget_config['allow_global_dashboard'] === false)
                        {
                            $self->log()->logDebug('skipping the module, as [allow_global_dashboard] is set to false');
                            continue;
                        }
                    }
                    $files = CAT_Helper_Directory::getInstance()
                             ->setSkipFiles(array('index.php','widgets.config.php'))
                             ->getPHPFiles($dir);
                    sort($files);
                    $self->log()->logDebug('files:',$files);
                    // check local settings
                    for($i=count($files)-1;$i>=0;$i--)
                    {
                        $widget_config = self::getWidgetConfig($files[$i]);
                        $self->log()->logDebug(sprintf('widget config for [%s]',$files[$i]),$widget_config);
                        if($module=='global')
                        {
                            if(isset($widget_config['allow_global_dashboard']) && $widget_config['allow_global_dashboard'] === false)
                            {
                                unset($files[$i]);
                                $self->log()->logDebug('skipping current widget, [allow_global_dashboard] is false');
                            }
                            elseif(!$list && isset($widget_config['auto_add_global_dashboard']) && $widget_config['auto_add_global_dashboard'] === false)
                            {
                                $self->log()->logDebug('skipping current widget, [auto_add_global_dashboard] is false');
                                unset($files[$i]);
                            }
                        }
                    }
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
                if(file_exists(CAT_PATH.'/modules/'.$root[1].'/css/backend.css'))
                {
                    CAT_Helper_Page::addCSS(CAT_URL.'/modules/'.$root[1].'/css/backend.css','backend');
                }
                $widget_settings = array();
                $temp            = explode('/',CAT_Helper_Directory::sanitizePath($widget['widget_path']));
                while( ($module_dir = array_shift($temp)) == '' ) { /* just skip */ }
                $function_name   = 'render_widget_'.$module_dir.'_'.pathinfo($widget['widget_path'],PATHINFO_FILENAME);
                include CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/'.$widget['widget_path']);
                if(function_exists($function_name))
                {
                    $widget['content'] = $function_name();
                    if(isset($widget_settings) && is_array($widget_settings))
                    {
                        $widget['settings'] = $widget_settings;
                    }
                }
            }
            return $widget;
        }   // end function render()
        

    }

}