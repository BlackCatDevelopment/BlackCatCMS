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
if(!isset($_POST['group_id']) OR !is_numeric($_POST['group_id']))
{
	header("Location: ".ADMIN_URL."/pages/index.php");
	exit( 0 );
}
else
{
	$group_id = $_POST['group_id'];
}

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(WB_PATH.'/modules/admin.php');

// Include WB functions file
require(WB_PATH.'/framework/functions.php');

// Vagroup_idate all fields
if($admin->get_post('title') == '')
{
	$admin->print_error(
		$MESSAGE['GENERIC']['FILL_IN_ALL'],
		WB_URL.'/modules/news/modify_group.php?page_id='.$page_id.'&section_id='.$section_id.'&group_id='.$group_id
	);
}
else
{
	$title = $admin->get_post_escaped('title');
	$active = $admin->get_post_escaped('active');
}

// Update row
$database->query("UPDATE ".TABLE_PREFIX."mod_news_groups SET title = '$title', active = '$active' WHERE group_id = '$group_id'");

// Check if the user uploaded an image or wants to delete one
if(isset($_FILES['image']['tmp_name']) AND $_FILES['image']['tmp_name'] != '')
{
	// Get real filename and set new filename
	$filename = $_FILES['image']['name'];
	$new_filename = WB_PATH.MEDIA_DIRECTORY.'/.news/image'.$group_id.'.jpg';
	// Make sure the image is a jpg file
	$file4=substr($filename, -4, 4);
	if(($file4 != '.jpg')and($file4 != '.JPG')and($file4 != '.png')and($file4 != '.PNG') and ($file4 !='jpeg') and ($file4 != 'JPEG'))
    {
		$admin->print_error(
			$MESSAGE['GENERIC']['FILE_TYPE'].' JPG (JPEG) or PNG a',
			WB_URL.'/modules/news/modify_group.php?page_id='.$page_id.'&section_id='.$section_id.'&group_id='.$group_id
		);
	} elseif(
	(($_FILES['image']['type']) != 'image/jpeg' AND mime_content_type($_FILES['image']['tmp_name']) != 'image/jpg')
	and
	(($_FILES['image']['type']) != 'image/png' AND mime_content_type($_FILES['image']['tmp_name']) != 'image/png')
	){
		$admin->print_error(
			$MESSAGE['GENERIC']['FILE_TYPE'].' JPG (JPEG) or PNG b',
			WB_URL.'/modules/news/modify_group.php?page_id='.$page_id.'&section_id='.$section_id.'&group_id='.$group_id
		);
	}
	// Make sure the target directory exists
	make_dir(WB_PATH.MEDIA_DIRECTORY.'/.news');
	// Upload image
	move_uploaded_file($_FILES['image']['tmp_name'], $new_filename);
	// Check if we need to create a thumb
	$query_settings = $database->query("SELECT resize FROM ".TABLE_PREFIX."mod_news_settings WHERE section_id = '$section_id'");
	$fetch_settings = $query_settings->fetchRow();
	$resize = $fetch_settings['resize'];
	if($resize != 0)
    {
		// Resize the image
		$thumb_location = WB_PATH.MEDIA_DIRECTORY.'/.news/thumb'.$group_id.'.jpg';
		if(make_thumb($new_filename, $thumb_location, $resize))
        {
			// Delete the actual image and replace with the resized version
			unlink($new_filename);
			rename($thumb_location, $new_filename);
		}
	}
}
if(isset($_POST['delete_image']) AND $_POST['delete_image'] != '')
{
	// Try unlinking image
	if(file_exists(WB_PATH.MEDIA_DIRECTORY.'/.news/image'.$group_id.'.jpg'))
    {
		unlink(WB_PATH.MEDIA_DIRECTORY.'/.news/image'.$group_id.'.jpg');
	}
}

// Check if there is a db error, otherwise say successful
if($database->is_error()) {
	$admin->print_error($database->get_error(), WB_URL.'/modules/news/modify_group.php?page_id='.$page_id.'&section_id='.$section_id.'&group_id='.$group_id);
} else {
	$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();

?>