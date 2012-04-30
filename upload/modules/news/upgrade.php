<?php

/**
 *  @module         news
 *  @version        see info.php of this module
 *  @author         Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos)
 *  @copyright      2004-2012, Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos) 
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

if(defined('WB_URL'))
{

  // first copy content of original news_tables to xsik_tables	
  $database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."xsik_news_posts`");
  $database->query("RENAME TABLE `".TABLE_PREFIX."mod_news_posts` TO `".TABLE_PREFIX."xsik_news_posts`"); 

  $database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."xsik_news_groups`");  
  $database->query("RENAME TABLE `".TABLE_PREFIX."mod_news_groups` TO `".TABLE_PREFIX."xsik_news_groups`");    
  
  $database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."xsik_news_comments`");  
  $database->query("RENAME TABLE `".TABLE_PREFIX."mod_news_comments` TO `".TABLE_PREFIX."xsik_news_comments`");    
  
  $database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."xsik_news_settings`");
  $database->query("RENAME TABLE `".TABLE_PREFIX."mod_news_settings` TO `".TABLE_PREFIX."xsik_news_settings`");     
 
 
 // then delete and create original tables
  $database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."mod_news_posts`");
	$mod_news = 'CREATE TABLE IF NOT EXISTS `'.TABLE_PREFIX.'mod_news_posts` ( '
					 . '`post_id` INT NOT NULL AUTO_INCREMENT,'
					 . '`section_id` INT NOT NULL DEFAULT \'0\','
					 . '`page_id` INT NOT NULL DEFAULT \'0\','
					 . '`group_id` INT NOT NULL DEFAULT \'0\','
					 . '`active` INT NOT NULL DEFAULT \'0\','
					 . '`position` INT NOT NULL DEFAULT \'0\','
					 . '`title` VARCHAR(255) NOT NULL DEFAULT \'\','
					 . '`link` TEXT NOT NULL ,'
					 . '`content_short` TEXT NOT NULL ,'
					 . '`content_long` TEXT NOT NULL ,'
					 . '`commenting` VARCHAR(7) NOT NULL DEFAULT \'\','
					 . '`published_when` INT NOT NULL DEFAULT \'0\','
					 . '`published_until` INT NOT NULL DEFAULT \'0\','
					 . '`posted_when` INT NOT NULL DEFAULT \'0\','
					 . '`posted_by` INT NOT NULL DEFAULT \'0\','
					 . 'PRIMARY KEY (post_id)'
					 . ' )';
	$database->query($mod_news);

	
  $database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."mod_news_groups`");
	$mod_news = 'CREATE TABLE IF NOT EXISTS `'.TABLE_PREFIX.'mod_news_groups` ( '
					 . '`group_id` INT NOT NULL AUTO_INCREMENT,'
					 . '`section_id` INT NOT NULL DEFAULT \'0\','
					 . '`page_id` INT NOT NULL DEFAULT \'0\','
					 . '`active` INT NOT NULL DEFAULT \'0\','
					 . '`position` INT NOT NULL DEFAULT \'0\','
					 . '`title` VARCHAR(255) NOT NULL DEFAULT \'\','
					 . 'PRIMARY KEY (group_id)'
                . ' )';
	$database->query($mod_news);
	

  $database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."mod_news_comments`");  
	$mod_news = 'CREATE TABLE IF NOT EXISTS `'.TABLE_PREFIX.'mod_news_comments` ( '
					 . '`comment_id` INT NOT NULL AUTO_INCREMENT,'
					 . '`section_id` INT NOT NULL DEFAULT \'0\','
					 . '`page_id` INT NOT NULL DEFAULT \'0\','
					 . '`post_id` INT NOT NULL DEFAULT \'0\','
					 . '`title` VARCHAR(255) NOT NULL DEFAULT \'\','
					 . '`comment` TEXT NOT NULL ,'
		   	    . '`commented_when` INT NOT NULL DEFAULT \'0\','
					 . '`commented_by` INT NOT NULL DEFAULT \'0\','
					 . 'PRIMARY KEY (comment_id)'
                . ' )';

	$database->query($mod_news);
	

  $database->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."mod_news_settings`");  
	$mod_news = 'CREATE TABLE IF NOT EXISTS `'.TABLE_PREFIX.'mod_news_settings` ( '
					 . '`section_id` INT NOT NULL DEFAULT \'0\','
					 . '`page_id` INT NOT NULL DEFAULT \'0\','
					 . '`header` TEXT NOT NULL ,'
					 . '`post_loop` TEXT NOT NULL ,'
					 . '`footer` TEXT NOT NULL ,'
					 . '`posts_per_page` INT NOT NULL DEFAULT \'0\','
					 . '`post_header` TEXT NOT NULL,'
					 . '`post_footer` TEXT NOT NULL,'
					 . '`comments_header` TEXT NOT NULL,'
					 . '`comments_loop` TEXT NOT NULL,'
					 . '`comments_footer` TEXT NOT NULL,'
					 . '`comments_page` TEXT NOT NULL,'
					 . '`commenting` VARCHAR(7) NOT NULL DEFAULT \'\','
					 . '`resize` INT NOT NULL DEFAULT \'0\','
					 . ' `use_captcha` INT NOT NULL DEFAULT \'0\','
					 . 'PRIMARY KEY (section_id)'
                . ' )';

	$database->query($mod_news);

  // insert content from sik_tables to original tables
 $database->query("INSERT INTO `".TABLE_PREFIX."mod_news_posts` SELECT * FROM `".TABLE_PREFIX."xsik_news_posts`");   
 $database->query("INSERT INTO `".TABLE_PREFIX."mod_news_groups` SELECT * FROM `".TABLE_PREFIX."xsik_news_groups`");   
 $database->query("INSERT INTO `".TABLE_PREFIX."mod_news_comments` SELECT * FROM `".TABLE_PREFIX."xsik_news_comments`");   
 $database->query("INSERT INTO `".TABLE_PREFIX."mod_news_settings` SELECT * FROM `".TABLE_PREFIX."xsik_news_settings`");      

		
};

	// Make news post access files dir
	require_once(WB_PATH.'/framework/functions.php');
	make_dir(WB_PATH.MEDIA_DIRECTORY.'/newspics'); // create directory for images
  
		copy(
			WB_PATH.PAGES_DIRECTORY.'/posts/index.php',
			WB_PATH.MEDIA_DIRECTORY.'/newspics/index.php'
		);  

?>