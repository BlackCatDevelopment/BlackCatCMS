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
 *   @author          LEPTON v2.0 Black Cat Edition Development
 *   @copyright       2013, LEPTON v2.0 Black Cat Edition Development
 *   @link            http://www.lepton2.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        LEPTON2BCE_Modules
 *   @package         ckeditor4
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
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

if ( !defined('WB_PATH')) die(header('Location: ../../index.php'));

$debug = false;
if (true === $debug) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL|E_STRICT);
}

/**
 *	Function called by parent, default by the wysiwyg-module
 *	
 *	@param	string	The name of the textarea to watch
 *	@param	mixed	The "id" - some other modules handel this param differ
 *	@param	string	Optional the width, default "100%" of given space.
 *	@param	string	Optional the height of the editor - default is '250px'
 *
 *
 */
function show_wysiwyg_editor($name, $id, $content, $width = '100%', $height = '250px') {
    global $database;
    // get settings
    $query  = "SELECT * from `".TABLE_PREFIX."mod_wysiwyg_admin_v2` where `editor`='".WYSIWYG_EDITOR."'";
    $result = $database->query ($query );
    $config = array();
    if($result->numRows())
    {
        while( false !== ( $row = $result->fetchRow(MYSQL_ASSOC) ) )
        {
            if ( substr_count( $row['set_value'], '#####' ) ) // array values
            {
                $row['set_value'] = explode( '#####', $row['set_value'] );
            }
            $config[] = $row;
        }
    }
    global $parser;
    $parser->setPath(realpath(dirname(__FILE__).'/templates/default'));
    echo $parser->get(
        'wysiwyg.lte',
        array(
            'name'    => $name,
            'id'      => $id,
            'width'   => $width,
            'height'  => $height,
            'config'  => $config,
            'content' => htmlspecialchars(str_replace(array('&gt;','&lt;','&quot;','&amp;'),array('>','<','"','&'),$content))
        )
    );
}
?>