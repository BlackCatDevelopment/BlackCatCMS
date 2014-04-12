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
    class CAT_Helper_Template_DriverDecorator extends CAT_Helper_Template
    {

        private $te;
        private $dirh;
        private $seen  = array();
        private $paths = array(
            'current'           => NULL,
            'frontend'          => NULL,
            'frontend_fallback' => NULL,
            'backend'           => NULL,
            'backend_fallback'  => NULL,
            'workdir'           => NULL
        );
        private $search_order = array(
            'current', 'frontend', 'frontend_fallback', 'backend', 'backend_fallback', 'workdir'
        );
        public  $template_block;
        protected $_config = array( 'loglevel' => CAT_Helper_KLogger::CRIT );

        public function __construct( $obj )
        {
            parent::__construct();
            $this->te = $obj;
            // get current working directory
            $callstack = debug_backtrace();
            $this->te->paths['workdir']
                = ( isset( $callstack[0] ) && isset( $callstack[0]['file'] ) )
                ? CAT_Helper_Directory::sanitizePath(realpath(dirname($callstack[0]['file'])))
                : CAT_Helper_Directory::sanitizePath(realpath(dirname(__FILE__)));

            if (file_exists( $this->te->paths['workdir'].'/templates' ))
            {
                $this->te->paths['workdir'] .= '/templates';
            }
            $this->te->paths['current'] = $this->te->paths['workdir'];
            $this->dirh = CAT_Helper_Directory::getInstance();
        }

        public function __call($method, $args)
        {
            if ( ! method_exists( $this->te, $method ) )
                $this->logger->logCrit('No such method: ['.$method.']');
            switch(count($args))
            {
                case 0:
                    return $this->te->$method();
                case 1:
                    return $this->te->$method($args[0]);
                case 2:
                    return $this->te->$method($args[0], $args[1]);
                case 3:
                    return $this->te->$method($args[0], $args[1], $args[2]);
                case 4:
                    return $this->te->$method($args[0], $args[1], $args[2], $args[3]);
                case 5:
                    return $this->te->$method($args[0], $args[1], $args[2], $args[3], $args[4]);
                default:
                    return call_user_func_array(array($this->te, $method), $args);
            }
            //return call_user_func_array(array($this->te, $method), $args);
        }

        /**
         * set current template search path
         *
         * @access public
         * @param  string  $path
         * @param  string  $context - frontend (default) or backend
         * @return boolean
         *
         **/
         public function setPath ( $path, $context = 'frontend' )
         {
            $path = CAT_Helper_Directory::sanitizePath($path);
            $this->logger->logDebug(sprintf('context [%s] path [%s]', $context, $path ));
            if ( file_exists( $path ) )
            {
                $this->te->paths[$context]  = $path;
                $this->te->paths['current'] = $path;
                if(!isset($this->te->paths[$context.'_fallback']))
                    $this->te->paths[$context.'_fallback'] = $path;
                return true;
            }
            else
            {
                $this->logger->logWarn( 'unable to set template path: does not exist!', $path );
                return false;
            }
        }   // end function setPath()

        /**
         * set template fallback path (for templates not found in default path)
         *
         * @access public
         * @param  string  $path
         * @param  string  $context - frontend (default) or backend
         * @return boolean
         *
         **/
        public function setFallbackPath ( $path, $context = 'frontend' )
        {
            $path = CAT_Helper_Directory::sanitizePath($path);
            $this->logger->logDebug(sprintf('context [%s] fallback path [%s]', $context, $path ));
            if ( file_exists( $path ) ) {
                $this->te->paths[$context.'_fallback'] = $path;
            return true;
            }
            else
            {
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
            $file = $this->findTemplate($name);
            if ( $file )
                return $file;
            else
                return false;
        }   // end function hasTemplate()

        /**
         *
         * @access public
         * @return
         **/
        public function findTemplate($_tpl)
        {
            // cached
            if(isset($this->seen[$this->te->paths['current'] . $_tpl]))
                return $this->seen[$this->te->paths['current'] . $_tpl];

            $suffix = pathinfo($_tpl,PATHINFO_EXTENSION);
            $has_suffix = ( $suffix != '' ) ? true : false;

            // scan search paths (if any)
            $paths = array();
            $s_paths = $this->te->paths;
            // sort paths by key; this sets 'workdir' to the end of the array
            ksort($s_paths);
            // move 'current' to begin
            $temp = array('current' => $s_paths['current']);
            unset($s_paths['current']);
            $s_paths = $temp + $s_paths;
            foreach($s_paths as $key => $value)
                if(isset($s_paths[$key]) && file_exists($s_paths[$key]))
                    $paths[] = $value;
            // remove doubles
            $paths = array_unique($paths);
            // (re-)set suffix filter
            $this->dirh->setSuffixFilter(array('tpl','htt','lte'));

            foreach ( $paths as $dir ) {
                if($has_suffix && file_exists($dir.'/'.$_tpl))
                    $file = $dir.'/'.$_tpl;
                else
                    $file = $this->dirh->findFile($_tpl,$dir,true);
                if ( $file )
                {
                    $this->seen[$this->te->paths['current'] . $_tpl] = $file;
                    return $file;
                }
            }
            $this->logger->logCrit( "The template [$_tpl] does not exists in one of the possible template paths!", $paths );
            // the template does not exists, so at least prompt an error
            $br = "\n";
            trigger_error(
                CAT_Helper_I18n::getInstance()->translate(
                    "The template [{{ tpl }}] does not exists in one of the possible template paths!{{ paths }}",
                    array(
                        'tpl'   => $_tpl,
                        'paths' => ( $this->te->_config['show_paths_on_error']
                                ? $br.$br.CAT_Helper_I18n::getInstance()->translate('Searched paths').':'.$br.implode($br,$paths).$br
                                : NULL )
                    )
                ), E_USER_ERROR
            );
        }   // end function findTemplate()

    }
}