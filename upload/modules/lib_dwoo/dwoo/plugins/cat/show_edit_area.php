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
 *   @copyright       2016, Black Cat Development
 *   @link            https://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'framework/class.secure.php')) {
		include($root.'framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

function Dwoo_Plugin_show_edit_area( Dwoo $dwoo, $name, $id, $content, $width = '100%', $height = '350px' ) {
    if ( !function_exists( 'show_edit_area' ) )
	{
        // find code editor (we support EditArea and ACE for now)
        foreach(array('ace_editor','edit_area') as $path)
        {
            $file = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/modules/'.$path.'/include.php');
            if(file_exists($file))
            {
    		    @require_once($file);
                break;
            }
        }
	}
    if ( function_exists( 'show_edit_area' ) )
	{
	    ob_start();
	    show_edit_area( $name, $id, $content, $width, $height );
	    $content = ob_get_clean();
	    echo $content;
    }
    else
    {
        echo sprintf('<textarea name="%s" id="%s" style="width:%s;height:%s;">%s</textarea>',
                     $name, $id, $width, $height, $content );
    }
}

?>