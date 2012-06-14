<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 * @author		Christophe Dolivet (EditArea)
 * @author		Christian Sommer (WB wrapper)
 * @author		LEPTON Project
 * @copyright	2009-2010, Website Baker Project 
 * @copyright       2010-2011, LEPTON Project
 * @link			http://www.LEPTON-cms.org
 * @license		http://www.gnu.org/licenses/gpl.html
 * @license_terms	please see info.php of this module
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


 
function show_wysiwyg_editor($name, $id, $content, $width = '100%', $height = '350px') { 
	global $section_id, $page_id, $database, $preview;
	
	$syntax = 'php';
	$syntax_selection = true;
	$allow_resize = 'both';
	$allow_toggle = true;
	$start_highlight = true;
	$min_width = 600;
	$min_height = 300;
	$toolbar = 'default';

	// set default toolbar if no user defined was specified
	if ($toolbar == 'default') {
		$toolbar = 'search, fullscreen, |, undo, redo, |, select_font, syntax_selection, |, highlight, reset_highlight, |, help';
		$toolbar = (!$syntax_selection) ? str_replace('syntax_selection,', '', $toolbar) : $toolbar;
	}

	// check if used Website Baker backend language is supported by EditArea
	$language = 'en';
	if (defined('LANGUAGE') && file_exists(dirname(__FILE__) . '/langs/' . strtolower(LANGUAGE) . '.js')) {
		$language = strtolower(LANGUAGE);
	}

	// check if highlight syntax is supported by edit_area
	$syntax = in_array($syntax, array('css', 'html', 'js', 'php', 'xml','csv')) ? $syntax : 'php';

	// check if resize option is supported by edit_area
	$allow_resize = in_array($allow_resize, array('no', 'both', 'x', 'y')) ? $allow_resize : 'no';
	
	if (!isset($_SESSION['edit_area'])) {
		$script = WB_URL.'/modules/edit_area/edit_area/edit_area_full.js';
		$register = "\n<script src=\"".$script."\" type=\"text/javascript\"></script>\n";

		if (!isset($preview)) {
			$last = $database->get_one("SELECT section_id from ".TABLE_PREFIX."sections where page_id='".$page_id."' order by position desc limit 1"); 
			$_SESSION['edit_area'] = $last;
		}

	} else {
		$register = "";
		if ($section_id == $_SESSION['edit_area']) unset($_SESSION['edit_area']);
	}
	
	// the Javascript code
	$register .= "
	<script type=\"text/javascript\">
		editAreaLoader.init({
			id: '".$id."',
			start_highlight: ".$start_highlight.",
			syntax: '".$syntax."',
			min_width: ".$min_width.",
			min_height: ".$min_height.",
			allow_resize: '".$allow_resize."',
			allow_toggle: ".$allow_toggle.",
			toolbar: '".$toolbar."',
			language: '".$language."'
		});
	</script>
	";
	
	$editor = sprintf("%s\n".'<textarea cols="80" rows="20"  id="%s" name="%s" style="width: %s; height: %s;">%s</textarea>', $register, $id, $name, $width, $height, $content);
	echo $editor;
} // show_wysiwyg_editor()

?>