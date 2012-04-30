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
if(!isset($_GET['group_id']) OR !is_numeric($_GET['group_id'])) {
	header("Location: ".ADMIN_URL."/pages/index.php");
	exit(0);
} else {
	$group_id = $_GET['group_id'];
}

// Include WB admin wrapper script
require(WB_PATH.'/modules/admin.php');

// Get header and footer
$query_content = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_news_groups WHERE group_id = '$group_id'");
$fetch_content = $query_content->fetchRow();

/**
 * Use images?
 */
$post_header = $database->get_one("SELECT `post_header` FROM `".TABLE_PREFIX."mod_news_settings` WHERE `section_id` = '$section_id'");
if($post_header) {	
	if(strpos($post_header, '[GROUP_IMAGE]')){
		$use_images = TRUE;
	}
}

?>

<h2><?php echo $TEXT['ADD'].'/'.$TEXT['MODIFY'].' '.$TEXT['GROUP']; ?></h2>

<form name="modify" action="<?php echo WB_URL; ?>/modules/news/save_group.php" method="post" enctype="multipart/form-data" style="margin: 0;">

<input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
<input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />

<table class="row_a" cellpadding="2" cellspacing="0" border="0" width="100%">
<tr>
	<td width="80"><?php echo $TEXT['TITLE']; ?>:</td>
	<td>
		<input type="text" name="title" value="<?php echo (htmlspecialchars($fetch_content['title'])); ?>" style="width: 98%;" maxlength="255" />
	</td>
</tr>
<?php if(isset($use_images) && $use_images == TRUE){ ?>
<tr>
	<td><?php echo $TEXT['IMAGE']; ?>:</td>
	<?php if(file_exists(WB_PATH.MEDIA_DIRECTORY.'/.news/image'.$group_id.'.jpg')) { ?>
	<td>
		<a href="<?php echo WB_URL.MEDIA_DIRECTORY; ?>/.news/image<?php echo $group_id; ?>.jpg" title="<?php echo $TEXT['VIEW']; ?>" target="_blank">
		<img class="image_preview" src="<?php echo WB_URL.MEDIA_DIRECTORY; ?>/.news/image<?php echo $group_id; ?>.jpg" alt="<?php echo $TEXT['VIEW']; ?>" />		
		</a>
		&nbsp;
		<input type="checkbox" name="delete_image" id="delete_image" value="true" />
		<label for="delete_image"><?php echo $TEXT['DELETE']; ?></label></label>
	</td>
	<?php } else { ?>
	<td>
		<input type="file" name="image" />
	</td>
	<?php } ?>
</tr>
<?php } ?>
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
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
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

<?php

// Print admin footer
$admin->print_footer();

?>