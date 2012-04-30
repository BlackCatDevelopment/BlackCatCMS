<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
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

$data_dwoo['URL'] = array(
	'MODULES'		=> $admin->get_permission('templates')  ? ADMIN_URL . '/modules/index.php' : false,
	'TEMPLATES'		=> ADMIN_URL . '/templates/index.php',
	'LANGUAGES'		=> $admin->get_permission('languages')  ? ADMIN_URL . '/languages/index.php' : false,
);

// Insert values into language list
$result = $database->query("SELECT * FROM ".TABLE_PREFIX."addons WHERE type = 'language' order by name");
if ( $result->numRows() > 0 )
{
	$counter=0;
	while ( $addon = $result->fetchRow() )
	{
		// Clear all variables
		$language_code		= '';
		$language_name		= '';
		$language_version	= '';
		$language_platform	= '';
		$language_author	= '';
		$language_license	= '';

		// Insert values
		if ( file_exists(WB_PATH.'/languages/'.$addon['directory'].'.php'))
		{
			require(WB_PATH.'/languages/'.$addon['directory'].'.php');

			$data_dwoo['languages'][$counter] = array(
				'CODE'			=> $language_code,
				'NAME'			=> $language_name,
				'VALUE'			=> $addon['directory'],
				'AUTHOR'		=> $language_author,
				'VERSION'		=> $language_version,
				'DESIGNED_FOR'	=> $language_platform,
				'ADMIN_URL'		=> ADMIN_URL,
				'WB_URL'		=> WB_URL,
				'WB_PATH'		=> WB_PATH,
				'THEME_URL'		=> THEME_URL,
				'LICENSE'		=> $language_license,
				'ERROR'			=> false
			);
		}
		else
		{
			$data_dwoo['languages'][$counter]['ERROR'] = true;
		}
		$counter++;
	}
	// Restore language to original code
	require(WB_PATH.'/languages/'.LANGUAGE.'.php');
}
$data_dwoo['LANGUAGE']	= LANGUAGE;

// Insert permissions values
$data_dwoo['permissions']['LANGUAGES_VIEW']			= $admin->get_permission('languages_view')		? true : false;
$data_dwoo['permissions']['LANGUAGES_INSTALL']		= $admin->get_permission('languages_install')	? true : false;
$data_dwoo['permissions']['LANGUAGES_UNINSTALL']	= $admin->get_permission('languages_uninstall')	? true : false;

// print page
$parser->output('backend_languages_index.lte',$data_dwoo);

// Print admin footer
$admin->print_footer();

?>