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
 * @version         $Id: Object.php 1257 2011-10-26 13:18:23Z webbird $
 *
 */

/**
 *
 * Base class for all Helper classes; provides some common methods
 *
 */
if ( ! class_exists( 'LEPTON_Object', false ) ) {

    if ( ! class_exists( 'LEPTON_Helper_KLogger', false ) ) {
		@include dirname(__FILE__).'/Helper/KLogger.php';
	}

	class LEPTON_Object
	{
	
	    protected $debugLevel      = 8; // 8 = OFF
	    // array to store config options
        protected $_config         = array( 'loglevel' => 8 );
        // Language helper object handle
        private static $lang;
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
		
		public function lang()
		{
		    if ( ! is_object( self::$lang ) )
		    {
		    	if ( ! class_exists( 'LEPTON_Helper_I18n', false ) ) {
					@include dirname(__FILE__).'/Helper/I18n.php';
				}
				self::$lang = new LEPTON_Helper_I18n();
			}
			return self::$lang;
		}
		
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
         * prints a formatted error message
         *
         * @access public
         * @param  string  $msg  - error message
         * @param  mixed   $args - additional args to print
         *
         **/
        public function printError( $msg = NULL, $args = NULL ) {
            $caller       = debug_backtrace();
            $caller_class = isset( $caller[1]['class'] )
                          ? $caller[1]['class']
                          : NULL;
            echo "<div id=\"leperror\">\n",
                 "  <h1>$caller_class Fatal Error</h1><br /><br />\n",
                 "  <div style=\"color: #FF0000; font-weight: bold; font-size: 1.2em;\">\n",
                 "  $msg\n";

            if ( $args ) {
                $dump = print_r( $args, 1 );
                $dump = preg_replace( "/\r?\n/", "\n          ", $dump );
                echo "<br />\n";
                echo "<pre>\n";
                echo "          ", $dump;
                echo "</pre>\n";
            }

            // remove path info from file
            $file = basename( $caller[1]['file'] );

            echo "<br /><br /><span style=\"font-size: smaller;\">[ ",
                 $file, ' : ',
                 $caller[1]['line'], ' : ',
                 $caller[1]['function'],
                 " ]</span><br />\n";

            if ( $this->debugLevel == DEBUG ) {
                echo "<h2>Debug backtrace:</h2>\n",
                     "<textarea cols=\"100\" rows=\"20\" style=\"width: 100%;\">";
                print_r( debug_backtrace() );
                echo "</textarea>";
            }

            echo "  </div>\n</div><!-- id=\"leperror\" -->\n";

        }   // end function printError()
        
        /**
         * wrapper to printError(); print error message and exit
         *
         * see printError() for @params
         *
         * @access public
         *
         **/
		public function printFatalError( $msg = NULL, $args = NULL ) {
		    $this->printError( $msg = NULL, $args = NULL );
		    exit;
		}   // end function printFatalError()

        /**
         * sanitize path (remove '/./', '/../', '//')
         *
         * @access public
         * @param  string  $path - path to sanitize
         * @return string
         *
         **/
        public function sanitizePath( $path )
        {
			$path       = str_replace( '\\', '/', $path );
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
            $new_path = implode("/", $parts);
            // windows
            if ( ! preg_match( '/^[a-z]\:/i', $new_path ) ) {
				$new_path = '/' . $new_path;
			}
            return $new_path;
        }   // end function sanitizePath()
        
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
		    if ( $bool === true ) {
               	$this->debugLevel = 7; // 7 = Debug
			}
			else {
			    $this->debugLevel = 8; // 8 = OFF
			}
        }   // end function debug()

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
                    $debug_dir = $this->sanitizePath( LEPTON_PATH.'/temp/logs' );
                    if ( ! file_exists( $debug_dir ) ) {
                        mkdir( $debug_dir, 0777 );
                    }
                    $this->logObj = LEPTON_Helper_KLogger::instance( $debug_dir, $this->debugLevel );
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