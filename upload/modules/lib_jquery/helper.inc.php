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
 *   @copyright       2014, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Module
 *   @package         lib_jquery
 *
 */

if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) {
		include($root.'/framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

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