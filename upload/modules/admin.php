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
	include(CAT_PATH.'/framework/class.secure.php'); 
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

$print_info_banner = true;

$backend = CAT_Backend::getInstance('pages','pages_modify');
$user    = CAT_Users::getInstance();
$val     = CAT_Helper_Validate::getInstance();
$page_id = $val->get('_REQUEST','page_id','numeric');

// for backward compatibility
include CAT_PATH.'/framework/class.admin.php';
$admin = new admin('Pages', 'pages_modify');

if ( ! $page_id )
{
	header("Location: index.php");
	exit(0);
}

// always enable CSRF protection in backend; does not work with
// AJAX so scripts called via AJAX should set this constant
if (!defined('CAT_AJAX_CALL'))
{
    CAT_Helper_Protect::getInstance()->enableCSRFMagic();
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

// Get perms
$sql  = 'SELECT `admin_groups`,`admin_users` FROM `%spages` ';
$sql .= 'WHERE `page_id` = %d';

$res_pages = $backend->db()->query(sprintf($sql,CAT_TABLE_PREFIX,$page_id));
$rec_pages = $res_pages->fetchRow();

$old_admin_groups = explode(',', str_replace('_', '', $rec_pages['admin_groups']));
$old_admin_users  = explode(',', str_replace('_', '', $rec_pages['admin_users']));

$in_group = FALSE;
foreach($user->get_groups_id() as $cur_gid)
{
    if (in_array($cur_gid, $old_admin_groups))
	{
        $in_group = TRUE;
    }
}
if((!$in_group) && !is_numeric(array_search($user->get_user_id(), $old_admin_users)))
{
	$backend->print_error('You do not have permissions to modify this page');
}

// some additional security checks:
// Check whether the section_id belongs to the page_id at all
if ($section_id != 0) {
	$sql  = "SELECT `module` FROM `%ssections` WHERE `page_id` = %d AND `section_id` = %d";
	$res_sec = $backend->db()->query(sprintf($sql,CAT_TABLE_PREFIX,$page_id,$section_id));
	if ($backend->db()->is_error())
	{
		$backend->print_error($backend->db()->get_error());
	}
	if ($res_sec->numRows() == 0)
	{
		$backend->print_error('Not Found');
	}

	// check module permissions:
	$sec = $res_sec->fetchRow( MYSQL_ASSOC );
	if (!$user->get_permission($sec['module'], 'module'))
	{
		$backend->print_error('You do not have permissions to modify this page');
	}	
}

// Workout if the developer wants to show the info banner
if(isset($print_info_banner) && $print_info_banner == true)
{
    $backend->print_banner();
}

// Work-out if the developer wants us to update the timestamp for when the page was last modified
if(isset($update_when_modified) && $update_when_modified == true)
{
	$sql  = 'UPDATE `%spages` ';
	$sql .= 'SET `modified_when` = '.time().', ';
	$sql .=     '`modified_by`   = '.intval($admin->get_user_id()).' ';
	$sql .=     'WHERE page_id   = '.intval($page_id);
	$backend->db()->query(sprintf($sql,CAT_TABLE_PREFIX));
}

?>