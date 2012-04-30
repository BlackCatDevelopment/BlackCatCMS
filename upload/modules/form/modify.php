<?php

/**
 *  @author         Ryan Djurovich
 *  @author         Rudolph Lartey
 *  @author         John Maats
 *  @author         Dietrich Roland Pehlke
 *  @copyright      2004-2011 Ryan Djurovich, Rudolph Lartey, John Maats, Dietrich Roland Pehlke
 *  @license        see info.php of this module
 *  @todo           separate HTML from code, in addition the used HTML is no longer 
 *                  valid and uses deprecated attributes i.e. cellpadding a.s.o.
 *  @version        $Id$
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	include(WB_PATH.'/framework/class.secure.php'); 
} else {
	$root = "../";
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) { 
		include($root.'/framework/class.secure.php'); 
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php

global $database;
global $TEXT;
global $page_id;
global $section_id;

//overwrite php.ini on Apache servers for valid SESSION ID Separator
if(function_exists('ini_set')) {
	ini_set('arg_separator.output', '&amp;');
}

//Delete all form fields with no title
$database->query("DELETE FROM ".TABLE_PREFIX."mod_form_fields  WHERE page_id = '$page_id' and section_id = '$section_id' and title=''");

?>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td align="left" width="33%">
		<input type="button" value="<?php echo $TEXT['ADD'].' '.$TEXT['FIELD']; ?>" onclick="javascript: window.location = '<?php echo WB_URL; ?>/modules/form/add_field.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>';" style="width: 100%;" />
	</td>
	<td align="right" width="33%">
		<input type="button" value="<?php echo $TEXT['SETTINGS']; ?>" onclick="javascript: window.location = '<?php echo WB_URL; ?>/modules/form/modify_settings.php?page_id=<?php echo $page_id; ?>&amp;section_id=<?php echo $section_id; ?>';" style="width: 100%;" />
	</td>
</tr>
</table>

<br />

<h2><?php echo $TEXT['MODIFY'].'/'.$TEXT['DELETE'].' '.$TEXT['FIELD']; ?></h2>
<?php

// Loop through existing fields
$query_fields = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_form_fields` WHERE section_id = '$section_id' ORDER BY position ASC");
if($query_fields->numRows() > 0) {
	$num_fields = $query_fields->numRows();
	$row = 'a';
	?>
	<table cellpadding="2" cellspacing="0" border="0" width="100%">
	<?php
	while(false !== ($field = $query_fields->fetchRow())) {
		?>
		<tr class="row_<?php echo $row; ?>">
			<td width="20" style="padding-left: 5px;">
				<a href="<?php echo WB_URL; ?>/modules/form/modify_field.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&field_id=<?php echo $field['field_id']; ?>" title="<?php echo $TEXT['MODIFY']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/modify_16.png" border="0" alt="^" />
				</a>
			</td>		
			<td>
				<a href="<?php echo WB_URL; ?>/modules/form/modify_field.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&field_id=<?php echo $field['field_id']; ?>">
					<?php echo $field['title']; ?>
				</a>
			</td>
			<td width="175">
				<?php
				echo $TEXT['TYPE'].':';
				if($field['type'] == 'textfield') {
					echo $TEXT['SHORT_TEXT'];
				} elseif($field['type'] == 'textarea') {
					echo $TEXT['LONG_TEXT'];
				} elseif($field['type'] == 'heading') {
					echo $TEXT['HEADING'];
				} elseif($field['type'] == 'select') {
					echo $TEXT['SELECT_BOX'];
				} elseif($field['type'] == 'checkbox') {
					echo $TEXT['CHECKBOX_GROUP'];
				} elseif($field['type'] == 'radio') {
					echo $TEXT['RADIO_BUTTON_GROUP'];
				} elseif($field['type'] == 'email') {
					echo $TEXT['EMAIL_ADDRESS'];
				}
				?>
			</td>
			<td width="95">		
			<?php 
			if ($field['type'] != 'group_begin') {
				echo $TEXT['REQUIRED'].': '; if($field['required'] == 1) { echo $TEXT['YES']; } else { echo $TEXT['NO']; }
			}
			?>
			</td>
			<td width="110">
			<?php
			if ($field['type'] == 'select') {
				$field['extra'] = explode(',',$field['extra']);
				echo $TEXT['MULTISELECT'].': '; if($field['extra'][1] == 'multiple') { echo $TEXT['YES']; } else { echo $TEXT['NO']; }
			}
			?>
			</td>
			<td width="20">
			<?php if($field['position'] != 1) { ?>
				<a href="<?php echo WB_URL; ?>/modules/form/move_up.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&field_id=<?php echo $field['field_id']; ?>" title="<?php echo $TEXT['MOVE_UP']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/up_16.png" border="0" alt="^" />
				</a>
			<?php } ?>
			</td>
			<td width="20">
			<?php if($field['position'] != $num_fields) { ?>
				<a href="<?php echo WB_URL; ?>/modules/form/move_down.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&field_id=<?php echo $field['field_id']; ?>" title="<?php echo $TEXT['MOVE_DOWN']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/down_16.png" border="0" alt="v" />
				</a>
			<?php } ?>
			</td>
			<td width="20">
				<a href="javascript: confirm_link('<?php echo $TEXT['ARE_YOU_SURE']; ?>', '<?php echo WB_URL; ?>/modules/form/delete_field.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&field_id=<?php echo $field['field_id']; ?>');" title="<?php echo $TEXT['DELETE']; ?>">
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

?>

<br /><br />

<h2><?php echo $TEXT['SUBMISSIONS']; ?></h2>

<?php

// Query submissions table
$query_submissions = $database->query("SELECT * FROM `".TABLE_PREFIX."mod_form_submissions` WHERE section_id = '$section_id' ORDER BY submitted_when ASC");
if($query_submissions->numRows() > 0) {
	?>
	<table cellpadding="2" cellspacing="0" border="0" width="100%">
	<?php
	// List submissions
	$row = 'a';
	while(false !== ($submission = $query_submissions->fetchRow())) {
		?>
		<tr class="row_<?php echo $row; ?>">
			<td width="20" style="padding-left: 5px;">
				<a href="<?php echo WB_URL; ?>/modules/form/view_submission.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&submission_id=<?php echo $submission['submission_id']; ?>" title="<?php echo $TEXT['OPEN']; ?>">
					<img src="<?php echo THEME_URL; ?>/images/folder_16.png" alt="<?php echo $TEXT['OPEN']; ?>" border="0" />
				</a>
			</td>
			<td width="237"><?php echo $TEXT['SUBMISSION_ID'].': '.$submission['submission_id']; ?></td>
			<td><?php echo $TEXT['SUBMITTED'].': '.date(TIME_FORMAT.', '.DATE_FORMAT, $submission['submitted_when']); ?></td>
			<td width="20">
				<a href="javascript: confirm_link('<?php echo $TEXT['ARE_YOU_SURE']; ?>', '<?php echo WB_URL; ?>/modules/form/delete_submission.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&submission_id=<?php echo $submission['submission_id']; ?>');" title="<?php echo $TEXT['DELETE']; ?>">
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

?>
