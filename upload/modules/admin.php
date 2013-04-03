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
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 * @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
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
$page_id = $val->get('_REQUEST','page_id','numeric');

if ( ! $page_id )
{
	header("Location: index.php");
	exit(0);
}

$section_id = $val->get('_REQUEST','section_id','numeric');

// Get section id if there is one
if ( ! $section_id )
{
	// Check if we should redirect the user if there is no section id
	if(!isset($section_required))
	{
		$section_id = 0;
	} else {
		header("Location: $section_required");
		exit(0);
	}
}

// Create js back link
$js_back = 'javascript: history.go(-1);';

// Create new admin object
include(CAT_PATH.'/framework/class.admin.php');
// header will be set here, see database->is_error
$admin = new admin('Pages', 'pages_modify');

// Get perms
// $database = new database();
$sql  = 'SELECT `admin_groups`,`admin_users` FROM `'.CAT_TABLE_PREFIX.'pages` ';
$sql .= 'WHERE `page_id` = '.intval($page_id);

$res_pages = $database->query($sql);
$rec_pages = $res_pages->fetchRow();

$old_admin_groups = explode(',', str_replace('_', '', $rec_pages['admin_groups']));
$old_admin_users  = explode(',', str_replace('_', '', $rec_pages['admin_users']));

$in_group = FALSE;
foreach($admin->get_groups_id() as $cur_gid)
{
    if (in_array($cur_gid, $old_admin_groups))
	{
        $in_group = TRUE;
    }
}
if((!$in_group) && !is_numeric(array_search($admin->get_user_id(), $old_admin_users)))
{
	$admin->print_error($MESSAGE['PAGES']['INSUFFICIENT_PERMISSIONS']);
}

// some additional security checks:
// Check whether the section_id belongs to the page_id at all
if ($section_id != 0) {
	$sql  = "SELECT `module` FROM `".CAT_TABLE_PREFIX."sections` WHERE `page_id` = '$page_id' AND `section_id` = '$section_id'";
	$res_sec = $database->query($sql);
	if ($database->is_error())
	{
		$admin->print_error($database->get_error());
	}
	if ($res_sec->numRows() == 0)
	{
		$admin->print_error($MESSAGE['PAGES']['NOT_FOUND']);
	}

	// check module permissions:
	$sec = $res_sec->fetchRow( MYSQL_ASSOC );
	if (!$admin->get_permission($sec['module'], 'module'))
	{
		$admin->print_error($MESSAGE['PAGES']['INSUFFICIENT_PERMISSIONS']);
	}	
}

// Workout if the developer wants to show the info banner
if(isset($print_info_banner) && $print_info_banner == true)
{
	// Get page details
	// $database = new database(); not needed
	$sql  = 'SELECT `page_id`,`page_title`,`modified_by`,`modified_when` FROM `'.CAT_TABLE_PREFIX.'pages` ';
	$sql .= 'WHERE `page_id` = '.intval($page_id);
	$res_pages = $database->query($sql);
	if($database->is_error())
	{
		// $admin->print_header();  don't know why
		$admin->print_error($database->get_error());
	}
	if($res_pages->numRows() == 0)
	{
		// $admin->print_header();   don't know why
		$admin->print_error($MESSAGE['PAGES']['NOT_FOUND']);
	} else {
		$rec_pages = $res_pages->fetchRow();
	}

	// Get display name of person who last modified the page
	$user = $admin->get_user_details($rec_pages['modified_by']);

	// Convert the unix ts for modified_when to human a readable form
	if($rec_pages['modified_when'] != 0)
	{
		$modified_ts = date(TIME_FORMAT.', '.DATE_FORMAT, $rec_pages['modified_when']);
	} else {
		$modified_ts = 'Unknown';
	}

	// Include page info script
	$template = new Template(CAT_THEME_PATH.'/templates');
	$template->set_file('page', 'pages_modify.htt');
	$template->set_block('page', 'main_block', 'main');
	$template->set_var(array(
				'PAGE_ID' => $rec_pages['page_id'],
				'PAGE_TITLE' => ($rec_pages['page_title']),
				'MODIFIED_BY' => $user['display_name'],
				'MODIFIED_BY_USERNAME' => $user['username'],
				'MODIFIED_WHEN' => $modified_ts,
				'CAT_ADMIN_URL' => CAT_ADMIN_URL
				));

	$template->set_block('main_block', 'show_modify_block', 'show_modify');
	if($modified_ts == 'Unknown')
	{
    	$template->set_block('show_modify', '');
		$template->set_var('CLASS_DISPLAY_MODIFIED', 'hide');
	} else {
		$template->set_var('CLASS_DISPLAY_MODIFIED', '');
    	$template->parse('show_modify', 'show_modify_block', true);
	}

	$template->set_block('main_block', 'show_section_block', 'show_section');
	// Work-out if we should show the "manage sections" link
    $sql  = 'SELECT `section_id` FROM `'.CAT_TABLE_PREFIX.'sections` ';
	$sql .= 'WHERE `page_id` = '.intval($page_id).' AND `module` = "menu_link"';
	if( ( $res_sections = $database->query($sql) ) && ($database->is_error() == false ) )
	{
		if($res_sections->numRows() > 0)
		{
			$template->set_block('show_section', '');
			$template->set_var('DISPLAY_MANAGE_SECTIONS', 'none');
		}elseif(MANAGE_SECTIONS == 'enabled')
		{
			$template->set_var('TEXT_MANAGE_SECTIONS', $HEADING['MANAGE_SECTIONS']);
    		$template->parse('show_section', 'show_section_block', true);
		}else {
			$template->set_block('show_section', '');
			$template->set_var('DISPLAY_MANAGE_SECTIONS', 'none');
		}
	} else {
		$admin->print_error($database->get_error());
	}

	// Insert language TEXT
	$template->set_var(array(
				'TEXT_CURRENT_PAGE' => $TEXT['CURRENT_PAGE'],
				'TEXT_CHANGE' => $TEXT['CHANGE'],
				'LAST_MODIFIED' => $MESSAGE['PAGES']['LAST_MODIFIED'],
				'TEXT_CHANGE_SETTINGS' => $TEXT['CHANGE_SETTINGS'],
				'HEADING_MODIFY_PAGE' => $HEADING['MODIFY_PAGE']
				));

	// Parse and print header template
	$template->parse('main', 'main_block', false);
	$template->pparse('output', 'page');
}

// Work-out if the developer wants us to update the timestamp for when the page was last modified
if(isset($update_when_modified) && $update_when_modified == true)
{
	$sql  = 'UPDATE `'.CAT_TABLE_PREFIX.'pages` ';
	$sql .= 'SET `modified_when` = '.time().', ';
	$sql .=     '`modified_by`   = '.intval($admin->get_user_id()).' ';
	$sql .=     'WHERE page_id   = '.intval($page_id);
	$database->query($sql);
}

?>