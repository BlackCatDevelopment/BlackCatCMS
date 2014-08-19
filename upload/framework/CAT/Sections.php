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
        	$self->db()->query(
                'INSERT INTO `:prefix:sections` SET `page_id`=:id, `module`=:module, `position`=:pos, `block`=:block',
                array('id'=>$page_id, 'module'=>$module, 'pos'=>$position, 'block'=>$add_to_block)
            );

        	if ( !$self->db()->isError() )
        		return $self->db()->lastInsertId(); // Get the section id
            else
                return false;
        }   // end function addSection()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function updateSection($section_id, $options)
        {
            $sql    = 'UPDATE `:prefix:sections` SET ';
            $params = array('id'=>$section_id);
            foreach($options as $key => $value)
            {
                $sql .= $key.' = :'.$key.', ';
                $params[$key] = $value;
            }
            $sql  = preg_replace('~,\s*$~','',$sql);
            $sql .= ' WHERE section_id = :id LIMIT 1';

		    self::getInstance()->db()->query(
                $sql,
                $params
            );
            return self::getInstance()->db()->is_error()
                ? false
                : true;
        }   // end function updateSection()

        /**
         *
         * @access public
         * @return
         **/
        public static function deleteSection($section_id,$page_id)
        {
            $self = self::getInstance();
        	$q    = $self->db()->query(
                'DELETE FROM `:prefix:sections` WHERE `section_id`=:id',
                array('id'=>$section_id)
            );

        	if ( $self->db()->isError() )
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
	     * if $page_id is empty, all active sections are returned
	     *
	     * @access public
	     * @param  integer  $page_id
	     * @param  integer  $block    optional block ID
	     * @param  boolean  $backend  default false
	     * @return array()
	     **/
	    public static function getActiveSections($page_id=NULL, $block=null, $backend=false)
	    {
            // cache data
	        if (!self::$active)
	        {
                if(!self::$instance)
                    self::getInstance();
                // get all sections for all pages
                $q      = 'SELECT *'
                        . ' FROM `:prefix:sections` '
                        . ' ORDER BY block, position';
	            $sec    = self::$instance->db()->query($q);
	            if ($sec->rowCount() == 0)
	                return NULL;
	            while ($section = $sec->fetch())
	            {
	                // skip this section if it is out of publication-date
	                $now = time();
	                if (!(($now <= $section['publ_end'] || $section['publ_end'] == 0) && ($now >= $section['publ_start'] || $section['publ_start'] == 0)))
	                    continue;
	                self::$active[$section['page_id']][$section['block']][] = $section;
	            }
	        }

            // if a block is given
	        if ( $block )
				return ( isset( self::$active[$page_id][$block] ) )
					? self::$active[$page_id][$block]
					: array();

            // if a page_id is given
            if($page_id)
            {
    			$all = array();
    			foreach( self::$active[$page_id] as $block => $values )
    				foreach( $values as $value )
    			    	array_push( $all, $value );
    			return $all;
            }

            // default
            return self::$active;
			
	    }   // end function getActiveSections()
	    
	    /**
         *
         * @access public
         * @return
         **/
        public static function getSection($section_id)
        {
            $self = self::getInstance();
        	$q = $self->db()->query(
                'SELECT * FROM `:prefix:sections` WHERE `section_id` = :id',
                array('id'=>$section_id)
            );
        	if($q->rowCount() == 0)
                return false;
        	return $q->fetch();
        }   // end function getSection()

        /**
         *
         * @access public
         * @return
         **/
        public static function getSections($page_id)
        {
            $self = self::getInstance();
            $q    = $self->db()->query(
                'SELECT * FROM `:prefix:sections` WHERE `page_id` = :page_id ORDER BY position ASC',
                array('page_id'=>$page_id)
            );
            if($q->rowCount())
                return $q->fetchAll();
            return array();
        }   // end function getSections()
        
	    /**
         * gets all sections of given type; if a page_id is given, for that
         * page only, all sections of this type otherwise
          *
          * @access public
         * @param  integer  $page_id (default NULL = all)
         * @param  string   $type    (default 'wysiwyg')
         * @param  integer  $limit   (default 1)
         * @param  boolean  $all     (default false) skip sections out of pub time
         * @return array
          **/
        public static function getSectionsByType($page_id=NULL,$type='wysiwyg',$limit=1,$all=false)
        {
            $limit  = ( isset($limit) && $limit && is_int($limit) )
                    ? $limit
                    : 1;
            $pub_sql = NULL;
            $result  = NULL;
            $params  = array();
            if(!$all)
            {
                $now     = time();
                $pub_sql = '(( :time1 BETWEEN `publ_start` AND `publ_end`) OR '
                         . '( :time2 > `publ_start` AND `publ_end`=0)) '
                         ;
                $params  = array('time1'=>$now,'time2'=>$now);
            }
            $self   = self::getInstance();
            $SQL    = "SELECT `section_id`, `page_id` FROM `:prefix:sections` "
                    . "WHERE "
                    . ( $page_id ? "`page_id` = :page_id  AND " : '' )
                    . "`module` = :module AND `section_id`>0 "
                    . ( $pub_sql ? ' AND '.$pub_sql : '' )
                    . "ORDER BY `position` ASC LIMIT " . $limit;
            $params['module'] = $type;
            if($page_id) $params['page_id'] = $page_id;
            $result = $self->db()->query($SQL,$params);
            return $result->rowCount()
                ?  $result->fetchAll()
                :  false;
         }   // end function getSectionsByType()

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
        public static function getPageForSection($section_id)
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
        }   // end function getPageForSection()

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
            $sql = 'SELECT COUNT(*) FROM `:prefix:sections` ';
            $sql .= 'WHERE (' . $now . ' BETWEEN `publ_start` AND `publ_end`) OR ';
            $sql .= '(' . $now . ' > `publ_start` AND `publ_end`=0) ';
            $sql .= 'AND `section_id`=' . $section_id;
            return($database->query($sql)->fetchColumn() != false);
	    }

        /**
         * checks if given section has given type
         *
         * @access public
         * @param  integer  $section_id
         * @param  string   $type (module)
         * @return boolean
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
            $res = self::$instance->db()->query(
                  'SELECT `module` FROM `:prefix:sections` '
                . 'WHERE `page_id` = :id AND `module` = "menu_link"',
                array('id'=>$page_id)
            );
            if($res && $res->rowCount())
                return true;
            return false;
        }   // end function isMenuLink()

	}
}

?>