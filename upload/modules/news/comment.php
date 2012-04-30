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



// Include config file

// Check if there is a post id
if(!isset($_GET['post_id']) OR !is_numeric($_GET['post_id'])
    OR !isset($_GET['section_id']) OR !is_numeric($_GET['section_id']))
{
	header("Location: ".WB_URL.PAGES_DIRECTORY."");
	exit( 0 );
}
$post_id = $_GET['post_id'];
$section_id = $_GET['section_id'];

// Query post for page id
$query_post = $database->query("SELECT post_id,title,section_id,page_id FROM ".TABLE_PREFIX."mod_news_posts WHERE post_id = '$post_id'");
if($query_post->numRows() == 0)
{
    header("Location: ".WB_URL.PAGES_DIRECTORY."");
	exit( 0 );
}
else
{
	$fetch_post = $query_post->fetchRow();
	$page_id = $fetch_post['page_id'];
	$section_id = $fetch_post['section_id'];
	$post_id = $fetch_post['post_id'];
	$post_title = $fetch_post['title'];
	define('SECTION_ID', $section_id);
	define('POST_ID', $post_id);
	define('POST_TITLE', $post_title);

	// don't allow commenting if its disabled, or if post or group is inactive
	$t = time();
	$table_posts = TABLE_PREFIX."mod_news_posts";
	$table_groups = TABLE_PREFIX."mod_news_groups";
	$query = $database->query("
		SELECT p.post_id
		FROM $table_posts AS p LEFT OUTER JOIN $table_groups AS g ON p.group_id = g.group_id
		WHERE p.post_id='$post_id' AND p.commenting != 'none' AND p.active = '1' AND ( g.active IS NULL OR g.active = '1' )
		AND (p.published_when = '0' OR p.published_when <= $t) AND (p.published_until = 0 OR p.published_until >= $t)
	");
	if($query->numRows() == 0)
    {
		header("Location: ".WB_URL.PAGES_DIRECTORY."");
	    exit( 0 );
	}

	// don't allow commenting if ASP enabled and user doesn't comes from the right view.php
	if(ENABLED_ASP && (!isset($_SESSION['comes_from_view']) OR $_SESSION['comes_from_view']!=POST_ID))
    {
		header("Location: ".WB_URL.PAGES_DIRECTORY."");
	    exit( 0 );
	}

	// Get page details
	$query_page = $database->query("SELECT parent,page_title,menu_title,keywords,description,visibility FROM ".TABLE_PREFIX."pages WHERE page_id = '$page_id'");
	if($query_page->numRows() == 0)
    {
		header("Location: ".WB_URL.PAGES_DIRECTORY."");
	    exit( 0 );
	}
    else
    {
		$page = $query_page->fetchRow();
		// Required page details
		define('PAGE_CONTENT', WB_PATH.'/modules/news/comment_page.php');
		// Include index (wrapper) file
		require(WB_PATH.'/index.php');
	}
}

?>