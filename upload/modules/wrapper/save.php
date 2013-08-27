<?php

/**
 * This file is part of an ADDON for use with Black Cat CMS Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @module          wrapper
 * @author          WebsiteBaker Project
 * @author          LEPTON Project
 * @copyright       2004-2010, WebsiteBaker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 *
 * @reformatted     2011-09-28
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

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require( CAT_PATH . '/modules/admin.php' );

// Update the mod_wrapper table with the contents
if ( isset( $_POST[ 'url' ] ) )
{
	$url    = $admin->add_slashes( strip_tags( $_POST[ 'url' ] ) );
	$height = ( isset($_POST['height']) ? $_POST['height'] : 400      );
	$width  = ( isset($_POST['width'])  ? $_POST['width']  : 400      );
	$type   = ( isset($_POST['wrapper_type'])   ? $_POST['wrapper_type']   : 'iframe' );
	if ( !is_numeric( $height ) )
	{
		$height = 400;
	}
	if ( !is_numeric( $width ) )
	{
		$width = 400;
	}
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