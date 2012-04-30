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
 * @version         $Id: view.php 1462 2011-12-12 16:31:23Z frankh $
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



// load module language file
$lang = (dirname(__FILE__)) . '/languages/' . LANGUAGE . '.php';
require_once(!file_exists($lang) ? (dirname(__FILE__)) . '/languages/EN.php' : $lang );

//overwrite php.ini on Apache servers for valid SESSION ID Separator
if(function_exists('ini_set'))
{
	ini_set('arg_separator.output', '&amp;');
}

// Check if there is a start point defined
if(isset($_GET['p']) AND is_numeric($_GET['p']) AND $_GET['p'] >= 0)
{
	$position = $_GET['p'];
} else {
	$position = 0;
}

// Get user's username, display name, email, and id - needed for insertion into post info
$users = array();
$query_users = $database->query("SELECT user_id,username,display_name,email FROM ".TABLE_PREFIX."users");
if($query_users->numRows() > 0)
{
	while( false != ($user = $query_users->fetchRow()) )
    {
		// Insert user info into users array
		$user_id = $user['user_id'];
		$users[$user_id]['username'] = $user['username'];
		$users[$user_id]['display_name'] = $user['display_name'];
		$users[$user_id]['email'] = $user['email'];
	}
}
// Get groups (title, if they are active, and their image [if one has been uploaded])
if (isset($groups))
{
   unset($groups);
}

$groups[0]['title'] = '';
$groups[0]['active'] = true;
$groups[0]['image'] = '';

$query_users = $database->query("SELECT group_id,title,active FROM ".TABLE_PREFIX."mod_news_groups WHERE section_id = '$section_id' ORDER BY position ASC");
if($query_users->numRows() > 0)
{
	while( false != ($group = $query_users->fetchRow()) )
    {
		// Insert user info into users array
		$group_id = $group['group_id'];
		$groups[$group_id]['title'] = ($group['title']);
		$groups[$group_id]['active'] = $group['active'];
		if(file_exists(WB_PATH.MEDIA_DIRECTORY.'/.news/image'.$group_id.'.jpg'))
        {
			$groups[$group_id]['image'] = WB_URL.MEDIA_DIRECTORY.'/.news/image'.$group_id.'.jpg';
		} else {
			$groups[$group_id]['image'] = '';
		}
	}
}



// Check if we should show the main page or a post itself
if(!defined('POST_ID') OR !is_numeric(POST_ID))
{

	// Check if we should only list posts from a certain group
	if(isset($_GET['g']) AND is_numeric($_GET['g']))
    {
		$query_extra = " AND group_id = '".$_GET['g']."'";
	} else {
		$query_extra = '';
	}

	// Check if we should only list posts from a certain group
	if(isset($_GET['g']) AND is_numeric($_GET['g']))
    {
		$query_extra = " AND group_id = '".$_GET['g']."'";
	} else {
		$query_extra = '';
	}

	// Get settings
	$query_settings = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_news_settings WHERE section_id = '$section_id'");
	if($query_settings->numRows() > 0)
    {
		$fetch_settings = $query_settings->fetchRow();
		$setting_header = ($fetch_settings['header']);
		$setting_post_loop = ($fetch_settings['post_loop']);
		$setting_footer = ($fetch_settings['footer']);
		$setting_posts_per_page = $fetch_settings['posts_per_page'];
	} else {
		$setting_header = '';
		$setting_post_loop = '';
		$setting_footer = '';
		$setting_posts_per_page = '';
	}

	$t = time();
	// Get total number of posts
	$query_total_num = $database->query("SELECT post_id, section_id FROM ".TABLE_PREFIX."mod_news_posts
		WHERE section_id = '$section_id' AND active = '1' AND title != '' $query_extra
		AND (published_when = '0' OR published_when <= $t) AND (published_until = 0 OR published_until >= $t)");
	$total_num = $query_total_num->numRows();

	// Work-out if we need to add limit code to sql
	if($setting_posts_per_page != 0)
    {
		$limit_sql = " LIMIT $position, $setting_posts_per_page";
	} else {
		$limit_sql = "";
	}

	// Query posts (for this page)
	$query_posts = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_news_posts
		WHERE section_id = '$section_id' AND active = '1' AND title != ''$query_extra
		AND (published_when = '0' OR published_when <= $t) AND (published_until = 0 OR published_until >= $t)
		ORDER BY position DESC".$limit_sql);
	$num_posts = $query_posts->numRows();

	// Create previous and next links
	if($setting_posts_per_page != 0)
    {
		if($position > 0)
        {
			if(isset($_GET['g']) AND is_numeric($_GET['g']))
            {
				$pl_prepend = '<a href="?p='.($position-$setting_posts_per_page).'&amp;g='.$_GET['g'].'">&lt;&lt; ';
			} else {
				$pl_prepend = '<a href="?p='.($position-$setting_posts_per_page).'">&lt;&lt; ';
			}
			$pl_append = '</a>';
			$previous_link = $pl_prepend.$TEXT['PREVIOUS'].$pl_append;
			$previous_page_link = $pl_prepend.$TEXT['PREVIOUS_PAGE'].$pl_append;
		} else {
			$previous_link = '';
			$previous_page_link = '';
		}
		if($position + $setting_posts_per_page >= $total_num)
        {
			$next_link = '';
			$next_page_link = '';
		} else {
			if(isset($_GET['g']) AND is_numeric($_GET['g']))
            {
				$nl_prepend = '<a href="?p='.($position+$setting_posts_per_page).'&amp;g='.$_GET['g'].'"> ';
			} else {
				$nl_prepend = '<a href="?p='.($position+$setting_posts_per_page).'"> ';
			}
			$nl_append = ' &gt;&gt;</a>';
			$next_link = $nl_prepend.$TEXT['NEXT'].$nl_append;
			$next_page_link = $nl_prepend.$TEXT['NEXT_PAGE'].$nl_append;
		}
		if($position+$setting_posts_per_page > $total_num)
        {
			$num_of = $position+$num_posts;
		} else {
			$num_of = $position+$setting_posts_per_page;
		}

		$out_of = ($position+1).'-'.$num_of.' '.strtolower($TEXT['OUT_OF']).' '.$total_num;
		$of = ($position+1).'-'.$num_of.' '.strtolower($TEXT['OF']).' '.$total_num;
		$display_previous_next_links = '';
	} else {
		$display_previous_next_links = 'none';
	}

	if ($num_posts === 0)
    {
		$setting_header = '';
		$setting_post_loop = '';
		$setting_footer = '';
		$setting_posts_per_page = '';
	}

	// Print header
	if($display_previous_next_links == 'none')
    {
		print  str_replace( array('[NEXT_PAGE_LINK]','[NEXT_LINK]','[PREVIOUS_PAGE_LINK]','[PREVIOUS_LINK]','[OUT_OF]','[OF]','[DISPLAY_PREVIOUS_NEXT_LINKS]'),
                            array('','','','','','', $display_previous_next_links), $setting_header);
	} else {
		print str_replace(  array('[NEXT_PAGE_LINK]','[NEXT_LINK]','[PREVIOUS_PAGE_LINK]','[PREVIOUS_LINK]','[OUT_OF]','[OF]','[DISPLAY_PREVIOUS_NEXT_LINKS]'),
                            array($next_page_link, $next_link, $previous_page_link, $previous_link, $out_of, $of, $display_previous_next_links), $setting_header);
	}
	if($num_posts > 0)
    {
		if($query_extra != '')
        {
			?>
			<div class="selected-group-title">
				<?php print '<a href="'.htmlspecialchars(strip_tags($_SERVER['SCRIPT_NAME'])).'">'.PAGE_TITLE.'</a> &gt;&gt; '.$groups[$_GET['g']]['title']; ?>
			</div>
			<?php
		}
		while( false != ($post = $query_posts->fetchRow()) )
        {
			if(isset($groups[$post['group_id']]['active']) AND $groups[$post['group_id']]['active'] != false)
            { // Make sure parent group is active
				$uid = $post['posted_by']; // User who last modified the post
				// Workout date and time of last modified post
				if ($post['published_when'] === '0') $post['published_when'] = time();
				if ($post['published_when'] > $post['posted_when'])
                {
					$post_date = date(DATE_FORMAT, $post['published_when']);
					$post_time = date(TIME_FORMAT, $post['published_when']);
				} else {
					$post_date = date(DATE_FORMAT, $post['posted_when']);
					$post_time = date(TIME_FORMAT, $post['posted_when']);
				}

				$publ_date = date(DATE_FORMAT,$post['published_when']);
				$publ_time = date(TIME_FORMAT,$post['published_when']);

				// Work-out the post link
				$post_link = page_link($post['link']);

                $post_link_path = str_replace(WB_URL, WB_PATH,$post_link);
                if(file_exists($post_link_path))
                {
    				$create_date = date(DATE_FORMAT, filemtime ( $post_link_path ));
    				$create_time = date(TIME_FORMAT, filemtime ( $post_link_path ));
                } else {
                    $create_date = $publ_date;
                    $create_time = $publ_time;
                }

				if(isset($_GET['p']) AND $position > 0)
                {
					$post_link .= '?p='.$position;
				}
				if(isset($_GET['g']) AND is_numeric($_GET['g']))
                {
					if(isset($_GET['p']) AND $position > 0) { $post_link .= '&amp;'; } else { $post_link .= '?'; }
                    {
					$post_link .= 'g='.$_GET['g'];
                    }
				}

				// Get group id, title, and image
				$group_id = $post['group_id'];
				$group_title = $groups[$group_id]['title'];
				$group_image = $groups[$group_id]['image'];
				$display_image = ($group_image == '') ? "none" : "inherit";
				$display_group = ($group_id == 0) ? 'none' : 'inherit';

				if ($group_image != "") $group_image= "<img src='".$group_image."' alt='".$group_title."' />";

				// Replace [wblink--PAGE_ID--] with real link
				$short = ($post['content_short']);
				$wb->preprocess($short);

				// Loop Post Image
				$post_pic_url = '';
				$post_picture = '';
				if(file_exists(WB_PATH.MEDIA_DIRECTORY.'/newspics/image'.$post['post_id'].'.jpg')){
					$post_pic_url = WB_URL.MEDIA_DIRECTORY.'/newspics/image'.$post['post_id'].'.jpg';
					$post_picture = '<img src="'.$post_pic_url.'" alt="'.$post['title'].'" class="news_loop_image" />';
				}
				
			   // number of comments:
			   $com_count = '';
			   $pid = $post['post_id'];
			   $qc = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_news_comments WHERE section_id = '$section_id' AND post_id = '$pid'");
			   if ($qc->numRows() == 1) {
				  $com_count = "1 Kommentar";
			   } 
			   if ($qc->numRows() > 1) {
				  $com_count = $qc->numRows() . " Kommentare";
			   } 

				// Replace vars with values
				$post_long_len = strlen($post['content_long']);
				$vars = array('[PICTURE]', '[PIC_URL]', '[PAGE_TITLE]', '[GROUP_ID]', '[GROUP_TITLE]', '[GROUP_IMAGE]', '[DISPLAY_GROUP]', '[DISPLAY_IMAGE]', '[TITLE]',
							  '[SHORT]', '[LINK]', '[MODI_DATE]', '[MODI_TIME]', '[CREATED_DATE]', '[CREATED_TIME]', '[PUBLISHED_DATE]', '[PUBLISHED_TIME]', '[USER_ID]',
							  '[USERNAME]', '[DISPLAY_NAME]', '[EMAIL]', '[TEXT_READ_MORE]','[SHOW_READ_MORE]', '[COM_COUNT]');
				if(isset($users[$uid]['username']) AND $users[$uid]['username'] != '')
                {
					if($post_long_len < 9)
                    {
						$values = array($post_picture, $post_pic_url, PAGE_TITLE, $group_id, $group_title, $group_image, $display_group, $display_image, $post['title'],
										$short, '#" onclick="javascript:void(0);return false;" style="cursor:no-drop;', $post_date, $post_time, $create_date, $create_time,
										$publ_date, $publ_time, $uid, $users[$uid]['username'], $users[$uid]['display_name'], $users[$uid]['email'], '', 'hidden', $com_count);
					} else {
					   	$values = array($post_picture, $post_pic_url, PAGE_TITLE, $group_id, $group_title, $group_image, $display_group, $display_image, $post['title'],
										$short, $post_link, $post_date, $post_time, $create_date, $create_time, $publ_date, $publ_time, $uid, $users[$uid]['username'],
										$users[$uid]['display_name'], $users[$uid]['email'], $MOD_NEWS['TEXT_READ_MORE'], 'visible', $com_count);
					}
				} else {
					if($post_long_len < 9)
                    {
						$values = array($post_picture, $post_pic_url, PAGE_TITLE, $group_id, $group_title, $group_image, $display_group, $display_image, $post['title'],
										$short, '#" onclick="javascript:void(0);return false;" style="cursor:no-drop;', $post_date, $post_time, $create_date, $create_time,
										$publ_date, $publ_time, '', '', '', '', '','hidden', $com_count);
					} else {
						$values = array($post_picture, $post_pic_url, PAGE_TITLE, $group_id, $group_title, $group_image, $display_group, $display_image, $post['title'],
										$short, $post_link, $post_date, $post_time, $create_date, $create_time, $publ_date, $publ_time, '', '', '', '',
										$MOD_NEWS['TEXT_READ_MORE'],'visible', $com_count);
					}
				}
				print str_replace($vars, $values, $setting_post_loop);
			}
		}
	}
    // Print footer
    if($display_previous_next_links == 'none')
    {
    	print  str_replace(array('[NEXT_PAGE_LINK]','[NEXT_LINK]','[PREVIOUS_PAGE_LINK]','[PREVIOUS_LINK]','[OUT_OF]','[OF]','[DISPLAY_PREVIOUS_NEXT_LINKS]'), array('','','','','','', $display_previous_next_links), $setting_footer);
    }
    else
    {
    	print str_replace(array('[NEXT_PAGE_LINK]','[NEXT_LINK]','[PREVIOUS_PAGE_LINK]','[PREVIOUS_LINK]','[OUT_OF]','[OF]','[DISPLAY_PREVIOUS_NEXT_LINKS]'), array($next_page_link, $next_link, $previous_page_link, $previous_link, $out_of, $of, $display_previous_next_links), $setting_footer);
    }

}
elseif(defined('POST_ID') AND is_numeric(POST_ID))
{

  // print '<h2>'.POST_ID.'/'.PAGE_ID.'/'.POST_SECTION.'</h2>';
  //if(defined('POST_SECTION') AND POST_SECTION == $section_id)
  {
	// Get settings
	$query_settings = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_news_settings WHERE section_id = '$section_id'");
	if($query_settings->numRows() > 0)
    {
		$fetch_settings = $query_settings->fetchRow();
		$setting_post_header = ($fetch_settings['post_header']);
		$setting_post_footer = ($fetch_settings['post_footer']);
		$setting_comments_header = ($fetch_settings['comments_header']);
		$setting_comments_loop = ($fetch_settings['comments_loop']);
		$setting_comments_footer = ($fetch_settings['comments_footer']);
	} else {
		$setting_post_header = '';
		$setting_post_footer = '';
		$setting_comments_header = '';
		$setting_comments_loop = '';
		$setting_comments_footer = '';
    }
    
	// Get page info
	$query_page = $database->query("SELECT link FROM ".TABLE_PREFIX."pages WHERE page_id = '".PAGE_ID."'");
	if($query_page->numRows() > 0)
    {
		$page = $query_page->fetchRow( MYSQL_ASSOC );
		$page_link = page_link($page['link']);
		if(isset($_GET['p']) AND $position > 0)
        {
			$page_link .= '?p='.$_GET['p'];
		}
		if(isset($_GET['g']) AND is_numeric($_GET['g']))
        {
			if(isset($_GET['p']) AND $position > 0) { $page_link .= '&amp;'; } else { $page_link .= '?'; }
			$page_link .= 'g='.$_GET['g'];
		}
	} else {
		exit($MESSAGE['PAGES']['NOT_FOUND']);
	}

	// Get post info
	$t = time();
	$query_post = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_news_posts
		WHERE post_id = '".POST_ID."' AND active = '1'
		AND (published_when = '0' OR published_when <= $t) AND (published_until = 0 OR published_until >= $t)");

	if($query_post->numRows() > 0)
    {
    
		$post = $query_post->fetchRow( MYSQL_ASSOC );
		if(isset($groups[$post['group_id']]['active']) AND $groups[$post['group_id']]['active'] != false)
        { // Make sure parent group is active
			$uid = $post['posted_by']; // User who last modified the post
			// Workout date and time of last modified post
			if ($post['published_when'] === '0') $post['published_when'] = time();
			if ($post['published_when'] > $post['posted_when'])
            {
				$post_date = date(DATE_FORMAT, $post['published_when']);
				$post_time = date(TIME_FORMAT, $post['published_when']);
			}
            else
            {
				$post_date = date(DATE_FORMAT, $post['posted_when']);
				$post_time = date(TIME_FORMAT, $post['posted_when']);
			}

			$publ_date = date(DATE_FORMAT,$post['published_when']);
			$publ_time = date(TIME_FORMAT,$post['published_when']);

			// Work-out the post link
			$post_link = page_link($post['link']);

			$post_link_path = str_replace(WB_URL, WB_PATH,$post_link);
            if(file_exists($post_link_path))
            {
    			$create_date = date(DATE_FORMAT, filemtime ( $post_link_path ));
				$create_time = date(TIME_FORMAT, filemtime ( $post_link_path ));
			} else {
            	$create_date = $publ_date;
                $create_time = $publ_time;
			}
			
			// Get group id, title, and group image
			$group_id = $post['group_id'];
			$group_title = $groups[$group_id]['title'];
			$group_image = $groups[$group_id]['image'];
			$display_image = ($group_image == '') ? "none" : "inherit";
			$display_group = ($group_id == 0) ? 'none' : 'inherit';

			if ($group_image != "") $group_image= "<img src='".$group_image."' alt='".$group_title."' />";
			
			// Post Image
			$post_pic_url = '';
			$post_picture = '';
			if(file_exists(WB_PATH.MEDIA_DIRECTORY.'/newspics/image'.POST_ID.'.jpg')){
				$post_pic_url = WB_URL.MEDIA_DIRECTORY.'/newspics/image'.POST_ID.'.jpg';
				$post_picture = '<img src="'.$post_pic_url.'" alt="'.$post['title'].'" class="news_post_image" />';
			}
		
			$display_user = (isset($users[$uid]['username']) AND $users[$uid]['username'] != '') ? true : false;

			$post_short = $post['content_short'];
			$wb->preprocess($post_short);

			$post_long = ($post['content_long'] != '') ? $post['content_long'] : $post['content_short'];
			$wb->preprocess($post_long);
						
			$vars = array(
				'[PICTURE]'		=> $post_picture,
				'[PIC_URL]'		=> $post_pic_url,
				'[PAGE_TITLE]'	=> PAGE_TITLE,
				'[GROUP_ID]'	=> $group_id,
				'[GROUP_TITLE]'	=> $group_title,
				'[GROUP_IMAGE]'	=> $group_image,
				'[DISPLAY_GROUP]'	=> $display_group,
				'[DISPLAY_IMAGE]'	=> $display_image,
				'[TITLE]'		=> $post['title'],
				'[SHORT]'		=> $post_short, // *
				'[BACK]'		=> $page_link,
				'[TEXT_BACK]'	=> $MOD_NEWS['TEXT_BACK'],
				'[TEXT_LAST_CHANGED]'	=> $MOD_NEWS['TEXT_LAST_CHANGED'],
				'[MODI_DATE]'	=> $post_date,
				'[TEXT_AT]'		=> $MOD_NEWS['TEXT_AT'],
				'[MODI_TIME]'	=> $post_time,
				'[CREATED_DATE]'	=> $create_date,
				'[CREATED_TIME]'	=> $create_time,
				'[PUBLISHED_DATE]'	=> $publ_date,
				'[PUBLISHED_TIME]'	=> $publ_time,
				'[TEXT_POSTED_BY]'	=> $MOD_NEWS['TEXT_POSTED_BY'],
				'[TEXT_ON]'			=> $MOD_NEWS['TEXT_ON'],
				'[USER_ID]'			=> ( (true === $display_user) ? $uid : ""),
				'[USERNAME]'		=> ( (true === $display_user) ? $users[$uid]['username'] : "" ),
				'[DISPLAY_NAME]'	=> ( (true === $display_user) ? $users[$uid]['display_name'] : "" ),
				'[EMAIL]'			=> ( (true === $display_user) ? $users[$uid]['email'] : "" )
			);
		}
	} else {
	    $wb->print_error($MESSAGE['FRONTEND']['SORRY_NO_ACTIVE_SECTIONS'], 'view.php', false);
	}

	// Print post header
	print str_replace( array_keys($vars), array_values($vars), $setting_post_header);

	print $post_long;

	// Print post footer
	print str_replace( array_keys($vars), array_values($vars), $setting_post_footer);

	// Show comments section if we have to
	if(($post['commenting'] == 'private' AND isset($wb) AND $wb->is_authenticated() == true) OR $post['commenting'] == 'public')
    {
		/**
		 *	Comments header
		 *
		 */
		$vars = array(
			'[ADD_COMMENT_URL]' => WB_URL.'/modules/news/comment.php?post_id='.POST_ID.'&amp;section_id='.$section_id,
			'[TEXT_COMMENTS]'	=> $MOD_NEWS['TEXT_COMMENTS']
		);
		echo str_replace( array_keys($vars), array_values($vars), $setting_comments_header);

		// Query for comments
		$query_comments = $database->query("SELECT title,comment,commented_when,commented_by FROM ".TABLE_PREFIX."mod_news_comments WHERE post_id = '".POST_ID."' ORDER BY commented_when ASC");
		if($query_comments->numRows() > 0)
        {
			while( false != ($comment = $query_comments->fetchRow( MYSQL_ASSOC ) ) )
            {
				// Display Comments without slashes, but with new-line characters
				$comment['comment'] = nl2br($wb->strip_slashes($comment['comment']));
				$comment['title'] = $wb->strip_slashes($comment['title']);
				// Print comments loop
				$commented_date = date(DATE_FORMAT, $comment['commented_when']);
				$commented_time = date(TIME_FORMAT, $comment['commented_when']);
				$uid = $comment['commented_by'];
				
				$display_user = (isset($users[$uid]['username']) AND $users[$uid]['username'] != '') ? true : false;
				
				$vars = array(
					'[TITLE]'	=> $comment['title'],
					'[COMMENT]'	=> $comment['comment'],
					'[TEXT_ON]'	=> $MOD_NEWS['TEXT_ON'],
					'[DATE]'	=> $commented_date,
					'[TEXT_AT]'	=> $MOD_NEWS['TEXT_AT'],
					'[TIME]'	=> $commented_time,
					'[TEXT_BY]'	=> $MOD_NEWS['TEXT_BY'],
					'[USER_ID]'	=> ( true === $display_user ) ? $uid : '0',
					'[USERNAME]'=> ( true === $display_user ) ? $users[$uid]['username'] : $MOD_NEWS['TEXT_UNKNOWN'],
					'[DISPLAY_NAME]' => ( true === $display_user ) ? $users[$uid]['display_name'] : $MOD_NEWS['TEXT_UNKNOWN'],
					'[EMAIL]'	=> ( true === $display_user ) ? $users[$uid]['email'] : ""
				);
				
				echo str_replace( array_keys($vars), array_values($vars), $setting_comments_loop);
			}
		} else {
			/**
			 *	No comments found
			 *
			 */
			echo (isset($MOD_NEWS['TEXT_NO_COMMENT'])) ? $MOD_NEWS['TEXT_NO_COMMENT'].'<br />' : 'None found<br />';
		}

		/**
		 *	Print comments footer
		 *
		 */
		$vars = array(
			'[ADD_COMMENT_URL]'	=> WB_URL.'/modules/news/comment.php?post_id='.POST_ID.'&amp;section_id='.$section_id,
			'[TEXT_ADD_COMMENT]' => $MOD_NEWS['TEXT_ADD_COMMENT']
		);
		echo str_replace( array_keys($vars), array_values($vars), $setting_comments_footer);

	}

    }

	if(ENABLED_ASP)
    {
		$_SESSION['comes_from_view'] = POST_ID;
		$_SESSION['comes_from_view_time'] = time();
	}

}
?>