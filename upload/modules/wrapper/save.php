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
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Module
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

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require( CAT_PATH . '/modules/admin.php' );

// Update the mod_wrapper table with the contents
if ( isset( $_POST[ 'url' ] ) )
{
	$url    = $admin->add_slashes( strip_tags( $_POST[ 'url' ] ) );
	$height = ( isset($_POST['height']) ? $_POST['height'] : 400      );
	$width  = ( isset($_POST['width'])        ? $_POST['width']          : '100%'   );
	$type   = ( isset($_POST['wrapper_type'])   ? $_POST['wrapper_type']   : 'iframe' );
	if ( is_numeric( $height ) )
		$height .= 'px';
	if ( is_numeric( $width ) )
		$width  .= 'px';
	$query = "UPDATE " . CAT_TABLE_PREFIX . "mod_wrapper SET url = '$url', height = '$height', width = '$width', wtype = '$type' WHERE section_id = '$section_id'";
	$database->query( $query );
}

// Check if there is a database error, otherwise say successful
if ( $database->is_error() )
{
	$admin->print_error( $database->get_error(), $js_back );
}
else
{
	$admin->print_success( $MESSAGE[ 'PAGES' ][ 'SAVED' ], CAT_ADMIN_URL . '/pages/modify.php?page_id=' . $page_id );
}

// Print admin footer
$admin->print_footer();

?>