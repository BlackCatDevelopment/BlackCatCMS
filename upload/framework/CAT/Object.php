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

/**
 *
 * Base class for all Helper classes; provides some common methods
 *
 */
if ( ! class_exists( 'CAT_Object', false ) ) {

    if ( ! class_exists( 'CAT_Helper_KLogger', false ) ) {
        @include dirname(__FILE__).'/Helper/KLogger.php';
    }

    class CAT_Object
    {

        // array to store config options
        protected $_config         = array( 'loglevel' => 8 );
        // Language helper object handle
        protected static $lang;
        // database handle
        protected $db;
        // KLogger object handle
        private   $logObj;
        
        // Log levels
        const EMERG  = 0;  // Emergency: system is unusable
        const ALERT  = 1;  // Alert: action must be taken immediately
        const CRIT   = 2;  // Critical: critical conditions
        const ERR    = 3;  // Error: error conditions
        const WARN   = 4;  // Warning: warning conditions
        const NOTICE = 5;  // Notice: normal but significant condition
        const INFO   = 6;  // Informational: informational messages
        const DEBUG  = 7;  // Debug: debug messages
        const OFF    = 8;

        /**
         * inheritable constructor; allows to set object variables
         **/
        public function __construct ( $options = array() ) {
            if ( is_array( $options ) ) {
                $this->config( $options );
            }
            // allow to set log level on object creation
            if ( isset( $this->_config['loglevel'] ) ) {
                $this->debugLevel = $this->_config['loglevel'];
            }
            // allow to enable debugging on object creation; this will override
            // 'loglevel' if both are set
            if ( isset( $this->_config['debug'] ) ) {
                $this->debug(true);
            }
        }   // end function __construct()
        
        public function __destruct() {}

        public function __call($method, $args)
        {
            if ( ! isset($this) || ! is_object($this) )
                return false;
            if ( method_exists( $this, $method ) )
                return call_user_func_array(array($this, $method), $args);
        }
        
        public static function lang()
        {
            if ( ! is_object(CAT_Object::$lang) )
            {
                CAT_Object::$lang = CAT_Helper_I18n::getInstance(CAT_Registry::get('LANGUAGE',NULL,'EN'));
            }
            return CAT_Object::$lang;
        }   // end function lang()
        
        /**
         * set config values
         *
         * This method allows to set object variables at runtime.
         * If $option is an array, the array keys are treated as object var
         * names, the array values as their values. The second param $value
         * is ignored in this case.
         * If $option is a string, it is treated as object var name; in this
         * case, $value must be set.
         *
         * @access public
         * @param  mixed    $option
         * @param  string   $value
         * @return void
         *
         **/
        public function config( $option, $value = NULL ) {
            if ( is_array( $option ) )
            {
                $this->_config = array_merge( $this->_config, $option );
            }
            else
            {
                $this->_config[$option] = $value;
            }
            return $this;
        }   // end function config()
        
        /**
         * create a guid; used by the backend, but can also be used by modules
         *
         * @access public
         * @param  string  $prefix - optional prefix
         * @return string
         **/
        public static function createGUID($prefix='')
        {
            if(!$prefix||$prefix='') $prefix=rand();
            $s = strtoupper(md5(uniqid($prefix,true)));
            $guidText =
                substr($s,0,8) . '-' .
                substr($s,8,4) . '-' .
                substr($s,12,4). '-' .
                substr($s,16,4). '-' .
                substr($s,20);
            return $guidText;
        }   // end function createGUID()
        
        /**
         * prints a formatted error message
         *
         * @access public
         * @param  string  $message - error message
         * @param  string  $link    - page to forward to
         * @param  boolean $print_header
         * @param  mixed   $args    - additional args to print
         *
         **/
        public static function printError( $message = NULL, $link = 'index.php', $print_header = true, $args = NULL ) {
            $print_footer = false;
            $caller       = debug_backtrace();

            // remove first item (it's the printError() method itself)
            array_shift($caller);
            // if called by printFatalError(), shift again...
            if ( isset( $caller[0]['function'] ) && $caller[0]['function'] == 'printFatalError' ) {
                array_shift($caller);
            }
            $caller_class = isset( $caller[0]['class'] )
                          ? $caller[0]['class']
                          : NULL;

            if (true === is_array($message)){
                $message = implode("<br />", $message);
            }

            $message = CAT_Object::lang()->translate($message);

            // avoid "headers already sent" error
            if ( ! headers_sent() && $print_header ) {
                $print_footer = true;
                echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=windows-1250">
  <title>BlackCat CMS - '.$caller_class.' Fatal Error</title>
  </head>
    <style type=\"text/css\">
      #caterror{
          border:3px solid #f00;padding:5px 5px 5px 170px;margin:25px auto;width:75%;color:#f00;background-color:#f3d8d8;
          background-image:url(data:image/png;base64,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);
          background-repeat:no-repeat;
          background-position:15px center;
      }
      #catbacklink{
          border:1px solid #ccc;margin:25px auto;padding:5px;width:75%;
      }
    </style>
  <body>
        <div id=\"fc_content_header\">
	      <a class=\"fc_button_back ui-corner-right\" href=\"$link\">Zurück</a>
        </div>
';
            }

            // if we're able to use the template parser...
            global $parser;
            if (!is_object($parser) || ( !CAT_Backend::isBackend() && !defined('CAT_PAGE_CONTENT_DONE')) )
            {
                echo "
    <div id=\"caterror\">
      <h1>BlackCat CMS Fatal Error</h1><br /><br />
        <div style=\"color: #FF0000; font-weight: bold; font-size: 1.2em;\">
            $message\n";
            }
            else
            {
                if(!CAT_Backend::isBackend())
                {
                    $parser->setPath(sanitize_path(CAT_PATH.'/templates/'.DEFAULT_TEMPLATE.'/templates/default'));
                    $parser->setFallbackPath(CAT_THEME_PATH.'/templates/default');
                }
                else
                {
                    CAT_Backend::initPaths();
                }
                $parser->output('error', array('MESSAGE'=>$message,'LINK'=>$link));
            }

            if ( $args )
            {
                $dump = print_r( $args, 1 );
                $dump = preg_replace( "/\r?\n/", "\n          ", $dump );
                echo "<br />\n";
                echo "<pre>\n";
                echo "          ", $dump;
                echo "</pre>\n";
            }

            // remove path info from file
            $file     = ( isset($caller[1]) && isset($caller[1]['file']) )
                      ? basename( $caller[1]['file'] )
                      : (
                          ( isset($caller[0]) && isset($caller[0]['file']) )
                          ? basename( $caller[0]['file'] )
                          : 'unknown'
                        );
            $line     = ( isset($caller[1]) && isset($caller[1]['line'])     )
                      ? $caller[1]['line']
                      : (
                          ( isset($caller[0]) && isset($caller[0]['line'])     )
                          ? $caller[0]['line']
                          : '-'
                        );
            $function = ( isset($caller[1]) && isset($caller[1]['function']) )
                      ? $caller[1]['function']
                      : (
                          ( isset($caller[0]) && isset($caller[0]['function']) )
                          ? $caller[0]['function']
                          : '-'
                        );

            echo "<br /><br /><span style=\"font-size: smaller;\">[ ",
                 $file, ' : ', $line, ' : ', $function,
                 " ]</span><br />\n";

            #if ( $this->debugLevel == self::DEBUG ) {
            #    echo "<h2>Debug backtrace:</h2>\n",
            #         "<textarea cols=\"100\" rows=\"20\" style=\"width: 100%;\">";
            #    print_r( $caller );
            #    echo "</textarea>";
            #}

            echo "  </div>\n</div>\n";

            if ( $print_footer ) {
                echo "</body></html>\n";
            }

        }   // end function printError()

        /**
         * wrapper to printError(); print error message and exit
         *
         * see printError() for @params
         *
         * @access public
         *
         **/
        public static function printFatalError( $message = NULL, $link = 'index.php', $print_header = true, $args = NULL ) {
            CAT_Object::printError( $message, $link, $print_header, $args );
            exit;
        }   // end function printFatalError()

        /**
         *  Print a success message and redirect the user to another page
         *
         *  @access public
         *  @param  mixed   $message     - message string or an array with a couple of messages
         *  @param  string  $redirect    - redirect url; default is "index.php"
         *  @param  boolean $auto_footer - optional flag to 'print' the footer. Default is true.
         *  @return void    exit()s
         */
    	public static function printMsg($message, $redirect = 'index.php', $auto_footer = true)
    	{
    		global $parser;

    		if (true === is_array($message)){
    			$message = implode("<br />", $message);
    		}

    		$parser->setPath(CAT_THEME_PATH . '/templates');
    		$parser->setFallbackPath(CAT_THEME_PATH . '/templates');

    		$parser->output('success',array(
                'MESSAGE'        => CAT_Helper_I18n::getInstance()->translate($message),
                'REDIRECT'       => $redirect,
                'REDIRECT_TIMER' => CAT_Registry::get('REDIRECT_TIMER'),
            ));

    		if ($auto_footer == true)
    		{
                $caller       = debug_backtrace();
                // remove first item (it's the printMsg() method itself)
                array_shift($caller);
                $caller_class
                    = isset( $caller[0]['class'] )
                    ? $caller[0]['class']
                    : NULL;
    			if ($caller_class && method_exists($caller_class, "print_footer"))
    			{
                    if( is_object($caller_class) )
    				    $caller_class->print_footer();
                    else
                        $caller_class::print_footer();
    			}
                else {
                    //echo "unable to print footer - no such method $caller_class -> print_footer()";
                }
                exit();
    		}
        }   // end function printMsg()


        
/*******************************************************************************
 * LOGGING / DEBUGGING
 ******************************************************************************/
        
        /**
         * enable or disable debugging at runtime
         *
         * @access public
         * @param  boolean  enable (TRUE) / disable (FALSE)
         *
         **/
        public function debug( $bool ) {
            if ( $bool === true )
            {
                $this->debugLevel = 7; // 7 = Debug
            }
            else
            {
                $this->debugLevel = 8; // 8 = OFF
            }
        }   // end function debug()

        /**
         * returns a database connection handle
         *
         * This function must be used by all classes, as we plan to replace
         * the database class in later versions!
         *
         * @access public
         * @return object
         **/
        public function db()
        {
            if ( ! $this->db || ! is_object($this->db) )
            {
                if ( ! CAT_Registry::exists('CAT_PATH',false) )
                    CAT_Registry::define('CAT_PATH',dirname(__FILE__).'/../..');
                if ( ! class_exists('database',false) )
                    @include_once CAT_Registry::get('CAT_PATH').'/framework/class.database.php';
                $this->db = new database();
            }
            return $this->db;
        }   // end function db()

        /**
           * Accessor to KLogger class; this makes using the class significant faster!
           *
           * @access public
           * @return object
           *
           **/
          public function log () {
            if ( $this->debugLevel < 8 ) { // 8 = OFF
                if ( ! is_object( $this->logObj ) ) {
                    if ( ! CAT_Registry::exists('CAT_PATH',false) )
                        CAT_Registry::define('CAT_PATH',dirname(__FILE__).'/../..');
                    $debug_dir = CAT_PATH.'/temp/logs'
                               . ( $this->debugLevel == 7 ? '/debug_'.get_class($this) : '' );
                    if ( ! file_exists( $debug_dir ) ) {
                        mkdir( $debug_dir, 0777 );
                    }
                    $this->logObj = CAT_Helper_KLogger::instance( $debug_dir, $this->debugLevel );
                }
                return $this->logObj;
            }
            return $this;
          }   // end function log ()

        /**
         * Fake KLogger access methods if debugLevel is set to 8 (=OFF)
         **/
        public function logInfo  () {}
        public function logNotice() {}
        public function logWarn  () {}
        public function logError () {}
        public function logFatal () {}
        public function logAlert () {}
        public function logCrit  () {}
        public function logEmerg () {}
        public function logDebug () {}

    }
}