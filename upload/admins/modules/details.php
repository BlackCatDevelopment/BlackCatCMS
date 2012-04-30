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



require_once(WB_PATH .'/framework/functions.php');
require_once(WB_PATH.'/framework/class.admin.php');
// No print admin header
$admin = new admin('Addons', 'modules_view', false);

// Get module name
if(!isset($_POST['file']) OR $_POST['file'] == "")
{
	header("Location: index.php");
	exit(0);
}
else
{
	$file = preg_replace("/\W/", "", $admin->add_slashes($_POST['file']));  // fix secunia 2010-92-1
}

// Check if the module exists
if(!file_exists(WB_PATH.'/modules/'.$file)) {
	header("Location: index.php");
	exit(0);
}

// Print admin header
$admin = new admin('Addons', 'modules_view');

// Setup module object
$template = new Template(THEME_PATH.'/templates');
$template->set_file('page', 'modules_details.htt');
$template->set_block('page', 'main_block', 'main');

// Insert values
$result = $database->query("SELECT * FROM ".TABLE_PREFIX."addons WHERE type = 'module' AND directory = '$file'");
if($result->numRows() > 0) {
	$module = $result->fetchRow();
}

// check if a module description exists for the displayed backend language
$tool_description = false;
if(function_exists('file_get_contents') && file_exists(WB_PATH.'/modules/'.$file.'/languages/'.LANGUAGE .'.php')) {
	// read contents of the module language file into string
	$data = @file_get_contents(WB_PATH .'/modules/' .$file .'/languages/' .LANGUAGE .'.php');
	// use regular expressions to fetch the content of the variable from the string
	$tool_description = get_variable_content('module_description', $data, false, false);
	// replace optional placeholder {WB_URL} with value stored in config.php
	if($tool_description !== false && strlen(trim($tool_description)) != 0) {
		$tool_description = str_replace('{WB_URL}', WB_URL, $tool_description);
	} else {
		$tool_description = false;
	}
}		
if($tool_description !== false) {
	// Override the module-description with correct desription in users language
	$module['description'] = $tool_description;
}
if(file_exists(WB_PATH.'/modules/'.$module['directory'].'/icon.png')){
	list($width, $height, $type, $attr) = getimagesize(WB_PATH.'/modules/'.$module['directory'].'/icon.png');
	// Check whether file is 32*32 pixel and is an PNG-Image
	$template->set_var(
		'ICON',
		( $width == 32 && $height == 32 && $type == 3 )
		? '<img src="'.WB_URL.'/modules/'.$module['directory'].'/icon.png" alt="Icon" title="'.$module['description'].'" />'
		: NULL
	);
}

$template->set_var(array(
	'NAME' => $module['name'],
	'AUTHOR' => $module['author'],
	'DESCRIPTION' => $module['description'],
	'VERSION' => $module['version'],
	'DESIGNED_FOR' => $module['platform'],
	'ADMIN_URL' => ADMIN_URL,
	'WB_URL' => WB_URL,
	'WB_PATH' => WB_PATH,
	'THEME_URL' => THEME_URL,
	'LICENSE'	=> $module['license']
	)
);
						
switch ($module['function']) {
	case NULL:
		$type_name = $TEXT['UNKNOWN'];
		break;
	case 'page':
		$type_name = $TEXT['PAGE'];
		break;
	case 'wysiwyg':
		$type_name = $TEXT['WYSIWYG_EDITOR'];
		break;
	case 'tool':
		$type_name = $TEXT['ADMINISTRATION_TOOL'];
		break;
	case 'admin':
		$type_name = $TEXT['ADMIN'];
		break;
	case 'administration':
		$type_name = $TEXT['ADMINISTRATION'];
		break;
	case 'snippet':
		$type_name = $TEXT['CODE_SNIPPET'];
		break;
	case 'library':
		$type_name = $TEXT['LIBRARY'];	
		break;
	default:
		$type_name = $TEXT['UNKNOWN'];
}
$template->set_var('TYPE', $type_name);

// Insert language headings
$template->set_var(array(
	'HEADING_MODULE_DETAILS' => $HEADING['MODULE_DETAILS']
	)
);
// Insert language text and messages
$template->set_var(array(
	'TEXT_NAME' => $TEXT['NAME'],
	'TEXT_TYPE' => $TEXT['TYPE'],
	'TEXT_AUTHOR' => $TEXT['AUTHOR'],
	'TEXT_VERSION' => $TEXT['VERSION'],
	'TEXT_DESIGNED_FOR' => $TEXT['DESIGNED_FOR'],
	'TEXT_DESCRIPTION' => $TEXT['DESCRIPTION'],
	'TEXT_BACK' => $TEXT['BACK'],
	'TEXT_LICENSE'	=> $TEXT['LICENSE']
	)
);

// Parse module object
$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');

// Print admin footer
$admin->print_footer();

?>