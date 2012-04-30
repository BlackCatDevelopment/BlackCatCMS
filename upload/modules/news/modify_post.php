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



// Get id
if(!isset($_GET['post_id']) OR !is_numeric($_GET['post_id'])) {
	header("Location: ".ADMIN_URL."/pages/index.php");
	exit(0);
} else {
	$post_id = $_GET['post_id'];
}

// Include WB admin wrapper script
require(WB_PATH.'/modules/admin.php');

// Get header and footer
$query_content = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_news_posts WHERE post_id = '$post_id'");
$fetch_content = $query_content->fetchRow( MYSQL_ASSOC );

if (!defined('WYSIWYG_EDITOR') OR WYSIWYG_EDITOR=="none" OR !file_exists(WB_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php')) {
	function show_wysiwyg_editor($name,$id,$content,$width,$height) {
		echo '<textarea name="'.$name.'" id="'.$id.'" rows="10" cols="1" style="width: '.$width.'; height: '.$height.';">'.$content.'</textarea>';
	}
} else {
	$id_list=array("short","long");
	require(WB_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php');
}

/**
 * Use images?
 */
$query_settings = $database->query("SELECT `post_loop`, `post_header` FROM `".TABLE_PREFIX."mod_news_settings` WHERE `section_id` = '$section_id'");
if($query_settings->numRows() > 0) {
	$settings = $query_settings->fetchRow();
	$string = $settings['post_loop'].$settings['post_header'];
	if(strpos($string,'[PIC_URL]') || strpos($string,'[PICTURE]') ){
		$use_images = TRUE;
	}
}

// include jscalendar-setup
$jscal_use_time = true; // whether to use a clock, too
require_once(WB_PATH."/include/jscalendar/wb-setup.php");

?>
<h2><?php echo $TEXT['ADD'].'/'.$TEXT['MODIFY'].' '.$TEXT['POST']; ?></h2>
<link href="<?php echo WB_URL; ?>/include/jscalendar/calendar-system.css" rel="stylesheet" type="text/css" />
<div class="jsadmin jcalendar hide"></div> 
<form name="modify" action="<?php echo WB_URL; ?>/modules/news/save_post.php" method="post"  enctype="multipart/form-data" style="margin: 0;">
<input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
<input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
<input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
<input type="hidden" name="link" value="<?php echo $fetch_content['link']; ?>" />

<table class="row_a" cellpadding="2" cellspacing="0" width="100%">
<tr>
	<td><?php echo $TEXT['TITLE']; ?>:</td>
	<td width="80%">
		<input type="text" name="title" value="<?php echo (htmlspecialchars($fetch_content['title'])); ?>" style="width: 98%;" maxlength="255" />
	</td>
</tr>

<?php
	$query = $database->query("SELECT group_id,title FROM ".TABLE_PREFIX."mod_news_groups WHERE section_id = '$section_id' ORDER BY position ASC");
	if($query->numRows() > 0) {
?>

<tr>
	<td><?php echo $TEXT['GROUP']; ?>:</td>
	<td>
		<select name="group" style="width: 100%;">
			<option value="0"><?php echo $TEXT['NONE']; ?></option>
			<?php
			$query = $database->query("SELECT group_id,title FROM ".TABLE_PREFIX."mod_news_groups WHERE section_id = '$section_id' ORDER BY position ASC");
			if($query->numRows() > 0) {
				// Loop through groups
				while(false != ($group = $query->fetchRow( MYSQL_ASSOC ) ) ) {
					?>
					<option value="<?php echo $group['group_id']; ?>"<?php if($fetch_content['group_id'] == $group['group_id']) { echo ' selected="selected"'; } ?>><?php echo $group['title']; ?></option>
					<?php
				}
			}
			?>
		</select>
	</td>
<?php } ?>
</tr>
<tr>
	<td><?php echo $TEXT['COMMENTING']; ?>:</td>
	<td>
		<select name="commenting" style="width: 100%;">
			<option value="none"><?php echo $TEXT['DISABLED']; ?></option>
			<option value="public" <?php if($fetch_content['commenting'] == 'public') { echo ' selected="selected"'; } ?>><?php echo $TEXT['PUBLIC']; ?></option>
			<option value="private" <?php if($fetch_content['commenting'] == 'private') { echo ' selected="selected"'; } ?>><?php echo $TEXT['PRIVATE']; ?></option>
		</select>
	</td>
</tr>
<tr>
	<td><?php echo $TEXT['ACTIVE']; ?>:</td>
	<td>
		<input type="radio" name="active" id="active_true" value="1" <?php if($fetch_content['active'] == 1) { echo ' checked="checked"'; } ?> />
		<label for="active_true"><?php echo $TEXT['YES']; ?></label>
		&nbsp;
		<input type="radio" name="active" id="active_false" value="0" <?php if($fetch_content['active'] == 0) { echo ' checked="checked"'; } ?> />
		<label for="active_false"><?php echo $TEXT['NO']; ?></label>
	</td>
</tr>

<tr>
	<td><?php echo $TEXT['PUBL_START_DATE']; ?>:</td>
	<td>
	<input type="text" id="publishdate" name="publishdate" value="<?php if($fetch_content['published_when']!=0) print date($jscal_format, $fetch_content['published_when']);?>" style="width: 120px;" />
	<img src="<?php echo THEME_URL ?>/images/clock_16.png" id="publishdate_trigger" style="cursor: pointer;" title="<?php echo $TEXT['CALENDAR']; ?>" alt="<?php echo $TEXT['CALENDAR']; ?>" onmouseover="this.style.background='lightgrey';" onmouseout="this.style.background=''" />
	<img src="<?php echo THEME_URL ?>/images/clock_del_16.png" style="cursor: pointer;" title="<?php echo $TEXT['DELETE_DATE']; ?>" alt="<?php echo $TEXT['DELETE_DATE']; ?>" onmouseover="this.style.background='lightgrey';" onmouseout="this.style.background=''" onclick="document.modify.publishdate.value=''" />
	</td>
</tr>
<tr>
	<td><?php echo $TEXT['PUBL_END_DATE']; ?>:</td>
	<td>
	<input type="text" id="enddate" name="enddate" value="<?php if($fetch_content['published_until']==0) print ""; else print date($jscal_format, $fetch_content['published_until'])?>" style="width: 120px;" />
	<img src="<?php echo THEME_URL ?>/images/clock_16.png" id="enddate_trigger" style="cursor: pointer;" title="<?php echo $TEXT['CALENDAR']; ?>" alt="<?php echo $TEXT['CALENDAR']; ?>" onmouseover="this.style.background='lightgrey';" onmouseout="this.style.background=''" />
	<img src="<?php echo THEME_URL ?>/images/clock_del_16.png" style="cursor: pointer;" title="<?php echo $TEXT['DELETE_DATE']; ?>" alt="<?php echo $TEXT['DELETE_DATE']; ?>" onmouseover="this.style.background='lightgrey';" onmouseout="this.style.background=''" onclick="document.modify.enddate.value=''" />
	</td>
</tr>
<?php if(isset($use_images) && $use_images == TRUE){ ?>
<tr>
	<td><?php echo $TEXT['IMAGE']; ?>:</td>
	<?php if(file_exists(WB_PATH.MEDIA_DIRECTORY.'/newspics/image'.$post_id.'.jpg')) { ?>
	<td>
		<a href="<?php echo WB_URL.MEDIA_DIRECTORY; ?>/newspics/image<?php echo $post_id; ?>.jpg" title="<?php echo $TEXT['VIEW']; ?>" target="_blank" border="0">
		<img class="image_preview" src="<?php echo WB_URL.MEDIA_DIRECTORY; ?>/newspics/image<?php echo $post_id; ?>.jpg" alt="<?php echo $TEXT['VIEW']; ?>" />		
		</a>
		&nbsp;
		<input type="checkbox" name="delete_image" id="delete_image" value="true" />
		<label for="delete_image"><?php echo $TEXT['DELETE']; ?></label>
	</td>
	<?php } else { ?>
	<td>
		<input type="file" name="newspic" />
	</td>
	<?php } ?>
</tr>
<?php } ?>
</table>

<table class="row_a" cellpadding="2" cellspacing="0" border="0" width="100%">
<tr>
	<td valign="top"><?php echo $TEXT['SHORT']; ?>:</td>
</tr>
<tr>
	<td>
	<?php
  	show_wysiwyg_editor("short","short",htmlspecialchars($fetch_content['content_short']),"100%","250px");
	?>
	</td>
</tr>
<tr>
	<td valign="top"><?php echo $TEXT['LONG']; ?>:</td>
</tr>
<tr>
	<td>
	<?php
	show_wysiwyg_editor("long","long",htmlspecialchars($fetch_content['content_long']),"100%","550px");
	?>
	</td>
</tr>
</table>

<table cellpadding="2" cellspacing="0" border="0" width="100%">
<tr>
	<td align="left">
		<input name="save" type="submit" value="<?php echo $TEXT['SAVE']; ?>" style="width: 100px; margin-top: 5px;" />
	</td>
	<td align="right">
		<input type="button" value="<?php echo $TEXT['CANCEL']; ?>" onclick="javascript: window.location = '<?php echo ADMIN_URL; ?>/pages/modify.php?page_id=<?php echo $page_id; ?>';" style="width: 100px; margin-top: 5px;" />
	</td>
</tr>
</table>
</form>

<script type="text/javascript">
	Calendar.setup(
		{
			inputField  : "publishdate",
			ifFormat    : "<?php echo $jscal_ifformat ?>",
			button      : "publishdate_trigger",
			firstDay    : <?php echo $jscal_firstday ?>,
			<?php if(isset($jscal_use_time) && $jscal_use_time==TRUE)
            { ?>
				showsTime   : "true",
				timeFormat  : "24",
			<?php
            } ?>
			date        : "<?php echo $jscal_today ?>",
			range       : [1970, 2037],
			step        : 1
		}
	);
	Calendar.setup(
		{
			inputField  : "enddate",
			ifFormat    : "<?php echo $jscal_ifformat ?>",
			button      : "enddate_trigger",
			firstDay    : <?php echo $jscal_firstday ?>,
			<?php if(isset($jscal_use_time) && $jscal_use_time==TRUE)
            { ?>
				showsTime   : "true",
				timeFormat  : "24",
			<?php
            } ?>
			date        : "<?php echo $jscal_today ?>",
			range       : [1970, 2037],
			step        : 1
		}
	);
</script>

<br />

<h2><?php echo $TEXT['MODIFY'].'/'.$TEXT['DELETE'].' '.$TEXT['COMMENT']; ?></h2>

<?php

// Loop through existing posts
$query_comments = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_news_comments` WHERE section_id = '$section_id' AND post_id = '$post_id' ORDER BY commented_when DESC");
if($query_comments->numRows() > 0) {
	$row = 'a';
	?>
	<table cellpadding="2" cellspacing="0" border="0" width="100%">
	<?php
	while($comment = $query_comments->fetchRow()) {
		?>
		<tr class="row_<?php echo $row; ?>" >
			<td width="20" style="padding-left: 5px;">
				<a href="<?php echo WB_URL; ?>/modules/news/modify_comment.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;comment_id=<?php echo $comment['comment_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/modify_16.png" border="0" alt="^" />
				</a>
			</td>	
			<td>
				<a href="<?php echo WB_URL; ?>/modules/news/modify_comment.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;comment_id=<?php echo $comment['comment_id']; ?>">
					<?php echo $comment['title']; ?>
				</a>
			</td>
			<td width="20">
				<a href="javascript: confirm_link('<?php echo $TEXT['ARE_YOU_SURE']; ?>', '<?php echo WB_URL; ?>/modules/news/delete_comment.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>&amp;post_id=<?php echo $post_id; ?>&amp;comment_id=<?php echo $comment['comment_id']; ?>');" title="<?php echo $TEXT['DELETE']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/delete_16.png" border="0" alt="X" />
				</a>
			</td>
		</tr>
		<?php
		// Alternate row color
		if($row == 'a') {
			$row = 'b';
		} else {
			$row = 'a';
		}
	}
	?>
	</table>
	<?php
} else {
	echo $TEXT['NONE_FOUND'];
}


// Print admin footer
$admin->print_footer();

?>