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
	
	    private $lep_active_sections;
	    private $pages_seen;

	    /**
	     *
	     *
	     *
	     *
	     **/
	    public function get_active_sections( $page_id, $block = null, $backend = false )
	    {
	        global $database;
	        if ( ! is_object( $database ) )
	        {
	            @require_once(dirname(__FILE__).'/../class.database.php');
			    // Create database class
			    $database = new database();
	        }
	        if (!isset($this->active_sections) || !is_array($this->active_sections))
	        {
	            $this->active_sections = array();
	            // First get all sections for this page
	            $sql = "SELECT section_id,module,block,publ_start,publ_end FROM " . CAT_TABLE_PREFIX . "sections WHERE page_id = '" . $page_id . "' ORDER BY block, position";
	            $query_sections = $database->query($sql);
	            if ($query_sections->numRows() == 0)
	            {
	                return NULL;
	            }
	            while ($section = $query_sections->fetchRow(MYSQL_ASSOC))
	            {
	                // skip this section if it is out of publication-date
	                $now = time();
	                if (!(($now <= $section['publ_end'] || $section['publ_end'] == 0) && ($now >= $section['publ_start'] || $section['publ_start'] == 0)))
	                {
	                    continue;
	                }
	                $this->active_sections[$section['block']][] = $section;
	            }
	        }

	        $this->pages_seen[$page_id] = true;

	        if ( $block )
	        {
				return ( isset( $this->active_sections[$block] ) )
					? $this->active_sections[$block]
					: NULL;
			}

			$all = array();
			foreach( $this->active_sections as $block => $values )
			{
				foreach( $values as $value )
				{
			    	array_push( $all, $value );
				}
			}
			
			return $all;
			
	    }   // end function get_active_sections()
	    
	    /**
	     *
	     *
	     *
	     *
	     **/
	    public function has_active_sections( $page_id )
		{
	        if ( ! isset( $this->pages_seen[$page_id] ) )
	        {
	            $this->get_active_sections($page_id);
	        }
	        return ( count($this->active_sections) ? true : false );
	    }   // end function has_active_sections()

        /**
         * checks if given section is active
         *
         * @access public
         * @param  int    $section_id
         * @return boolean
         **/
        public function section_is_active($section_id)
        {
            global $database;
            $now = time();
            $sql = 'SELECT COUNT(*) FROM `' . CAT_TABLE_PREFIX . 'sections` ';
            $sql .= 'WHERE (' . $now . ' BETWEEN `publ_start` AND `publ_end`) OR ';
            $sql .= '(' . $now . ' > `publ_start` AND `publ_end`=0) ';
            $sql .= 'AND `section_id`=' . $section_id;
            return($database->get_one($sql) != false);
	    }

	}
}

?>