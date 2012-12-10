<?php

/**
 * This file is part of LEPTON2 Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author			LEPTON2 Project
 * @copyright		2012, LEPTON2 Project
 * @link			http://lepton2.org
 * @license			http://www.gnu.org/licenses/gpl.html
 * @license_terms	please see LICENSE and COPYING files in your package
 *
 *
 */
 
// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {
	include(LEPTON_PATH.'/framework/class.secure.php');
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

require_once(LEPTON_PATH.'/framework/class.admin.php');
$admin = new admin('admintools', 'admintools');

// Include the WB functions file
require_once(LEPTON_PATH.'/framework/functions.php');

// =========================================================================== 
// ! Create the controller, it is reusable and can render multiple templates 	
// =========================================================================== 
global $parser;

// Insert tools into tool list
$results = $database->query("SELECT `directory`,`name`,`description` FROM ".TABLE_PREFIX."addons WHERE type = 'module' AND function = 'tool' AND `directory` not in ('".(implode("','",$_SESSION['MODULE_PERMISSIONS']))."') order by name");
if ( $results->numRows() > 0 )
{
	$data_dwoo['TOOL_LIST']		= true;
	$counter	= 0;
	while ( false != ($tool = $results->fetchRow( MYSQL_ASSOC ) ) )
	{
		$data_dwoo['tools'][$counter]	= array(
			'TOOL_NAME'		=> $tool['name'],
			'TOOL_DIR'		=> $tool['directory']
		);
		// check if a module description exists for the displayed backend language
		$module_description		= false;
		$language_file			= LEPTON_PATH.'/modules/'.$tool['directory'].'/languages/' . $admin->lang->getLang() . '.php';
		if ( true === file_exists($language_file) )
		{
			require( $language_file );
		}
		$data_dwoo['tools'][$counter]['TOOL_DESCRIPTION']	= ( $module_description === false ) ? 
			$tool['description'] :
			$module_description;

		// ===================================================== 
		// ! Check whether icon is available for the admintool   
		// ===================================================== 
		if ( file_exists(LEPTON_PATH.'/modules/'.$tool['directory'].'/icon.png') )
		{
			list($width, $height, $type, $attr) = getimagesize(LEPTON_PATH.'/modules/'.$tool['directory'].'/icon.png');

			// Check whether file is 32*32 pixel and is an PNG-Image
			$data_dwoo['tools'][$counter]['ICON']	= ($width == 32 && $height == 32 && $type == 3) ?
				WB_URL.'/modules/'.$tool['directory'].'/icon.png' :
				false;
		}
		$counter++;
	}
}
else
{
	$data_dwoo['TOOL_LIST']		= false;
}


// print page
$parser->output('backend_admintools_index.lte',$data_dwoo);

// Print admin footer
$admin->print_footer();

?>