<?php

/*******************************************************************************
    Library Admin
    jQuery / jQuery UI Library
    (c) 2010 Bianka Martinovic - All rights reserved
    http://www.webbird.de/
*******************************************************************************/


include_once dirname(__FILE__).'/../../libraryadmin/inc/class.LibraryUtils.php';

class jQueryAdmin extends LibraryUtils {

    protected $lib_info;
    
    /**
     * Constructor
     *
     * Loads the library info into the current object and dds some global
     * template vars
     *
     * @access public
     * @return void
     *
     **/
    public function __construct() {
        global $lib_info;
        include_once dirname(__FILE__).'/../library_info.php';
        $this->lib_info = $lib_info;
        parent::__construct();
    }   // end function __construct()
    
    public function core_files() {
        return array( 'jquery-core/jquery-core.min.js' );
    }   // end function core_files()
    
    // set noConflict mode
    public function no_conflict_mode( $preset_content ) {
        $preset_content = preg_replace( '#\$\(#', 'jQuery(', $preset_content );
        $preset_content = preg_replace( '#\$\.#', 'jQuery.', $preset_content );
        if ( ! preg_match( '#jquery\.noconflict#i', $preset_content ) ) {
            $preset_content = preg_replace( '#\<head\>#i', '<head><script type="text/javascript">if ( typeof jQuery != "undefined" ) { jQuery.noConflict(); }</script>', $preset_content );
        }
        return $preset_content;
    }
    
    /**
     *
     *
     *
     *
     **/
    public function component_types() {
        return
            array(
                'ui'       => array(
                                  'label'     => 'UI Components',
                                  'subdir'    => 'jquery-ui/ui',
                                  'regexp'    => '/^(.*)\.ui\.(.*)\.min\.js$/',
                                  'exclude'   => '/core/i',
                                  'core'      => 'jquery.ui.core.min.js',
                             ),
                'effects'  => array(
                                  'label'     => 'UI Effects',
                                  'subdir'    => 'jquery-ui/ui',
                                  'regexp'    => '/^(.*)\.effects\.(.*)\.min\.js$/',
                                  'exclude'   => '/core/i',
                                  'core'      => 'jquery.effects.core.min.js',
                             ),
                'external' => array(
                                  'label'     => 'External',
                                  'subdir'    => 'jquery-ui/external',
                                  'regexp'    => '/^(?:(jquery)\.)(.*)\.js$/',
                              ),
                'themes'   => array(
                                  'label'     => 'Themes',
                                  'subdir'    => 'jquery-ui/themes',
                                  'regexp'    => '/^(.*)\.css$/',
                                  'include'   => 'jquery-ui.css',
                                  'show_dirs' => true,
                                  'default'   => 'empty',
                              ),

            );
    }   // end function component_types()
    
}