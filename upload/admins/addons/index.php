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
$admin = new admin('Addons', 'addons');

// Setup template object
$tpl = new Template(THEME_PATH.'/templates');
$tpl->set_file('page', 'addons.htt');
$tpl->set_block('page', 'main_block', 'main');

// Insert values into the template object
$tpl->set_var(array(
		'ADMIN_URL' => ADMIN_URL,
		'THEME_URL' => THEME_URL,
		'WB_URL' => WB_URL
	)
);

/**
 *	Setting up the blocks
 */
$tpl->set_block('main_block', "modules_block", "modules");
$tpl->set_block('main_block', "templates_block", "templates");
$tpl->set_block('main_block', "languages_block", "languages");
$tpl->set_block('main_block', "reload_block", "reload");

$tpl->set_block('main_block', 'show_advanced_block', 'show_advanced');

/**
 *	Insert permission values into the template object
 *	Obsolete as we are using blocks ... see "parsing the blocks" section
 */
$display_none = "style=\"display: none;\"";
if($admin->get_permission('modules') != true) 	$tpl->set_var('DISPLAY_MODULES', $display_none);
if($admin->get_permission('templates') != true)	$tpl->set_var('DISPLAY_TEMPLATES', $display_none);
if($admin->get_permission('languages') != true)	$tpl->set_var('DISPLAY_LANGUAGES', $display_none);
if($admin->get_permission('admintools') != true)$tpl->set_var('DISPLAY_ADVANCED', $display_none);

if( ($admin->get_permission('admintools') != true) &&
    ($admin->get_permission('admintools') != true) &&
	($admin->get_permission('admintools') != true) &&
	($admin->get_permission('admintools') != true)   )
	{
		$tpl->set_var('DISPLAY_ALL', $display_none);
	}

$tpl->parse('show_advanced', 'show_advanced_block', true);
if(!isset($_GET['advanced']) || $admin->get_permission('admintools') != true)
{
	$tpl->set_var('DISPLAY_RELOAD', $display_none);
	$tpl->set_block('show_advanced', '');
}

/**
 *	Insert section names and descriptions
 */
$tpl->set_var(array(
	'ADDONS_OVERVIEW' => $MENU['ADDONS'],
	'MODULES' => $MENU['MODULES'],
	'TEMPLATES' => $MENU['TEMPLATES'],
	'LANGUAGES' => $MENU['LANGUAGES'],
	'MODULES_OVERVIEW' => $OVERVIEW['MODULES'],
	'TEMPLATES_OVERVIEW' => $OVERVIEW['TEMPLATES'],
	'LANGUAGES_OVERVIEW' => $OVERVIEW['LANGUAGES'],
	'TXT_ADMIN_SETTINGS' => $TEXT['ADMIN'] . ' ' . $TEXT['SETTINGS'],
	'MESSAGE_RELOAD_ADDONS' => $MESSAGE['ADDON_RELOAD'],
	'TEXT_RELOAD' => $TEXT['RELOAD'],
	'RELOAD_URL' => ADMIN_URL . '/addons/reload.php',
	'URL_ADVANCED' => $admin->get_permission('admintools')
                ? '<a href="' . ADMIN_URL . '/addons/index.php?advanced">' . $TEXT['ADVANCED'] . '</a>' : '',
	'ADVANCED_URL' => $admin->get_permission('admintools') ? ADMIN_URL . '/addons/index.php' : '',
    'TEXT_ADVANCED' => $TEXT['ADVANCED'],
	'ADDON_MANUAL_INSTALLATION_WARNING' => $MESSAGE['ADDON_RELOAD'].'<br /><span class="red">'.$TEXT['REQUIRED'].'!! '.$TEXT['BACKUP_DATABASE'].'.</span> '.$MESSAGE['ADDON_MANUAL_INSTALLATION_WARNING'],
	'RELOAD_ALL' => $TEXT['OTHERS'].' '.$MENU['ADDONS']

	)
);

/**
 *	Parsing the blocks ...
 */
if ( $admin->get_permission('modules') == true) $tpl->parse('main_block', "modules_block", true);
if ( $admin->get_permission('templates') == true) $tpl->parse('main_block', "templates_block", true);
if ( $admin->get_permission('languages') == true) $tpl->parse('main_block', "languages_block", true);
if ( isset($_GET['advanced']) && $admin->get_permission('admintools') == true)
{
	$tpl->parse('main_block', "reload_block", true);
}

/**
 *	Parse template object
 */
$tpl->parse('main', 'main_block', false);
$tpl->pparse('output', 'page');

/**
 *	Print admin footer
 */
$admin->print_footer();

?>