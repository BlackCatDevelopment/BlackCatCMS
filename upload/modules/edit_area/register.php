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



if (!function_exists('registerEditArea'))
{
	function registerEditArea(
                $id = 'code_area',
                $syntax = 'php',
                $syntax_selection = true,
                $allow_resize = 'both',
                $allow_toggle = true,
                $start_highlight = true,
                $min_width = 600,
                $min_height = 300,
                $toolbar = 'default'
				)
	{

		// set default toolbar if no user defined was specified
		if ($toolbar == 'default') {
			$toolbar = 'search, fullscreen, |, undo, redo, |, select_font, syntax_selection, |, highlight, reset_highlight, |, help';
			$toolbar = (!$syntax_selection) ? str_replace('syntax_selection,', '', $toolbar) : $toolbar;
		}

		// check if used Website Baker backend language is supported by EditArea
		$language = 'en';
		if (defined('LANGUAGE') && file_exists(dirname(__FILE__) . '/langs/' . strtolower(LANGUAGE) . '.js'))
        {
			$language = strtolower(LANGUAGE);
		}

		// check if highlight syntax is supported by edit_area
		$syntax = in_array($syntax, array('css', 'html', 'js', 'php', 'xml','csv')) ? $syntax : 'php';

		// check if resize option is supported by edit_area
		$allow_resize = in_array($allow_resize, array('no', 'both', 'x', 'y')) ? $allow_resize : 'no';

		/**
		 *	Try to load the basic js only one time.
		 */
		$return_value = "";
		if (!defined('EDIT_AREA_LOADED')) {
			define('EDIT_AREA_LOADED', true);
			$script_url = WB_URL.'/modules/edit_area/edit_area/edit_area_full.js';
			$return_value .= "\n<script src='".$script_url."' type='text/javascript'></script>\n";
		}
		
		$return_value .= "
		<script type='text/javascript'>
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
		
		return $return_value;	
	}
}

if (!function_exists('getEditAreaSyntax')) {
	function getEditAreaSyntax($file) 
	{
		// returns the highlight scheme for edit_area
		$syntax = 'php';
		if (is_readable($file)) {
			// extract file extension
			$file_info = pathinfo($file);
		
			switch ($file_info['extension']) {
				case 'htm': case 'html': case 'htt':
					$syntax = 'html';
	  				break;

	 			case 'css':
					$syntax = 'css';
	  				break;

				case 'js':
					$syntax = 'js';
					break;

				case 'xml':
					$syntax = 'xml';
					break;

	 			case 'php': case 'php3': case 'php4': case 'php5':
					$syntax = 'php';
	  				break;

				default:
					$syntax = 'php';
					break;
			}
		}
		return $syntax ;
	}
}

?>