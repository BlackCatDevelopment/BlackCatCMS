<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          LEPTON Project, Black Cat Development
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         wysiwyg_admin
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

  

$table = CAT_TABLE_PREFIX ."mod_wysiwyg_admin_v2";

$jobs = array();
$jobs[] = "DROP TABLE IF EXISTS `".CAT_TABLE_PREFIX."mod_editor_admin`";
$jobs[] = "DROP TABLE IF EXISTS `".$table."`";

$jobs[] = "CREATE TABLE `".$table."` (
	`id`		int(11) NOT NULL AUTO_INCREMENT,
	`skin`		varchar(255) NOT NULL DEFAULT 'cirkuit',
	`menu`		varchar(255) NOT NULL DEFAULT 'Smart',
	`width`		varchar(64) NOT NULL DEFAULT '100%',
	`height`	varchar(64) NOT NULL DEFAULT '250px',
	`editor`	varchar(255) NOT NULL DEFAULT 'tiny_mce_jq',
	PRIMARY KEY (`id`)
)";

$jobs[] = "INSERT INTO `".$table."` (`skin`, `menu`, `width`, `height`, `editor`) VALUES( 'none', 'none', '100%', '250px', 'none');";
$jobs[] = "INSERT INTO `".$table."` (`skin`, `menu`, `width`, `height`, `editor`) VALUES( 'kama', 'Smart', '100%', '250px', 'ckeditor');";
$jobs[] = "INSERT INTO `".$table."` (`skin`, `menu`, `width`, `height`, `editor`) VALUES( 'cirkuit', 'Smart', '100%', '250px', 'tiny_mce_jq');";
$jobs[] = "INSERT INTO `".$table."` (`skin`, `menu`, `width`, `height`, `editor`) VALUES( 'default', 'default', '100%', '250px', 'edit_area');";

/**
 *	Additonal queries to avoid db-conflicts if the install.php is reloaded by the backend-adminstration.
 *
 */
$jobs[] = "DELETE from `".CAT_TABLE_PREFIX."sections` where `page_id`='-120' and `section_id`='-1'";
$jobs[] = "DELETE from `".CAT_TABLE_PREFIX."mod_wysiwyg` where `page_id`='-120' and `section_id`='-1'";

$jobs[] = "INSERT INTO `".CAT_TABLE_PREFIX."sections` (`page_id`,`section_id`,`position`,`module`) VALUES('-120','-1','1','wysiwyg')";
$jobs[] = "INSERT INTO `".CAT_TABLE_PREFIX."mod_wysiwyg` (`page_id`,`section_id`,`content`,`text`) VALUES('-120','-1','<p><b>Berthold\'s</b> quick brown fox jumps over the lazy dog and feels as if he were in the seventh heaven of typography.</p>','Berthold\'s quick brown fox jumps over the lazy dog and feels as if he were in the seventh heaven of typography.')";

$errors = array();

foreach($jobs as $query) {
	$database->query( $query );
	if ( $database->is_error() ) $errors[] = $database->get_error();
}

/** 
 *	Any errors to display?
 *
 */
if (count($errors) > 0) $admin->print_error( implode("<br />\n", $errors), 'javascript: history.go(-1);');

?>