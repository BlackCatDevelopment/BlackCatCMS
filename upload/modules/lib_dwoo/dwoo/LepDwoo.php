<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          Dwoo Template Engine
 * @author          LEPTON Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.lepton-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 *
 *
 */

// try to include LEPTON class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
	if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) {
			include($dir.'/framework/class.secure.php'); $inc = true;	break;
		}
	}
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include LEPTON class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}
// end include LEPTON class.secure.php

//include realpath(dirname(__FILE__)).'/Dwoo.php';

class LepDwoo extends Dwoo {

    protected $workdir         = NULL;
    protected $path            = NULL;
    protected $fallback_path   = NULL;
    protected $debuglevel      = CAT_Helper_KLogger::CRIT;
    protected $logger          = NULL;
    protected static $_globals = array();
    
    public function __construct($compileDir = null, $cacheDir = null)
    {
        parent::__construct( $compileDir, $cacheDir );
        
        // get current working directory
        $callstack = debug_backtrace();
        $this->workdir
            = ( isset( $callstack[0] ) && isset( $callstack[0]['file'] ) )
            ? realpath( dirname( $callstack[0]['file'] ) )
            : realpath( dirname(__FILE__) );

        if (
             file_exists( $this->workdir.'/templates' )
        ) {
            $this->setPath( $this->workdir.'/templates' );
        }
        
        if ( ! class_exists('CAT_Helper_KLogger',false) ) {
            include dirname(__FILE__).'/../../../framework/CAT/Helper/KLogger.php';
		}
        $this->logger = new CAT_Helper_KLogger( CAT_PATH.'/temp', $this->debuglevel );
        
    }   // end function __construct()

    /**
     * set default template search path
     *
     * @access public
     * @param  string  $path
     * @return boolean
     *
     **/
    public function setPath ( $path ) {
        if ( file_exists( $path ) ) {
            $this->logger->logDebug( 'setting path:', $path );
            $this->path = realpath($path);
            return true;
        }
        else {
            $this->logger->logWarn( 'unable to set template path: does not exist!', $path );
            return false;
        }
    }   // end function setPath()
    
    /**
     * set template fallback path (for templates not found in default path)
     *
     * @access public
     * @param  string  $path
     * @return boolean
     *
     **/
    public function setFallbackPath ( $path ) {
        if ( file_exists( $path ) ) {
            $this->logger->logDebug( 'setting fallback path:', $path );
            $this->fallback_path = realpath($path);
            return true;
        }
        else {
            $this->logger->logWarn( 'unable to set fallback template path: does not exist!', $path );
            return false;
        }
    }   // end function setFallbackPath()
    
    /**
     * set global replacement values
     *
     * Usage
     *    $t->setGlobals( 'varname', 'value' );
     * or
     *    $t->setGlobals( array( 'var1' => 'val1', 'var2' => 'val2', ... ) );
     *
     * The second param is ignored if $var is an array
     *
     * @access public
     * @param  string || array  $var
     * @param  string           $value (optional)
     *
     **/
    public function setGlobals( $var, $value = NULL ) {

        if ( ! is_array( $var ) && isset( $value ) ) {
           self::$_globals[ $var ] = $value;
           return;
        }

        if ( is_array( $var ) ) {
            foreach ( $var as $k => $v ) {
                self::$_globals[ $k ] = $v;
            }
        }

    }  // end function setGlobals()
    
    /**
     * check if template exists in current search path(s)
     **/
    public function hasTemplate($name)
    {
        // scan search paths (if any)
        $paths = array();
        if ( $this->path ) {
            $paths[] = $this->path;
        }
        if ( $this->fallback_path ) {
            $paths[] = $this->fallback_path;
        }
        $paths[] = $this->workdir;
        // remove doubles
        $paths = array_unique($paths);
        foreach ( $paths as $dir ) {
            if ( file_exists( $dir.'/'.$name ) ) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * this overrides and extends the original get() method Dwoo provides:
     * - use the template search and fallback paths
     *
     * @access public
     * @param  see original Dwoo docs
     * @return see original Dwoo docs
     *
     **/
    public function get($_tpl, $data = array(), $_compiler = null, $_output = false)
    {
        // add globals to $data array
        if ( is_array(self::$_globals) && count(self::$_globals) && is_array($data)) {
            $data = array_merge( self::$_globals, $data ); 
        }
        if ( ! is_object ( $_tpl ) ) {
            if ( ! file_exists( $_tpl ) )
            {
                // scan search paths (if any)
                $paths = array();
                if ( $this->path ) {
                    $paths[] = $this->path;
                }
                if ( $this->fallback_path ) {
                    $paths[] = $this->fallback_path;
                }
                $paths[] = $this->workdir;
                // remove doubles
                $paths = array_unique($paths);
                foreach ( $paths as $dir ) {
                    if ( file_exists( $dir.'/'.$_tpl ) ) {
                        return parent::get( realpath($dir.'/'.$_tpl), $data, $_compiler, $_output );
                    }
                }
                $this->logger->logCrit( "The template [$_tpl] does not exists in one of the possible template paths!", $paths );
                // the template does not exists, so at least prompt an error
                trigger_error("The template <b>$_tpl</b> does not exists in one of the possible template paths!", E_USER_ERROR);
            } else {
            	return parent::get( $_tpl, $data, $_compiler, $_output );
            }
        }
        else {
            return parent::get( $_tpl, $data, $_compiler, $_output );
        }
        
    }   // end function get()
    
}   // end class LepDwoo

?>