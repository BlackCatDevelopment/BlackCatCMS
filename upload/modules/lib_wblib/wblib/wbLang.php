<?php

/**
 *
 *          _     _  _ _
 *         | |   | |(_) |
 *    _ _ _| |__ | | _| |__
 *   | | | |  _ \| || |  _ \
 *   | | | | |_) ) || | |_) )
 *   \___/|____/ \_)_|____/
 *
 *
 *   @category     wblib
 *   @package      wbLang
 *   @author       BlackBird Webprogrammierung
 *   @copyright    (c) 2014 BlackBird Webprogrammierung
 *   @license      GNU LESSER GENERAL PUBLIC LICENSE Version 3
 *
 **/

namespace wblib;

/**
 * language (internationalization) handling class
 *
 * @category   wblib
 * @package    wbLang
 * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
 */
if(!class_exists('wblib\wbLang',false))
{
    class wbLang
    {
        /**
         * array of instances (one instance per language)
         **/
        public static  $instances  = array();
        /**
         *
         **/
        private static $spaces     = 1;
        /**
         * logger
         **/
        private static $analog     = NULL;
        /**
         * log level
         **/
        public  static $loglevel   = 8;
        /**
         * array of default options
         **/
        public  static $defaults    = array(
            'default'          => 'EN',
            'search_paths'     => array(),
            'case_insensitive' => false
        );
        /**
         * currently used language (for static use)
         **/
        private static $_current    = 'EN';
        /**
         * list of files already loaded (don't load twice)
         **/
        private static $_loaded     = array();
        /**
         * available language strings
         **/
        private static $_strings    = array();
        /**
         * available language strings, keys converted to lowercase
         **/
        private static $_strings_lo = array();

        // private to make sure that constructor can only be called
        // using getInstance()
        private function __construct() {
// ????????????????????????????
            if(func_num_args())
                $this->args = func_get_args();
        }    // end function __construct()

        // no cloning!
        private function __clone() {}

        // allow to call static methods as object methods
        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }   // end function __call()

        /**
         * Create an instance
         * @access public
         * @param  array   $options    - OPTIONAL
         * @return object
         **/
        public static function getInstance()
        {
            $language = call_user_func_array('self::getLang',func_get_args());
            if ( !$language ) $language = 'en_gb';
            if ( !array_key_exists( $language, self::$instances ) ) {
                self::log(sprintf('creating new instance with name [%s]',$language),7);
                self::$instances[$language] = new self(func_get_args());
                self::$_current             = $language;
                // add local path to array
                self::addPath(dirname(__FILE__).'/languages');
                self::addFile($language);
            }
            return self::$instances[$language];
        }   // end function getInstance()

        /**
	     * add a language file
	     *
	     * @access public
	     * @param  string  $file
	     * @param  string  $path
	     * @param  string  $var   - default 'LANG'
	     * @return boolean
	     **/
        public static function addFile($file,$path=NULL,$var=NULL)
        {
            self::log('> addFile()',7);
            $check_var = 'LANG';
            $result    = false;

            if(isset($var))
            {
                $var = str_ireplace( '$', '', $var );
                eval( 'global $' . $var . ';' );
                eval( "\$lang_var = & \$$var;" );
                $check_var = $var;
            }

            // check for file suffix; should be the same as the suffix of
            // this class
            if(pathinfo($file,PATHINFO_EXTENSION) !== pathinfo(__FILE__,PATHINFO_EXTENSION))
                $file .= '.'.pathinfo(__FILE__,PATHINFO_EXTENSION);

            if(!empty($path))
            {
                array_unshift(self::$defaults['search_paths'], self::path($path));
                self::$defaults['search_paths'] = array_unique(self::$defaults['search_paths']);
            }

            foreach(self::$defaults['search_paths'] as $path)
            {
                foreach(array($file,strtoupper($file),strtolower($file)) as $case )
                {
                    if(file_exists(self::path($path.'/'.$case)))
                    {
                        self::log(sprintf('found language file [%s] in path [%s]',$case,$path),7);
                        self::checkFile(self::path($path.'/'.$case), $check_var);
                        $result = true;
                    }
                }
            }

            if(!$result)
                self::log(sprintf('language file [%s] not found!',$file),3);

            self::log('< addFile()',7);
        } // end function addFile ()

        /**
         * add language file path
         *
         * @access public
         * @param  string   $path  - existing directory
         * @return void
         **/
        public static function addPath($path,$var=NULL)
        {
            self::log('> addPath()',7);
            $path = self::path($path);
            self::log(sprintf('trying to add path [%s]',$path),7);
            if ( file_exists($path) && is_dir($path) )
            {
                self::log(sprintf('setting language path to [%s]',$path),7);
                if(!in_array($path,self::$defaults['search_paths']))
                    array_push(self::$defaults['search_paths'],$path);
            }
            else
            {
                // we don't throw an exception here, just drop a message to the
                // error log
                self::log(sprintf('not found (or not a directory): [%s]',$path),2);
            }
            self::log('< addPath()',7);
        } // end function addPath()

        /**
         * check for valid language file
         *
         * @access public
         * @param  string  $file
         * @param  string  $check_var
         * @param  boolean $check_only
         * @return boolean
         **/
        public static function checkFile( $file, $check_var, $check_only = false )
        {
            self::log('> checkFile()',7);
            if(!isset(self::$_loaded[$file]) || self::$_loaded[$file]!=1)
            {
                try
                {
                    // require the language file
                    @require $file ;
                    // check if the var is defined now
                    if ( isset( ${$check_var} ) )
                    {
                        self::log(sprintf('require passed, [%s] is set',$check_var),7);
                        $isIndexed = array_values( ${$check_var} ) === ${$check_var};
                        if ($isIndexed)
                        {
                            self::log('< checkFile() (isIndexed)',7);
                            return false;
                        }

                        if ( $check_only )
                        {
                            self::log('< checkFile() (check_only)',7);
                            return ${$check_var};
                        }
                        else
                        {
                            // this will replace any keys already loaded!
                            self::$_strings = array_merge( self::$_strings, ${$check_var} );
                            if ( preg_match( "/(\w+)\.php/", $file, $matches ) )
                                self::$_current = $matches[1];
                            self::$_loaded[$file] = 1;
                            self::log(sprintf('loaded language file [%s], [%d] strings added',$file,count(${$check_var})),7);
                            self::log('< checkFile()',7);
                            return true;
                        }
                    }
                    else
                    {
                        self::log(sprintf('invalid lang file [%s]',$file),2);
                        self::log('< checkFile()',7);
                        return false;
                    }
                }
                catch( wbLangException $e )
                {
                }
            }
            else
            {
                self::log('< checkFile() (already loaded)',7);
            }
            self::log('< checkFile()',7);
        }   // end function checkFile()

        /**
         * get current language shortcut
         *
         * @access public
         * @return string
         *
         **/
        public static function current()
        {
            return self::$_current;
        } // end function current()

        /**
         *
         * @access public
         * @return
         **/
        public static function getLang()
        {
            $args     = array();
            $language = NULL;
            if ( func_num_args() )
                $args = func_get_args();
            self::log('args: '.var_export($args,1),7);
            if ( isset($args[0]) )
            {
                if ( ! is_array($args[0]) )
                    $language = array_shift($args);
                elseif( isset($args['lang']) )
                    $language = $args['lang'];
            }
            if ( !$language )
                $language = self::getfrombrowser();
            if ( !$language )
                $language = self::$defaults['default'];
            return ( is_array($language) ? array_shift($language) : $language );
        }   // end function getLang()

        /**
         * convenience function; shortcut to translate()
         **/
        public static function lang($msg,$attr = array())
        {
            return self::translate($msg,$attr);
        }   // end function lang()

        /**
         * change current language; creates a new instance if the given language
         * is not loaded yet
         *
         * @access public
         * @param  string  $lang
         * @return
         **/
        public static function set($lang)
        {
            return self::getInstance($lang);
        }   // end function set()

        /**
         * (re)set language file path
         *
         * @access public
         * @param  string   $path  - existing directory
         * @return void
         **/
        public static function setPath($path,$var=NULL)
        {
            self::log('> setPath()',7);
            $path = self::path($path);
            self::log(sprintf('trying to set path [%s]',$path),7);
            if ( file_exists($path) && is_dir($path) )
            {
                self::log(sprintf('setting language path to [%s]',$path),7);
                self::$defaults['paths'] = array($path);
            }
            else
            {
                // we don't throw an exception here, just drop a message to the
                // error log
                self::log(sprintf('not found (or not a directory): [%s]',$path),2);
            }
            self::log('< setPath()',7);
        } // end function setPath()

        /**
         * convenience function; shortcut to translate()
         **/
        public static function t($msg,$attr = array())
        {
            return self::translate($msg,$attr);
        }   // end function t()

        /**
         * "translate" (try to find the given message in the language array)
         *
         * Will return the original string (but with placeholders replaced) if
         * the string is not found in language array.
         *
         * To handle plurals, use
         *    [[{{variable}} |singular|plural]]
         *
         * Example:
         *    [[{{count}} |item|items]]
         * Result
         *    -> count 0: '0 items'
         *    -> count 1: '1 item'
         *    -> count 2: '2 items'
         *
         * @access public
         * @param  string   $msg  - message to search for
         * @param  array    $attr - attributes to replace in string
         * @return string
         **/
        public static function translate($msg,$attr = array())
        {
            self::log('> translate()',7);

if(!is_scalar($msg)) {
    echo "Invalid call to \wblib\wbLang::translate()!<br />Message:<textarea style=\"width:100%;height:200px;color:#000;background-color:#fff;\">";
    print_r( $msg );
    echo "</textarea>";
    echo "Backtrace:<textarea style=\"width:100%;height:200px;color:#000;background-color:#fff;\">";
    print_r( debug_backtrace() );
    echo "</textarea>";
}
            self::log(sprintf('message ~%s~',$msg),7);

            if(empty($msg) || is_bool($msg))
                return $msg;

            if(is_array($attr) && count($attr))
                self::log(sprintf('params: [%s]',var_export($attr,1)),7);
            else
                $attr = array();

            // direct match
            if ( array_key_exists($msg, self::$_strings) )
                $msg = self::$_strings[$msg];
            self::log(sprintf('after direct match: ~%s~',$msg),7);
            // case insensitive match
            if ( self::$defaults['case_insensitive'] && array_key_exists($msg, self::$_strings_lo) )
                $msg = self::$_strings_lo[$msg];
            self::log(sprintf('after case insensitive match: ~%s~',$msg),7);

            // get plurals
            preg_match_all( '~\[\[\s*?{{\s*?([^}].+?)\s*?}}\s*?\|\s*?([^\|].+?)\s*?\|\s*?([^\]].+?)\]\]~i', $msg, $matches, PREG_SET_ORDER );
            self::log('preg_match_all result (match plurals):',7);
            self::log(var_export($matches,1),7);

            foreach ( $matches as $match )
            {
                if ( isset($attr[$match[1]]) )
                {
                    $num    = $attr[$match[1]];
                    $string = ( (int)$num > 1 || (int)$num == 0 )
                            ? $match[3]
                            : $match[2];
                    self::log(sprintf('replacing [%s] with [%s]',$match[0],$num.' '.$string),7);
                    $msg = str_replace($match[0], $num.' '.$string, $msg);
                }
                else
                {
                    self::log(sprintf('no such attr [%s], using [%s]',$match[1],$match[3]),7);
                    $msg = str_replace($match[0], '0 '.$match[3], $msg);
                }
            }
            // replace attributes
            foreach ( $attr as $key => $value )
                $msg = preg_replace( "~{{\s*$key\s*}}~i", $value, $msg );

            self::log(sprintf('after replacing attrs: ~%s~',$msg),7);
            self::log('< translate()',7);

            return $msg;
        } // end function translate()

        /**
         * Get browser language(s)
         *
         * This method is based on code you may find here:
         * http://aktuell.de.selfhtml.org/artikel/php/httpsprache/
         *
         * @access public
         * @param  boolean $strict_mode
         * @return string
         **/
        public static function getfrombrowser( $strict_mode = true )
        {
            $browser_langs = array();
            $lang_variable = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

            if ( empty( $lang_variable ) )
                return self::$defaults[ 'default' ];

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
                $lang_code = explode( '-', $matches[1] );
                if ( isset( $matches[ 2 ] ) )
                    $lang_quality = (float)$matches[2];
                else
                    $lang_quality = 1.0;

                while ( count( $lang_code ) )
                {
                    $browser_langs[] = array(
                        'lang' => strtoupper(join('-',$lang_code)),
                        'qual' => $lang_quality
                    );
                    // don't use abbreviations in strict mode
                    if ( $strict_mode )
                        break;
                    array_pop( $lang_code );
                }
            }

            // order array by quality
            $langs = self::sort( $browser_langs, 'qual', 'desc', true );
            $ret   = array();
            foreach ( $langs as $lang )
                $ret[] = strtolower(str_replace('-','_',$lang['lang']));

            return $ret;
        } // end function getfrombrowser()

    	/**
    	 *
    	 *
    	 *
    	 *
    	 **/
    	public function isLoaded( $file )
    	{
    	    if ( isset( self::$_loaded[$file] ) )
    	        return true;
    		return false;
    	}   // end function isLoaded()

        /**
         * accessor to Analog (if installed)
         *
         * Note: Log messages are ignored if no Analog is available!
         *
         * @access private
         * @param  string   $message
         * @param  integer  $level
         * @return
         **/
        private static function log($message, $level = 0)
        {
            if($level<>self::$loglevel) return;
            if( !self::$analog && !self::$analog == -1)
            {
                if(file_exists(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php'))
                {
                    include_once(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php');
                    wblib_init_3rdparty(dirname(__FILE__).'/debug/','wbLang',self::$loglevel);
                    self::$analog = true;
                }
                else
                {
                    self::$analog = -1;
                }
            }
            if(substr($message,0,1)=='<')
                self::$spaces--;
            self::$spaces = ( self::$spaces > 0 ? self::$spaces : 0 );
            $line = str_repeat('    ',self::$spaces).$message;
            if(substr($message,0,1)=='>')
                self::$spaces++;
            if ( self::$analog )
                \Analog::log($line,$level);
        }   // end function log()

        /**
         * array helper methods
         */

        /**
         * Found here:
         * http://www.php.net/manual/en/function.array-change-key-case.php#107715
         **/
        public static function array_change_key_case_unicode($arr, $c = CASE_LOWER) {
            $c = ($c == CASE_LOWER) ? MB_CASE_LOWER : MB_CASE_UPPER;
            foreach ($arr as $k => $v) {
                $ret[mb_convert_case($k, $c, "UTF-8")] = $v;
            }
            return $ret;
        }   // end function array_change_key_case_unicode()

        /**
         * sort an array
         *
         *
         *
         **/
        public static function sort ( $array, $index, $order='asc', $natsort=FALSE, $case_sensitive=FALSE )
        {
            if( is_array($array) && count($array)>0 ) {
                 foreach(array_keys($array) as $key) {
                     $temp[$key]=$array[$key][$index];
                 }
                 if(!$natsort) {
                     ($order=='asc')? asort($temp) : arsort($temp);
                 }
                 else {
                     ($case_sensitive)? natsort($temp) : natcasesort($temp);
                     if($order!='asc') {
                         $temp=array_reverse($temp,TRUE);
                     }
                 }

                 foreach(array_keys($temp) as $key) {
                     (is_numeric($key))? $sorted[]=$array[$key] : $sorted[$key]=$array[$key];
                 }
                 return $sorted;
            }
            return $array;
        }   // end function sort()

        /**
         * fixes a path by removing //, /../ and other things
         *
         * @access public
         * @param  string  $path - path to fix
         * @return string
         **/
        public static function path($path)
        {
            // remove / at end of string; this will make sanitizePath fail otherwise!
            $path       = preg_replace( '~/{1,}$~', '', $path );
            // make all slashes forward
            $path       = str_replace( '\\', '/', $path );
            // bla/./bloo ==> bla/bloo
            $path       = preg_replace('~/\./~', '/', $path);
            // resolve /../
            // loop through all the parts, popping whenever there's a .., pushing otherwise.
            $parts      = array();
            foreach ( explode('/', preg_replace('~/+~', '/', $path)) as $part )
            {
                if ($part === ".." || $part == '')
                    array_pop($parts);
                elseif ($part!="")
                    $parts[] = $part;
            }
            $new_path = implode("/", $parts);
            // windows
            if ( ! preg_match( '/^[a-z]\:/i', $new_path ) )
                $new_path = '/' . $new_path;
            return $new_path;
        }   // end function path()

    }

    class wbLangException extends \Exception {}

}

