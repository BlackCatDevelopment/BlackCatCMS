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
        $this->lang()->addFile(LANGUAGE.'.php', dirname(__FILE__).'/../languages');
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
     * As there are no single files for UI components and effects, we use
     * an info file to list them and a single checkbox to enable them all at
     * once
     **/
    public function showUIComponents( $item, $type, $selected ) {
        $info = file_get_contents( dirname(__FILE__).'/../jquery-ui/ui/info.txt');
        $html = '<input type="checkbox" name="ui[]" id="ui"'
              . ( ( is_array($selected) && isset($selected['jquery-ui.min.js']) ) ? 'checked="checked"' : '' )
              . ' /> '
              . $this->lang()->translate('Use jQuery UI')
              . '<span style="float: right;"><a class="tt" href="#" target="_blank" '
              .  'rel="lyteframe" rev="width: 800px; height: 600px; scrolling: auto;"'
              .  '>'
              .  '<img src="'.LA_IMG_URL.'/info.png" alt="Info" />'
              .  '<span class="tooltip"><span class="top"></span><span class="middle">'
              .  $info
              .  '</span><span class="bottom"></span></span>'
              .  '</a></span>';
        return $html;
    }   // end function showUIComponents()
    
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
                                  'label'     => 'Use jQuery UI',
                                  'subdir'    => 'jquery-ui/ui',
                                  'core'      => 'jquery-ui.min.js',
                                  'method'    => 'showUIComponents',
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