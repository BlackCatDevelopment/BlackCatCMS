<?php

/**
 *	@module			wysiwyg Admin
 *	@version		see info.php of this module
 *	@authors		Dietrich Roland Pehlke
 *	@copyright		2010-2011 Dietrich Roland Pehlke
 *	@license		GNU General Public License
 *	@license terms	see info.php of this module
 *	@platform		see info.php of this module
 *	@requirements	PHP 5.2.x and higher
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

  

$table = TABLE_PREFIX ."mod_wysiwyg_admin";

$jobs = array();
$jobs[] = "DROP TABLE IF EXISTS `".TABLE_PREFIX."mod_editor_admin`";
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
$jobs[] = "DELETE from `".TABLE_PREFIX."sections` where `page_id`='-120' and `section_id`='-1'";
$jobs[] = "DELETE from `".TABLE_PREFIX."mod_wysiwyg` where `page_id`='-120' and `section_id`='-1'";

$jobs[] = "INSERT INTO `".TABLE_PREFIX."sections` (`page_id`,`section_id`,`position`,`module`) VALUES('-120','-1','1','wysiwyg')";
$jobs[] = "INSERT INTO `".TABLE_PREFIX."mod_wysiwyg` (`page_id`,`section_id`,`content`,`text`) VALUES('-120','-1','<p><b>Berthold\'s</b> quick brown fox jumps over the lazy dog and feels as if he were in the seventh heaven of typography.</p>','Berthold\'s quick brown fox jumps over the lazy dog and feels as if he were in the seventh heaven of typography.')";

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