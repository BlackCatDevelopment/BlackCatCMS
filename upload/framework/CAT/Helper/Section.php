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

if (!class_exists('CAT_Helper_Section'))
{
    if (!class_exists('CAT_Object', false))
    {
        @include dirname(__FILE__) . '/../Object.php';
    }

    class CAT_Helper_Section extends CAT_Object
    {
        protected      $_config             = array(
            'loglevel'  => 8,
        );
        private static $instance;

        /**
         * the constructor
         *
         * @access public
         * @return void
         **/
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
         * adds a new section
         *
         * @access public
         * @param  integer  $page_id
         * @param  string   $module
         * @param  integer  $position
         * @param  integer  $add_to_block
         * @return boolean
         **/
        public static function addSection($page_id, $module, $position, $add_to_block)
        {
            if(!self::$instance) self::getInstance(true);
            self::$instance->db()->query(
                'INSERT INTO `:prefix:sections` SET `page_id`=:page_id, `module`=:module, `position`=:pos, `block`=:block_id',
                array(
                    'page_id'  => $page_id,
                    'module'   => $module,
                    'pos'      => $position,
                    'block_id' => $add_to_block
                )
            );
        	if ( !self::$instance->db()->isError() )
        	{
        		// $section_id = self::$instance->db()->lastInsertId();
        		if ( file_exists( CAT_PATH.'/modules/'.$module.'/add.php') )
        			require( CAT_PATH.'/modules/'.$module.'/add.php');
                return true;
        	}
            return false;
        }   // end function addSection()

        /**
         * returns the properties of a given section
         *
         * @access public
         * @param  integer  $section_id
         * @param  boolean  $active_only
         * @return
         **/
        public static function getSection($section_id, $active_only = false)
        {
            $sql    = 'SELECT * FROM `:prefix:sections` WHERE `section_id`=:id';
            $params = array('id'=>$section_id);
            if($active_only)
            {
                $now  = time();
                $sql .= ' AND ( :time1 BETWEEN `publ_start` AND `publ_end`) OR '
                     .  '( :time2 > `publ_start` AND `publ_end`=0 ) ';
                $params['time1'] = $params['time2'] = $now;
            }
            $sec = self::getInstance()->db()->query($sql,$params);
            if($sec->rowCount())
                return $sec->fetch();
            return false;
        }   // end function getSection()

        /**
         * 
         *
         * @access public
         * @param  integer  $section_id
         * @return
         **/
        public static function hasType($section_id,$type)
        {
            $opt = array('id'=>$section_id, 'mod'=>$type );
            $sql = 'SELECT * FROM `:prefix:sections` WHERE `section_id`=:id AND `module`=:mod';
            $sec = self::getInstance()->db()->query($sql,$opt);
            if($sec->rowCount())
                return true;
            return false;
        }   // end function hasType()

        /**
         *
         * @access public
         * @return
         **/
        public static function getActiveSections($page_id=NULL)
        {
            $result = array();
            $now    = time();
            $sql    = 'SELECT * FROM `:prefix:sections` '
                    . 'WHERE ( :now1 BETWEEN `publ_start` AND `publ_end`) OR '
                    . '      ( :now2 > `publ_start` AND `publ_end`=0)';
            $params = array('now1'=>$now,'now2'=>$now);
            if($page_id)
            {
                $sql .= ' AND `page_id`=:page_id';
                $params['page_id'] = $page_id;
            }
            $sec = self::getInstance(true)->db()->query($sql,$params);
            if ( $sec->rowCount() > 0 )
                while ( false !== ( $section = $sec->fetch() ) )
                    $result[$section['page_id']][] = $section;
            return $result;
        }   // end function getActiveSections()
        

        /**
         * gets the first section for given $page_id that has a module of type $type
         *
         * @access public
         * @param  integer  $page_id
         * @param  string   $type
         * @return mixed    result array containing the section_id on success,
         *                  false otherwise (no such section)
         **/
        public static function getSectionForPage($page_id,$type=NULL)
        {
            $opt = array('page_id'=>$page_id, 'module'=>$type);
            $sql = 'SELECT `section_id` FROM `:prefix:sections` WHERE `page_id`=:page_id AND `module`=:module';
            $sec = self::getInstance()->db()->query($sql,$opt);
            if($sec->rowCount())
                return $sec->fetch();
            return false;
        }   // end function getSectionForPage()

        /**
         * gets the page_id for a given section
         *
         * @access public
         * @param  integer $section_id
         * @return integer
         **/
        public static function getSectionPage($section_id)
        {
            $sec = self::getInstance()->db()->query(
                'SELECT `page_id` FROM `:prefix:sections` WHERE `section_id`=:id',
                array('id'=>$section_id)
            );
            if($sec->rowCount())
            {
                $result = $sec->fetch();
                return $result['page_id'];
            }
        }   // end function getSectionPage()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function updateSection($section_id, $options)
        {
            $sql    = 'UPDATE `:prefix:sections` SET ';
            $params = array( 'id' => $section_id );
            foreach($options as $key => $value)
            {
                $sql .= '`'.$key.'`=:'.$key.', ';
                $params[$key] = $value;
            }
            $sql  = preg_replace('~,\s*$~','',$sql);
            $sql .= ' WHERE section_id = :id LIMIT 1';
		    self::getInstance()->db()->query($sql,$params);
            return self::getInstance()->db()->isError()
                ? false
                : true;
        }   // end function updateSection()
        
        
        /**
         *
         * @access public
         * @return
         **/
        public static function deleteSection($section_id)
        {
        	self::getInstance()->db()->query(
                'DELETE FROM `:prefix:sections` WHERE `section_id` = :id LIMIT 1',
                array('id'=>$section_id)
            );
            return self::getInstance()->db()->isError()
                ? false
                : true;
        }   // end function deleteSection()

    }
}
