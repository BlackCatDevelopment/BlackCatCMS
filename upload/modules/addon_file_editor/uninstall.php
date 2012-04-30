<?php
/**
 * Admin tool: Addon File Editor
 *
 * This tool allows you to "edit", "delete", "create", "upload" or "backup" files of installed 
 * Add-ons such as modules, templates and languages via the Website Baker backend. This enables
 * you to perform small modifications on installed Add-ons without downloading the files first.
 *
 * This file contains the wrapper routines when de-installing this module.
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

// remove Addon File Editor settings table from database
$table = TABLE_PREFIX . 'mod_addon_file_editor';
$database->query("DROP TABLE IF EXISTS `$table`");

// remove temporary download folder in /temp if exists (do not perform module version check in config.inc.php)
$no_check = true;
require_once('config.inc.php');
require_once('functions.inc.php');
removeFileOrFolder($temp_zip_path);

?>