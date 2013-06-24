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

if (!class_exists('CAT_Helper_Droplet')) {

    if (!class_exists('CAT_Object', false)) {
	    @include dirname(__FILE__).'/../Object.php';
	}
	#require_once CAT_PATH.'/modules/lib_lepton/pages_load/library.php';
	require_once CAT_PATH.'/modules/lib_search/search.droplets.php';
	
	class CAT_Helper_Droplet extends CAT_Object	{

        protected      $_config         = array( 'loglevel' => 8 );
        private static $instance = NULL;

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
		 * Install a Droplet from a ZIP file (the ZIP may contain more than one
		 * Droplet)
		 *
		 * @access public
		 * @param  string  $temp_file - name of the ZIP file
		 * @return array   see droplets_import() method
		 *
		 **/
		public static function installDroplet( $temp_file )
		{
		    if ( ! function_exists( 'droplets_import' ) )
			{
			    require_once CAT_PATH.'/modules/droplets/include.php';
			}
			$temp_unzip = CAT_PATH.'/temp/droplets_unzip/';
            CAT_Helper_Directory::createDirectory( $temp_unzip );
			return droplets_import( $temp_file, $temp_unzip );
		}   // end function installDroplet()

	    /**
	     * Register the Droplet $droplet_name for the $page_id for loading a CSS
	     * file with the specified $file_name.
	     * If $file_path is specified the file will be loaded from $file_path, 
	     * otherwise the file will be loaded from the desired $module_directory.
	     * If $page_id is set to -1 the CSS file will be loaded at every page 
	     * (this option is intended for usage in templates)
	     *
	     * @param integer $page_id
	     * @param string $droplet_name
	     * @param string $module_directory - only the directory name
	     * @param string $file_name - the filename with extension
	     * @param string $file_path - relative to the root
	     * @return boolean on success
	     */	    	  
	    public static function register_css($page_id, $droplet_name, $module_directory, $file_name, $file_path='') {
	        return register_droplet($page_id, $droplet_name, $module_directory, 'css', $file_name, $file_path);
	    }
	    
	    /**
         * Unregister the Droplet $droplet_name from the $page_id with the settings
         * $module_directory and $file_name
         * 
         * @param integer $page_id
         * @param string $droplet_name
         * @param sring $module_directory
         * @param string $file_name
         */
	    public static function unregister_css($page_id, $droplet_name, $module_directory, $file_name) {
	        return unregister_droplet($page_id, $droplet_name, $module_directory, 'css', $file_name);
	    }
	    
	    /**
         * Check wether the Droplet $droplet_name is registered for setting CSS Headers
         * 
         * @param integer $page_id
         * @param string $droplet_name
         * @param string $module_directory
         * @return boolean true if the Droplet is registered
         */
	    public static function is_registered_css($page_id, $droplet_name, $module_directory) {
	        return is_registered_droplet($page_id, $droplet_name, $module_directory, 'css');
	    }

	    /**
	     * Register the Droplet $droplet_name for the $page_id for loading a JS
	     * JavaScript file with the specified $file_name.
	     * If $file_path is specified the file will be loaded from $file_path, 
	     * otherwise the file will be loaded from the desired $module_directory.
	     * If $page_id is set to -1 the JS file will be loaded at every page 
	     * (this option is intended for usage in templates)
	     *
	     * @param integer $page_id
	     * @param string $droplet_name
	     * @param string $module_directory - only the directory name
	     * @param string $file_name - the filename with extension
	     * @param string $file_path - relative to the root
	     * @return boolean on success
	     */	    	  
	    public static function register_js($page_id, $droplet_name, $module_directory, $file_name, $file_path='') {
	        return register_droplet($page_id, $droplet_name, $module_directory, 'js', $file_name, $file_path);
	    }
	     
	    /**
         * Unregister the Droplet $droplet_name from the $page_id with the settings
         * $module_directory and $file_name
         * 
         * @param integer $page_id
         * @param string $droplet_name
         * @param sring $module_directory
         * @param string $file_name
         */
	    public static function unregister_js($page_id, $droplet_name, $module_directory, $file_name) {
	        return unregister_droplet($page_id, $droplet_name, $module_directory, 'js', $file_name);
	    }
	     
	    /**
         * Check wether the Droplet $droplet_name is registered for setting JS Headers
         * 
         * @param integer $page_id
         * @param string $droplet_name
         * @param string $module_directory
         * @return boolean true if the Droplet is registered
         */
	    public static function is_registered_js($page_id, $droplet_name, $module_directory) {
	        return is_registered_droplet($page_id, $droplet_name, $module_directory, 'js');
	    }
	     
	    /**
         * Check for entries for the desired $page_id or for entries which should
         * be loaded at every page, load the specified CSS and JS files in the 
         * global $HEADER array
         * 
         * @param integer $page_id
         * @return boolean true on success
         */
	    public static function get_headers($page_id) {
	        return get_droplet_headers($page_id);
	    } // get_headers()
	    
	    /**
	     * Register the Droplet $droplet_name in $module_directory for the
	     * search of $page_id
	     * 
	     * @param integer $page_id
	     * @param string $droplet_name
	     * @param string $module_directory
	     * @return boolean true on success
	     */
	    public static function register_for_search($page_id, $droplet_name, $module_directory) {
	        return register_droplet_for_search($droplet_name, $page_id, $module_directory);
	    }
	    
	    /**
	     * Unregister the Droplet $droplet_name in $module_directory for the
	     * search of $page_id
	     * 
	     * @param integer $page_id
	     * @param string $droplet_name
	     * @return boolean true on success
	     */
	    public static function unregister_for_search($page_id, $droplet_name) {
	        return unregister_droplet_for_search($droplet_name, $page_id);
	    }
	    
	    /**
	     * Check if the Droplet $droplet_name is registered for search
	     * 
	     * @param string $droplet_name
	     * @return boolean true on success
	     */
	    public static function is_registered_for_search($droplet_name) {
	        return is_droplet_registered_for_search($droplet_name);
	    }
	    
        /**
         * this method takes the output and processes the included droplets;
         * as droplet code may contain other droplets, the max. loop depth is
         * restricted to avoid endless loops
         *
         * @access public
         * @param  string  $content
         * @param  integer $max_loops - default 3
         * @return string
         **/
        public static function process( &$content, $max_loops = 3 )
        {
            $max_loops = ( (int) $max_loops = 0 ? 3 : (int) $max_loops );
            while ( ( self::evaluate($content) === true ) && ( $max_loops > 0 ) )
            {
                $max_loops--;
            }
            return $content;
        }   // end function process()

        /**
         * evaluates the droplet code
         *
         * @access private
         * @param  string   $_x_codedata
         * @param  string   $_x_varlist
         * @param  string   $content
         * @return eval result
         **/
        private static function do_eval( $_x_codedata, $_x_varlist, &$content )
        {
            self::getInstance()->log()->LogDebug('evaluating: '.$_x_codedata);
            extract( $_x_varlist, EXTR_SKIP );
            return ( eval( $_x_codedata ) );
        }   // end function do_eval()

        /**
         * evaluates the droplets contained in $content
         *
         * @access public
         * @param  string $content
         * @return string
         **/
        private static function evaluate(&$content)
        {

            $self = self::getInstance();

            $self->log()->LogDebug('processing content:');
            $self->log()->LogDebug($content);

            // collect all droplets from document
            $droplet_tags         = array();
            $droplet_replacements = array();

            if ( preg_match_all( '/\[\[(.*?)\]\]/', $content, $found_droplets ) )
            {
                foreach ( $found_droplets[1] as $droplet )
                {
                    if ( array_key_exists('[['.$droplet.']]', $droplet_tags ) === false )
                    {
                        // go in if same droplet with same arguments is not processed already
                        $varlist = array();
                        // split each droplet command into droplet_name and request_string
                        $tmp            = preg_split( '/\?/', $droplet, 2 );
                        $droplet_name   = $tmp[0];
                        $request_string = ( isset($tmp[1]) ? $tmp[1] : '' );
                        if ( $request_string != '' )
                        {
                            // make sure we can parse the arguments correctly
                            $request_string = html_entity_decode( $request_string, ENT_COMPAT, DEFAULT_CHARSET );
                            // create array of arguments from query_string
                            $argv = preg_split( '/&(?!amp;)/', $request_string );
                            foreach ( $argv as $argument )
                            {
                                // split argument in pair of varname, value
                                list( $variable, $value ) = explode( '=', $argument, 2 );
                                if ( !empty( $value ) )
                                {
                                    // re-encode the value and push the var into varlist
                                    $varlist[$variable] = htmlentities( $value, ENT_COMPAT, DEFAULT_CHARSET );
                                }
                            }
                        }
                        else
                        {
                            // no arguments given, so
                            $droplet_name = $droplet;
                        }

                        $self->log()->LogDebug('doing request: '.sprintf(
                            'SELECT `code` FROM `%smod_droplets` WHERE `name` LIKE "%s" AND `active` = 1',
                            CAT_TABLE_PREFIX, $droplet_name
                        ));

                        // request the droplet code from database
                        $codedata = $self->db()->get_one(sprintf(
                            'SELECT `code` FROM `%smod_droplets` WHERE `name` LIKE "%s" AND `active` = 1',
                            CAT_TABLE_PREFIX, $droplet_name
                        ));

                        $self->log()->LogDebug('code: '.$codedata);

                        if ( !is_null($codedata) )
                        {
                            $newvalue = self::do_eval( $codedata, $varlist, $content );
                            $self->log()->LogDebug('eval result: '.$newvalue);

                            // check returnvalue (must be a string of 1 char at least or (bool)true
                            if ( $newvalue == '' && $newvalue !== true )
                            {
                                if ( $self->_config['loglevel'] == 7 )
                                {
                                    $newvalue = sprintf(
                                        '<span class="mod_droplets_err">Error evaluating droplet [[%s]]: no valid returnvalue.</span>',
                                        $droplet
                                    );
                                }
                                else
                                {
                                    $newvalue = true;
                                }
                            }
                            if ( $newvalue === true )
                            {
                                $newvalue = "";
                            }
                            // remove any defined CSS section from code. For valid XHTML a CSS-section is allowed inside <head>...</head> only!
                            $newvalue = preg_replace( '/<style.*>.*<\/style>/siU', '', $newvalue );
                        }
                        else
                        {
                            // just remove droplet placeholder if no code was found
                            if ( $self->_config['loglevel'] == 7 )
                            {
                                $newvalue = '<span class="mod_droplets_err">No such droplet: ' . $droplet . '</span>';
                            }
                            else
                            {
                                $newvalue = true;
                            }
                        }
                        $droplet_tags[]         = '[[' . $droplet . ']]';
                        $droplet_replacements[] = $newvalue;
                    }
                }    // End foreach( $found_droplets[1] as $droplet )
                // replace each Droplet-Tag with coresponding $newvalue
                $content = str_replace( $droplet_tags, $droplet_replacements, $content );
            }

            $self->log()->LogDebug('returning:');
            $self->log()->LogDebug($content);

            return $content;
        }   // end function evaluate()

	} // class CAT_Helper_Droplet
	
} // if class_exists()	
