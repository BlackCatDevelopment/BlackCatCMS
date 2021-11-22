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

$user    = CAT_Users::getInstance();

// Get content
$result = CAT_Helper_Page::getInstance()->db()->query(
    "SELECT `content` FROM `:prefix:mod_wysiwyg` WHERE `section_id`=:id",
    array('id'=>$section_id)
);

// if there is any content....
if($result&&$result->numRows()>0)
{
    $fetch = $result->fetch(PDO::FETCH_ASSOC);
    // enable frontend editing?
    if($user->is_authenticated() && $user->checkPermission('pages','pages_modify',false))
    {
        $fetch['content'] = '<div data-editable data-name="wysiwyg_'.$page_id.'_'.$section_id.'">'
                          . $fetch['content']
                          . '</div>';
        // wrap droplets
        $fetch['content'] = preg_replace_callback(
            '/\[\[(.*?)\]\]/',
            function($matches) { return '<span contenteditable="false" data-droplet="'.$matches[1].'">'.$matches[0].'</span>'; },
            $fetch['content']
        );
    }
    echo $fetch['content'];
}
