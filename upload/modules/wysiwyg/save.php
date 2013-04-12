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
 * @author          Ryan Djurovich
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         wysiwyg
 *
 */

if (defined('CAT_PATH')) {	
	include(CAT_PATH.'/framework/class.secure.php'); 
} else {
	$oneback = "../";
	$root = $oneback;
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= $oneback;
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
require(CAT_PATH.'/modules/admin.php');

// Include the WB functions file
require_once(CAT_PATH.'/framework/functions.php');

/**
 *	Update the mod_wysiwygs table with the contents
 *	
 *	M.f.i	- The database-test for errors should be inside the condition block.
 *			- Additional tests for possible cross-attacks.
 *			- Additional test for the user CAN modify a) this modul conten and b) this section!
 */
if(isset($_POST['content'.$section_id])) {
    // for non-admins only
    if(!$admin->get_controller('Users')->ami_group_member(1))
    {
        // if HTMLPurifier is enabled...
        $r = $database->get_one('SELECT * FROM `'.CAT_TABLE_PREFIX.'mod_wysiwyg_admin_v2` WHERE set_name="enable_htmlpurifier" AND set_value="1"');
        if($r) {
            // use HTMLPurifier to clean up the output
            $content = $admin->get_helper('Protect')->purify($_POST['content'.$section_id],array('Core.CollectErrors'=>true));
        }
    }
    else {
        $content = $admin->add_slashes($_POST['content'.$section_id]);
    }
	/**
	 *	searching in $text will be much easier this way
	 *
	 */
	$text = umlauts_to_entities(strip_tags($content), strtoupper(DEFAULT_CHARSET), 0);

	$query = "REPLACE INTO `".CAT_TABLE_PREFIX."mod_wysiwyg` VALUES ( '$section_id', $page_id, '$content', '$text' );";
	$database->query($query);
	if ($database->is_error()) trigger_error(sprintf('[%s - %s] %s', __FILE__, __LINE__, $database->get_error()), E_USER_ERROR);	
}

$edit_page = CAT_ADMIN_URL.'/pages/modify.php?page_id='.$page_id.'#'.SEC_ANCHOR.$section_id;

// Check if there is a database error, otherwise say successful
if($database->is_error()) {
	$admin->print_error($database->get_error(), $js_back);
} else {
	$admin->print_success('Page saved successfully', $edit_page );
}

// Print admin footer
$admin->print_footer();

?>