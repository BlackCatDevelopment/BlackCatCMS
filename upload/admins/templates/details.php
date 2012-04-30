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
$admin = new admin('Addons', 'templates_view',false);

// Get template name
if(!isset($_POST['file']) OR $_POST['file'] == "") {
	$add = (isset($_GET['leptoken']) ? "?leptoken=".$_GET['leptoken'] : "" );
	die( header("Location: index.php".$add) );

} else {
	$file = preg_replace("/\W-_/i", "", $admin->add_slashes($_POST['file']));  // fix secunia 2010-92-1
}

// Check if the template exists
if(!file_exists(WB_PATH.'/templates/'.$file)) {
	$add = (isset($_GET['leptoken']) ? "?leptoken=".$_GET['leptoken'] : "" );
	die( header("Location: index.php".$add) );
}

// Print admin header
$admin = new admin('Addons', 'templates_view');

// Setup template object
$template = new Template(THEME_PATH.'/templates');
$template->set_file('page', 'templates_details.htt');
$template->set_block('page', 'main_block', 'main');

// Insert values
$result = $database->query("SELECT * FROM ".TABLE_PREFIX."addons WHERE type = 'template' AND directory = '$file'");
if($result->numRows() > 0) {
	$row = $result->fetchRow();
}

// check if a template description exists for the displayed backend language
$tool_description = false;
if(function_exists('file_get_contents') && file_exists(WB_PATH.'/templates/'.$file.'/languages/'.LANGUAGE .'.php')) {
	// read contents of the template language file into string
	$data = @file_get_contents(WB_PATH .'/templates/' .$file .'/languages/' .LANGUAGE .'.php');
	// use regular expressions to fetch the content of the variable from the string
	$tool_description = get_variable_content('template_description', $data, false, false);
	// replace optional placeholder {WB_URL} with value stored in config.php
	if($tool_description !== false && strlen(trim($tool_description)) != 0) {
		$tool_description = str_replace('{WB_URL}', WB_URL, $tool_description);
	} else {
		$tool_description = false;
	}
}
if($tool_description !== false) {
	// Override the template-description with correct desription in users language
	$row['description'] = $tool_description;
}	

$template->set_var(array(
	'NAME' => $row['name'],
	'AUTHOR' => $row['author'],
	'DESCRIPTION' => $row['description'],
	'VERSION' => $row['version'],
	'DESIGNED_FOR' => $row['platform'],
	'LICENSE'	=> $row['license']
	)
);

// Insert language headings
$template->set_var(array(
	'HEADING_TEMPLATE_DETAILS' => $HEADING['TEMPLATE_DETAILS']
	)
);
// Insert language text and messages
$template->set_var(array(
	'TEXT_NAME' => $TEXT['NAME'],
	'TEXT_AUTHOR' => $TEXT['AUTHOR'],
	'TEXT_VERSION' => $TEXT['VERSION'],
	'TEXT_DESIGNED_FOR' => $TEXT['DESIGNED_FOR'],
	'TEXT_DESCRIPTION' => $TEXT['DESCRIPTION'],
	'TEXT_BACK' => $TEXT['BACK'],
	'TEXT_LICENSE'	=> $TEXT['LICENSE']
	)
);

// Parse template object
$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');

// Print admin footer
$admin->print_footer();

?>