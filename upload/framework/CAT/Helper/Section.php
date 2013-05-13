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
            self::$instance->db()->query(sprintf(
                'INSERT INTO `%ssections` SET `page_id`=%d, `module`="%s", `position`=%d, `block`=%d;',
                CAT_TABLE_PREFIX, $page_id, $module, $position, $add_to_block
            ));
        	if ( !self::$instance->db()->is_error() )
        	{
        		$section_id = self::$instance->db()->get_one("SELECT LAST_INSERT_ID()");
        		if ( file_exists( CAT_PATH . '/modules/' . $module . '/add.php') )
        			require( CAT_PATH . '/modules/' . $module . '/add.php');
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
            $opt = array( CAT_TABLE_PREFIX, $section_id );
            $sql = 'SELECT * FROM `%ssections` WHERE `section_id`=%d';
            if($active_only)
            {
                $now  = time();
                $sql .= ' AND ( %d BETWEEN `publ_start` AND `publ_end`) OR '
                     .  '( %d > `publ_start` AND `publ_end`=0 ) ';
                array_push($opt,$now,$now);
            }
            $sec = self::getInstance()->db()->query(vsprintf($sql,$opt));
            if($sec->numRows())
            {
                return $sec->fetchRow(MYSQL_ASSOC);
            }
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
            $opt = array( CAT_TABLE_PREFIX, $section_id, $type );
            $sql = 'SELECT * FROM `%ssections` WHERE `section_id`=%d AND `module`="%s"';
            $sec = self::getInstance()->db()->query(vsprintf($sql,$opt));
            if($sec->numRows())
                return true;
            return false;
        }   // end function hasType()

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
            $opt = array( CAT_TABLE_PREFIX, $page_id, $type );
            $sql = 'SELECT `section_id` FROM `%ssections` WHERE `page_id`=%d AND `module`="%s"';
            $sec = self::getInstance()->db()->query(vsprintf($sql,$opt));
            if($sec->numRows())
                return $sec->fetchRow(MYSQL_ASSOC);
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
            $sec = self::getInstance()->db()->query(sprintf(
                'SELECT page_id FROM `%ssections` WHERE `section_id`=%d',
                CAT_TABLE_PREFIX, $section_id
            ));
            if($sec->numRows())
            {
                $result = $sec->fetchRow(MYSQL_ASSOC);
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
            $sql = 'UPDATE `%ssections` SET ';
            foreach($options as $key => $value)
            {
                $sql .= $key.'="'.$value.'", ';
            }
            $sql  = preg_replace('~,\s*$~','',$sql);
            $sql .= ' WHERE section_id = %d LIMIT 1';
		    self::getInstance()->db()->query(sprintf(
                $sql,
                CAT_TABLE_PREFIX, $section_id
            ));
            return self::getInstance()->db()->is_error()
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
        	self::getInstance()->db()->query(sprintf(
                'DELETE FROM `%ssections` WHERE `section_id` = %d LIMIT 1',
                CAT_TABLE_PREFIX, $section_id
            ));
            return self::getInstance()->db()->is_error()
                ? false
                : true;
        }   // end function deleteSection()
        


    }
}
