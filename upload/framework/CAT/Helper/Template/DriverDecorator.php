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
 *   @link            http://www.blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if ( ! class_exists('CAT_Helper_Template_DriverDecorator',false) )
{
    class CAT_Helper_Template_DriverDecorator extends CAT_Helper_Template {

    private $te;
    public  $path;
    public  $fallback_path;
    public  $template_block;

    public function __construct( $obj )
    {
        parent::__construct();
        $this->te = $obj;
                // get current working directory
        $callstack = debug_backtrace();
        $this->te->workdir
            = ( isset( $callstack[0] ) && isset( $callstack[0]['file'] ) )
            ? realpath( dirname( $callstack[0]['file'] ) )
            : realpath( dirname(__FILE__) );

        if (
             file_exists( $this->te->workdir.'/templates' )
        ) {
            $this->setPath( $this->te->workdir.'/templates' );
        }
    }

    public function __call($method, $args)
    {
        if ( ! method_exists( $this->te, $method ) )
        {
            $this->logger->logCrit('No such method: ['.$method.']');
        }
        return call_user_func_array(array($this->te, $method), $args);
    }

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
            $this->te->path = realpath($path);
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
            $this->te->fallback_path = realpath($path);
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
    public function setGlobals( $var, $value = NULL )
    {
        $class = get_class($this->te);
        if ( ! is_array( $var ) && isset( $value ) ) {
           $class::$_globals[ $var ] = $value;
           return;
        }
        if ( is_array( $var ) ) {
            foreach ( $var as $k => $v ) {
                $class::$_globals[ $k ] = $v;
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
    }
}