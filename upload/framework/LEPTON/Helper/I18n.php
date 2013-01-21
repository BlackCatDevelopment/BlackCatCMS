<?php

/**
 *
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON v2.0 Black Cat Edition Development
 * @copyright       2013, LEPTON v2.0 Black Cat Edition Development
 * @link            http://www.lepton2.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */
 
if ( ! class_exists( 'LEPTON_Object', false ) ) {
    @include dirname(__FILE__).'/../Object.php';
}
if ( ! class_exists( 'LEPTON_Helper_Array', false ) ) {
    @include dirname(__FILE__).'/Array.php';
}
if ( ! class_exists( 'LEPTON_Helper_Directory', false ) ) {
    @include dirname(__FILE__).'/Directory.php';
}

if ( ! class_exists( 'LEPTON_Helper_I18n', false ) ) {
	class LEPTON_Helper_I18n extends LEPTON_Object
	{
        protected      $debugLevel    = 8; // 8 = OFF
	    // default language
	    protected      $_config       = array( 'defaultlang' => 'EN', 'langPath' => '/languages' );
	    // array to store language strings
	    private static $_lang         = array();
	    // default language
	    private static $_current_lang = NULL;
	    // remember already loaded files
	    private        $_loaded      = array();
	    // accessor to path helper
	    private        $_path         = NULL;
	    // match counter for parseFile(); you can get the result using getMatchCount()
	    private        $_last_matches = 0;
	    
	    public static $_translated         = array();
	    public  static $_store_translations = false;

	    /**
	     * constructor
	     **/
	    public function __construct( $options = array() )
	    {
	        parent::__construct( $options );
	        if ( ! isset($options['lang']) )
	        {
	            if ( defined('LANGUAGE') )
				{
	            	$options['lang'] = LANGUAGE;
				}
	        }
	        if ( isset($options['lang']) )
	        {
	        	self::$_current_lang = $options['lang'];
			}
			if ( ! is_object( $this->_path ) )
			{
			    $this->_path = new LEPTON_Helper_Directory();
			}
	        $this->init();
	    } // end function __construct()

	    /**
	     *
	     *
	     *
	     *
	     **/
	    public function init( $var = NULL )
	    {
	        $this->log()->logDebug( 'lang var: '.$var );
	        $caller = debug_backtrace();
	        if ( self::$_current_lang == '' )
	        {
	            $lang_files = $this->getBrowserLangs();
	        }
	        else
	        {
	            $lang_files = array(
	                 self::$_current_lang
	            );
	        }
	        if ( file_exists( dirname( $caller[ 1 ][ 'file' ] ) . '/languages' ) )
	        {
	            //$this->_langPath = dirname($caller[1]['file']).'/languages';
	            $this->_config[ 'langPath' ] = dirname( $caller[ 1 ][ 'file' ] ) . '/languages';
	        }
	        elseif ( file_exists( dirname( $caller[ 1 ][ 'file' ] ) . '/../languages' ) )
	        {
	            //$this->_langPath = dirname($caller[1]['file']).'/../languages';
	            $this->_config[ 'langPath' ] = dirname( $caller[ 1 ][ 'file' ] ) . '/../languages';
	        }
	        // add default lang
	        $lang_files[] = 'EN';
	        $lang_files   = array_unique( $lang_files );
	        $this->log()->logDebug( 'language files to search for: ', $lang_files );
	        foreach ( $lang_files as $l )
	        {
	            $file = $l . '.php';
	            if ( $this->addFile( $file, $var ) )
	            {
	                break;
	            }
	        }
	    } // end function init()

	    /**
	     *
	     *
	     *
	     *
	     **/
	    public function addFile( $file, $path = NULL, $var = NULL )
	    {
	        $this->log()->logDebug( 'FILE ['.$file.'] PATH ['.$path.'] VAR ['.$var.']' );
	        $check_var = 'LANG';

	        if ( isset( $var ) )
	        {
	            $var = str_ireplace( '$', '', $var );
	            eval( 'global $' . $var . ';' );
	            eval( "\$lang_var = & \$$var;" );
	            $check_var = $var;
	        }

	        if ( empty( $path ) )
	        {
	            $path = $this->_config[ 'langPath' ];
	        }

	        $file = $this->_path->sanitizePath( $path . '/' . $file );

	        if ( file_exists( $file ) && ! $this->isLoaded($file) )
	        {
	            $this->log()->logDebug( 'found language file: ', $file );
	            $this->checkFile( $file, $check_var );
	        }
	        $this->log()->logDebug( 'language file does not exist: ', $file );

	    } // end function addFile ()
	    
	    /**
	     *
	     *
	     *
	     *
	     **/
		public function checkFile( $file, $check_var, $check_only = false )
		{
			{
				// require the language file
			    @require( $file );
				// check if the var is defined now
			    if ( isset( ${$check_var} ) )
			    {
                    $isIndexed = array_values( ${$check_var} ) === ${$check_var};
                    if ( $isIndexed )
                    {
                        return false;
                    }
			        if ( $check_only )
			        {
			        	return ${$check_var};
					}
					else
					{
		                self::$_lang = array_merge( self::$_lang, ${$check_var} );
		                if ( preg_match( "/(\w+)\.php/", $file, $matches ) )
		                {
		                    self::$_current_lang = $matches[ 1 ];
		                }
		                $this->_loaded[$file] = 1;
		                $this->log()->logDebug( 'loaded language file: ', $file );
		                return true;
					}
	            }
	            else
	            {
	                $this->log()->logInfo( 'invalid lang file: ', $file );
	                return false;
	            }
			}
		}   // end function checkFile()

	    /**
	     * set language file path
	     *
	     * @access public
	     * @param  string   $path  - language file path (must exist!)
	     * @return void
	     *
	     **/
	    public function setPath( $path, $var = NULL )
	    {
	        if ( file_exists( $path ) )
	        {
	            $this->log()->logDebug( 'setting language path to: ', $path );
	            $this->_config[ 'langPath' ] = $path;
	            $this->init( $var );
	        }
	        else
	        {
	            $this->printError( 'language file path does not exist: ' . $path );
	        }

	    } // end function setPath ()

	    /**
	     * get current language shortcut
	     *
	     * @access public
	     * @return string
	     *
	     **/
	    public function getLang()
	    {
            if ( ! isset(self::$_current_lang) || self::$_current_lang === NULL )
            {
                $langs = $this->getBrowserLangs();
                return $langs[1];
            }
	        return self::$_current_lang;
	    } // end function getLang()

	    /**
	     * try to find the given message in the language array
	     *
	     * Will return the original string (but with placeholders replaced) if
	     * string is not found in language array.
	     *
	     * @access public
	     * @param  string   $msg  - message to search for
	     * @param  array    $attr - attributes to replace in string
	     * @return string
	     *
	     **/
	    public function translate( $msg, $attr = array() )
	    {
	        $this->log()->logDebug( 'translate: '.$msg );
	        if ( empty( $msg ) || is_bool( $msg ) )
	        {
	            return $msg;
	        }
	        if ( self::$_store_translations )
	        {
	            self::$_translated[] = $msg;
	        }
	        if ( array_key_exists( $msg, self::$_lang ) )
	        {
	            $msg = self::$_lang[ $msg ];
	        }
	        if ( is_array( $attr ) )
	        {
		        foreach ( $attr as $key => $value )
		        {
					$msg = preg_replace( "~{{\s*$key\s*}}~i", $value, $msg );
		        }
			}
	        return $msg;
	    } // end function translate()

	    /**
	     * dump language array (strings beginning with $prefix)
	     *
	     * @access public
	     * @param  string   $prefix
	     * @return array
	     *
	     **/
	    public function dump( $prefix = NULL )
	    {
	        if ( $prefix )
	        {
	            $dump = array();
	            foreach ( self::$_lang as $k => $v )
	            {
	                if ( preg_match( "/^$prefix/", $k ) )
	                {
	                    $dump[ $k ] = $v;
	                }
	            }
	            return $dump;
	        }
	        else
	        {
	            return self::$_lang;
	        }
	    } // end function dump()
	    
		/**
		 * parse given file for translate() calls; returns an array of strings
		 *
		 * @access public
		 * @param  string  $file - abs. path name
		 * @return mixed
		 *     array if any strings were found
		 *     false if no such file or no strings found
		 *
		 **/
		public function parseFile( $file )
		{
		    if ( ! file_exists( $file ) ) {
		        return false;
			}
			$this->_last_matches = 0;
			$string = implode( '', file($file) );
			
			if ( $string ) {
			    $tokens  = token_get_all($string);
				$strings = array();
				$count   = 0;
				foreach( $tokens as $i => $c ) {
		        	if ( is_array($c) ) {
		        	    $n = $i + 1;
		        	    if ( token_name($c[0]) == 'T_STRING' && $c[1] == 'translate' && $tokens[$n] == '(' ) {
		        	        // start new stack
		        	        $stack  = array();
		        	        // starting line
		        	        $start  = $c[2];
                            // count match
							$this->_last_matches++;
							// move on until we find a comma or a closing bracket
							$c = $tokens[++$n];
							while (
							    ( is_array($c) || $c != ')' )
							) {
							    if ( $c[0] != 371 && $c[0] != 309 ) // omit T_WHITESPACE and vars
							    {
									$stack[] = $c[1];
							}
								$c = $tokens[++$n];
								if ( $c == ',' ) {
								    break;
								}
							}
							
							$text = trim( implode('',$stack) );
							if ( $text != '' )
							{
								$strings[] = array( 'line' => $start, 'text' => $text );
							}
						}
		        	}
		    	}
		    	return $strings;
			}
			return false;
		}   // end function parseFile()
		
		/**
		 *
		 *
		 *
		 *
		 **/
		public function getMatchCount()
		{
		    return $this->_last_matches;
		}   // end function getMatchCount()
		
		/**
		 *
		 *
		 *
		 *
		 **/
		public function isLoaded( $file )
		{
		    if ( isset( $this->_loaded[$file] ) )
		    {
		        return true;
			}
			return false;
		}   // end function isLoaded()

	    /**
	     * This method is based on code you may find here:
	     * http://aktuell.de.selfhtml.org/artikel/php/httpsprache/
	     *
	     *
	     **/
	    private function getBrowserLangs( $strict_mode = true )
	    {

            if ( ! isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) )
            {
                return $this->_config[ 'defaultlang' ];
            }

	        $browser_langs = array();
	        $lang_variable = $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ];

	        if ( empty( $lang_variable ) )
	        {
	            return $this->_config[ 'defaultlang' ];
	        }

	        $accepted_languages = preg_split( '/,\s*/', $lang_variable );
	        $current_q          = 0;

	        foreach ( $accepted_languages as $accepted_language )
	        {
	            // match valid language entries
	            $res = preg_match( '/^([a-z]{1,8}(?:-[a-z]{1,8})*)(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $accepted_language, $matches );

	            // invalid syntax
	            if ( !$res )
	            {
	                continue;
	            }

	            // get language code
	            $lang_code = explode( '-', $matches[ 1 ] );

	            if ( isset( $matches[ 2 ] ) )
	            {
	                $lang_quality = (float) $matches[ 2 ];
	            }
	            else
	            {
	                $lang_quality = 1.0;
	            }

	            while ( count( $lang_code ) )
	            {
	                $browser_langs[] = array(
	                     'lang' => strtoupper( join( '-', $lang_code ) ),
	                    'qual' => $lang_quality
	                );
	                // don't use abbreviations in strict mode
	                if ( $strict_mode )
	                {
	                    break;
	                }
	                array_pop( $lang_code );
	            }
	        }

	        // order array by quality
	        $sorter = new LEPTON_Helper_Array();
	        $langs  = $sorter->ArraySort( $browser_langs, 'qual', 'desc', true );
	        $ret    = array();
	        foreach ( $langs as $lang )
	        {
	            $ret[] = $lang[ 'lang' ];
	        }

	        return $ret;

	    } // end getBrowserLangs()

	}

}

?>