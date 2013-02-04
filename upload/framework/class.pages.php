<?php

/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 *
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2013, Black Cat Development
 * @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
 *
 */


// include class.secure.php to protect this file and the whole CMS!
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
// end include class.secure.php

// Load the other required class files if they are not already loaded
require_once(CAT_PATH."/framework/class.database.php");
require_once(CAT_PATH.'/framework/class.wb.php');



/**
 * pages class.
 *
 * @extends wb
 */
class pages extends wb
{


	public function __construct( $permission = false )
	{
		global $database;
		$this->db_handle = clone($database);
		if ( isset( $permission ) && is_array( $permission ) )
		{
			$this->permissions		= array(
					'PAGES'					=> $permission['pages'],
					'DISPLAY_ADD_L0'		=> $permission['pages_add_l0'],
					'DISPLAY_ADD'			=> $permission['pages_add'],
					'PAGES_MODIFY'			=> $permission['pages_modify'],
					'PAGES_DELETE'			=> $permission['pages_delete'],
					'PAGES_SETTINGS'		=> $permission['pages_settings'],
					'DISPLAY_INTRO'			=> $permission['pages_intro']
			);
		}
	}





}

?>