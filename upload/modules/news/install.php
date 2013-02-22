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
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://www.blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Module
 *   @package         news
 *
 */

// include class.secure.php to protect this file and the whole CMS!
if ( defined( 'CAT_PATH' ) )
{
    include( CAT_PATH . '/framework/class.secure.php' );
}
else
{
    $oneback = "../";
    $root    = $oneback;
    $level = 1;
    while ( ( $level < 10 ) && ( !file_exists( $root . '/framework/class.secure.php' ) ) )
    {
        $root .= $oneback;
        $level += 1;
    }
    if ( file_exists( $root . '/framework/class.secure.php' ) )
    {
        include( $root . '/framework/class.secure.php' );
    }
    else
    {
        trigger_error( sprintf( "[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER[ 'SCRIPT_NAME' ] ), E_USER_ERROR );
    }
}

if(defined('CAT_URL'))
{
	
	// $database->query("DROP TABLE IF EXISTS `".CAT_TABLE_PREFIX."mod_news_posts`");
	$mod_news = 'CREATE TABLE IF NOT EXISTS `'.CAT_TABLE_PREFIX.'mod_news_posts` ( '
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
	
	// $database->query("DROP TABLE IF EXISTS `".CAT_TABLE_PREFIX."mod_news_groups`");
	$mod_news = 'CREATE TABLE IF NOT EXISTS `'.CAT_TABLE_PREFIX.'mod_news_groups` ( '
					 . '`group_id` INT NOT NULL AUTO_INCREMENT,'
					 . '`section_id` INT NOT NULL DEFAULT \'0\','
					 . '`page_id` INT NOT NULL DEFAULT \'0\','
					 . '`active` INT NOT NULL DEFAULT \'0\','
					 . '`position` INT NOT NULL DEFAULT \'0\','
					 . '`title` VARCHAR(255) NOT NULL DEFAULT \'\','
					 . 'PRIMARY KEY (group_id)'
                . ' )';
	$database->query($mod_news);
	
	// $database->query("DROP TABLE IF EXISTS `".CAT_TABLE_PREFIX."mod_news_comments`");
	$mod_news = 'CREATE TABLE IF NOT EXISTS `'.CAT_TABLE_PREFIX.'mod_news_comments` ( '
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
	
	// $database->query("DROP TABLE IF EXISTS `".CAT_TABLE_PREFIX."mod_news_settings`");
	$mod_news = 'CREATE TABLE IF NOT EXISTS `'.CAT_TABLE_PREFIX.'mod_news_settings` ( '
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
		
    $mod_search = "SELECT * FROM ".CAT_TABLE_PREFIX."search WHERE value = 'news'";
    $insert_search = $database->query($mod_search);
    if( $insert_search->numRows() == 0 )
    {
    	// Insert info into the search table
    	// Module query info
    	$field_info = array();
    	$field_info['page_id'] = 'page_id';
    	$field_info['title'] = 'page_title';
    	$field_info['link'] = 'link';
    	$field_info['description'] = 'description';
    	$field_info['modified_when'] = 'modified_when';
    	$field_info['modified_by'] = 'modified_by';
    	$field_info = serialize($field_info);
    	$database->query("INSERT INTO ".CAT_TABLE_PREFIX."search (name,value,extra) VALUES ('module', 'news', '$field_info')");
    	// Query start
    	$query_start_code = "SELECT [TP]pages.page_id, [TP]pages.page_title,	[TP]pages.link, [TP]pages.description, [TP]pages.modified_when, [TP]pages.modified_by	FROM [TP]mod_news_posts, [TP]mod_news_groups, [TP]mod_news_comments, [TP]mod_news_settings, [TP]pages WHERE ";
    	$database->query("INSERT INTO ".CAT_TABLE_PREFIX."search (name,value,extra) VALUES ('query_start', '$query_start_code', 'news')");
    	// Query body
    	$query_body_code = "
    	[TP]pages.page_id = [TP]mod_news_posts.page_id AND [TP]mod_news_posts.title LIKE \'%[STRING]%\'
    	OR [TP]pages.page_id = [TP]mod_news_posts.page_id AND [TP]mod_news_posts.content_short LIKE \'%[STRING]%\'
    	OR [TP]pages.page_id = [TP]mod_news_posts.page_id AND [TP]mod_news_posts.content_long LIKE \'%[STRING]%\'
    	OR [TP]pages.page_id = [TP]mod_news_comments.page_id AND [TP]mod_news_comments.title LIKE \'%[STRING]%\'
    	OR [TP]pages.page_id = [TP]mod_news_comments.page_id AND [TP]mod_news_comments.comment LIKE \'%[STRING]%\'
    	OR [TP]pages.page_id = [TP]mod_news_settings.page_id AND [TP]mod_news_settings.header LIKE \'%[STRING]%\'
    	OR [TP]pages.page_id = [TP]mod_news_settings.page_id AND [TP]mod_news_settings.footer LIKE \'%[STRING]%\'
    	OR [TP]pages.page_id = [TP]mod_news_settings.page_id AND [TP]mod_news_settings.post_header LIKE \'%[STRING]%\'
    	OR [TP]pages.page_id = [TP]mod_news_settings.page_id AND [TP]mod_news_settings.post_footer LIKE \'%[STRING]%\'
    	OR [TP]pages.page_id = [TP]mod_news_settings.page_id AND [TP]mod_news_settings.comments_header LIKE \'%[STRING]%\'
    	OR [TP]pages.page_id = [TP]mod_news_settings.page_id AND [TP]mod_news_settings.comments_footer LIKE \'%[STRING]%\'";
    	$database->query("INSERT INTO ".CAT_TABLE_PREFIX."search (name,value,extra) VALUES ('query_body', '$query_body_code', 'news')");
    	// Query end
    	$query_end_code = "";
    	$database->query("INSERT INTO ".CAT_TABLE_PREFIX."search (name,value,extra) VALUES ('query_end', '$query_end_code', 'news')");

    	// Insert blank row (there needs to be at least on row for the search to work)
    	$database->query("INSERT INTO ".CAT_TABLE_PREFIX."mod_news_posts (section_id,page_id, `link`, `content_short`, `content_long`) VALUES ('0', '0', '', '', '')");
    	$database->query("INSERT INTO ".CAT_TABLE_PREFIX."mod_news_groups (section_id,page_id) VALUES ('0', '0')");
    	$database->query("INSERT INTO ".CAT_TABLE_PREFIX."mod_news_comments (section_id,page_id, `comment`) VALUES ('0', '0', '')");
    	$database->query("INSERT INTO ".CAT_TABLE_PREFIX."mod_news_settings (section_id,page_id, `header`, `post_loop`, `footer`, `post_header`, `post_footer`, `comments_header`, `comments_loop`, `comments_footer`, `comments_page`) VALUES ('0', '0', '', '', '', '', '', '', '', '', '')");
    }

    $addons_helper = new CAT_Helper_Addons();

    // add files to class_secure
    foreach(
        array( 'add_group.php', 'add_post.php', 'comment.php', 'delete_comment.php', 'delete_group.php', 'delete_post.php',
               'modify_comment.php', 'modify_group.php', 'modify_post.php', 'modify_settings.php', 'move_down.php', 'move_up.php',
               'rss.php', 'save_comment.php', 'save_group.php', 'save_post.php', 'save_settings.php', 'submit_comment.php' )
        as $file
    ) {
        if ( false === $addons_helper->sec_register_file( 'news', $file ) )
        {
             error_log( "Unable to register file -$file-!" );
        }
    }

	// Make news post access files dir
	require_once(CAT_PATH.'/framework/functions.php');
	make_dir(CAT_PATH.MEDIA_DIRECTORY.'/newspics'); // create directory for images
	
	if(make_dir(CAT_PATH.PAGES_DIRECTORY.'/posts')) {
		// Add a index.php file to prevent directory spoofing
		$content = ''.
"<?php

 /**
 *  @module         news
 *  @version        see info.php of this module
 *  @author			Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos)
 *  @copyright		2004-2011, Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos) 
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 */

header('Location: ../');
?>";
		$handle = fopen(CAT_PATH.PAGES_DIRECTORY.'/posts/index.php', 'w');
		fwrite($handle, $content);
		fclose($handle);
		CAT_Helper_Directory::getInstance()->setPerms(CAT_PATH.PAGES_DIRECTORY.'/posts/index.php');
		
		/**
		 *	Try to copy the index.php also in the newspicts folder inside
		 *	the media-directory.
		 *
		 */
		copy(
			CAT_PATH.PAGES_DIRECTORY.'/posts/index.php',
			CAT_PATH.MEDIA_DIRECTORY.'/newspics/index.php'
		);
	}
};

?>