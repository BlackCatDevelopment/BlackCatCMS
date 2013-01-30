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

if (!class_exists('CAT_Helper_DropLEP')) {

    if (!class_exists('CAT_Object', false)) {
	    @include dirname(__FILE__).'/../Object.php';
	}
	require_once CAT_PATH.'/modules/lib_lepton/pages_load/library.php';
	require_once CAT_PATH.'/modules/lib_search/search.dropleps.php';
	
	class CAT_Helper_DropLEP extends CAT_Object	{
	
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
			    require_once CAT_PATH.'/modules/dropleps/include.php';
			}
			$temp_unzip = CAT_PATH.'/temp/unzip/';
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
	    
	} // class CAT_Helper_DropLEP
	
} // if class_exists()	
