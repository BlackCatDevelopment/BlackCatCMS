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

if ( ! class_exists('Dwoo',false) )
{
    include(CAT_PATH.'/modules/lib_dwoo/dwoo/dwooAutoload.php');
}

if ( ! class_exists('CAT_Helper_Template_DwooDriver',false) )
{
    class CAT_Helper_Template_DwooDriver extends Dwoo {

    protected $debuglevel      = CAT_Helper_KLogger::CRIT;
    protected $_config         = array( 'loglevel' => CAT_Helper_KLogger::CRIT, 'show_paths_on_error' => true );
    public    $workdir         = NULL;
    public    $path            = NULL;
    public    $fallback_path   = NULL;
    public    static $_globals = array();

    public function __construct()
    {
        $cache_path = CAT_PATH.'/temp/cache';
        if (!file_exists($cache_path)) mkdir($cache_path, 0755, true);
        $compiled_path = CAT_PATH.'/temp/compiled';
        if (!file_exists($compiled_path)) mkdir($compiled_path, 0755, true);
        parent::__construct( $compiled_path, $cache_path );
        if ( ! class_exists('CAT_Helper_KLogger',false) ) {
            include dirname(__FILE__).'/../../../framework/CAT/Helper/KLogger.php';
		}
        $this->logger = new CAT_Helper_KLogger( CAT_PATH.'/temp', $this->debuglevel );
    }   // end function __construct()

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
                $dirh  = CAT_Helper_Directory::getInstance();
                $dirh->setSuffixFilter(array('tpl','htt','lte'));
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
                    $file = $dirh->findFile($_tpl,$dir,true);
                    if ( $file ) {
                        return parent::get( realpath($file), $data, $_compiler, $_output );
                    }
                }
                $this->logger->logCrit( "The template [$_tpl] does not exists in one of the possible template paths!", $paths );
                // the template does not exists, so at least prompt an error
                trigger_error(
                    CAT_Helper_I18n::getInstance()->translate(
                        "The template [{{ tpl }}] does not exists in one of the possible template paths!{{ paths }}",
                        array(
                            'tpl'   => $_tpl,
                            'paths' => ( $this->_config['show_paths_on_error']
                                    ? '<br /><br />'.CAT_Helper_I18n::getInstance()->translate('Searched paths').':<br />&nbsp;&nbsp;&nbsp;'.implode('<br />&nbsp;&nbsp;&nbsp;',$paths).'<br />'
                                    : NULL )
                        )
                    ), E_USER_ERROR
                );
            } else {
            	return parent::get( $_tpl, $data, $_compiler, $_output );
            }
        }
        else {
            return parent::get( $_tpl, $data, $_compiler, $_output );
        }

    }   // end function get()

    }   // end class CAT_Helper_Template_DwooDriver
}
