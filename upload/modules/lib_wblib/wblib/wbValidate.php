<?php

/**
 *          _     _  _ _     ______
 *         | |   | |(_) |   (_____ \
 *    _ _ _| |__ | | _| |__   ____) )
 *   | | | |  _ \| || |  _ \ / ____/
 *   | | | | |_) ) || | |_) ) (_____
 *    \___/|____/ \_)_|____/|_______)
 *
 *   @category     wblib2
 *   @package      wbValidate
 *   @author       BlackBird Webprogrammierung
 *   @copyright    (c) 2014 BlackBird Webprogrammierung
 *   @license      GNU LESSER GENERAL PUBLIC LICENSE Version 3
 *
 **/

namespace wblib;

/**
 * validate form input
 *
 * @category   wblib2
 * @package    wbValidate
 * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
 */
if ( ! class_exists( 'wbValidate', false ) )
{
    class wbValidate {

        /**
         * array of named instances
         **/
        private static $instance   = NULL;
        /**
         * logger
         **/
        private static $analog    = NULL;
        /**
         * space before log message
         **/
        protected static $spaces  = 0;
        /**
         * log level
         **/
        public  static $loglevel  = 0;
        /**
         * storage for incoming (tainted) data
         **/
        private static $_tainted  = array();
        /**
         * storage for already validated data
         **/
        private static $_valid    = array();

        // private to make sure that constructor can only be called
        // using getInstance()
        private function __construct() {
            
        }    // end function __construct()

        // no cloning!
        private function __clone() {}

        /**
         * Create an instance 
         *
         * if $hardened is set to true, the global arrays _GET, _POST and
         * _REQUEST will be unset()
         *
         * @access public
         * @param  boolean $hardened
         * @return object
         **/
        public static function getInstance($hardened=false)
        {
            if(!is_object(self::$instance))
            {
                self::$instance = new self();
                self::$_tainted['_REQUEST'] = $_REQUEST;
                self::$_tainted['_GET']     = $_GET;
                self::$_tainted['_POST']    = $_POST;
                self::$_tainted['_SERVER']  = $_SERVER;
                if($hardened) {
                    unset($_REQUEST);
                    unset($_POST);
                    unset($_GET);
                }
            }
            return self::$instance;
        }   // end function getInstance()

        /**
         * accessor to Analog (if installed)
         *
         * Note: Log messages are ignored if no Analog is available!
         *
         * @access protected
         * @param  string   $message - log message
         * @param  integer  $level   - log level; default: 3 (error)
         * @return void
         **/
        public static function log($message, $level = 3)
        {
            $class = get_called_class();
            //if($class != 'wblib\wbValidate') return;
            if($level>$class::$loglevel)     return;
            if( !self::$analog && !self::$analog == -1)
            {
                if(file_exists(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php'))
                {
                    include_once(dirname(__FILE__).'/3rdparty/Analog/wblib.init.inc.php');
                    wblib_init_3rdparty(dirname(__FILE__).'/debug/','wbValidate',$class::$loglevel);
                    self::$analog = true;
                }
                else
                {
                    self::$analog = -1;
                }
            }
            if ( self::$analog !== -1 )
            {
                if(substr($message,0,1)=='<')
                    self::$spaces--;
                self::$spaces = ( self::$spaces > 0 ? self::$spaces : 0 );
                $line = str_repeat('    ',self::$spaces).$message;
                if(substr($message,0,1)=='>')
                    self::$spaces++;
                \Analog::log($line,$level);
            }
        }   // end function log()
        /**
         * checks globals for a key named $varname, validating as $as
         *
         * @access public
         * @param  string  $value - value to validate
         * @param  string  $as    - validate as string|boolean|...
         * @return
         **/
        public static function check($value,$as)
        {
            self::log('> check()',7);
            self::log(sprintf('value [%s] as [%s]',$value,$as),7);

            // internal method
            if( is_callable(array('\wblib\wbValidateValidate', 'as_'.$as)) === true )
            {
                $func = 'as_'.$as;
                self::log(sprintf('using func [%s]',$func),7);
                self::log('< check()',7);
                return \wblib\wbValidateValidate::$func($value);
            }
            // PHP method
            elseif(function_exists('is_'.$as))
            {
                $func = 'is_'.$as;
                self::log(sprintf('using func [%s]',$func),7);
                if($func($value))
                {
                    self::log('< check()',7);
                    return $value;
                }
                else
                {
                    self::log('no such func, returning false',7);
                    self::log('< check()',7);
                    return false;
                }
            }
            else
            {
                self::log('no validation func, returning false',7);
                self::log('< check()',7);
                return false;
            }
        }   // end function check()

        /**
         * checks if a key is set in given context (global)
         *
         * @access public
         * @param  string  $var     - key to check
         * @param  string  $context - _REQUEST|_GET|_POST; default _REQUEST
         * @return boolean
         **/
        public static function exists($var,$context='_REQUEST')
        {
            if(isset(self::$_valid[$context][$var]) || isset(self::$_tainted[$context][$var]))
                return true;
            else
                return false;
        }   // end function exists()

        /**
         * retrieve validated param from globals
         *
         * @access public
         * @param  string  $var     - global key (var name)
         * @param  mixed   $default - optional default value
         * @param  string  $as      - validate as (string, integer, ...), default string
         * @param  string  $context - one of _REQUEST/_GET/_POST, default _POST
         * @return mixed
         **/
        public static function param($var,$default=NULL,$as='string',$context='_REQUEST')
        {
            if(!is_object(self::$instance)) self::getInstance();
            // already validated
            if(isset(self::$_valid[$context]) && isset(self::$_valid[$context][$var]) && isset(self::$_valid[$context][$var][$as]))
                return self::$_valid[$context][$var][$as];
            if(!isset(self::$_tainted[$context]) || !isset(self::$_tainted[$context][$var]))
                return ( $default ? $default : NULL );
            // validate
            $value = self::check(self::$_tainted[$context][$var], $as);
            if(!$value)
                return ( $default ? $default : NULL );
            self::$_valid[$context][$var][$as] = $value;
            return $value;
        }   // end function param()

    }

    class wbValidateException extends \Exception {}

    class wbValidateValidate
    {

        /**
         * checks if given value is a valid string:
         * - uses is_string() to check if it is a string, returning false if not
         * - uses wbValidateSanitize::string() to return the sanitized string
         *
         * @access public
         * @param  string  $string
         * @return mixed
         **/
        public static function as_string($string)
        {
            if(!is_string($string))
                return false;
            // sanitize
            $string = \wblib\wbValidateSanitize::string($string);
            // mask HTML
            return htmlentities($string);
        }   // end function as_string()

        /**
         * checks if given value is an integer:
         * - uses is_int() to check if it is an int, returning false if not
         * - uses int() to return sanitized int (though this should not be
         *   necessary)
         *
         * @access public
         * @param  string  $string
         * @return mixed
         **/
        public static function as_integer($string)
        {
            if(!is_int($string))
                return false;
            // sanitize
            return ( int($string) );
        }   // end function as_integer()

    }

    /**
     * sanitation class; cleans up values
     *
     * @category   wblib2
     * @package    wbValidateValidate
     * @copyright  Copyright (c) 2014 BlackBird Webprogrammierung
     * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3
     */
    class wbValidateSanitize {
        /**
         * sanitize URL
         *
         * @access public
         * @param  string  $string - URL to validate
         * @return string
         **/
        public static function url($string)
        {
            $string    = htmlspecialchars((filter_var($string, FILTER_SANITIZE_URL)));
            // href="http://..." ==> href isn't relative
            $rel_parsed = parse_url($string);
            $path       = $rel_parsed['path'];
            $path       = preg_replace('~/\./~', '/', $path); // bla/./bloo ==> bla/bloo
            // resolve /../
            // loop through all the parts, popping whenever there's a .., pushing otherwise.
            $parts      = array();
            foreach ( explode('/', preg_replace('~/+~', '/', $path)) as $part )
            {
                if ($part === ".." || $part == '')
                {
                    array_pop($parts);
                }
                elseif ($part!="")
                {
                    $parts[] = $part;
                }
            }
            return
            (
                  ( is_array($rel_parsed) && array_key_exists( 'scheme', $rel_parsed ) )
                ? $rel_parsed['scheme'] . '://' . $rel_parsed['host'] . ( isset($rel_parsed['port']) ? ':'.$rel_parsed['port'] : NULL )
                : ""
            ) . "/" . implode("/", $parts);
        }   // end function url()

        /**
         * sanitizes a string, only allowing ASCII >= 32, encoding & to &amp;
         * and adding quotes
         *
         * @access public
         * @return
         **/
        public static function string($string)
        {
            return filter_var($string,FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_LOW|FILTER_FLAG_ENCODE_AMP);
        }   // end function string()
        
    }
}

