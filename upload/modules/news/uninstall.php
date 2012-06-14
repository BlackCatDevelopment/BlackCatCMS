<?php

/**
 *  @module         news
 *  @version        see info.php of this module
 *  @author         Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos)
 *  @copyright      2004-2011, Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos) 
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
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



$database->query("DELETE FROM ".TABLE_PREFIX."search WHERE name = 'module' AND value = 'news'");
$database->query("DELETE FROM ".TABLE_PREFIX."search WHERE extra = 'news'");
$database->query("DROP TABLE ".TABLE_PREFIX."mod_news_posts");
$database->query("DROP TABLE ".TABLE_PREFIX."mod_news_groups");
$database->query("DROP TABLE ".TABLE_PREFIX."mod_news_comments");
$database->query("DROP TABLE ".TABLE_PREFIX."mod_news_settings");

require_once(WB_PATH.'/framework/functions.php');
rm_full_dir(WB_PATH.PAGES_DIRECTORY.'/posts');
rm_full_dir(WB_PATH.MEDIA_DIRECTORY.'/.news');

?>