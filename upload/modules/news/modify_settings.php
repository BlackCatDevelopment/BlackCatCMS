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



// Include WB admin wrapper script
require(WB_PATH.'/modules/admin.php');

// include core functions of WB 2.7 to edit the optional module CSS files (frontend.css, backend.css)
@include_once(WB_PATH .'/framework/module.functions.php');

// check if module language file exists for the language set by the user (e.g. DE, EN)
if(!file_exists(WB_PATH .'/modules/news/languages/'.LANGUAGE .'.php')) {
	// no module language file exists for the language set by the user, include default module language file EN.php
	require_once(WB_PATH .'/modules/news/languages/EN.php');
} else {
	// a module language file exists for the language defined by the user, load it
	require_once(WB_PATH .'/modules/news/languages/'.LANGUAGE .'.php');
}

// Get header and footer
$query_content = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_news_settings WHERE section_id = '$section_id'");
$fetch_content = $query_content->fetchRow();

// Set raw html <'s and >'s to be replace by friendly html code
$raw = array('<', '>');
$friendly = array('&lt;', '&gt;');

// check if backend.css file needs to be included into the <body></body> of modify.php
if(!method_exists($admin, 'register_backend_modfiles') && file_exists(WB_PATH ."/modules/form/backend.css")) {
	echo '<style type="text/css">';
	include(WB_PATH .'/modules/form/backend.css');
	echo "\n</style>\n";
}

?>
<h2><?php echo $MOD_NEWS['SETTINGS']; ?></h2>
<?php
// include the button to edit the optional module CSS files (function added with WB 2.7)
// Note: CSS styles for the button are defined in backend.css (div class="mod_moduledirectory_edit_css")
// Place this call outside of any <form></form> construct!!!
if(function_exists('edit_module_css'))
{
	edit_module_css('news');
}
?>

<form name="modify" action="<?php echo WB_URL; ?>/modules/news/save_settings.php" method="post" style="margin: 0;">

	<input type="hidden" name="section_id" value="<?php echo $section_id; ?>" />
	<input type="hidden" name="page_id" value="<?php echo $page_id; ?>" />
<?php
$leptoken_add = (isset($_GET['leptoken']) ? $_GET['leptoken'] : "");
if ( strlen( $leptoken_add ) > 0 ) {
	echo "\n\t<input type='hidden' name='leptoken' value='".$leptoken_add."' />\n";
} ?>
	<table class="row_a" cellpadding="2" cellspacing="0" width="100%">
		<tr>
			<td colspan="2"><strong><?php echo $HEADING['GENERAL_SETTINGS']; ?></strong></td>
		</tr>
		<tr>
			<td class="setting_name"><?php echo $TEXT['HEADER']; ?>:</td>
			<td class="setting_value">
				<textarea name="header" rows="10" cols="1" style="width: 98%; height: 80px;"><?php echo ($fetch_content['header']); ?></textarea>
			</td>
		</tr>
		<tr>
			<td class="setting_name"><?php echo $TEXT['POST'].' '.$TEXT['LOOP']; ?>:</td>
			<td class="setting_value">
				<textarea name="post_loop" rows="10" cols="1" style="width: 98%; height: 60px;"><?php echo ($fetch_content['post_loop']); ?></textarea>
			</td>
		</tr>
		<tr>
			<td class="setting_name"><?php echo $TEXT['FOOTER']; ?>:</td>
			<td class="setting_value">
				<textarea name="footer" rows="10" cols="1" style="width: 98%; height: 80px;"><?php echo str_replace($raw, $friendly, ($fetch_content['footer'])); ?></textarea>
			</td>
		</tr>
		<tr>
			<td class="setting_name"><?php echo $TEXT['POST_HEADER']; ?>:</td>
			<td class="setting_value">
				<textarea name="post_header" rows="10" cols="1" style="width: 98%; height: 60px;"><?php echo str_replace($raw, $friendly, ($fetch_content['post_header'])); ?></textarea>
			</td>
		</tr>
		<tr>
			<td class="setting_name"><?php echo $TEXT['POST_FOOTER']; ?>:</td>
			<td class="setting_value">
				<textarea name="post_footer" rows="10" cols="1" style="width: 98%; height: 60px;"><?php echo str_replace($raw, $friendly, ($fetch_content['post_footer'])); ?></textarea>
			</td>
		</tr>
		<tr>
			<td class="setting_name"><?php echo $TEXT['POSTS_PER_PAGE']; ?>:</td>
			<td class="setting_value">
				<select name="posts_per_page" style="width: 98%;">
					<option value=""><?php echo $TEXT['UNLIMITED']; ?></option>
					<?php
					for($i = 1; $i <= 20; $i++) {
						if($fetch_content['posts_per_page'] == ($i*5)) { $selected = ' selected="selected"'; } else { $selected = ''; }
						echo '<option value="'.($i*5).'"'.$selected.'>'.($i*5).'</option>';
					}
					?>
				</select>
			</td>
		</tr>
		<?php if(extension_loaded('gd') AND function_exists('imageCreateFromJpeg')) { /* Make's sure GD library is installed */ ?>
		<tr>
			<td class="setting_name"><?php echo $TEXT['RESIZE_IMAGE_TO']; ?>:</td>
			<td class="setting_value">
				<select name="resize" style="width: 98%;">
					<option value=""><?php echo $TEXT['NONE']; ?></option>
					<?php
					$SIZES['50'] = 'Max. 50px';
					$SIZES['75'] = 'Max. 75px';
					$SIZES['100'] = 'Max. 100px';
					$SIZES['125'] = 'Max. 125px';
					$SIZES['150'] = 'Max. 150px';
					foreach($SIZES AS $size => $size_name) {
						if($fetch_content['resize'] == $size) { $selected = ' selected="selected"'; } else { $selected = ''; }
						echo '<option value="'.$size.'"'.$selected.'>'.$size_name.'</option>';
					}
					?>
				</select>
			</td>
		</tr>
		<?php } ?>    
	</table>
	<table class="row_a" cellpadding="2" cellspacing="0" width="100%" style="margin-top: 3px;">
		<tr>
			<td colspan="2"><strong><?php echo $TEXT['COMMENTS']; ?></strong></td>
		</tr>
		<tr>
			<td class="setting_name"><?php echo $TEXT['COMMENTING']; ?>:</td>
			<td class="setting_value">
				<select name="commenting" style="width: 98%;">
					<option value="none"><?php echo $TEXT['DISABLED']; ?></option>
					<option value="public" <?php if($fetch_content['commenting'] == 'public') { echo ' selected="selected"'; } ?>><?php echo $TEXT['PUBLIC']; ?></option>
					<option value="private" <?php if($fetch_content['commenting'] == 'private') { echo 'selected="selected"'; } ?>><?php echo $TEXT['PRIVATE']; ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="setting_name"><?php echo $TEXT['CAPTCHA_VERIFICATION']; ?>:</td>
			<td>
				<input type="radio" name="use_captcha" id="use_captcha_true" value="1"<?php if($fetch_content['use_captcha'] == true) { echo ' checked="checked"'; } ?> />
				<label for="use_captcha_true"><?php echo $TEXT['ENABLED']; ?></label>
				<input type="radio" name="use_captcha" id="use_captcha_false" value="0"<?php if($fetch_content['use_captcha'] == false) { echo ' checked="checked"'; } ?> />
				<label for="use_captcha_false"><?php echo $TEXT['DISABLED']; ?></label>
			</td>
		</tr>

		<tr>
			<td class="setting_name"><?php echo $TEXT['COMMENTS'].' '.$TEXT['HEADER']; ?>:</td>
			<td class="setting_value">
				<textarea name="comments_header" rows="10" cols="1" style="width: 98%; height: 60px;"><?php echo str_replace($raw, $friendly, ($fetch_content['comments_header'])); ?></textarea>
			</td>
		</tr>
		<tr>
			<td class="setting_name"><?php echo $TEXT['COMMENTS'].' '.$TEXT['LOOP']; ?>:</td>
			<td class="setting_value">
				<textarea name="comments_loop" rows="10" cols="1" style="width: 98%; height: 60px;"><?php echo str_replace($raw, $friendly, ($fetch_content['comments_loop'])); ?></textarea>
			</td>
		</tr>
		<tr>
			<td class="setting_name"><?php echo $TEXT['COMMENTS'].' '.$TEXT['FOOTER']; ?>:</td>
			<td class="setting_value">
				<textarea name="comments_footer" rows="10" cols="1" style="width: 98%; height: 60px;"><?php echo str_replace($raw, $friendly, ($fetch_content['comments_footer'])); ?></textarea>
			</td>
		</tr>
		<tr>
			<td class="setting_name"><?php echo $TEXT['COMMENTS'].' '.$TEXT['PAGE']; ?>:</td>
			<td class="setting_value">
				<textarea name="comments_page" rows="10" cols="1" style="width: 98%; height: 80px;"><?php echo str_replace($raw, $friendly, ($fetch_content['comments_page'])); ?></textarea>
			</td>
		</tr>
	</table>
	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td class="left">
				<input name="save" type="submit" value="<?php echo $TEXT['SAVE']; ?>" style="width: 100px; margin-top: 5px;" />
			</td>
			<td class="right">
				<input type="button" value="<?php echo $TEXT['CANCEL']; ?>" onclick="javascript: window.location = '<?php echo ADMIN_URL; ?>/pages/modify.php?page_id=<?php echo $page_id; ?>';" style="width: 100px; margin-top: 5px;" />
			</td>
		</tr>
	</table>
</form>

<?php

// Print admin footer
$admin->print_footer();

?>