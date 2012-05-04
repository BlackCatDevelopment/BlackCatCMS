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
if ( ! function_exists('sanitize_url') ) {
	@include dirname(__FILE__).'/../functions.php';
}

if ( ! class_exists( 'LEPTON_Pages', false ) ) {

	class LEPTON_Pages extends LEPTON_Object
	{
	
	    protected $debugLevel      = 8; // 8 = OFF
	    
	    private $space = '    ';
	    
	    // header components
	    private static $css      = array();
	    private static $meta     = array();
	    private static $js       = array();
	    private static $jquery   = array();
	    
	    // scan dirs
	    private static $css_search_path = array();
	    private static $js_search_path  = array();
	    
	    // footer components
	    private static $script   = array();
	    private static $f_jquery = array();
	    private static $f_js     = array();
	    
	    /**
	     * calls appropriate function for analyzing and printing page footers
		 *
		 * @access public
		 * @param  string  $for - 'backend'/'frontend'
		 * @return mixed
	     *
	     **/
		public function getFooters( $for )
		{
            // what for?
            if ( ! $for || $for == '' || ( $for != 'frontend' && $for != 'backend' ) ) {
				$for = 'frontend';
			}
			$this->log()->logDebug( 'creating footers for ['.$for.']' );
			
			if ( $for == 'backend' ) {
                return $this->getBackendFooters();
			}
			else {
			    return $this->getFrontendFooters();
			}
		}   // end function getFooters()

		/**
		 * calls appropriate function for analyzing and printing page headers
		 *
		 * @access public
		 * @param  string  $for - 'backend'/'frontend'
		 * @param  string  $section - backend section name to load JS for
		 * @return mixed
		 *
		 **/
	    public function getHeaders( $for = NULL, $section = false )
	    {
	    
	        // don't do this twice
			if (defined('LEP_HEADERS_SENT'))
			{
			    $this->log()->logDebug( 'headers already sent, returning' );
				return;
			}
			
			// what for?
            if ( ! $for || $for == '' || ( $for != 'frontend' && $for != 'backend' ) ) {
				$for = 'frontend';
			}
			$this->log()->logDebug( 'creating headers for ['.$for.']' );
			
			// do we have a page id?
			$page_id = defined( 'PAGE_ID' )
				? PAGE_ID
				: (
					( isset($_REQUEST['page_id']) && is_numeric($_REQUEST['page_id']) )
						? $_REQUEST['page_id']
						: NULL
				);
            $this->log()->logDebug( 'page id: ['.$page_id.']' );
            
            if ( $for == 'backend' ) {
                return $this->getBackendHeaders($section);
			}
			else {
			    return $this->getFrontendHeaders($section);
			}

	    }   // end function getHeaders()
	    
	    /**
	     *
	     *
	     *
	     *
	     **/
		public function getBackendFooters()
		{
		
		    // -----------------------------------------------------------------
	        // -----                    backend theme                      -----
	        // -----------------------------------------------------------------
	        $file = $this->sanitizePath( LEPTON_PATH.'/templates/'.DEFAULT_THEME.'/footers.inc.php' );
	        if (file_exists($file))
			{
			    $this->log()->logDebug( sprintf( 'adding footer items for backend theme [%s]', DEFAULT_THEME ) );
			    $this->_load_footers_inc( $file, 'backend', 'templates/'.DEFAULT_THEME );
			}   // end loading theme
			
			// -----------------------------------------------------------------
			// -----                     admin tool                        -----
			// -----------------------------------------------------------------
	        if ( isset($_REQUEST['tool']) )
	        {
	            $path = $this->sanitizePath( LEPTON_PATH.'/modules/'.$_REQUEST['tool'].'/tool.php' );
	            $this->log()->logDebug( sprintf( 'handle admin tool [%s] - path [%s]', $_REQUEST['tool'], $path ) );

			    if ( file_exists($path) )
	            {
                    $file = $this->sanitizePath( LEPTON_PATH.'/modules/'.$_REQUEST['tool'].'/footers.inc.php' );
					if ( file_exists( $file ) )
					{
						$this->log()->logDebug( sprintf( 'adding footer items for admin tool [%s]', $_REQUEST['tool'] ) );
			    		$this->_load_footers_inc( $file, 'backend', 'templates/'.DEFAULT_THEME );
					}
	            }
			}
		
      		// -----------------------------------------------------------------
	        // -----                scan for js files                      -----
	        // -----------------------------------------------------------------
	        if ( count(LEPTON_Pages::$js_search_path) )
	        {
	        	foreach( LEPTON_Pages::$js_search_path as $directory )
				{
					$file = $this->sanitizePath( $directory.'/backend_body.js' );
					if ( file_exists(LEPTON_PATH.'/'.$file) ) {
						LEPTON_Pages::$f_js[] = '<script type="text/javascript" src="'
										      . sanitize_url( LEPTON_URL.$file )
										      . '"></script>' . "\n";
					}
				}
			}
			
			return $this->getJQuery('footer').
				   $this->getJavaScripts('footer');
			
		}   // end function getBackendFooters()
	    
	    /**
	     *
	     *
	     *
	     *
	     **/
	    public function getBackendHeaders($section)
	    {
	    
	        // -----------------------------------------------------------------
	        // -----                    backend theme                      -----
	        // -----------------------------------------------------------------
	        $file = $this->sanitizePath( LEPTON_PATH.'/templates/'.DEFAULT_THEME.'/headers.inc.php' );
	        if (file_exists($file))
			{
			    $this->log()->logDebug( sprintf( 'adding items for backend theme [%s]', DEFAULT_THEME ) );
			    $this->_load_headers_inc( $file, 'backend', 'templates/'.DEFAULT_THEME, $section );
			}   // end loading theme
			
			// -----------------------------------------------------------------
			// -----                     admin tool                        -----
			// -----------------------------------------------------------------
	        if ( isset($_REQUEST['tool']) )
	        {
	        
	            $path = $this->sanitizePath( LEPTON_PATH.'/modules/'.$_REQUEST['tool'].'/tool.php' );
	            $this->log()->logDebug( sprintf( 'handle admin tool [%s] - path [%s]', $_REQUEST['tool'], $path ) );

			    if ( file_exists($path) )
	            {
                    array_push(
						LEPTON_Pages::$css_search_path,
				        '/modules/' . $_REQUEST['tool'],
				        '/modules/' . $_REQUEST['tool'] . '/css'
					);
					array_push(
						LEPTON_Pages::$js_search_path,
				        '/modules/' . $_REQUEST['tool'],
				        '/modules/' . $_REQUEST['tool'] . '/js'
					);
					
					$file = $this->sanitizePath( LEPTON_PATH.'/modules/'.$_REQUEST['tool'].'/headers.inc.php' );
					if ( file_exists( $file ) )
					{
						$this->log()->logDebug( sprintf( 'adding items for admin tool [%s]', $_REQUEST['tool'] ) );
			    		$this->_load_headers_inc( $file, 'backend', 'modules/'.$_REQUEST['tool'], $section );
					}
				}
	        }
	        // -----------------------------------------------------------------
			// -----                  edit page                            -----
			// -----------------------------------------------------------------
	        else
			{
			    $this->_load_sections('backend');
			}
	        
	        // -----------------------------------------------------------------
	        // -----                scan for css files                     -----
	        // -----------------------------------------------------------------
	        $this->_load_css('backend');
	        
	        // -----------------------------------------------------------------
	        // -----                scan for js files                      -----
	        // -----------------------------------------------------------------
	        $this->_load_js('backend');
			
			// return the results
			return $this->getCSS().
				   $this->getJQuery( 'header' ).
				   $this->getJavaScripts( 'header' );
				   
	    }   // end function getBackendHeaders()
	    
	    /**
	     *
	     *
	     *
	     *
	     **/
		public function getFrontendFooters()
		{

		    // -----------------------------------------------------------------
	        // -----                  frontend theme                       -----
	        // -----------------------------------------------------------------
	        $file = $this->sanitizePath( LEPTON_PATH.'/templates/'.DEFAULT_TEMPLATE.'/footers.inc.php' );
	        if (file_exists($file))
			{
			    $this->log()->logDebug( sprintf( 'adding footer items for frontend template [%s]', DEFAULT_TEMPLATE ) );
			    $this->_load_footers_inc( $file, 'frontend', 'templates/'.DEFAULT_TEMPLATE );
			}   // end loading theme
			
			// -----------------------------------------------------------------
	        // -----                  scan for js files                    -----
	        // -----------------------------------------------------------------
	        if ( count(LEPTON_Pages::$js_search_path) )
	        {
	        	foreach( LEPTON_Pages::$js_search_path as $directory )
				{
					$file = $this->sanitizePath( $directory.'/frontend_body.js' );
					if ( file_exists(LEPTON_PATH.'/'.$file) ) {
						LEPTON_Pages::$f_js[] = $this->space
											  . '<script type="text/javascript" src="'
										      . sanitize_url( LEPTON_URL.$file )
										      . '"></script>' . "\n";
					}
				}
			}

			return $this->getJQuery('footer').
				   $this->getJavaScripts('footer');
				   
		}   // end function getFrontendFooters()
	    
	    /**
	     *
	     *
	     *
	     *
	     **/
	    public function getFrontendHeaders()
	    {
	        // -----------------------------------------------------------------
	        // -----                  frontend theme                       -----
	        // -----------------------------------------------------------------
	        $file = $this->sanitizePath( LEPTON_PATH.'/templates/'.DEFAULT_TEMPLATE.'/headers.inc.php' );
	        if (file_exists($file))
			{
				$this->log()->logDebug( sprintf( 'adding items for backend theme [%s]', DEFAULT_TEMPLATE ) );
				$this->_load_headers_inc( $file, 'frontend', 'templates/'.DEFAULT_TEMPLATE );
			}
			
			// -----------------------------------------------------------------
	        // -----                  sections (modules)                   -----
	        // -----------------------------------------------------------------
			$this->_load_sections('frontend');
			
			// -----------------------------------------------------------------
	        // -----                  scan for css files                   -----
	        // -----------------------------------------------------------------
	        $this->_load_css('frontend');

	        // -----------------------------------------------------------------
	        // -----                  scan for js files                    -----
	        // -----------------------------------------------------------------
	        $this->_load_js('frontend');

			// return the results
			return $this->getCSS().
				   $this->getJQuery( 'header' ).
				   $this->getJavaScripts( 'header' );
			
	    }   // end function getFrontendHeaders()
	    
	    /**
	     * returns the items of static array $css as HTML link markups
	     *
	     * @access public
	     * @return HTML
	     *
	     **/
	    public function getCSS()
	    {
	        $output = NULL;
	        if ( count(LEPTON_Pages::$css) )
	        {
	            foreach( LEPTON_Pages::$css as $item )
	            {
            		// make sure we have an URI (LEPTON_URL included)
					$file	= (preg_match('#' . LEPTON_URL . '#i', $item['file'])
							? $item['file']
							: LEPTON_URL . '/' . $item['file']);
					$output .= '<link rel="stylesheet" type="text/css" href="'
							.  sanitize_url($file)
							.  '" media="'
							.  (isset($item['media']) ? $item['media'] : 'all')
							. '" />' . "\n";
				}
			}
			return $output;
	    }   // end function getCSS()
	    
	    /**
	     * returns the items of static array $jquery
	     *
	     * @access public
	     * @return HTML
	     *
	     **/
	    public function getJQuery( $for = 'header' )
	    {
	        if ( $for == 'header' )
	        {
	            $static =& LEPTON_Pages::$jquery;
			}
			else {
			    $static =& LEPTON_Pages::$f_jquery;
			}
			if ( count($static) )
			{
			    return implode( $static );
			}
			return NULL;
	    }   // end function getJQuery()
	    
	    /**
	     * returns the items of static array $js
	     *
	     * @access public
	     * @return HTML
	     *
	     **/
	    public function getJavaScripts( $for = 'header' )
	    {
	        if ( $for == 'header' )
	        {
	            $static =& LEPTON_Pages::$js;
			}
			else {
			    $static =& LEPTON_Pages::$f_js;
			}
			if ( count($static) )
			{
			    return implode( "\n", $static ) . "\n";
			}
			return NULL;
	    }   // end function getJQuery()
	    
	    /**
	     *
	     *
	     *
	     *
	     **/
		private function _analyze_css( &$arr, $path_prefix = NULL )
		{
		    if ( is_array($arr) )
		    {
		        $check_paths = array();
		        if ( $path_prefix != '' )
				{
				    $check_paths = explode('/',$path_prefix);
				    $check_paths = array_reverse($check_paths);
				}
				foreach( $arr as $css )
			    {
			        // no file - no good
	                if ( ! isset($css['file']) )
	                {
	                    continue;
					}
					// relative path?
					if ( ! preg_match( '#/modules/#i', $css['file'] ) )
					{
						foreach( $check_paths as $subdir )
					    {
					        if ( ! preg_match( '#'.$subdir.'/#', $css['file'] ) )
							{
							    $css['file'] = $this->sanitizePath( $subdir.'/'.$css['file'] );
							}
						}
					}
	                LEPTON_Pages::$css[] = $css;
			    }
			}
		}   // end function _analyze_css()
	    
	    /**
	     * analyzes javascripts array and fills static array $js
	     *
	     * The components of given $path_prefix are checked to be included in
	     * the file name (and added if not)
	     *
	     * @access private
	     * @param  array    $arr
	     * @param  string   $path_prefix
	     * @return void
	     *
	     **/
	    private function _analyze_javascripts( &$arr, $for = 'frontend', $path_prefix = NULL, $section = false )
	    {
	    
        	if ( $for == 'frontend' )
	    {
		        $static =& LEPTON_Pages::$js;
		    }
		    else {
		        $static =& LEPTON_Pages::$f_js;
		    }

			if ( is_array($arr) )
			{
			    $check_paths = array();
		        if ( $path_prefix != '' )
				{
				    $check_paths = explode('/',$path_prefix);
				    $check_paths = array_reverse($check_paths);
				}
				
				if ( isset($arr['all']) )
				{
					foreach ( $arr['all'] as $item )
					{
					    if ( ! preg_match( '#/modules/#i', $item ) )
						{
						    foreach( $check_paths as $subdir )
						    {
						        if ( ! preg_match( '#'.$subdir.'/#', $item ) )
								{
								    $item = $this->sanitizePath( $subdir.'/'.$item );
								}
							}
						}
						$static[] = $this->space
								  . '<script type="text/javascript" src="'
							      . sanitize_url( LEPTON_URL.$item )
								  . '"></script>';
					}
				}
				
				if ( isset($arr['individual']) )
				{
				    if ( is_array($arr['individual']) )
				    {
						foreach ( $arr['individual'] as $section_name => $item )
						{
							if ( $section_name == strtolower($section) )
							{
							    foreach( $check_paths as $subdir )
							    {
							        if ( ! preg_match( '#'.$subdir.'/#', $item ) )
									{
									    $item = $this->sanitizePath( $subdir.'/'.$item );
									}
								}
								$static[] = $this->space
										  . '<script type="text/javascript" src="'
										  . sanitize_url( LEPTON_URL.$item )
										  . '"></script>';
							}
						}
					}
				}
			}
			else
			{
				$static[] = $this->space
						  . '<script type="text/javascript" src="'
					      . sanitize_url(LEPTON_URL . '/' . $arr)
						  . '"></script>';
			}
	    }   // end function _analyze_javascripts()
	    
	    /**
	     *
	     *
	     *
	     *
	     **/
		private function _analyze_jquery_components( &$arr, $for = 'frontend' )
		{
		
		    if ( $for == 'frontend' )
		    {
		        $static =& LEPTON_Pages::$jquery;
		    }
		    else {
		        $static =& LEPTON_Pages::$f_jquery;
		    }

	        // make sure that we load the core if needed, even if the
			// author forgot to set the flags
			if (
				   ( isset($arr['ui'])            && $arr['ui'] === true             )
				|| ( isset($arr['ui-effects'])    && is_array($arr['ui-effects'])    )
				|| ( isset($arr['ui-components']) && is_array($arr['ui-components']) )
			) {
				$arr['core'] = true;
			}

			// make sure we load the ui core if needed
			if ( isset($arr['ui-components']) && is_array($arr['ui-components'])
				|| ( isset($arr['ui-effects']) && is_array($arr['ui-effects']) )
			) {
				$arr['ui'] = true;
			}
			if ( isset($arr['ui-effects']) && is_array($arr['ui-effects']) && ( !in_array( 'core' , $arr['ui-effects'] ) ) )
			{
				array_unshift( $arr['ui-effects'] , 'core' );
			}
			
			// load the components
			if ( isset($arr['ui-theme']) && file_exists(LEPTON_PATH.'/modules/lib_jquery/jquery-ui/themes/'.$arr['ui-theme']) ) {
				$static[] = $this->space
						  . '<link rel="stylesheet" type="text/css" href="'
						  . sanitize_url(LEPTON_URL.'/modules/lib_jquery/jquery-ui/themes/'.$arr['ui-theme'].'/jquery-ui.css')
						  . '" media="all" />' . "\n";
			}
			
			// core is always added to header
			if ( isset($arr['core']) && $arr['core'] === true ) {
				LEPTON_Pages::$jquery[] = $this->space
										. '<script type="text/javascript" src="'
										. sanitize_url(LEPTON_URL.'/modules/lib_jquery/jquery-core/jquery-core.min.js')
										. '"></script>' . "\n";
			}
			
			// ui is always added to header
			if ( isset($arr['ui']) && $arr['ui'] === true ) {
				LEPTON_Pages::$jquery[] = $this->space
										. '<script type="text/javascript" src="'
										. sanitize_url(LEPTON_URL.'/modules/lib_jquery/jquery-ui/ui/jquery.ui.core.min.js')
										. '"></script>' . "\n";
			}
			
			if ( isset($arr['ui-effects']) && is_array($arr['ui-effects']) ) {
				foreach( $arr['ui-effects'] as $item ) {
					$static[] = $this->space
							  . '<script type="text/javascript" src="'
							  . sanitize_url(LEPTON_URL.'/modules/lib_jquery/jquery-ui/ui/jquery.effects.'.$item.'.min.js')
							  . '"></script>' . "\n";
				}
			}
			if ( isset($arr['ui-components']) && is_array($arr['ui-components']) ) {
				foreach( $arr['ui-components'] as $item ) {
					$static[] = $this->space
							  . '<script type="text/javascript" src="'
							  . sanitize_url(LEPTON_URL.'/modules/lib_jquery/jquery-ui/ui/jquery.ui.'.$item.'.min.js')
							  . '"></script>' . "\n";
				}
			}
			if ( isset($arr['all']) && is_array($arr['all']) ) {
				foreach( $arr['all'] as $item ) {
					$static[] = $this->space
							  . '<script type="text/javascript" src="'
							  . sanitize_url( LEPTON_URL . '/modules/lib_jquery/plugins/' . $item . '/' . $item . '.js' )
							  . '"></script>' . "\n";
				}
			}
			if ( isset($arr['individual']) && is_array( $arr['individual'] ) ) {
				foreach( $arr['individual'] as $section_name => $item ) {
					if ( $section_name == strtolower($section) )
					{
						$static[] = '<script type="text/javascript" src="'
								  . sanitize_url( LEPTON_URL . '/modules/lib_jquery/plugins/' . $item . '/' . $item . '.js' )
								  . '"></script>' . "\n";
					}
				}
			}

		}   // end function _analyze_jquery_components()
		
		/**
		 *
		 *
		 *
		 *
		 **/
		private function _load_css( $for = 'frontend' )
		{
			if ( count(LEPTON_Pages::$css_search_path) )
	        {
	            // automatically add CSS files
				foreach( LEPTON_Pages::$css_search_path as $directory )
				{
					// backend.css
					$file = $this->sanitizePath( $directory.'/'.$for.'.css' );
					if ( file_exists(LEPTON_PATH.'/'.$file) )
					{
						LEPTON_Pages::$css[] = array(
							'media' => 'all',
							'file'  => $file
						);
					}
					// backend_print.css
				    $file = $this->sanitizePath( $directory.'/'.$for.'_print.css' );
				    if ( file_exists(LEPTON_PATH.'/'.$file) )
					{
				        LEPTON_Pages::$css[] = array(
							'media' => 'print',
							'file'  => $file
						);
				    }
				}
	        }
		}   // end function _load_css()
		
		/**
		 *
		 *
		 *
		 *
		 **/
        private function _load_footers_inc( $file, $for, $path_prefix, $section )
        {
            // reset array
			$mod_footers = array();
			// load file
			require $file;
			// analyze
			if ( isset($mod_footers[$for]) && is_array($mod_footers[$for]) && count($mod_footers[$for]) )
			{
                if ( isset($mod_footers[$for]['jquery']) && is_array($mod_footers[$for]['jquery']) && count($mod_footers[$for]['jquery']) )
				{
				    $this->_analyze_jquery_components($mod_footers[$for]['jquery'][0]);
				}
			}
        }   // end function _load_footers_inc()
		
		/**
		 *
		 *
		 *
		 *
		 **/
		private function _load_headers_inc( $file, $for, $path_prefix, $section = NULL )
		{
			// reset array
			$mod_headers = array();
			// load file
			require $file;
			// analyze
			if ( isset($mod_headers[$for]) && is_array($mod_headers[$for]) && count($mod_headers[$for]) )
			{
			    // ----- CSS -----
				if ( isset($mod_headers[$for]['css']) && is_array($mod_headers[$for]['css']) && count($mod_headers[$for]['css']) )
				{
				    $this->_analyze_css( $mod_headers[$for]['css'], $path_prefix );
				}
				// ----- jQuery -----
				if ( isset($mod_headers[$for]['jquery']) && is_array($mod_headers[$for]['jquery']) && count($mod_headers[$for]['jquery']) )
				{
				    $this->_analyze_jquery_components($mod_headers[$for]['jquery'][0]);
				}
				// ----- other JS -----
				if ( isset($mod_headers[$for]['js']) && is_array($mod_headers[$for]['js']) && count($mod_headers[$for]['js']) )
				{
                    $this->_analyze_javascripts($mod_headers[$for]['js'][0], $for, $path_prefix.'/js', $section );
				}
			}
		}
		
		/**
		 *
		 *
		 *
		 *
		 **/
		private function _load_js( $for = 'frontend' )
		{
			if ( count(LEPTON_Pages::$js_search_path) )
	        {
	        	foreach( LEPTON_Pages::$js_search_path as $directory )
				{
					$file = $this->sanitizePath( $directory.'/backend.js' );
					if ( file_exists(LEPTON_PATH.'/'.$file) ) {
						LEPTON_Pages::$js = '<script type="text/javascript" src="'
										  . sanitize_url( LEPTON_URL.$file )
										  . '"></script>' . "\n";
					}
				}
				}
		}   // end function _load_js()
		
		/**
		 *
		 *
		 *
		 *
		 **/
		private function _load_sections( $for = 'frontend' )
		{
			$page_id = defined( 'PAGE_ID' )
             	     ? PAGE_ID
                     : (
                         ( isset($_GET['page_id']) && is_numeric($_GET['page_id']) )
                         ? $_GET['page_id']
                         : NULL
                       );

			if ( $page_id && is_numeric($page_id) )
        	{
	            // ...get active sections
			    if ( ! class_exists( 'LEPTON_Sections' ) )
			    {
			        @require_once $this->sanitizePath( dirname(__FILE__).'/Sections.php' );
				}
				$sec_h    = new LEPTON_Sections();
				$sections = $sec_h->get_active_sections($page_id);
	            if ( is_array($sections) && count($sections) )
	            {
	                global $current_section;
	                foreach ($sections as $section)
	                {
	                    $module = $section['module'];
	                    $file   = $this->sanitizePath(LEPTON_PATH.'/modules/'.$module.'/headers.inc.php');
						// find header definition file
	                    if ( file_exists($file) )
	                    {
	                        $current_section = $section['section_id'];
							$this->_load_headers_inc( $file, $for, 'modules/'.$module, $section );
						}
						array_push(
							LEPTON_Pages::$css_search_path,
					        '/modules/' . $module,
					        '/modules/' . $module . '/css'
						);
						array_push(
							LEPTON_Pages::$js_search_path,
					        '/modules/' . $module,
					        '/modules/' . $module . '/js'
						);
					}   // foreach ($sections as $section)
				}       // if (count($sections))
			}
		}
		
	}   // end class

}

?>