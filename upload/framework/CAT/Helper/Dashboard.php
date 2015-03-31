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
        public static function getDashboard()
        {
            $config = self::getDashboardConfig();
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
        }

        /**
         *
         * @access public
         * @return
         **/
        public static function getDashboardConfig()
        {
            $self     = self::getInstance();
            $data     = $self->db()->query(
                'SELECT * FROM `:prefix:dashboard` WHERE `user_id`=?',
                array(CAT_Users::get_user_id())
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
                $config = self::getDefaultDashboardConfig();
                self::saveDashboardConfig($config);
            }
            return $config;
        }   // end function getDashboardConfig()

        /**
         * get default configuration
         *
         * @access public
         * @return array
         **/
        public static function getDefaultDashboardConfig()
        {
            // noted: widgets are sorted by path / filename
            $widgets = CAT_Helper_Widget::getWidgets();
            $config  = array('layout'=>'50-50');
            if(count($widgets))
            {
                $percol = ceil(count($widgets)/2);
                foreach(array(1,2) as $column)
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
            }
            return $config;
        }   // end function getDefaultDashboardConfig()

        /**
         *
         * @access public
         * @return
         **/
        public static function hideWidget($widget)
        {
            $config  = self::getDashboardConfig();
            $item    = CAT_Helper_Array::ArrayFilterByKey($config['widgets'],'widget_path',$widget);
            if(is_array($item))
            {
                $item[0]['isMinimized'] = true;
                $config['widgets'] = array_merge($config['widgets'],$item);
                self::saveDashboardConfig($config);
            }
        }   // end function hideWidget()

        /**
         *
         * @access public
         * @return
         **/
        public static function showWidget($widget)
        {
            $config  = self::getDashboardConfig();
            $item    = CAT_Helper_Array::ArrayFilterByKey($config['widgets'],'widget_path',$widget);
            if(is_array($item))
            {
                $item[0]['isMinimized'] = false;
                $config['widgets'] = array_merge($config['widgets'],$item);
                self::saveDashboardConfig($config);
            }
        }   // end function hideWidget()

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
         * @param  array  $items
         * @return
         **/
        public static function moveWidget($items)
        {
            // get current config
            $config  = self::getDashboardConfig();
            // retrieve widgets from source column
            $source_column  = ($items['source']['column']+1);
            $source_widgets = CAT_Helper_Array::ArrayFilterByKey($config['widgets'],'column',$source_column);
            // retrieve widgets from target column
            $target_column  = ($items['target']['column']+1);
            $target_widgets = CAT_Helper_Array::ArrayFilterByKey($config['widgets'],'column',$target_column);
            // filter moved element from source column
            $filter         = $items['source']['items'];
            foreach ($source_widgets as $i => $element)
            {
                if(!in_array($element['widget_path'],$filter))
                {
                    unset($source_widgets[$i]);
                    break;
                }
            }
            if(is_array($element))
            {
                // move element to target
                $element['column'] = $target_column;
                $target_widgets[]  = $element;
                // save as new config
                $config['widgets'] = array_merge(
                    $config['widgets'],
                    $source_widgets,
                    $target_widgets
                );
                self::saveDashboardConfig($config);
                // reorder target
                self::reorderColumn($items['target']['column']+1,$items['target']['items']);
                // reorder source
                self::reorderColumn($items['source']['column']+1,$items['source']['items']);
            }
        }   // end function moveWidget()

        /**
         *
         * @access public
         * @return
         **/
        public static function reorderColumn($column,$order)
        {
            $config  = self::getDashboardConfig();
            $widgets = CAT_Helper_Array::ArrayFilterByKey($config['widgets'],'column',$column);
            usort($widgets, function($a, $b) use ($order) {
                return array_search($a['widget_path'], $order) - array_search($b['widget_path'], $order);
            });
            $config['widgets'] = array_merge($config['widgets'],$widgets);
            self::saveDashboardConfig($config);
        }   // end function reorderColumn()
        

        /**
         * save dashboard configuration
         *
         * @access public
         * @return boolean
         **/
        public static function saveDashboardConfig($config)
        {
            $self     = self::getInstance();
            $action   = 'REPLACE';
            if(!isset($config['user_id']))
            {
                $config['user_id'] = CAT_Users::get_user_id();
                $action = 'INSERT';
            }
            if(!isset($config['layout']))  $config['layout']  = '50-50';
            $self->db()->query(
                $action . ' INTO `:prefix:dashboard` (`user_id`,`layout`,`widgets`) VALUES( ?, ?, ? )',
                array($config['user_id'],$config['layout'],serialize($config['widgets']))
            );
        }   // end function saveDashboardConfig()
        
        
    }
}