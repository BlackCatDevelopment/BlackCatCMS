<?php

/**
 *
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version         $Id: Directory.php 1501 2011-12-21 13:22:57Z webbird $
 *
 */

if ( ! class_exists( 'LEPTON_Object', false ) ) {
    @include dirname(__FILE__).'/Object.php';
}

if ( ! class_exists( 'LEPTON_Sections', false ) ) {

	class LEPTON_Sections extends LEPTON_Object
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
	        if (!isset($this->lep_active_sections) || !is_array($this->lep_active_sections))
	        {
	            $this->lep_active_sections = array();
	            // First get all sections for this page
	            $sql = "SELECT section_id,module,block,publ_start,publ_end FROM " . TABLE_PREFIX . "sections WHERE page_id = '" . $page_id . "' ORDER BY block, position";
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
	                $this->lep_active_sections[$section['block']][] = $section;
	            }
	        }

	        $this->pages_seen[$page_id] = true;

	        if ( $block )
	        {
				return ( isset( $this->lep_active_sections[$block] ) )
					? $this->lep_active_sections[$block]
					: NULL;
			}

			$all = array();
			foreach( $this->lep_active_sections as $block => $values )
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
	        return ( count($this->lep_active_sections) ? true : false );
	    }

	}
}

?>