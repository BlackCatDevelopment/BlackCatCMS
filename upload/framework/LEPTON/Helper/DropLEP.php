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
 * @version         $Id$
 *
 */

if (!class_exists('LEPTON_Helper_DropLEP')) {

    if (!class_exists('LEPTON_Object', false)) {
	    @include dirname(__FILE__).'/../Object.php';
	}
	require_once WB_PATH.'/modules/lib_lepton/pages_load/library.php';
	require_once WB_PATH.'/modules/lib_search/search.dropleps.php';
	
	class LEPTON_Helper_DropLEP extends LEPTON_Object	{
	
		/**
		 * Install a DropLEP from a ZIP file (the ZIP may contain more than one
		 * DropLEP)
		 *
		 * @access public
		 * @param  string  $temp_file - name of the ZIP file
		 * @return array   see dropleps_import() method
		 *
		 **/
		public function installDroplep( $temp_file )
		{
		    if ( ! method_exists( 'dropleps_import' ) )
			{
			    require_once LEPTON_PATH.'/modules/dropleps/include.php';
			}
			$temp_unzip = LEPTON_PATH.'/temp/unzip/';
			return dropleps_import( $temp_file, $temp_unzip );
		}   // end function installDroplep()

	    /**
	     * Register the DropLEP $droplep_name for the $page_id for loading a CSS 
	     * file with the specified $file_name.
	     * If $file_path is specified the file will be loaded from $file_path, 
	     * otherwise the file will be loaded from the desired $module_directory.
	     * If $page_id is set to -1 the CSS file will be loaded at every page 
	     * (this option is intended for usage in templates)
	     *
	     * @param integer $page_id
	     * @param string $droplep_name
	     * @param string $module_directory - only the directory name
	     * @param string $file_name - the filename with extension
	     * @param string $file_path - relative to the root
	     * @return boolean on success
	     */	    	  
	    public function register_css($page_id, $droplep_name, $module_directory, $file_name, $file_path='') {
	        return register_droplep($page_id, $droplep_name, $module_directory, 'css', $file_name, $file_path);
	    }
	    
	    /**
         * Unregister the DropLEP $droplep_name from the $page_id with the settings
         * $module_directory and $file_name
         * 
         * @param integer $page_id
         * @param string $droplep_name
         * @param sring $module_directory
         * @param string $file_name
         */
	    public function unregister_css($page_id, $droplep_name, $module_directory, $file_name) {
	        return unregister_droplep($page_id, $droplep_name, $module_directory, 'css', $file_name);
	    }
	    
	    /**
         * Check wether the DropLEP $droplep_name is registered for setting CSS Headers
         * 
         * @param integer $page_id
         * @param string $droplep_name
         * @param string $module_directory
         * @return boolean true if the DropLEP is registered
         */
	    public function is_registered_css($page_id, $droplep_name, $module_directory) {
	        return is_registered_droplep($page_id, $droplep_name, $module_directory, 'css');	    
	    }

	    /**
	     * Register the DropLEP $droplep_name for the $page_id for loading a JS 
	     * JavaScript file with the specified $file_name.
	     * If $file_path is specified the file will be loaded from $file_path, 
	     * otherwise the file will be loaded from the desired $module_directory.
	     * If $page_id is set to -1 the JS file will be loaded at every page 
	     * (this option is intended for usage in templates)
	     *
	     * @param integer $page_id
	     * @param string $droplep_name
	     * @param string $module_directory - only the directory name
	     * @param string $file_name - the filename with extension
	     * @param string $file_path - relative to the root
	     * @return boolean on success
	     */	    	  
	    public function register_js($page_id, $droplep_name, $module_directory, $file_name, $file_path='') {
	        return register_droplep($page_id, $droplep_name, $module_directory, 'js', $file_name, $file_path);
	    }
	     
	    /**
         * Unregister the DropLEP $droplep_name from the $page_id with the settings
         * $module_directory and $file_name
         * 
         * @param integer $page_id
         * @param string $droplep_name
         * @param sring $module_directory
         * @param string $file_name
         */
	    public function unregister_js($page_id, $droplep_name, $module_directory, $file_name) {
	        return unregister_droplep($page_id, $droplep_name, $module_directory, 'js', $file_name);
	    }
	     
	    /**
         * Check wether the DropLEP $droplep_name is registered for setting JS Headers
         * 
         * @param integer $page_id
         * @param string $droplep_name
         * @param string $module_directory
         * @return boolean true if the DropLEP is registered
         */
	    public function is_registered_js($page_id, $droplep_name, $module_directory) {
	        return is_registered_droplep($page_id, $droplep_name, $module_directory, 'js');
	    }
	     
	    /**
         * Check for entries for the desired $page_id or for entries which should
         * be loaded at every page, load the specified CSS and JS files in the 
         * global $HEADER array
         * 
         * @param integer $page_id
         * @return boolean true on success
         */
	    public function get_headers($page_id) {
	        return get_droplep_headers($page_id); 
	    } // get_headers()
	    
	    /**
	     * Register the DropLEP $droplep_name in $module_directory for the 
	     * search of $page_id
	     * 
	     * @param integer $page_id
	     * @param string $droplep_name
	     * @param string $module_directory
	     * @return boolean true on success
	     */
	    public function register_for_search($page_id, $droplep_name, $module_directory) {
	        return register_droplep_for_search($droplep_name, $page_id, $module_directory);
	    }
	    
	    /**
	     * Unregister the DropLEP $droplep_name in $module_directory for the 
	     * search of $page_id
	     * 
	     * @param integer $page_id
	     * @param string $droplep_name
	     * @return boolean true on success
	     */
	    public function unregister_for_search($page_id, $droplep_name) {
	        return unregister_droplep_for_search($droplep_name, $page_id);
	    }
	    
	    /**
	     * Check if the DropLEP $droplep_name is registered for search
	     * 
	     * @param string $droplep_name
	     * @return boolean true on success
	     */
	    public function is_registered_for_search($droplep_name) {
	        return is_droplep_registered_for_search($droplep_name);
	    }
	    
	} // class LEPTON_Helper_DropLEP
	
} // if class_exists()	
