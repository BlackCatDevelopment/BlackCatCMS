<?php

/*******************************************************************************
    Library Admin - jQuery Library
    (c) 2010 - 2013 Bianka Martinovic - All rights reserved
    http://www.webbird.de/
*******************************************************************************/

global $lib_info;
$lib_info = array(
    'library_path'    => 'lib_jquery',
    'library_name'    => 'jQuery',
    'library_version' => '1.11.0',
    'library_info'    => 'Includes jQuery Core v1.11.0, jQuery UI 1.10.4, UI Theme "base"',
    'library_class'   => 'jQueryAdmin',
    'preset_suffix'   => 'jquery',
    'scan_dirs'       => array(
                            '/presets',
                            '/jquery/presets',
                         ),
);

?>