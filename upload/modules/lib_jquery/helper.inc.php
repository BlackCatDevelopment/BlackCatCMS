<?php

/*******************************************************************************
    Library Admin - lib_jquery
    (c) 2010 Bianka Martinovic - All rights reserved
    http://www.webbird.de/
*******************************************************************************/

if ( ! defined( 'WB_URL' ) ) {
    require_once dirname(__FILE__).'/../../config.php';
}

/**
 * load file and replace some placeholders
 **/
function _loadFile( $file )
{

    if ( file_exists( $file ) )
    {
    
        $fh = fopen( $file, 'r' );
        $f_content = fread( $fh, filesize ($file) );
        fclose($fh);
        
        $f_content = str_ireplace(
            array(
                '{URL}',
                '{WB_URL}',
                '{WB_PATH}',
                '{{ insert_files }}',
            ),
            array(
                 WB_URL,
                 WB_URL,
                 WB_PATH,
                 '',
            ),
            $f_content
        );
            
        return $f_content;
    }

}   // end function _loadFile()

?>