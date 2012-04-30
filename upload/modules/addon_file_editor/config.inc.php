<?php
/**
 * Admin tool: Addon File Editor
 *
 * This tool allows you to "edit", "delete", "create", "upload" or "backup" files of installed 
 * Add-ons such as modules, templates and languages via the Website Baker backend. This enables
 * you to perform small modifications on installed Add-ons without downloading the files first.
 *
 * This file contains the global configuration options of WB File Editor.
 * 
 * LICENSE: GNU General Public License 3.0
 * 
 * @author		Christian Sommer (doc)
 * @copyright	(c) 2008-2010
 * @license		http://www.gnu.org/licenses/gpl.html
 * @version		1.0.0
 * @platform	Website Baker 2.8
*/

// prevent this file from being accessed directly
if (!defined('WB_PATH')) die(header('Location: ../../index.php'));

/**
 * ADJUST THE FOLLOWING SETTINGS ACCORDING YOUR NEEDS
*/
// add extension of text files you want to be editable (will be displayed with a text icon)
$text_extensions = array('txt', 'htm', 'html', 'htt', 'tmpl', 'tpl', 'xml', 'css', 'js', 'php', 'php3', 'php4', 'php5', 'jquery', 'preset');

// add extension for image files (will be displayed with a image icon)
$image_extensions = array('bmp', 'gif', 'jpg', 'jpeg', 'png');

// add extension for zip archives (will be displayed with a zip icon)
$archive_extensions = array('zip', 'rar', 'tar', 'gz');

// module/template folders (e.g. 'addon_file_editor') or languages (e.g. 'en') you want not to show (all loser case)
$hidden_addons = array();	

// true:=show all files (false:= only show files registered in text, image or archive array)
$show_all_files = false;

// maximum allowed file upload size in MB
$max_upload_size = 2;

// activate experimental support for the online Flash image editor service http://pixlr.com/
$pixlr_support = false;

#########################################################################################################
# NOTE: DO NOT CHANGE ANYTHING BELOW THIS LINE UNLESS YOU NOW WHAT YOU ARE DOING
#########################################################################################################
// extract path seperator and detect this module name
$path_sep = strtoupper(substr(PHP_OS, 0, 3) == 'WIN') ? '\\' : '/';
$module_folder = str_replace(WB_PATH . $path_sep . 'modules' . $path_sep, '', dirname(__FILE__));

/**
 * PATH AND URL VARIABLES USED BY THE MODULE
*/
$table = TABLE_PREFIX . 'addons';
$url_icon_folder = WB_URL . '/modules/' . $module_folder . '/icons';
$url_admintools = ADMIN_URL . '/admintools/tool.php?tool=' . $module_folder;
$url_action_handler = WB_URL . '/modules/' . $module_folder . '/action_handler.php';
$url_ftp_assistant = WB_URL . '/modules/' . $module_folder . '/ftp_assistant.php';

$temp_zip_path = WB_PATH . $path_sep . 'temp' . $path_sep . $module_folder . $path_sep;
$url_mod_path = WB_URL . '/modules/' . $module_folder;

// version check
if (!isset($no_check) && !file_exists(ADMIN_PATH . '/admintools/tool.php')) {
	// load module language file
	$lang = (dirname(__FILE__)) . '/languages/' . LANGUAGE . '.php';
	require_once(!file_exists($lang) ? (dirname(__FILE__)) . '/languages/EN.php' : $lang );
	$admin->print_error('<br /><strong style="color: red;">' . $LANG[0]['TXT_VERSION_ERROR'] . '</strong>');
}

?>