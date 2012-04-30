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



// Get language name
if(!isset($_POST['code']) OR $_POST['code'] == "") {
	header("Location: index.php");
	exit(0);
} else {
	$code = $_POST['code'];
}

// fix secunia 2010-93-2
if (!preg_match('/^[A-Z]{2}$/', $code)) {
	header("Location: index.php");
	exit(0);
}

// Check whether the language exists
if(!file_exists(WB_PATH.'/languages/'.$code.'.php')) {
	header("Location: index.php");
	exit(0);
}

// Print admin header
require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Addons', 'languages_view');

// Setup language object
$template = new Template(THEME_PATH.'/templates');
$template->set_file('page', 'languages_details.htt');
$template->set_block('page', 'main_block', 'main');

// Insert values
require(WB_PATH.'/languages/'.$code.'.php');
$template->set_var(array(
	'CODE' => $language_code,
	'NAME' => $language_name,
	'AUTHOR' => $language_author,
	'VERSION' => $language_version,
	'DESIGNED_FOR' => $language_platform,
	'ADMIN_URL' => ADMIN_URL,
	'WB_URL' => WB_URL,
	'WB_PATH' => WB_PATH,
	'THEME_URL' => THEME_URL,
	'LICENSE'	=> $language_license
	)
);

// Restore language to original code
require(WB_PATH.'/languages/'.LANGUAGE.'.php');

// Insert language headings
$template->set_var(array(
	'HEADING_LANGUAGE_DETAILS' => $HEADING['LANGUAGE_DETAILS']
	)
);
// Insert language text and messages
$template->set_var(array(
	'TEXT_CODE' => $TEXT['CODE'],
	'TEXT_NAME' => $TEXT['NAME'],
	'TEXT_TYPE' => $TEXT['TYPE'],
	'TEXT_AUTHOR' => $TEXT['AUTHOR'],
	'TEXT_VERSION' => $TEXT['VERSION'],
	'TEXT_DESIGNED_FOR' => $TEXT['DESIGNED_FOR'],
	'TEXT_BACK' => $TEXT['BACK'],
	'TEXT_LICENSE'	=> $TEXT['LICENSE']
	)
);

// Parse language object
$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');

// Print admin footer
$admin->print_footer();

?>