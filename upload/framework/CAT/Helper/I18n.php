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
 *   @copyright       2013 - 2015, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if ( ! class_exists( 'CAT_Object', false ) ) {
    @include dirname(__FILE__).'/../Object.php';
}
if ( ! class_exists( 'CAT_Helper_Array', false ) ) {
    @include dirname(__FILE__).'/Array.php';
}
if ( ! class_exists( 'CAT_Helper_Directory', false ) ) {
    @include dirname(__FILE__).'/Directory.php';
}

if ( ! class_exists( 'CAT_Helper_I18n', false ) )
{
	class CAT_Helper_I18n extends CAT_Object
	{
	    protected      $_config
            = array( 'defaultlang' => 'EN', 'langPath' => '/languages', 'workdir' => NULL, 'loglevel' => 8 );
	    // array to store language strings
	    private static $_lang               = array();
	    // default language
	    private static $_current_lang       = NULL;
	    // remember already loaded files
	    private        $_loaded             = array();
	    // accessor to path helper
	    private        $_path               = NULL;
	    // match counter for parseFile(); you can get the result using getMatchCount()
	    private        $_last_matches       = 0;
	    
	    public  static $_translated         = array();
	    public  static $_store_translations = false;
        private static $instances           = array();
        private static $search_paths        = array();

	    /**
	     * private constructor; use getInstance() to load this class
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
			    $this->_path = CAT_Helper_Directory::getInstance();
			}
	        $this->init();
	    } // end function __construct()

        /**
         * singleton pattern
         **/
        public static function getInstance( $lang = NULL )
        {
            if (!isset(self::$instances[$lang]) || !is_object(self::$instances[$lang]))
            {
                self::$instances[$lang] = new self(array('lang'=>$lang));
            }
            return self::$instances[$lang];
        }   // end function getInstance()

	    /**
	     *
	     *
	     *
	     *
	     **/
	    public function init( $var = NULL )
	    {
	        $this->log()->logDebug( 'lang var: '.$var );
	        $stack  = debug_backtrace();
            $caller = array_shift($stack);

            while (
                     $caller 
                && ! isset($caller['file'])
                || ( isset($caller['class']) && $caller['class'] == 'CAT_Helper_I18n' && $caller['function'] != 'getInstance' )
            ) {
                $caller = array_shift($stack);
            }

	        if ( self::$_current_lang == '' )
	            $lang_files = $this->getBrowserLangs();
	        else
	            $lang_files = array(self::$_current_lang);
            $this->log()->logDebug('lang files:', $lang_files);

            if ($caller )
    	        if(file_exists(CAT_Helper_Directory::sanitizePath(dirname($caller['file']).$this->_config['langPath'])))
    	            $this->_config['workdir'] = CAT_Helper_Directory::sanitizePath(dirname($caller['file']).$this->_config['langPath']);
    	        elseif(file_exists(CAT_Helper_Directory::sanitizePath(dirname($caller['file']).'/../'.$this->_config['langPath'])))
    	            $this->_config['workdir'] = CAT_Helper_Directory::sanitizePath(dirname($caller['file']).'/../'.$this->_config['langPath']);
                else
                    $this->_config['workdir'] = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/languages');
            $this->log()->logDebug('workdir path:',$this->_config['workdir']);

            self::$search_paths[] = $this->_config['workdir'];
            self::$search_paths   = array_unique(self::$search_paths);

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

	        if(!empty($path))
            {
                array_unshift(self::$search_paths, $path);
                self::$search_paths = array_unique(self::$search_paths);
            }

            foreach(self::$search_paths as $path)
            {
	            $file = CAT_Helper_Directory::sanitizePath($path.'/'.$file);
    	        if ( file_exists( $file ) && ! $this->isLoaded($file) )
    	        {
    	            $this->log()->logDebug( 'found language file: ', $file );
    	            $this->checkFile($file,$check_var);
    	        }
            }

            if(!$this->isLoaded($file))
            {
    	        $this->log()->logDebug( 'language file does not exist: ', $file );
                return false;
            }

            return true;

	    } // end function addFile ()
	    
	    /**
	     *
	     *
	     *
	     *
	     **/
		public function checkFile( $file, $check_var, $check_only = false )
		{

            $this->log()->logDebug(sprintf(
                'checking file [%s] for var [%s], check_only [%s]',
                $file, $check_var, $check_only
            ));

			{
				// require the language file
			    require $file ;

				// check if the var is defined now
			    if ( isset( ${$check_var} ) )
			    {
                    $this->log()->logDebug('found $check_var');
                    $isIndexed = array_values( ${$check_var} ) === ${$check_var};
                    if ( $isIndexed )
                    {
                        $this->log()->logDebug('indexed, returning false');
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
	                $this->log()->logInfo(sprintf(
                        'invalid lang file [%s], var [%s] is not set',
                        $file, $check_var
                    ));
	                return false;
	            }
			}
		}   // end function checkFile()

	    /**
         * check language string
         *
         * @access public
         * @param  string  $lang
         * @return boolean
         **/
        public function checkLang($lang)
        {
            return (preg_match('/^[A-Z]{2}$/', $lang))
                ? true
                : false;
        }

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
         * get known charsets; this was moved from ./backend/interface/charsets.php
         * the list may be filled from DB later
         *
         * @access public
         * @return array
         **/
        public function getCharsets()
        {
            return array(
                'utf-8'       => 'Unicode (utf-8)',
                'iso-8859-1'  => 'Latin-1 Western European (iso-8859-1)',
                'iso-8859-2'  => 'Latin-2 Central European (iso-8859-2)',
                'iso-8859-3'  => 'Latin-3 Southern European (iso-8859-3)',
                'iso-8859-4'  => 'Latin-4 Baltic (iso-8859-4)',
                'iso-8859-5'  => 'Cyrillic (iso-8859-5)',
                'iso-8859-6'  => 'Arabic (iso-8859-6)',
                'iso-8859-7'  => 'Greek (iso-8859-7)',
                'iso-8859-8'  => 'Hebrew (iso-8859-8)',
                'iso-8859-9'  => 'Latin-5 Turkish (iso-8859-9)',
                'iso-8859-10' => 'Latin-6 Nordic (iso-8859-10)',
                'iso-8859-11' => 'Thai (iso-8859-11)',
                'gb2312'      => 'Chinese Simplified (gb2312)',
                'big5'        => 'Chinese Traditional (big5)',
                'iso-2022-jp' => 'Japanese (iso-2022-jp)',
                'iso-2022-kr' => 'Korean (iso-2022-kr)'
            );
        }   // end function getCharsets()

	    /**
	     * This method is based on code you may find here:
	     * http://aktuell.de.selfhtml.org/artikel/php/httpsprache/
	     *
	     *
	     **/
	    public function getBrowserLangs($strict_mode=true)
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
	                continue;

	            // get language code
	            $lang_code = explode( '-', $matches[ 1 ] );

	            if ( isset( $matches[ 2 ] ) )
	                $lang_quality = (float) $matches[ 2 ];
	            else
	                $lang_quality = 1.0;

	            while ( count( $lang_code ) )
	            {
	                $browser_langs[] = array(
	                     'lang' => strtoupper( join( '-', $lang_code ) ),
	                    'qual' => $lang_quality
	                );
	                // don't use abbreviations in strict mode
	                if ( $strict_mode )
	                    break;

	                array_pop( $lang_code );
	            }
	        }

	        // order array by quality
	        $sorter = new CAT_Helper_Array();
	        $langs  = $sorter->ArraySort( $browser_langs, 'qual', 'desc', true );
	        $ret    = array();
	        foreach ( $langs as $lang )
	            $ret[] = $lang[ 'lang' ];

	        return $ret;

	    } // end getBrowserLangs()

        /**
         * looks for used languages by analyzing the pages
         *
         * @access public
         * @param  boolean  $guest_only - pages visible in frontend only, default: true
         * @param  boolean  $langs_only - get pages (false) or language shortcuts only (true), default: false
         * @return
         **/
        public static function getUsedLangs($guest_only=true,$langs_only=false)
        {
            $vis   = ( $guest_only ? 'public' : NULL );
            $pages = CAT_Helper_Page::getPagesByVisibility($vis);
            $langs = array();
            foreach($pages as $id)
            {
                $lang = CAT_Helper_Page::properties($id,'language');
                if($langs_only)
                {
                    $langs[$lang] = 1;
                }
                else
                {
                if(!isset($langs[$lang])) $langs[$lang] = array();
                $langs[$lang][] = array('page_id'=>$id, 'menu_title'=>CAT_Helper_Page::properties($id,'menu_title'));
	}
            }
            return $langs;
        }   // end function getUsedLangs()

}
}