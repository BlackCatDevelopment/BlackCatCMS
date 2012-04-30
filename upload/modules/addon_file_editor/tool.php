<?php
/**
 * Admin tool: Addon File Editor
 *
 * This tool allows you to "edit", "delete", "create", "upload" or "backup" files of installed 
 * Add-ons such as modules, templates and languages via the Website Baker backend. This enables
 * you to perform small modifications on installed Add-ons without downloading the files first.
 *
 * This file is contains the functions of the backend
 * 
 * LICENSE: GNU General Public License 3.0
 * 
 * @author		Christian Sommer (doc)
 * @copyright	(c) 2008-2010
 * @license		http://www.gnu.org/licenses/gpl.html
 * @version		1.1.2
 * @platform	Website Baker 2.8
*/

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die(header('Location: ../../index.php'));

// include module configuration and function file
require_once('config.inc.php');
require_once('functions.inc.php');

// load module language file
$lang = (dirname(__FILE__)) . '/languages/' . LANGUAGE . '.php';
require_once(!file_exists($lang) ? (dirname(__FILE__)) . '/languages/EN.php' : $lang );

// work out link to language specific help file
$help_file = 'help_' . (file_exists(dirname(__FILE__) . '/help/help_' . strtolower(LANGUAGE) . '.html') ? strtolower(LANGUAGE) : 'en') . '.html';

/**
 * Show outputs depending on selected display mode
*/
// include template class and set template directory
require_once(WB_PATH . '/include/phplib/template.inc');
$tpl = new Template(dirname(__FILE__) . '/htt');

// get valid addon- and file id from $_GET parameter
cleanGetParameters($aid, $fid);

if ($aid == '') {
	#################################################################################
	# CREATE OVERVIEW OF ADDONS WHICH ARE READABLE (MODULES, TEMPLATES, LANGUAGES)
	#################################################################################
	// fetch addon infos to $_SESSION['addon_list'] (installed; PHP readable; not in $hidden_addons)
	getAddons(isset($_GET['reload']));

	// set template file and assign module and template block
	$tpl->set_file('page', 'addons_overview.htt');
	$tpl->set_block('page', 'module_block', 'module_replace');
	$tpl->set_block('page', 'template_block', 'template_replace');
	$tpl->set_block('page', 'language_block', 'language_replace');

	// remove the comment block
	$tpl->set_block('page', 'comment_block', 'comment_replace');
	$tpl->set_block('comment_replace', '');

	// replace template placeholder with data from language file
	$LANG[1]['TXT_FTP_NOTICE'] = str_replace('{URL_ASSISTANT}', $url_ftp_assistant, $LANG[1]['TXT_FTP_NOTICE']);
	$add_vars = array(
		'URL_HELP_FILE'		=> $url_mod_path . '/help/' . $help_file,
		'URL_ADMIN_TOOL'	=> $url_admintools,
		'CLASS_SHOW_FTP_INFO' => 'hidden'
	);

	$tpl_vars = array_merge($LANG[1], $add_vars);
	foreach($tpl_vars as $key => $value) {
		$tpl->set_var($key, $value);
	}

	/**
	 * Create an overview list of add-ons readable by PHP and not listed in $hidden_addons
	*/
	$show_ftp_info = false;
	foreach ($_SESSION['addon_list'] as $addon_id => $addon) {
		$addon_type = strtoupper($addon['type']);

		// create addon specific variable ($module_counter, $template_counter, $language_counter)
		$addon_var = "{$addon['type']}_counter";
		$$addon_var = (isset($$addon_var)) ? $$addon_var : 0;
		
		// only show ftp info box if at least one addon folder is not writeable by PHP
		if (!is_writeable($addon['file'])) $show_ftp_info = true;
		
		// replace addon specific placeholder
		$tpl->set_var(array(
			'ADDON_NAME'			=> $addon['name'],
			'CLASS_ODD_EVEN'		=> ($$addon_var  % 2) ? 'odd ' : '',
			'CLASS_PERMISSION'		=> is_writeable($addon['file']) ? '' : 'permission',
			'URL_EDIT_ADDON'		=> $url_admintools . '&amp;aid=' . $addon_id,
			'TXT_EDIT_ADDON_FILE'	=> $LANG[1]['TXT_EDIT_' . "{$addon_type}" . '_FILES'] . 
										((is_writeable($addon['file'])) ? '' : $LANG[1]['TXT_FTP_SUPPORT']),
			'URL_ZIP_ADDON'			=> $url_mod_path . '/download.php?aid=' . $addon_id,
			'TXT_ZIP_ADDON_FILES'	=> $LANG[1]['TXT_ZIP_' . "{$addon_type}" . '_FILES'],
			'URL_ICON_FOLDER'		=> $url_icon_folder
			)
		);
		
		// parse addon specific template block (module_block, template_block or language_block)
		$tpl->parse("{$addon['type']}_replace", "{$addon['type']}_block", true);
		$$addon_var++;
	}
	
	if ($show_ftp_info) $tpl->set_var('CLASS_SHOW_FTP_INFO', '');
				
	// ouput the final template
	$tpl->pparse('output', 'page');

} elseif (is_numeric($aid) && isset($_SESSION['addon_list'][$aid])) {
	#################################################################################
	# SHOW FILEMANAGER WITH FILES AND FOLDERS OF THE SPECIFIED ADDON
	#################################################################################
	// set template file and assign file block
	$tpl->set_file('page',	'addons_filemanager.htt');
	$tpl->set_block('page', 'file_block', 'file_replace');

	// remove the comment block
	$tpl->set_block('page', 'comment_block', 'comment_replace');
	$tpl->set_block('comment_replace', '');

	// replace template placeholders with values from language file
	$LANG[1]['TXT_FTP_NOTICE'] = str_replace('{URL_ASSISTANT}', $url_ftp_assistant, $LANG[1]['TXT_FTP_NOTICE']);
	foreach($LANG[2] as $key => $value) {
		$tpl->set_var($key, $value);
	}

	// extract addon main path (e.g. /modules/addon_file_editor)
	$addon_main_path = str_replace(WB_PATH, '', $_SESSION['addon_list'][$aid]['path']);

	// replace additional template placeholder
	$tpl->set_var(array(
		'ADDON_NAME'				=> $_SESSION['addon_list'][$aid]['name'],
		'ADDON_PATH'				=> $addon_main_path,
		'TXT_HELP'					=> $LANG[1]['TXT_HELP'],
		'URL_HELP_FILE'				=> $url_mod_path . '/help/' . $help_file,
		'TXT_ADDON_TYPE'			=> $LANG[2]['TXT_' . strtoupper($_SESSION['addon_list'][$aid]['type'])],
		'URL_ICON_FOLDER'			=> $url_icon_folder,
		'TXT_CANCEL'				=> $TEXT['CANCEL'],
		'CLASS_HIDDEN'				=> ($_SESSION['addon_list'][$aid]['type'] == 'language' 
										&& !isset($_GET['list_all'])) ? 'hidden' : '',
		'TXT_FTP_NOTICE'			=> $LANG[1]['TXT_FTP_NOTICE'],
		'CLASS_SHOW_FTP_INFO'		=> 'hidden',
		'URL_EDIT_ADDON'			=> $url_admintools,
		'URL_RELOAD'				=> $url_admintools . '&amp;aid=' . $aid . '&amp;reload',
		'URL_CREATE_FILE_FOLDER'	=> $url_action_handler . '?aid=' . $aid . '&amp;action=4',
		'URL_UPLOAD_FILE'			=> $url_action_handler . '?aid=' . $aid . '&amp;action=5',
		)
	);
	
	// fetch file infos of actual add-on to $_SESSION['addon_file_infos']
	getAddonFileInfos($_SESSION['addon_list'][$aid]['path'], $aid, isset($_GET['reload']));
	
	// output current addon file infos
	$show_ftp_info = false;
	foreach ($_SESSION['addon_file_infos'] as $index => $file_info) {
		// skip the very first entry which contains current addon root folder
		if ($index == 0) continue;
		
		// if we process the WB language folder, only show the required language file
		if ($_SESSION['addon_list'][$aid]['type'] == 'language' && !isset($_GET['list_all'])) {
			if ($file_info['path'] != $_SESSION['addon_list'][$aid]['file']) continue;
		}

		// work out displayed file or folder name part
		if ($file_info['type'] == 'folder') {
			// extract sub path to current folder (e.g. /modules/anynews/htt/icons/ --> /htt/icons)
			$file_name = str_replace($_SESSION['addon_list'][$aid]['path'], '', $file_info['path']);
		} else {
			// extract file name (e.g. test.php from full path)
			$file_name = basename($file_info['path']);
		}

		// only show ftp info box if at least one addon folder is not writeable by PHP
		if (!is_writeable($file_info['path'])) $show_ftp_info = true;

		// create a link for all textfiles and images
		$icon_edit_url = '-';
		switch ($file_info['icon']) {
			case 'textfile':
				$url = $url_action_handler . '?aid=' . $aid . '&amp;fid=' . $index . '&amp;action=1';
				// make file name clickable (edit text file)
				$file_name = '<a href="' . $url . '" title="' . $LANG[2]['TXT_EDIT'] . '">' . $file_name . '</a>';
				$icon_edit_url = $url_action_handler . '?aid=' . $aid . '&amp;fid=' . $index . '&amp;action=1';
				break;
				
			case 'image':
				// build URL to the image file
				$url = str_replace(WB_PATH, '', $file_info['path']);
				$url = WB_URL . str_replace($path_sep, '/', $url);

				// create link to open image in new browser window
				$file_name = '<a href="' . $url . '" target="_blank" title="' . $LANG[2]['TXT_VIEW'] . '">' . $file_name . '</a>';

				// check if PIXLR Support is enabled
				if ($pixlr_support == true && (strpos(WB_URL, '/localhost/') == false)) {
					// open image with the online Flash image editor http://pixlr.com/
					$icon_edit_url = createPixlrURL($url, $file_info['path'], true);
				}
				break;
		}

		// replace placeholders with dynamic file information
		$tpl->set_var(array(
			'FILE_NAME'			=> $file_name,
			'FILE_SIZE'			=> ($file_info['size'] == '') ? '&nbsp;' : $file_info['size'],
			'FILE_MAKE_TIME'	=> $file_info['maketime'],
			'CLASS_ODD_EVEN'	=> ($index % 2 == 0) ? 'odd ' : '',
			'CLASS_FOLDER'		=> ($file_info['type'] == 'folder') ? 'folder ' : '',
			'CLASS_PERMISSION'	=> is_writeable($file_info['path']) ? '' : 'permission',
			'URL_ICON_FOLDER'	=> $url_icon_folder,
			'FILE_ICON'			=> $file_info['icon'],
			'TXT_FILE_TYPE'		=> $LANG[2]['TXT_FILE_TYPE_' . strtoupper($file_info['icon'])],
			'HIDE_EDIT_ICON'	=> ($icon_edit_url == '-') ? 'hidden' : '',
			'TXT_EDIT'			=> ($icon_edit_url <> '-' && $file_info['icon'] == 'image') 
									? ($LANG[2]['TXT_EDIT'] . ' (Online: PIXLR)') : $LANG[2]['TXT_EDIT'],
			'TARGET_BLANK'		=> ($icon_edit_url <> '-' && $file_info['icon'] == 'image') ? ' target="_blank"' : '',
			'URL_EDIT_FILE'		=> $icon_edit_url,
			'URL_RENAME_FILE'	=> $url_action_handler . '?aid=' . $aid . '&amp;fid=' . $index . '&amp;action=2',
			'URL_DELETE_FILE'	=> $url_action_handler . '?aid=' . $aid . '&amp;fid=' . $index . '&amp;action=3'
			)
		);

		// add file block to the template (append mode)
		$tpl->parse("file_replace", "file_block", true);
		$index++;
	}

	if ($show_ftp_info) $tpl->set_var('CLASS_SHOW_FTP_INFO', '');

	// ouput the final template
	$tpl->pparse('output', 'page');

} else {
	#################################################################################
	# FILEMANAGER NOT PROPERLY INITIALIZED - REDIRECT TO ADDON OVERVIEW PAGE
	#################################################################################
	$admin->print_error($LANG[3]['ERR_WRONG_PARAMETER'], $url_admintools);
}

?>