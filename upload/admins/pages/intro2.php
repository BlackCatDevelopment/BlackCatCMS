<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version         $Id$
 *
 */
 
// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
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



// Get posted content
if(!isset($_POST['content'])) {
	header("Location: intro".PAGE_EXTENSION."");
	exit(0);
} else {
	$content = $_POST['content'];
}

require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages_intro');

$content=$admin->strip_slashes($content);

// Include the WB functions file
require_once(WB_PATH.'/framework/functions.php');

if (strlen($content) == 0) {
	$admin->print_error($MESSAGE['PAGES_INTRO_EMPTY'], "intro.php");
} else {
	// Write new content
	$filename = WB_PATH.PAGES_DIRECTORY.'/intro'.PAGE_EXTENSION;
	$handle = fopen($filename, 'w');
	if(is_writable($filename)) {
		if(fwrite($handle, $content)) {
			fclose($handle);
			change_mode($filename, 'file');
			$admin->print_success($MESSAGE['PAGES_INTRO_SAVED']);
		} else {
			fclose($handle);
			$admin->print_error($MESSAGE['PAGES_INTRO_NOT_WRITABLE']);
		}
	} else {
		$admin->print_error($MESSAGE['PAGES_INTRO_NOT_WRITABLE']);
	}
}

// Print admin footer
$admin->print_footer();

?>