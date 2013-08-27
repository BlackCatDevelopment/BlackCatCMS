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
 *   @category        CAT_Modules
 *   @package         lib_dwoo
 *
 */

// include class.secure.php to protect this file and the whole CMS!
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
// end include class.secure.php

require_once CAT_PATH.'/framework/functions.php';

function Dwoo_Plugin_makeThumb(Dwoo $dwoo, $file='', $prefix='', $height=300, $width=200, $method='fit', $overwrite=false) {
	if ( $file == '' ) return false;

	// check if the file contains the Path to the image
	$file			= str_replace( CAT_URL, CAT_PATH, $file );
	$file			= strpos( $file, CAT_PATH ) === false ?
							CAT_PATH . $file :
							$file;

	// Set some values
	$temp_path		= CAT_PATH . '/temp/' . MEDIA_DIRECTORY . '/';
	$temp_url		= CAT_URL . '/temp/' . MEDIA_DIRECTORY . '/';
	$info			= pathinfo( $file );

	$new_path		= CAT_Helper_Directory::sanitizePath( $temp_path. $prefix . $info['filename'] . '_' . $width . '_' . $height . '.' . $info['extension'] );
	$new_url		= str_replace( CAT_PATH, CAT_URL, $new_path );

	// Create temp directory, if the folder doesn't exist
	if ( !file_exists( $temp_path ) )
		CAT_Helper_Directory::createDirectory( $temp_path, NULL, true );

	// Create the file, if the file does not exist or overwrite is set to true
	if ( !file_exists( $new_path ) || $overwrite == true )
	{
		CAT_Helper_Image::getInstance()->make_thumb(
			$file,
			$new_path,
			$height,
			$width,
			$method
		);
	}

	return $new_url;
	// end make_thumb()
}

?>
