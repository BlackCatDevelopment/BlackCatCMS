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
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php'); 
} else {
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) { 
			include($dir.'/framework/class.secure.php'); $inc = true;	break; 
		} 
	}
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}
// end include class.secure.php

//overwrite php.ini on Apache servers for valid SESSION ID Separator
if(function_exists('ini_set')) {
	ini_set('arg_separator.output', '&amp;');
}

/**
 *	Load Language file
 */
$lang = (dirname(__FILE__))."/languages/". LANGUAGE .".php";
require_once ( !file_exists($lang) ? (dirname(__FILE__))."/languages/EN.php" : $lang );

$js_delete_msg = (array_key_exists( 'CONFIRM_DELETE', $MOD_NEWS))
	? $MOD_NEWS['CONFIRM_DELETE']
	: $TEXT['ARE_YOU_SURE']
	;
	
?>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td align="left" width="33%">
		<input type="button" value="<?php echo $TEXT['ADD'].' '.$TEXT['POST']; ?>" onclick="javascript: window.location = '<?php echo WB_URL; ?>/modules/news/add_post.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>';" style="width: 100%;" />
	</td>
	<td align="left" width="33%">
		<input type="button" value="<?php echo $TEXT['ADD'].' '.$TEXT['GROUP']; ?>" onclick="javascript: window.location = '<?php echo WB_URL; ?>/modules/news/add_group.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>';" style="width: 100%;" />
	</td>
	<td align="right" width="33%">
		<input type="button" value="<?php echo $TEXT['SETTINGS']; ?>" onclick="javascript: window.location = '<?php echo WB_URL; ?>/modules/news/modify_settings.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>';" style="width: 100%;" />
	</td>
</tr>
</table>

<br />

<h2><?php echo $TEXT['MODIFY'].'/'.$TEXT['DELETE'].' '.$TEXT['POST']; ?></h2>

<?php

// Check if there is a start point defined
if(isset($_GET['p']) AND is_numeric($_GET['p']) AND $_GET['p'] >= 0) {
	$position = $_GET['p'];
} else {
	$position = 0;
}

// Get settings
$query_settings = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_news_settings WHERE section_id = '$section_id'");
if($query_settings->numRows() > 0) {
	$fetch_settings = $query_settings->fetchRow( MYSQL_ASSOC );
	$setting_header =	$fetch_settings['header'];
	$setting_post_loop = $fetch_settings['post_loop'];
	$setting_footer = $fetch_settings['footer'];
	$setting_posts_per_page = $fetch_settings['posts_per_page'];
} else {
	$setting_header = '';
	$setting_post_loop = '';
	$setting_footer = '';
	$setting_posts_per_page = '';
}

$t = time();
// Get total number of posts
$query_total_num = $database->query("SELECT post_id FROM ".TABLE_PREFIX."mod_news_posts WHERE section_id = '$section_id' ");
$total_num = $query_total_num->numRows();

// Work-out if we need to add limit code to sql
if($setting_posts_per_page != 0) {
	$limit_sql = " LIMIT $position,$setting_posts_per_page";
} else {
	$limit_sql = "";
}
	
// Query posts (for this page)
$query_posts = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_news_posts
	WHERE section_id = '$section_id'
	ORDER BY position DESC".$limit_sql);
$num_posts = $query_posts->numRows();
	
// Create previous and next links
if($setting_posts_per_page != 0) {
	/**
	 *	Patch, as the JS-redirect makes it nessesary to look for botth "leptoken" AND "amp;leptoken"
	 *
	 */
	if (array_key_exists('amp;leptoken', $_GET) ) $_GET['leptoken'] = $_GET['amp;leptoken'];
	$leptoken_add = (isset($_GET['leptoken']) ? "&amp;leptoken=".$_GET['leptoken'] : "");
	if (strlen( $leptoken_add) == 0) {
		if (isset($_POST['leptoken']) ) $leptoken_add =  "&amp;leptoken=".$_POST['leptoken'];
	}
	if($position > 0) {
		$pl_prepend = '<a href="?p='.($position-$setting_posts_per_page).'&amp;page_id='.$page_id.$leptoken_add.'">&lt;&lt; ';
		$pl_append = '</a>';
		$previous_link = $pl_prepend.$TEXT['PREVIOUS'].$pl_append;
		$previous_page_link = $pl_prepend.$TEXT['PREVIOUS_PAGE'].$pl_append;
	} else {
		$previous_link = '';
		$previous_page_link = '';
	}
	if($position+$setting_posts_per_page >= $total_num) {
		$next_link = '';
		$next_page_link = '';
	} else {
		$nl_prepend = '<a href="?p='.($position+$setting_posts_per_page).'&amp;page_id='.$page_id.$leptoken_add.'"> ';
		$nl_append = ' &gt;&gt;</a>';
		$next_link = $nl_prepend.$TEXT['NEXT'].$nl_append;
		$next_page_link = $nl_prepend.$TEXT['NEXT_PAGE'].$nl_append;
	}
	if($position+$setting_posts_per_page > $total_num) {
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


// Loop through existing posts
if($query_posts->numRows() > 0) {
	$num_posts = $query_posts->numRows();
	$row = 'a';
	?>
	<table cellpadding="2" cellspacing="0" border="0" width="100%">
	<?php
	$counter = 0;
	while($post = $query_posts->fetchRow( MYSQL_ASSOC )) {
		$counter++;
		?>
		<tr class="row_<?php echo $row; ?>">
			<td width="20" style="padding-left: 5px;">
				<a href="<?php echo WB_URL; ?>/modules/news/modify_post.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;post_id=<?php echo $post['post_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/modify_16.png" border="0" alt="Modify - " />
				</a>
			</td>
			<td>
				<a href="<?php echo WB_URL; ?>/modules/news/modify_post.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;post_id=<?php echo $post['post_id']; ?>">
					<?php echo ($post['title']); ?>
				</a>
			</td>
			<td width="180">
				<?php echo $TEXT['GROUP'].': ';
				// Get group title
				$query_title = $database->query("SELECT `title` FROM `".TABLE_PREFIX."mod_news_groups` WHERE `group_id` = '".$post['group_id']."'");
				if($query_title->numRows() > 0) {
					$fetch_title = $query_title->fetchRow( MYSQL_ASSOC );
					echo $fetch_title['title'];
				} else {
					echo $TEXT['NONE'];
				}
				?>
			</td>
			<td width="120">
				<?php echo $TEXT['COMMENTS'].': ';
				// Get number of comments
				$query_title = $database->query("SELECT `title` FROM `".TABLE_PREFIX."mod_news_comments` WHERE `post_id` = '".$post['post_id']."'");
				echo $query_title->numRows();
				?>
			</td>
			<td width="80">
				<?php echo $TEXT['ACTIVE'].': '; if($post['active'] == 1) { echo $TEXT['YES']; } else { echo $TEXT['NO']; } ?>
			</td>
			<td width="20">
			<?php
			$start = $post['published_when'];
			$end = $post['published_until'];
			$icon = '';
			if($start<=$t && $end==0)
				$icon=THEME_URL.'/images/noclock_16.png';
			elseif(($start<=$t || $start==0) && $end>=$t)
				$icon=THEME_URL.'/images/clock_16.png';
			else
				$icon=THEME_URL.'/images/clock_red_16.png';
			?>
			<a href="<?php echo WB_URL; ?>/modules/news/modify_post.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;post_id=<?php echo $post['post_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
				<img src="<?php echo $icon; ?>" border="0" alt="" />
			</a>
			</td>
			<td width="20">
			<?php if($counter > 1) { ?>
				<a href="<?php echo WB_URL; ?>/modules/news/move_down.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;post_id=<?php echo $post['post_id']; ?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/up_16.png" border="0" alt="^" />
				</a>
			<?php } ?>
			</td>
			<td width="20">
			<?php if($counter < $num_posts ) { ?>
				<a href="<?php echo WB_URL; ?>/modules/news/move_up.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;post_id=<?php echo $post['post_id']; ?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/down_16.png" border="0" alt="v" />
				</a>
			<?php } ?>
			</td>
			<td width="20">
				<a href="javascript: confirm_link('<?php printf( $js_delete_msg, $post['title'] ); ?>', '<?php echo WB_URL; ?>/modules/news/delete_post.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;post_id=<?php echo $post['post_id']; ?>');" title="<?php echo $TEXT['DELETE']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/delete_16.png" border="0" alt="X" />
				</a>
			</td>
		</tr>
		<?php
		// Alternate row color
		$row = ($row == 'a') ? 'b': 'a';
	}
	?>
	</table>
	<?php
} else {
	echo $TEXT['NONE_FOUND'];
}

/**
 *	Print the prev and next links
 *
 */
$setting_footer = '
<br />
<table cellpadding="2" cellspacing="0" border="0" width="70%">
 <tr>
   <td class="news_prev_link">[PREVIOUS_PAGE_LINK]</td>
   <td class="news_of" >[OF]</td>
   <td class="news_next_link" >[NEXT_PAGE_LINK]</td>
 </tr>
</table>
<br />';

$values = ($display_previous_next_links == 'none')
	? array (
		'[NEXT_PAGE_LINK]'		=> '',
		'[PREVIOUS_PAGE_LINK]'	=> '',
		'[OF]'					=> '' )
	: array (
		'[NEXT_PAGE_LINK]'		=> $next_page_link,
		'[PREVIOUS_PAGE_LINK]'	=> $previous_page_link,
		'[OF]'					=> $of )
	;

echo str_replace( array_keys( $values), array_values( $values ) , $setting_footer );

?>

<h2><?php echo $TEXT['MODIFY'].'/'.$TEXT['DELETE'].' '.$TEXT['GROUP']; ?></h2>

<?php

// Loop through existing groups
$query_groups = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_news_groups` WHERE section_id = '$section_id' ORDER BY position ASC");
if($query_groups->numRows() > 0) {
	$num_groups = $query_groups->numRows();
	$row = 'a';
	?>
	<table cellpadding="2" cellspacing="0" border="0" width="100%">
	<?php
	while($group = $query_groups->fetchRow( MYSQL_ASSOC )) {
		?>
		<tr class="row_<?php echo $row; ?>">
			<td width="20" style="padding-left: 5px;">
				<a href="<?php echo WB_URL; ?>/modules/news/modify_group.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;group_id=<?php echo $group['group_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/modify_16.png" border="0" alt="Modify - " />
				</a>
			</td>		
			<td>
				<a href="<?php echo WB_URL; ?>/modules/news/modify_group.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;group_id=<?php echo $group['group_id']; ?>">
          <?php echo $group['title'].' ('.$group['group_id'].')'; ?>
				</a>
			</td>
			<td width="80">
				<?php echo $TEXT['ACTIVE'].': '; if($group['active'] == 1) { echo $TEXT['YES']; } else { echo $TEXT['NO']; } ?>
			</td>
			<td width="20">
			<?php if($group['position'] != 1) { ?>
				<a href="<?php echo WB_URL; ?>/modules/news/move_up.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;group_id=<?php echo $group['group_id']; ?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/up_16.png" border="0" alt="^" />
				</a>
			<?php } ?>
			</td>
			<td width="20">
			<?php if($group['position'] != $num_groups) { ?>
				<a href="<?php echo WB_URL; ?>/modules/news/move_down.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;group_id=<?php echo $group['group_id']; ?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/down_16.png" border="0" alt="v" />
				</a>
			<?php } ?>
			</td>
			<td width="20">
				<a href="javascript: confirm_link('<?php echo $TEXT['ARE_YOU_SURE']; ?>', '<?php echo WB_URL; ?>/modules/news/delete_group.php?page_id=<?php echo $page_id; ?>&amp;group_id=<?php echo $group['group_id']; ?>');" title="<?php echo $TEXT['DELETE']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/delete_16.png" border="0" alt="X" />
				</a>
			</td>
		</tr>
		<?php
		// Alternate row color
		$row = ($row == 'a') ? 'b' : 'a';
	}
	?>
	</table>
	<?php
} else {
	echo $TEXT['NONE_FOUND'];
}
?>