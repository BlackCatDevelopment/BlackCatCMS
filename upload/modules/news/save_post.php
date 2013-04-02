<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *  @author         Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos)
 *  @copyright      2004-2011, Ryan Djurovich, Rob Smith, Dietrich Roland Pehlke, Christian M. Stefan (Stefek), Jurgen Nijhuis (Argos) 
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         news
 *
 */

if (defined('CAT_PATH')) {	
    if (defined('CAT_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
    include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));    $dir = $_SERVER['DOCUMENT_ROOT'];
    $inc = false;
    foreach ($subs as $sub) {
        if (empty($sub)) continue; $dir .= '/'.$sub;
        if (file_exists($dir.'/framework/class.secure.php')) {
            include($dir.'/framework/class.secure.php'); $inc = true;    break;
	}
	}
    if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

$val     = CAT_Helper_Validate::getInstance();
$dir     = CAT_Helper_Directory::getInstance();
$post_id = $val->sanitizePost('post_id','numeric');

// Get id
if (!$post_id)
{
    header("Location: " . CAT_ADMIN_URL . "/pages/index.php");
    exit(0);
}

// Include WB admin wrapper script
$update_when_modified = true; // Tells script to update when this page was last updated
require(CAT_PATH . '/modules/admin.php');

// Validate all fields
if ($val->sanitizePost('title') == '' AND $val->sanitizePost('url') == '')
{
    $admin->print_error($admin->lang->translate('Please go back and fill-in all fields'), CAT_URL . '/modules/news/modify_post.php?page_id=' . $page_id . '&section_id=' . $section_id . '&post_id=' . $id);
}
else
{
    $title      = $val->sanitizePost('title',NULL,true);
    $short      = $val->sanitizePost('short',NULL,true);
    $long       = $val->sanitizePost('long',NULL,true);
    $commenting = $val->sanitizePost('commenting',NULL,true);
    $active     = $val->sanitizePost('active',NULL,true);
    $old_link   = $val->sanitizePost('link',NULL,true);
    $group_id   = $val->sanitizePost('group',NULL,true);
}

// Get page link URL
$query_page = $database->query("SELECT level,link FROM " . CAT_TABLE_PREFIX . "pages WHERE page_id = '$page_id'");
$page = $query_page->fetchRow();
$page_level = $page['level'];
$page_link = $page['link'];

// Include WB functions file
require(CAT_PATH . '/framework/functions.php');

// Work-out what the link should be
$post_link = '/posts/' . page_filename($title) . PAGE_SPACER . $post_id;

// Make sure the post link is set and exists
// Make news post access files dir
$dir->createDirectory(CAT_PATH . PAGES_DIRECTORY . '/posts/');
$file_create_time = '';
if (!is_writable(CAT_PATH . PAGES_DIRECTORY . '/posts/'))
{
    $admin->print_error('Error creating access file in the pages directory(page), (insufficient privileges)');
}
elseif (($old_link != $post_link) OR !file_exists(CAT_PATH . PAGES_DIRECTORY . $post_link . PAGE_EXTENSION))
{
	// We need to create a new file
	// First, delete old file if it exists
    if (file_exists(CAT_PATH . PAGES_DIRECTORY . $old_link . PAGE_EXTENSION))
    {
        $file_create_time = filemtime(CAT_PATH . PAGES_DIRECTORY . $old_link . PAGE_EXTENSION);
        unlink(CAT_PATH . PAGES_DIRECTORY . $old_link . PAGE_EXTENSION);
	}

    // Specify the filename
    $filename = CAT_PATH . PAGES_DIRECTORY . '/' . $post_link . PAGE_EXTENSION;
    create_file($dir, $filename, $file_create_time);
}

// get publisedwhen and publisheduntil
$publishedwhen = jscalendar_to_timestamp($val->sanitizePost('publishdate',NULL,true));
if ($publishedwhen == '' || $publishedwhen < 1)
    $publishedwhen = time();
$publisheduntil = jscalendar_to_timestamp($val->sanitizePost('enddate',NULL,true), $publishedwhen);
if ($publisheduntil == '' || $publisheduntil < 1)
    $publisheduntil = 0;

// Update row
$database->query("UPDATE " . CAT_TABLE_PREFIX . "mod_news_posts SET group_id = '$group_id', title = '$title', link = '$post_link', content_short = '$short', content_long = '$long', commenting = '$commenting', active = '$active', published_when = '$publishedwhen', published_until = '$publisheduntil', posted_when = '" . time() . "', posted_by = '" . $admin->get_user_id() . "' WHERE post_id = '$post_id'");

// Check if the user uploaded an image or wants to delete one
if (isset($_FILES['newspic']['tmp_name']) AND $_FILES['newspic']['tmp_name'] != '')
{
	// Get real filename and set new filename
	$filename = $_FILES['newspic']['name'];
    $new_filename = CAT_PATH . MEDIA_DIRECTORY . '/newspics/image' . $post_id . '.jpg';
	// Make sure the image is a jpg file
    $file4        = substr($filename, -4, 4);
    if (($file4 != '.jpg') and ($file4 != '.JPG') and ($file4 != '.png') and ($file4 != '.PNG') and ($file4 != 'jpeg') and ($file4 != 'JPEG'))
    {
        $admin->print_error($MESSAGE['GENERIC']['FILE_TYPE'] . ' JPG (JPEG) or PNG a');
    }
    elseif ((($_FILES['newspic']['type']) != 'image/jpeg' AND mime_content_type($_FILES['newspic']['tmp_name']) != 'image/jpg') and (($_FILES['newspic']['type']) != 'image/png' AND mime_content_type($_FILES['newspic']['tmp_name']) != 'image/png'))
    {
        $admin->print_error($MESSAGE['GENERIC']['FILE_TYPE'] . ' JPG (JPEG) or PNG b');
	}
  
	// Upload image
	move_uploaded_file($_FILES['newspic']['tmp_name'], $new_filename);
	// Check if we need to create a thumb
    $query_settings = $database->query("SELECT resize FROM " . CAT_TABLE_PREFIX . "mod_news_settings WHERE section_id = '$section_id'");
	$fetch_settings = $query_settings->fetchRow();
	$resize = $fetch_settings['resize'];
    if ($resize != 0)
    {
		// Resize the image
        $thumb_location = CAT_PATH . MEDIA_DIRECTORY . '/newspics/thumb' . $post_id . '.jpg';
        if (make_thumb($new_filename, $thumb_location, $resize))
        {
			// Delete the actual image and replace with the resized version
			unlink($new_filename);
			rename($thumb_location, $new_filename);
		}
	}
}

if (isset($_POST['delete_image']) AND $_POST['delete_image'] != '')
{
	// Try unlinking image
    if (file_exists(CAT_PATH . MEDIA_DIRECTORY . '/newspics/image' . $post_id . '.jpg'))
    {
        unlink(CAT_PATH . MEDIA_DIRECTORY . '/newspics/image' . $post_id . '.jpg');
	}
}

// Check if there is a db error, otherwise say successful
if ($database->is_error())
{
    $admin->print_error($database->get_error(), CAT_URL . '/modules/news/modify_post.php?page_id=' . $page_id . '&section_id=' . $section_id . '&post_id=' . $admin->getIDKEY($id));
}
else
{
    $admin->print_success($TEXT['SUCCESS'], CAT_ADMIN_URL . '/pages/modify.php?page_id=' . $page_id);
}

// Print admin footer
$admin->print_footer();








function create_file($dir,$filename, $filetime = NULL)
{
    global $page_id, $section_id, $post_id;

    // We need to create a new file
    // First, delete old file if it exists
    if (file_exists(CAT_PATH . PAGES_DIRECTORY . $filename . PAGE_EXTENSION))
    {
        $filetime = isset($filetime) ? $filetime : filemtime($filename);
        unlink(CAT_PATH . PAGES_DIRECTORY . $filename . PAGE_EXTENSION);
    }
    else
    {
        $filetime = isset($filetime) ? $filetime : time();
    }
    // The depth of the page directory in the directory hierarchy
    // '/pages' is at depth 1
    $pages_dir_depth = count(explode('/', PAGES_DIRECTORY)) - 1;
    // Work-out how many ../'s we need to get to the index page
    $index_location  = '../';
    for ($i = 0; $i < $pages_dir_depth; $i++)
    {
        $index_location .= '../';
    }

    // Write to the filename
    $content = '<'.'?'.'php
    $page_id    = ' . $page_id . ';
    $section_id = ' . $section_id . ';
    $post_id    = ' . $post_id . ';
    define("POST_SECTION", $section_id);
    define("POST_ID", $post_id);
    require("' . $index_location . 'config.php");
    require(CAT_PATH."/index.php");
?>';

    if ($handle = fopen($filename, 'w+'))
    {
        fwrite($handle, $content);
        fclose($handle);
        if ($filetime)
        {
            touch($filename, $filetime);
        }
        $dir->setPerms($filename);
    }

}

// convert string from jscalendar to timestamp.
// converts dd.mm.yyyy and mm/dd/yyyy, with or without time.
// strtotime() may fails with e.g. "dd.mm.yyyy" and PHP4
function jscalendar_to_timestamp($str, $offset='') {
	$str = trim($str);
	if($str == '0' || $str == '')
		return('0');
	if($offset == '0')
		$offset = '';
	// convert to yyyy-mm-dd
	// "dd.mm.yyyy"?
	if(preg_match('/^\d{1,2}\.\d{1,2}\.\d{2}(\d{2})?/', $str)) {
		$str = preg_replace('/^(\d{1,2})\.(\d{1,2})\.(\d{2}(\d{2})?)/', '$3-$2-$1', $str);
	}
	// "mm/dd/yyyy"?
	if(preg_match('#^\d{1,2}/\d{1,2}/(\d{2}(\d{2})?)#', $str)) {
		$str = preg_replace('#^(\d{1,2})/(\d{1,2})/(\d{2}(\d{2})?)#', '$3-$1-$2', $str);
	}
	// use strtotime()
	if($offset!='')
		return(strtotime($str, $offset));
	else
	return(strtotime($str));
}