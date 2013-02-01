<?php

/*******************************************************************************
    Library Admin - lib_jquery
    (c) 2010 Bianka Martinovic - All rights reserved
    http://www.webbird.de/
*******************************************************************************/

if ( ! defined( 'CAT_URL' ) ) {
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
                '{CAT_URL}',
                '{CAT_PATH}',
                '{{ insert_files }}',
            ),
            array(
                 CAT_URL,
                 CAT_URL,
                 CAT_PATH,
                 '',
            ),
            $f_content
        );
            
        return $f_content;
    }

}   // end function _loadFile()

?>