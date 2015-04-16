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
 *   @copyright       2013, 2014, Black Cat Development
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
        protected $_config        = array( 'loglevel' => 8 );
        // Language helper object handle
        protected static $lang    = NULL;
        // database handle
        protected static $db      = NULL;
        // KLogger object handle
        protected        $logObj  = NULL;
        
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
        
        /**
         * accessor to I18n helper
         *
         * @access public
         * @return object
         **/
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
                $this->_config = array_merge( $this->_config, $option );
            else
                $this->_config[$option] = $value;
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
        public static function printError( $message = NULL, $link = 'index.php', $print_header = true, $args = NULL )
        {

            global $parser;

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

            // remove path info from file
            $file     = ( isset($caller[1]) && isset($caller[1]['file']) )
                      ? basename( $caller[1]['file'] )
                      : (
                          ( isset($caller[0]) && isset($caller[0]['file']) )
                          ? basename( $caller[0]['file'] )
                          : NULL
                        );
            $line     = ( isset($caller[1]) && isset($caller[1]['line'])     )
                      ? $caller[1]['line']
                      : (
                          ( isset($caller[0]) && isset($caller[0]['line'])     )
                          ? $caller[0]['line']
                          : NULL
                        );
            $function = ( isset($caller[1]) && isset($caller[1]['function']) )
                      ? $caller[1]['function']
                      : (
                          ( isset($caller[0]) && isset($caller[0]['function']) )
                          ? $caller[0]['function']
                          : NULL
                        );

            if (true === is_array($message))
                $message = implode("<br />", $message);

            if($file)
            {
                $logger = CAT_Helper_KLogger::instance(CAT_PATH.'/temp/logs',2);
                $logger->logFatal(sprintf(
                    'Fatal error with message [%s] emitted in [%s] line [%s] method [%s]',
                    $message,$file,$line,$function
                ));
                if($args) $logger->logFatal(var_export($args,1));
            }

            $message = CAT_Object::lang()->translate($message);

            // avoid "headers already sent" error
            if ( ! headers_sent() && $print_header )
            {
                $print_footer = true;
                if (!is_object($parser) || ( !CAT_Backend::isBackend() && !defined('CAT_PAGE_CONTENT_DONE')) )
                {
                    self::err_page_header();
            }
            }

            if (!is_object($parser) || ( !CAT_Backend::isBackend() && !defined('CAT_PAGE_CONTENT_DONE')) )
            {
                echo CAT_Object::lang()->translate('Ooops... A fatal error occured while processing your request!'),
                     "<br /><br />",
                     CAT_Object::lang()->translate('Error message'),
                     ":<br />",
                     CAT_Object::lang()->translate($message),
                     "<br /><br />";
                echo CAT_Object::lang()->translate("We're sorry!");
            }
            else
            {
                $parser->output(
                    'error.tpl',
                    array(
                        'message'  => $message,
                        'file'     => $file,
                        'line'     => $line,
                        'function' => $function,
                        'link'     => $link,
                    )
                );
            }

            if ($print_footer && !is_object($parser))
            {
                self::err_page_footer();
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
         *  Print a message and redirect the user to another page
         *
         *  @access public
         *  @param  mixed   $message     - message string or an array with a couple of messages
         *  @param  string  $redirect    - redirect url; default is "index.php"
         *  @param  boolean $auto_footer - optional flag to 'print' the footer. Default is true.
         *  @param  boolean $auto_exit   - optional flag to call exit() (default) or not
         *  @return void    exit()s
         */
    	public static function printMsg($message, $redirect = 'index.php', $auto_footer = true, $auto_exit = true)
    	{
    		global $parser;

    		if (true === is_array($message))
    			$message = implode("<br />", $message);

    		$parser->setPath(CAT_THEME_PATH . '/templates');
    		$parser->setFallbackPath(CAT_THEME_PATH . '/templates');

    		$parser->output('success',array(
                'MESSAGE'        => CAT_Object::lang()->translate($message),
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
                if($auto_exit)
                    exit();
    		}
        }   // end function printMsg()

        /**
         *
         * @access public
         * @return
         **/
        public static function json_success($message,$exit=true)
        {
            json_result(true,$message,$exit);
        }   // end function json_success()
        
        /**
         *
         * @access public
         * @return
         **/
        public static function json_error($message,$exit=true)
        {
            json_result(false,$message,$exit);
        }   // end function json_error()

        /**
         *
         * @access public
         * @return
         **/
        public static function json_result($success,$message,$exit=true)
        {
            if(!headers_sent())
                header('Content-type: application/json');
            echo json_encode(array(
                'success' => $success,
                'message' => self::lang()->translate($message)
            ));
            if($exit) exit();
        }   // end function json_result()

        /**
         *
         * @access private
         * @return
         **/
        private static function err_page_header()
        {
                echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=windows-1250">
  <title>BlackCat CMS - Fatal Error</title>
    <style type="text/css">
        body{font-family: HelveticaNeue,Helvetica,Arial,Verdana,sans-serif;font-size:1.3em;line-height: 1.5em;background-color:#2C2C2C;color:#fff;}
        .fc_header{background-color:#0e1115;border-bottom:1px dashed #2d2d2d;top:0;color:#900;font-size:.9em;left:0;padding-bottom:5px;padding-top:5px;position:absolute;text-align:center;width:100%;z-index:1;margin:0;}
        .fc_error{width:100%;height:100%;position:absolute;top:150px;text-align:center;}
        .fc_license{background-color:#0e1115;border-top:1px dashed #2d2d2d;bottom:0;color:#9e9e9e;font-size:.7em;left:0;padding-bottom:5px;padding-top:5px;position:absolute;text-align:center;width:100%;z-index:1;margin:0;}
        a {color: #5aa2da;text-decoration: none;}
    </style>
</head>
<body>
    <div class="fc_header">
        <h1>BlackCat CMS Fatal Error</h1>
    </div>
    <div class="fc_error">
';
        }   // end function err_page_header()
        
        /**
         *
         * @access private
         * @return
         **/
        private static function err_page_footer()
        {
            echo '
    </div>
    <div class="fc_license">
		<p>
            <a target="_blank" title="Black Cat CMS Core" href="http://blackcat-cms.org">Black Cat CMS Core</a> is released under the
			<a target="_blank" title="Black Cat CMS Core is GPL" href="http://www.gnu.org/licenses/gpl.html">GNU General Public License</a>.<br>
			<a target="_blank" title="Black Cat CMS Bundle" href="http://blackcat-cms.org">Black Cat CMS Bundle</a> is released under several different licenses.
		</p>
	</div>
</body>
</html>
';
        }   // end function err_page_footer()
        

        
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
                $this->debugLevel = 7; // 7 = Debug
            else
                $this->debugLevel = 8; // 8 = OFF
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
            if ( ! self::$db || ! is_object(self::$db) )
            {
                if ( ! CAT_Registry::exists('CAT_PATH',false) )
                    CAT_Registry::define('CAT_PATH',dirname(__FILE__).'/../..');
                self::$db = CAT_Helper_DB::getInstance();
            }
            return self::$db;
        }   // end function db()

        /**
         * Accessor to KLogger class; this makes using the class significant faster!
         *
         * @access public
         * @return object
         *
         **/
        public function log () {
            // 8 = OFF
            if ( $this->debugLevel < 8 )
            { 
                if ( ! is_object( $this->logObj ) )
                {
                    if ( ! CAT_Registry::exists('CAT_PATH',false) )
                        CAT_Registry::define('CAT_PATH',dirname(__FILE__).'/../..',1);
                    $debug_dir = CAT_PATH.'/temp/logs'
                               . ( $this->debugLevel == 7 ? '/debug_'.get_class($this) : '' );
                    if(get_class($this) != 'CAT_Helper_Directory')
                    $debug_dir = CAT_Helper_Directory::sanitizePath($debug_dir);
                    if ( ! file_exists( $debug_dir ) )
                        if(get_class($this) != 'CAT_Helper_Directory')
                        CAT_Helper_Directory::createDirectory( $debug_dir, 0777 );
                        else
                            mkdir($debug_dir,0777);
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