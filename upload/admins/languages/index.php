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



require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Addons', 'languages');

// Setup template object
$template = new Template(THEME_PATH.'/templates');
$template->set_file('page', 'languages.htt');
$template->set_block('page', 'main_block', 'main');

// Insert values into language list
$template->set_block('main_block', 'language_list_block', 'language_list');
$result = $database->query("SELECT * FROM ".TABLE_PREFIX."addons WHERE type = 'language' order by name");
if($result->numRows() > 0) {
	while($addon = $result->fetchRow()) {
		$template->set_var('VALUE', $addon['directory']);
		$template->set_var('NAME', $addon['name'].' ('.$addon['directory'].')');
		$template->parse('language_list', 'language_list_block', true);
	}
}

// Insert permissions values
if($admin->get_permission('languages_install') != true) {
	$template->set_var('DISPLAY_INSTALL', 'hide');
}
if($admin->get_permission('languages_uninstall') != true) {
	$template->set_var('DISPLAY_UNINSTALL', 'hide');
}
if($admin->get_permission('languages_view') != true) {
	$template->set_var('DISPLAY_LIST', 'hide');
}

// Insert language headings
$template->set_var(array(
								'HEADING_INSTALL_LANGUAGE' => $HEADING['INSTALL_LANGUAGE'],
								'HEADING_UNINSTALL_LANGUAGE' => $HEADING['UNINSTALL_LANGUAGE'],
								'HEADING_LANGUAGE_DETAILS' => $HEADING['LANGUAGE_DETAILS']
								)
						);
// insert urls
$template->set_var(array(
								'ADMIN_URL' => ADMIN_URL,
								'WB_URL' => WB_URL,
								'WB_PATH' => WB_PATH,
								'THEME_URL' => THEME_URL
								)
						);
// Insert language text and messages
$template->set_var(array(
	'URL_MODULES' => $admin->get_permission('modules') ? 
		'<a class="button" href="' . ADMIN_URL . '/modules/index.php">' . $MENU['MODULES'] . '</a>' : '',
	'URL_ADVANCED' => $admin->get_permission('admintools') ? 
		'<a class="button" href="' . ADMIN_URL . '/modules/index.php?advanced">' . $TEXT['ADVANCED'] . '</a>' : '',
	'URL_TEMPLATES' => $admin->get_permission('templates') ? 
		'<a class="button" href="' . ADMIN_URL . '/templates/index.php">' . $MENU['TEMPLATES'] . '</a>' : '',
	'TEXT_INSTALL' => $TEXT['INSTALL'],
	'TEXT_UNINSTALL' => $TEXT['UNINSTALL'],
	'TEXT_VIEW_DETAILS' => $TEXT['VIEW_DETAILS'],
	'TEXT_PLEASE_SELECT' => $TEXT['PLEASE_SELECT']
	)
);

// Parse template object
$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');

// Print admin footer
$admin->print_footer();

?>