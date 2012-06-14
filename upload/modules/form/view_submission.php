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

require_once('../../config.php');

// Get id
if(!isset($_GET['submission_id']) OR !is_numeric($_GET['submission_id'])) {
	header("Location: ".ADMIN_URL."/pages/index.php");
	exit(0);
} else {
	$submission_id = $_GET['submission_id'];
}

// Include WB admin wrapper script
require(WB_PATH.'/modules/admin.php');

global $database;
global $TEXT;
global $page_id;
global $section_id;
global $admin;

// Get submission details
$query_content = $database->query("SELECT * FROM ".TABLE_PREFIX."mod_form_submissions WHERE submission_id = '$submission_id'");
$submission = $query_content->fetchRow();

// Get the user details of whoever did this submission
$query_user = "SELECT username,display_name FROM ".TABLE_PREFIX."users WHERE user_id = '".$submission['submitted_by']."'";
$get_user = $database->query($query_user);
if($get_user->numRows() != 0) {
	$user = $get_user->fetchRow();
} else {
	$user['display_name'] = 'Unknown';
	$user['username'] = 'unknown';
}

?>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td><?php echo $TEXT['SUBMISSION_ID']; ?>:</td>
	<td><?php echo $submission['submission_id']; ?></td>
</tr>
<tr>
	<td><?php echo $TEXT['SUBMITTED']; ?>:</td>
	<td><?php echo date(TIME_FORMAT.', '.DATE_FORMAT, $submission['submitted_when']); ?></td>
<tr>
	<td><?php echo $TEXT['USER']; ?>:</td>
	<td><?php echo $user['display_name'].' ('.$user['username'].')'; ?></td>
</tr>
<tr>
	<td colspan="2">
		<hr />
	</td>
</tr>
<tr>
	<td colspan="2">
		<?php echo nl2br($submission['body']); ?>
	</td>
</tr>
</table>

<br />

<input type="button" value="<?php echo $TEXT['CLOSE']; ?>" onclick="javascript: window.location = '<?php echo ADMIN_URL; ?>/pages/modify.php?page_id=<?php echo $page_id; ?>';" style="width: 150px; margin-top: 5px;" />
<input type="button" value="<?php echo $TEXT['DELETE']; ?>" onclick="javascript: confirm_link('<?php echo $TEXT['ARE_YOU_SURE']; ?>', '<?php echo WB_URL; ?>/modules/form/delete_submission.php?page_id=<?php echo $page_id; ?>&section_id=<?php echo $section_id; ?>&submission_id=<?php echo $submission_id; ?>');" style="width: 150px; margin-top: 5px;" />
<?php

// Print admin footer
$admin->print_footer();

?>