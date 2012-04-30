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



require_once(WB_PATH.'/framework/class.wb.php');
$wb = new wb;

// Check if we should show the form or add a comment
if(isset($_GET['page_id']) AND is_numeric($_GET['page_id'])
    AND isset($_GET['section_id']) AND is_numeric($_GET['section_id'])
        AND isset($_GET['post_id']) AND is_numeric($_GET['post_id'])
            AND ( ( ENABLED_ASP AND isset($_POST['comment_'.date('W')]) AND $_POST['comment_'.date('W')] != '')
            OR ( !ENABLED_ASP AND isset($_POST['comment']) AND $_POST['comment'] != '' ) ) )
{

	if(ENABLED_ASP){
        $comment = $_POST['comment_'.date('W')];
	}
	else
    {
        $comment = $_POST['comment'];
	}

	$comment = $wb->add_slashes(strip_tags($comment));
	$title = $wb->add_slashes(strip_tags($_POST['title']));
	$page_id = $_GET['page_id'];
	$section_id = $_GET['section_id'];
	$post_id = $_GET['post_id'];

	// Check captcha
	$query_settings = $database->query("SELECT use_captcha FROM ".TABLE_PREFIX."mod_news_settings WHERE section_id = '$section_id'");
	if( !$query_settings->numRows())
    {
		header("Location: ".WB_URL.PAGES_DIRECTORY."");
	    exit( 0 );
	}
    else
    {
		$settings = $query_settings->fetchRow();
		$t=time();

        // Advanced Spam Protection
	    if(ENABLED_ASP AND ( ($_SESSION['session_started']+ASP_SESSION_MIN_AGE > $t)  // session too young
            OR (!isset($_SESSION['comes_from_view']))// user doesn't come from view.php
            OR (!isset($_SESSION['comes_from_view_time']) OR $_SESSION['comes_from_view_time'] > $t-ASP_VIEW_MIN_AGE) // user is too fast
            OR (!isset($_SESSION['submitted_when']) OR !isset($_POST['submitted_when'])) // faked form
            OR ($_SESSION['submitted_when'] != $_POST['submitted_when']) // faked form
            OR ($_SESSION['submitted_when'] > $t-ASP_INPUT_MIN_AGE && !isset($_SESSION['captcha_retry_news'])) // user too fast
            OR ($_SESSION['submitted_when'] < $t-43200) // form older than 12h
            OR ($_POST['email'] OR $_POST['url'] OR $_POST['homepage'] OR $_POST['comment']) /* honeypot-fields */ ) )
        {
            header("Location: ".WB_URL.PAGES_DIRECTORY."");
	        exit( 0 );
		}

		if(ENABLED_ASP)
        {
			if(isset($_SESSION['captcha_retry_news']))
            {
              unset($_SESSION['captcha_retry_news']);
            }
		}

		if($settings['use_captcha'])
        {
			if(isset($_POST['captcha']) AND $_POST['captcha'] != '')
            {
				// Check for a mismatch
				if(!isset($_POST['captcha']) OR !isset($_SESSION['captcha']) OR $_POST['captcha'] != $_SESSION['captcha'])
                {
					$_SESSION['captcha_error'] = $MESSAGE['MOD_FORM']['INCORRECT_CAPTCHA'];
					$_SESSION['comment_title'] = $title;
					$_SESSION['comment_body'] = $comment;
					header("Location: ".WB_URL."/modules/news/comment.php?post_id=".$post_id."&section_id=".$section_id."" );
	                exit( 0 );
				}
			}
            else
            {
				$_SESSION['captcha_error'] = $MESSAGE['MOD_FORM']['INCORRECT_CAPTCHA'];
				$_SESSION['comment_title'] = $title;
				$_SESSION['comment_body'] = $comment;
				header("Location: ".WB_URL."/modules/news/comment.php?post_id=".$post_id."&section_id=".$section_id."" );
	            exit( 0 );
			}
		}
	}

	if(isset($_SESSION['captcha'])) { unset($_SESSION['captcha']); }

	if(ENABLED_ASP)
    {
		unset($_SESSION['comes_from_view']);
		unset($_SESSION['comes_from_view_time']);
		unset($_SESSION['submitted_when']);
	}

	// Insert the comment into db
	$commented_when = time();
	if($wb->is_authenticated() == true)
    {
		$commented_by = $wb->get_user_id();
	}
    else
    {
		$commented_by = '';
	}

	$query = $database->query("INSERT INTO ".TABLE_PREFIX."mod_news_comments (section_id,page_id,post_id,title,comment,commented_when,commented_by) VALUES ('$section_id','$page_id','$post_id','$title','$comment','$commented_when','$commented_by')");
	// Get page link
	$query_page = $database->query("SELECT link FROM ".TABLE_PREFIX."mod_news_posts WHERE post_id = '$post_id'");
	$page = $query_page->fetchRow();
	header('Location: '.$wb->page_link($page['link']).'?post_id='.$post_id.'' );
	exit( 0 );
}
else
{
	if( isset($_GET['post_id']) AND is_numeric($_GET['post_id'])
        AND isset($_GET['section_id']) AND is_numeric($_GET['section_id']) )
    {
 		header("Location: ".WB_URL."/modules/news/comment.php?post_id=".($_GET['post_id'])."&section_id=".($_GET['section_id'])."" ) ;
	    exit( 0 );
    }
	else
    {
		header("Location: ".WB_URL.PAGES_DIRECTORY."");
	    exit( 0 );
    }
}

?>