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

require_once(CAT_PATH.'/framework/class.admin.php');
require_once(CAT_PATH.'/framework/functions.php');

$admin		= new admin('admintools', 'admintools');
$get_tool	= $admin->add_slashes( $admin->get_get('tool') );

if ( $get_tool == '' )
{
	header("Location: index.php");
	exit(0);
}

global $parser;
$parser->setGlobals('CAT_ADMIN_URL',CAT_ADMIN_URL);

// ============================== 
// ! Check if tool is installed   
// ============================== 
$result = $database->query("SELECT * FROM ".CAT_TABLE_PREFIX."addons WHERE type = 'module' AND function = 'tool' AND directory = '".$get_tool."'");
if ( $result->numRows() == 0 )
{
	header("Location: index.php");
	exit(0);
}
$tool	= $result->fetchRow();

// Set toolname
$data_dwoo['TOOL_NAME']		= $tool['name'];
$parser->setGlobals('TOOL_URL',CAT_ADMIN_URL.'/admintools/tool.php?tool='.$tool['directory']);

// Check if folder of tool exists
if ( file_exists(CAT_PATH.'/modules/'.$tool['directory'].'/tool.php') )
{
	if (
		  file_exists( CAT_PATH.'/modules/'.$tool['directory'].'/languages/'.$admin->lang->getLang().'.php' )
	) {
		$admin->lang->addFile( $admin->lang->getLang().'.php', CAT_PATH.'/modules/'.$tool['directory'].'/languages' );
	}
	// Cache the tool and add it to dwoo
	ob_start();
	require(CAT_PATH.'/modules/'.$tool['directory'].'/tool.php');
	$data_dwoo['TOOL']	= ob_get_contents();
	//ob_end_clean();
    ob_clean(); // allow multiple buffering for csrf-magic
}
else
{
	$admin->print_error($MESSAGE['GENERIC_ERROR_OPENING_FILE'] );
}

// print page
$parser->output( 'backend_admintools_tool.lte', $data_dwoo );

// Print admin footer
$admin->print_footer();

?>