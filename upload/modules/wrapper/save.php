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
 *   @category        CAT_Modules
 *   @package         wrapper
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

$backend = CAT_Backend::getInstance('pages','pages_modify');

$update_when_modified = true; // Tells script to update when this page was last updated
require CAT_PATH.'/modules/admin.php';

// Update the mod_wrapper table with the contents
if ( isset($_POST['url']) )
{
    $url    = CAT_Helper_Validate::sanitize_url($_POST['url']);
    $height = ( isset($_POST['height'])       ? $_POST['height']         : '400px'  );
    $width  = ( isset($_POST['width'])        ? $_POST['width']          : '100%'   );
    $type   = ( isset($_POST['wrapper_type']) ? $_POST['wrapper_type']   : 'object' );
    if ( is_numeric( $height ) )
        $height .= 'px';
    if ( is_numeric( $width ) )
        $width  .= 'px';
    $query = "UPDATE `:prefix:mod_wrapper` SET `url`=:url,`height`=:height,`width`=:width,`wtype`=:wtype WHERE `section_id`=:sec";
    $database->query($query,array(
        'url'=>$url, 'height'=>$height, 'width'=>$width, 'wtype'=>$type, 'sec'=>$section_id
    ));
}

// Check if there is a database error, otherwise say successful
if ( $database->isError() )
{
    $admin->print_error( $database->getError(), $js_back );
}
else
{
    $admin->print_success('Saved', CAT_ADMIN_URL . '/pages/modify.php?page_id=' . $page_id );
}

// Print admin footer
$admin->print_footer();

?>