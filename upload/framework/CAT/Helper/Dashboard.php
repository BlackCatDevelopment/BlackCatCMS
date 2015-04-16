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

if (!class_exists('CAT_Helper_Dashboard'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Dashboard extends CAT_Object
    {
        private static $instance;
        private static $not_on_dashboard = array();

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
        public static function getDashboard($module=NULL)
        {
            global $parser;

            // add JS
            CAT_Helper_Page::addJS('/framework/CAT/Helper/Dashboard/dashboard.js','backend','footer');

            if(file_exists(CAT_PATH.'/templates/'.DEFAULT_THEME.'/css/default/widgets.css'))
            {
                CAT_Helper_Page::addCSS(CAT_URL.'/templates/'.DEFAULT_THEME.'/css/default/widgets.css','backend');
            }

            $config = self::getDashboardConfig($module);
            $layout = explode('-',$config['layout']);
            $cols   = count($layout);

            foreach(range(1,$cols) as $col)
            {
                $config['columns'][$col] = array(
                    'width'   => $layout[($col-1)],
                    'widgets' => array()
                );
                $widgets = CAT_Helper_Array::ArrayFilterByKey($config['widgets'],'column',$col);
                foreach($widgets as $item)
                {
                    $config['columns'][$col]['widgets'][]
                        = CAT_Helper_Widget::render($item);
                }
            }

            return $config;
        }   // end function getDashboard()

        /**
         *
         * @access public
         * @return
         **/
        public static function getDashboardConfig($module=NULL)
        {
            $self     = self::getInstance();
            if(!$module) $module = 'backend';

            $data     = $self->db()->query(
                'SELECT * FROM `:prefix:dashboard` WHERE `user_id`=? AND `module`=?',
                array(CAT_Users::get_user_id(),$module)
            );
            if($data)
            {
                $config = $data->fetchRow();
                if(count($config) && isset($config['widgets']) && $config['widgets'] != '')
                {
                    $config['widgets'] = unserialize($config['widgets']);
                }
            }
            if(!$config)
            {
                $config = self::getDefaultDashboardConfig($module);
                self::saveDashboardConfig($config,$module);
            }
            else
            {
                self::$not_on_dashboard[$module] = CAT_Helper_Widget::findWidgets($module,$config['widgets']);
            }
            return $config;
        }   // end function getDashboardConfig()

        /**
         * get default configuration
         *
         * @access public
         * @return array
         **/
        public static function getDefaultDashboardConfig($module=NULL)
        {
            // note: widgets are sorted by path / filename
            $widgets = CAT_Helper_Widget::getWidgets($module);
            $config  = array('layout'=>'50-50');

            if($module) {
                $cfg = CAT_Helper_Widget::getWidgetConfig($module);
                if(is_array($cfg))
                {
                    $config = array_merge($config,$cfg);
                }
            }

            if(count($widgets))
            {
                $layout = explode('-',$config['layout']);
                $cols   = count($layout);
                $percol = floor(count($widgets)/$cols);
                foreach(range(1,$cols) as $column)
                {
                    $col   = array_splice($widgets,0,$percol);
                    foreach($col as $item)
                    {
                        $config['widgets'][] = array(
                            'column'      => $column,
                            'widget_path' => $item['widget_path'].'/'.$item['widget_file'],
                            'isHidden'    => false,
                            'isMinimized' => false
                        );
                    }
                }
                if(count($widgets)) //
                {
                    $col = 1;
                    foreach($widgets as $item)
                    {
                        $config['widgets'][] = array(
                            'column'      => $col,
                            'widget_path' => $item['widget_path'].'/'.$item['widget_file'],
                            'isHidden'    => false,
                            'isMinimized' => false
                        );
                        $col++;
                    }
                }
            }
            return $config;
        }   // end function getDefaultDashboardConfig()

        /**
         * get widgets not shown on the current dashboard (removed before)
         *
         * @access public
         * @return
         **/
        public static function getNotShown($module=NULL)
        {
            if(!$module) $module = 'backend';
            if(isset(self::$not_on_dashboard[$module]))
            {
                $list = array();
                $base = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules');
                foreach(array_values(self::$not_on_dashboard[$module]) as $item)
                {
                    ob_start();
                        $widget_settings = array();
                        include(CAT_Helper_Directory::sanitizePath($item));
                    ob_clean();
                    $list[] = array(
                        'path'  => str_replace($base,'',$item),
                        'title' => $widget_settings['widget_title']
                    );
                }
                return $list;
            }
            return false;
        }   // end function getNotShown()
        

        /**
         * allows to manage the widgets on a dashboard: hide/show, reorder,
         * move from one column to another
         *
         * @access public
         * @param  string  $module
         * @return boolean
         **/
        public static function manageWidgets($module=NULL)
        {
            $user    = CAT_Users::getInstance();
            $action  = CAT_Helper_Validate::sanitizePost('action');
            $result  = false;
            $module  = CAT_Helper_Validate::sanitizePost('module');
            switch($action) {
                case 'hide':
                    $result = CAT_Helper_Dashboard::hideWidget(CAT_Helper_Validate::sanitizePost('widget'),$module);
                    break;
                case 'show':
                    $result = CAT_Helper_Dashboard::showWidget(CAT_Helper_Validate::sanitizePost('widget'),$module);
                    break;
                case 'reorder':
                    // column is 0-based in the HTML, but 1-based in the code
                    $result = CAT_Helper_Dashboard::reorderColumn(
                        (CAT_Helper_Validate::sanitizePost('column')+1),
                        CAT_Helper_Validate::sanitizePost('order'),
                        $module
                    );
                    break;
                case 'move':
                    $result = CAT_Helper_Dashboard::moveWidget(CAT_Helper_Validate::sanitizePost('widget'),CAT_Helper_Validate::sanitizePost('items'),$module);
                    break;
                case 'remove':
                    $result = CAT_Helper_Dashboard::removeWidget(CAT_Helper_Validate::sanitizePost('widget'),$module);
                    break;
                case 'add':
                    $result = CAT_Helper_Dashboard::addWidget(CAT_Helper_Validate::sanitizePost('widget'),$module);
                    break;
                case 'reset':
                    $result = self::resetDashboard($module);
                    break;
            }
            return $result;
        }   // end function manageWidgets()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function addWidget($widget,$module=NULL)
        {
            $config  = self::getDashboardConfig($module);
            $item    = CAT_Helper_Array::ArrayFilterByKey($config['widgets'],'widget_path',$widget);
            if(!is_array($item) || !count($item)) // already there
            {
                $layout = explode('-',$config['layout']);
                $cols   = count($layout);
                $column = 1;
                // get the settings
                ob_start();
                    $widget_settings = array();
                    include(CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/'.$widget));
                ob_clean();
                // preferred column?
                if(isset($widget_settings['preferred_column']))
                {
                    if($widget_settings['preferred_column'] > $cols)
                    {
                        $column = $cols;
                    }
                    else
                    {
                        $column = $widget_settings['preferred_column'];
                    }
                }
                $config['widgets'][] = array(
                    'column'      => $column,
                    'widget_path' => $widget,
                    'isHidden'    => false,
                    'isMinimized' => false
                );
                return self::saveDashboardConfig($config,$module);
            }
            else
            {
                return false;
            }
        }   // end function addWidget()

        /**
         * hide (minimize) the widget
         *
         * @access public
         * @param  string  $widget
         * @return boolean
         **/
        public static function hideWidget($widget,$module=NULL)
        {
            $config  = self::getDashboardConfig($module);
            $item    = CAT_Helper_Array::ArrayFilterByKey($config['widgets'],'widget_path',$widget);
            if(is_array($item))
            {
                $item[0]['isMinimized'] = true;
                $config['widgets'] = array_merge($config['widgets'],$item);
                return self::saveDashboardConfig($config,$module);
            }
            return false;
        }   // end function hideWidget()

        /**
         * unhide (maximize) the widget
         *
         * @access public
         * @param  string  $widget
         * @return boolean
         **/
        public static function showWidget($widget,$module=NULL)
        {
            $config  = self::getDashboardConfig($module);
            $item    = CAT_Helper_Array::ArrayFilterByKey($config['widgets'],'widget_path',$widget);
            if(is_array($item))
            {
                $item[0]['isMinimized'] = false;
                $config['widgets'] = array_merge($config['widgets'],$item);
                return self::saveDashboardConfig($config);
            }
            return false;
        }   // end function showWidget()

        /**
         * moves a widget from one column to another
         *
         * incoming structure:
         *     $items => array(
         *         'source' => array('column' => <Number>, 'items' => <Array>)
         *         'target' => array('column' => <Number>, 'items' => <Array>)
         *     );
         *
         * @access public
         * @param  string $widget - the widget to be moved
         * @param  array  $items  - current items
         * @param  string $module - module name or 'backend'
         * @return
         **/
        public static function moveWidget($widget,$items,$module=NULL)
        {
            // get current config
            $config  = self::getDashboardConfig($module);
            // retrieve widgets from source column
            $source_column  = ($items['source']['column']+1);
            $source_widgets = CAT_Helper_Array::ArrayFilterByKey($config['widgets'],'column',$source_column);
            // retrieve widgets from target column
            $target_column  = ($items['target']['column']+1);
            $target_widgets = CAT_Helper_Array::ArrayFilterByKey($config['widgets'],'column',$target_column);
            // filter moved element from source column
            $element        = CAT_Helper_Array::ArrayFilterByKey($source_widgets,'widget_path',$widget);
            if(is_array($element))
            {
                $element = $element[0];
                // move element to target
                $element['column'] = $target_column;
                $target_widgets[]  = $element;
                // save as new config
                $config['widgets'] = array_merge(
                    $config['widgets'],
                    $source_widgets,
                    $target_widgets
                );
                if(self::saveDashboardConfig($config,$module))
                {
                    // reorder target
                    self::reorderColumn($target_column,$items['target']['items'],$module);
                }
            }
        }   // end function moveWidget()

        /**
         * removes a widget from the dashboard
         *
         * @access public
         * @param  string  $widget
         * @param  string  $module
         * @return boolean
         **/
        public static function removeWidget($widget,$module=NULL)
        {
            $config  = self::getDashboardConfig($module);
            $item    = CAT_Helper_Array::ArrayFilterByKey($config['widgets'],'widget_path',$widget);
            if(is_array($item))
            {
                return self::saveDashboardConfig($config,$module);
            }
            return false;
        }   // end function removeWidget()

        /**
         *
         * @access public
         * @return
         **/
        public static function renderDashboard($module=NULL,$direct_output=false)
        {
            global $parser;
            $config = self::getDashboard($module);
            $hidden = self::getNotShown($module);
            $parser->setPath(CAT_PATH.'/framework/CAT/Helper/Dashboard');
            if($direct_output)
            {
                $parser->output('dashboard.tpl',array('dashboard'=>$config,'module'=>$module,'addable'=>$hidden));
                $parser->resetPath();
            }
            else {
                $content = $parser->get('dashboard.tpl',array('dashboard'=>$config,'module'=>$module,'addable'=>$hidden));
                $parser->resetPath();
                return $content;
            }
            
        }   // end function renderDashboard()

        /**
         *
         * @access public
         * @return
         **/
        public static function reorderColumn($column,$order,$module=NULL)
        {
            $config  = self::getDashboardConfig($module);
            $widgets = CAT_Helper_Array::ArrayFilterByKey($config['widgets'],'column',$column);
            usort($widgets, function($a, $b) use ($order) {
                return array_search($a['widget_path'], $order) - array_search($b['widget_path'], $order);
            });
            $config['widgets'] = array_merge($config['widgets'],$widgets);
            return self::saveDashboardConfig($config,$module);
        }   // end function reorderColumn()

        /**
         * resets the dashboard by loading defaults
         *
         * @access public
         * @return
         **/
        public static function resetDashboard($module=NULL)
        {
            $self     = self::getInstance();
            if(!$module || $module == '') $module = 'backend';
            $self->db()->query(
                'DELETE FROM `:prefix:dashboard` WHERE `user_id`=? AND `module`=?',
                array( CAT_Users::get_user_id(), $module )
            );
            $config = self::getDefaultDashboardConfig($module);
            return self::saveDashboardConfig($config,$module);
        }   // end function resetDashboard()
        

        /**
         * save dashboard configuration
         *
         * @access public
         * @param  array   $config - config to save
         * @param  string  $module - for which module ('backend' for BE)
         * @return boolean
         **/
        public static function saveDashboardConfig($config,$module=NULL)
        {
            $self     = self::getInstance();
            $action   = 'REPLACE';
            if(!isset($config['user_id']))
            {
                $config['user_id'] = CAT_Users::get_user_id();
                $action = 'INSERT';
            }
            if(!isset($config['layout']))  $config['layout']  = '50-50';

            if(!$module)
                $module = 'backend';

            $self->db()->query(
                $action . ' INTO `:prefix:dashboard` (`user_id`,`module`,`layout`,`widgets`) VALUES( ?, ?, ?, ? )',
                array($config['user_id'],$module,$config['layout'],serialize($config['widgets']))
            );

            return ( $self->db()->isError() ? false : true );
        }   // end function saveDashboardConfig()
    }
}