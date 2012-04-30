<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version         $Id$
 *
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



// Get page & section id
if(!isset($_POST['page_id']) OR !is_numeric($_POST['page_id'])) {
	header("Location: index.php");
	exit(0);
} else {
	$page_id = intval($_POST['page_id']);
}
if(!isset($_POST['section_id']) OR !is_numeric($_POST['section_id'])) {
	header("Location: index.php");
	exit(0);
} else {
	$section_id = intval($_POST['section_id']);
}

require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages_modify');

// Get perms
$sql  = 'SELECT `admin_groups`,`admin_users` FROM `'.TABLE_PREFIX.'pages` ';
$sql .= 'WHERE `page_id` = '.$page_id;
$results = $database->query($sql);
$results_array = $results->fetchRow( MYSQL_ASSOC );
$old_admin_groups = explode(',', str_replace('_', '', $results_array['admin_groups']));
$old_admin_users = explode(',', str_replace('_', '', $results_array['admin_users']));
$in_old_group = FALSE;
foreach($admin->get_groups_id() as $cur_gid)
{
    if (in_array($cur_gid, $old_admin_groups))
    {
        $in_old_group = TRUE;
    }
}
if((!$in_old_group) AND !is_numeric(array_search($admin->get_user_id(), $old_admin_users)))
{
	$admin->print_error($MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
}
// Get page module
$sql  = 'SELECT `module` FROM `'.TABLE_PREFIX.'sections` ';
$sql .= 'WHERE `page_id`='.$page_id.' AND `section_id`='.$section_id;
$module = $database->get_one($sql);
if(!$module)
{
	$admin->print_error( $database->is_error() ? $database->get_error() : $MESSAGE['PAGES_NOT_FOUND']);
}

// Update the pages table
$now = time();
$sql  = 'UPDATE `'.TABLE_PREFIX.'pages` SET ';
$sql .= '`modified_when` = '.$now.', `modified_by` = '.$admin->get_user_id().' ';
$sql .= 'WHERE `page_id` = '.$page_id;
$database->query($sql);

// Include the modules saving script if it exists
if(file_exists(WB_PATH.'/modules/'.$module.'/save.php'))
{
	include_once(WB_PATH.'/modules/'.$module.'/save.php');
}
// Check if there is a db error, otherwise say successful
if($database->is_error())
{
	if( !isset($js_back) ) $js_back = ADMIN_URL.'/pages/modify.php?page_id='.$page_id;
	$admin->print_error( $database->get_error(), $js_back );
} else {
	$admin->print_success($MESSAGE['PAGES_SAVED'], ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
}

// Print admin footer
$admin->print_footer();

?>