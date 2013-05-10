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
    @include dirname(__FILE__).'/Object.php';
}

if ( ! class_exists( 'CAT_Sections', false ) ) {

	class CAT_Sections extends CAT_Object
	{
	
        protected      $_config  = array( 'loglevel' => 7 );

        private static $active   = array();
        private static $instance = NULL;

	    /**
         * constructor
	     *
         * @access private
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

        /**
         * allow to use methods in OO context
         **/
        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }   // end function __call()

	    /**
         *
         * @access public
         * @return
         **/
        public static function addSection($page_id,$module,$add_to_block)
        {
            $self = self::getInstance();
        	require(CAT_PATH.'/framework/class.order.php');
        	$order    = new order(CAT_TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
        	$position = $order->get_new($page_id);
        	$self->db()->query(sprintf(
                'INSERT INTO `%ssections` SET `page_id`=%d, `module`="%s", `position`=%d, `block`=%d;',
                CAT_TABLE_PREFIX, $page_id, $module, $position, $add_to_block
            ));

        	if ( !$self->db()->is_error() )
        		// Get the section id
        		return $self->db()->get_one("SELECT LAST_INSERT_ID()");
            else
                return false;
        }   // end function addSection()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function deleteSection($section_id,$page_id)
        {
            $self = self::getInstance();
        	$q    = $self->db()->query(sprintf(
                'DELETE FROM `%ssections` WHERE `section_id` = %d LIMIT 1',
                CAT_TABLE_PREFIX, $section_id
            ));

        	if ( $self->db()->is_error() )
        	{
        		return false;
        	}
        	else
        	{
        		require CAT_PATH.'/framework/class.order.php';
        		$order = new order(CAT_TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
        		$order->clean($page_id);
                return true;
        	}
        }   // end function deleteSection()
        

	    /**
	     * retrieves all active sections for a page
	     *
	     * @access public
	     * @param  integer  $page_id
	     * @param  integer  $block    optional block ID
	     * @param  boolean  $backend  default false
	     * @return array()
	     **/
	    public static function getActiveSections( $page_id, $block = null, $backend = false )
	    {
            $active = ( isset(self::$active) && isset(self::$active[$page_id]) && is_array(self::$active[$page_id]) )
                    ? self::$active[$page_id]
                    : NULL;

	        if (!$active)
	        {

                if(!self::$instance)
                    self::getInstance();

	            // First, get all sections for this page
	            $sec = self::$instance->db()->query(sprintf(
                      'SELECT section_id, module, block, publ_start, publ_end FROM %ssections '
                    . 'WHERE page_id = "%d" ORDER BY block, position',
                    CAT_TABLE_PREFIX, $page_id
                ));

	            if ($sec->numRows() == 0)
	            {
	                return NULL;
	            }

	            while ($section = $sec->fetchRow(MYSQL_ASSOC))
	            {
	                // skip this section if it is out of publication-date
	                $now = time();
	                if (!(($now <= $section['publ_end'] || $section['publ_end'] == 0) && ($now >= $section['publ_start'] || $section['publ_start'] == 0)))
	                {
	                    continue;
	                }
	                self::$active[$page_id][$section['block']][] = $section;
	            }
	        }

	        if ( $block )
	        {
				return ( isset( self::$active[$page_id][$block] ) )
					? self::$active[$page_id][$block]
					: array();
			}

			$all = array();
			foreach( self::$active[$page_id] as $block => $values )
			{
				foreach( $values as $value )
				{
			    	array_push( $all, $value );
				}
			}
			
			return $all;
			
	    }   // end function getActiveSections()
	    
	    /**
         *
         * @access public
         * @return
         **/
        public static function getSection($section_id)
        {
            $self = self::getInstance();
        	$q = $self->db()->query(sprintf(
                'SELECT `module` FROM `%ssections` WHERE `section_id` = %d',
                CAT_TABLE_PREFIX, $section_id
            ));
        	if($q->numRows() == 0)
                return false;
        	return $q->fetchRow(MYSQL_ASSOC);
        }   // end function getSection()
        
	    
	    /**
	     * checks if a page has active sections
	     *
	     * @access public
	     * @param  integer $page_id
	     * @return boolean
	     *
	     **/
	    public static function hasActiveSections( $page_id )
	        {
	        if (!isset(self::$active[$page_id]) )
	            self::getActiveSections($page_id);
	        return ( count(self::$active[$page_id]) ? true : false );
	    }   // end function hasActiveSections()

        /**
         * checks if given section is active
         *
         * @access public
         * @param  int    $section_id
         * @return boolean
         **/
        public static function section_is_active($section_id)
        {
            global $database;
            $now = time();
            $sql = 'SELECT COUNT(*) FROM `' . CAT_TABLE_PREFIX . 'sections` ';
            $sql .= 'WHERE (' . $now . ' BETWEEN `publ_start` AND `publ_end`) OR ';
            $sql .= '(' . $now . ' > `publ_start` AND `publ_end`=0) ';
            $sql .= 'AND `section_id`=' . $section_id;
            return($database->get_one($sql) != false);
	    }

        /**
         * checks if given page is of type menu_link
         *
         * @access public
         * @param  integer $page_id
         * @return boolean
         **/
        public static function isMenuLink($page_id)
        {
            if(!self::$instance)
                self::getInstance();
            $res = self::$instance->db()->query(sprintf(
                  'SELECT module FROM `%ssections` '
                . 'WHERE `page_id` = %d AND `module` = "menu_link"',
                CAT_TABLE_PREFIX,
                $page_id
            ));
            if($res && $res->numRows())
                return true;
            return false;
        }   // end function isMenuLink()


	}
}

?>