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
 *  @version        $Id: save_post.php 1172 2011-10-04 15:26:26Z frankh $
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



require_once(WB_PATH."/include/jscalendar/jscalendar-functions.php");

// Get id
if(!isset($_POST['post_id']) OR !is_numeric($_POST['post_id']))
{
	header("Location: ".ADMIN_URL."/pages/index.php");
	exit( 0 );
}
else
{
	$id = $_POST['post_id'];
	$post_id = $id;
}

function create_file($filename, $filetime=NULL )
{
global $page_id, $section_id, $post_id;

	// We need to create a new file
	// First, delete old file if it exists
	if(file_exists(WB_PATH.PAGES_DIRECTORY.$filename.PAGE_EXTENSION))
    {
        $filetime = isset($filetime) ? $filetime :  filemtime($filename);
		unlink(WB_PATH.PAGES_DIRECTORY.$filename.PAGE_EXTENSION);
	}
    else {
        $filetime = isset($filetime) ? $filetime : time();
    }
	// The depth of the page directory in the directory hierarchy
	// '/pages' is at depth 1
	$pages_dir_depth = count(explode('/',PAGES_DIRECTORY))-1;
	// Work-out how many ../'s we need to get to the index page
	$index_location = '../';
	for($i = 0; $i < $pages_dir_depth; $i++)
    {
		$index_location .= '../';
	}

	// Write to the filename
	$content = ''.
'<?php
$page_id = '.$page_id.';
$section_id = '.$section_id.';
$post_id = '.$post_id.';
define("POST_SECTION", $section_id);
define("POST_ID", $post_id);
require("'.$index_location.'config.php");
require(WB_PATH."/index.php");
?>';
	if($handle = fopen($filename, 'w+'))
    {
    	fwrite($handle, $content);
    	fclose($handle);
        if($filetime)
        {
        touch($filename, $filetime);
        }
    	change_mode($filename);
    }

}

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(WB_PATH.'/modules/admin.php');

// Validate all fields
if($admin->get_post('title') == '' AND $admin->get_post('url') == '')
{
	$admin->print_error($MESSAGE['GENERIC']['FILL_IN_ALL'], WB_URL.'/modules/news/modify_post.php?page_id='.$page_id.'&section_id='.$section_id.'&post_id='.$id);
}
else
{
	$title = $admin->get_post_escaped('title');
	$short = $admin->get_post_escaped('short');
	$long = $admin->get_post_escaped('long');
	$commenting = $admin->get_post_escaped('commenting');
	$active = $admin->get_post_escaped('active');
	$old_link = $admin->get_post_escaped('link');
	$group_id = $admin->get_post_escaped('group');
}

// Get page link URL
$query_page = $database->query("SELECT level,link FROM ".TABLE_PREFIX."pages WHERE page_id = '$page_id'");
$page = $query_page->fetchRow();
$page_level = $page['level'];
$page_link = $page['link'];

// Include WB functions file
require(WB_PATH.'/framework/functions.php');

// Work-out what the link should be
$post_link = '/posts/'.page_filename($title).PAGE_SPACER.$post_id;

// Make sure the post link is set and exists
// Make news post access files dir
make_dir(WB_PATH.PAGES_DIRECTORY.'/posts/');
$file_create_time = '';
if(!is_writable(WB_PATH.PAGES_DIRECTORY.'/posts/'))
{
	$admin->print_error($MESSAGE['PAGES']['CANNOT_CREATE_ACCESS_FILE']);
}
elseif(($old_link != $post_link) OR !file_exists(WB_PATH.PAGES_DIRECTORY.$post_link.PAGE_EXTENSION))
{
	// We need to create a new file
	// First, delete old file if it exists
	if(file_exists(WB_PATH.PAGES_DIRECTORY.$old_link.PAGE_EXTENSION))
    {
        $file_create_time = filemtime(WB_PATH.PAGES_DIRECTORY.$old_link.PAGE_EXTENSION);
		unlink(WB_PATH.PAGES_DIRECTORY.$old_link.PAGE_EXTENSION);
	}

    // Specify the filename
    $filename = WB_PATH.PAGES_DIRECTORY.'/'.$post_link.PAGE_EXTENSION;
    create_file($filename, $file_create_time);
}

// get publisedwhen and publisheduntil
$publishedwhen = jscalendar_to_timestamp($admin->get_post_escaped('publishdate'));
if($publishedwhen == '' || $publishedwhen < 1)
	$publishedwhen=time();
$publisheduntil = jscalendar_to_timestamp($admin->get_post_escaped('enddate'), $publishedwhen);
if($publisheduntil == '' || $publisheduntil < 1)
	$publisheduntil=0;

// Update row
$database->query("UPDATE ".TABLE_PREFIX."mod_news_posts SET group_id = '$group_id', title = '$title', link = '$post_link', content_short = '$short', content_long = '$long', commenting = '$commenting', active = '$active', published_when = '$publishedwhen', published_until = '$publisheduntil', posted_when = '".time()."', posted_by = '".$admin->get_user_id()."' WHERE post_id = '$post_id'");

// Check if the user uploaded an image or wants to delete one
if(isset($_FILES['newspic']['tmp_name']) AND $_FILES['newspic']['tmp_name'] != '')
{
	// Get real filename and set new filename
	$filename = $_FILES['newspic']['name'];
	$new_filename = WB_PATH.MEDIA_DIRECTORY.'/newspics/image'.$post_id.'.jpg';
	// Make sure the image is a jpg file
	$file4=substr($filename, -4, 4);
	if(($file4 != '.jpg')and($file4 != '.JPG')and($file4 != '.png')and($file4 != '.PNG') and ($file4 !='jpeg') and ($file4 != 'JPEG'))
    {
		$admin->print_error($MESSAGE['GENERIC']['FILE_TYPE'].' JPG (JPEG) or PNG a');
	} elseif(
	(($_FILES['newspic']['type']) != 'image/jpeg' AND mime_content_type($_FILES['newspic']['tmp_name']) != 'image/jpg')
	and
	(($_FILES['newspic']['type']) != 'image/png' AND mime_content_type($_FILES['newspic']['tmp_name']) != 'image/png')
	){
		$admin->print_error($MESSAGE['GENERIC']['FILE_TYPE'].' JPG (JPEG) or PNG b');
	}
  
	// Upload image
	move_uploaded_file($_FILES['newspic']['tmp_name'], $new_filename);
	// Check if we need to create a thumb
	$query_settings = $database->query("SELECT resize FROM ".TABLE_PREFIX."mod_news_settings WHERE section_id = '$section_id'");
	$fetch_settings = $query_settings->fetchRow();
	$resize = $fetch_settings['resize'];
	if($resize != 0)
    {
		// Resize the image
		$thumb_location = WB_PATH.MEDIA_DIRECTORY.'/newspics/thumb'.$post_id.'.jpg';
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
	if(file_exists(WB_PATH.MEDIA_DIRECTORY.'/newspics/image'.$post_id.'.jpg'))
    {
		unlink(WB_PATH.MEDIA_DIRECTORY.'/newspics/image'.$post_id.'.jpg');
	}
}

// Check if there is a db error, otherwise say successful
if($database->is_error())
{
	$admin->print_error($database->get_error(), WB_URL.'/modules/news/modify_post.php?page_id='.$page_id.'&section_id='.$section_id.'&post_id='.$admin->getIDKEY($id));
}
else
{
	$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();

?>