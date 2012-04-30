<?php
/**
 * Admin tool: Addon File Editor
 *
 * This tool allows you to "edit", "delete", "create", "upload" or "backup" files of installed 
 * Add-ons such as modules, templates and languages via the Website Baker backend. This enables
 * you to perform small modifications on installed Add-ons without downloading the files first.
 *
 * This file creates the module settings table when the module is installed.
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

// drop existing module tables
$table = TABLE_PREFIX . 'mod_addon_file_editor';
$database->query("DROP TABLE IF EXISTS `$table`");

// create new module table
$sql = "CREATE TABLE `$table` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ftp_enabled` INT(1) NOT NULL,
  `ftp_server` VARCHAR(255) collate latin1_general_ci NOT NULL,
  `ftp_user` VARCHAR(255) collate latin1_general_ci NOT NULL,
  `ftp_password` VARCHAR(255) collate latin1_general_ci NOT NULL,
  `ftp_port` INT NOT NULL,
  `ftp_start_dir` VARCHAR(255) collate latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
)";

$database->query($sql);

// add table with default values
$sql = "INSERT INTO `$table`
	(`ftp_enabled`, `ftp_server`, `ftp_user`, `ftp_password`, `ftp_port`, `ftp_start_dir`)
	VALUES ('0', '-', '-', '', '21', '/')
";	

$database->query($sql);

?>