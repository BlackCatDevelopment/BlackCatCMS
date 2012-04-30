<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2012, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version         $Id$
 *
 */
 
// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {
	include(LEPTON_PATH.'/framework/class.secure.php');
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

// Make sure people are allowed to access this page
if ( MANAGE_SECTIONS != 'enabled' )
{
	header('Location: '.ADMIN_URL);
	exit(0);
}

//require_once(LEPTON_PATH."/include/jscalendar/jscalendar-functions.php");

// =========================== 
// ! Create new admin object   
// =========================== 
require_once(LEPTON_PATH.'/framework/class.admin.php');
$admin			= new admin('Pages', 'pages_modify');

$page_id		= $admin->get_get('page_id');
if ( $page_id == '' )
{
	$page_id	= $admin->get_post('page_id');
}

// =============== 
// ! Get page id   
// =============== 
if ( !is_numeric( $page_id ) || $page_id == '' )
{
	header('Location: '.ADMIN_URL);
	exit(0);
}

// ============= 
// ! Get perms   
// ============= 
$results				= $database->query("SELECT `admin_groups`,`admin_users` FROM `".TABLE_PREFIX."pages` WHERE `page_id`= '".$page_id."'");
$results_array			= $results->fetchRow( MYSQL_ASSOC );

$old_admin_groups		= explode(',', $results_array['admin_groups']);
$old_admin_users		= explode(',', $results_array['admin_users']);
$in_old_group = false;
foreach ( $admin->get_groups_id() as $cur_gid )
{
	if ( in_array($cur_gid, $old_admin_groups) )
	{
		$in_old_group = true;
	}
}
if ( (!$in_old_group) && !is_numeric( array_search($admin->get_user_id(), $old_admin_users) ) )
{
	$admin->print_error( 'You do not have permissions to modify this page' );
}

// ==================== 
// ! Get page details   
// ==================== 
$results = $database->query("SELECT count(*) FROM `".TABLE_PREFIX."pages` WHERE `page_id`=".$page_id);
if ( $database->is_error() )
{
	$admin->print_error($database->get_error());
}
if ( $results->numRows() == 0 )
{
	$admin->print_error( 'Page not found' );
}

// ========================== 
// ! Set module permissions   
// ========================== 
$module_permissions		= $_SESSION['MODULE_PERMISSIONS'];

// ========================= 
// ! Get delete_section_id   
// ========================= 
$delete_section_id		= $admin->get_get('delete_section_id');
$update_section_id		= $admin->get_get('update_section_id');
$add_module				= $admin->add_slashes($admin->get_post('add_module'));

if ( $add_module != '' )
{
	// Get section info
	$module = preg_replace("/\W/", "", $add_module);  // fix secunia 2010-91-4
	/**
	 *	Is the module-name valide? Or in other words: does the module(-name) exists?
	 *
	 */
	$temp_result = $database->query("SELECT `name` from `".TABLE_PREFIX."addons` where `directory`='".$module."'");
	if ( !$temp_result )
	{
		$admin->print_error($database->get_error());
	}
	else
	{
		if ( $temp_result->numRows() <> 1 )
		{
			$admin->print_error( 'The module is not installed properly!' );
		}
	}
	unset($temp_result);
	/**
	 *	Got the current user the rights to "use" this module at all?
	 *
	 */
	if (true === in_array($module, $module_permissions ) )
	{
		$admin->print_error( 'Actualization not possibly' );
	}

	// Include the ordering class
	require(LEPTON_PATH.'/framework/class.order.php');
	// Get new order
	$order = new order(TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
	$position = $order->get_new($page_id);
	// Insert module into DB
	$sql	 = 'INSERT INTO `'.TABLE_PREFIX.'sections` SET ';
	$sql	.= '`page_id` = '.$page_id.', ';
	$sql	.= '`module` = "'.$module.'", ';
	$sql	.= '`position` = '.$position.', ';
	$sql	.= '`block`=1';
	$database->query($sql);
	if ( !$database->is_error() )
	{
		// Get the section id
		$section_id = $database->get_one("SELECT LAST_INSERT_ID()");
		// Include the selected modules add file if it exists
		if ( file_exists(LEPTON_PATH.'/modules/'.$module.'/add.php') )
		{
			require(LEPTON_PATH.'/modules/'.$module.'/add.php');
		}
	}
}
// ===================================================== 
// ! If delete_section_id is send, delete this section   
// ===================================================== 
else if ( is_numeric( $delete_section_id ) && $delete_section_id != '' )
{
	$section_id		= $delete_section_id;
	// =========================================== 
	// ! Get more information about this section   
	// =========================================== 
	$query_section	= $database->query('SELECT `module` FROM `'.TABLE_PREFIX.'sections` WHERE `section_id` ='.$section_id);

	if($query_section->numRows() == 0)
	{
		$admin->print_error('Section not found');
	}
	$section		= $query_section->fetchRow( MYSQL_ASSOC );

	// ================================================ 
	// ! Include the modules delete file if it exists   
	// ================================================ 
	if ( file_exists(LEPTON_PATH.'/modules/'.$section['module'].'/delete.php') )
	{
		require(LEPTON_PATH.'/modules/'.$section['module'].'/delete.php');
	}

	$query_section	= $database->query('DELETE FROM `'.TABLE_PREFIX.'sections` WHERE `section_id` ='.$section_id.' LIMIT 1');

	if ( $database->is_error() )
	{
		$admin->print_error($database->get_error());
	}
	else
	{
		// ======================= 
		// ! Reorder the section   
		// ======================= 
		require(LEPTON_PATH.'/framework/class.order.php');

		$order = new order(TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
		$order->clean($page_id);

		$admin->print_success( 'Success', ADMIN_URL . '/pages/modify.php?page_id=' . $page_id );
		exit();
	}
}
// ===================================================== 
// ! If delete_section_id is send, delete this section   
// ===================================================== 
else if ( is_numeric( $update_section_id ) && $update_section_id != '' )
{
	$block			= $admin->get_get('block');
	$name			= $admin->get_get('name');

	$day_from		= is_numeric( $admin->get_get('day_from') )			? $admin->get_get('day_from') : 0;
	$month_from		= is_numeric( $admin->get_get('month_from') )		? $admin->get_get('month_from') : 0;
	$year_from		= is_numeric( $admin->get_get('year_from') )		? $admin->get_get('year_from') : 0;
	$hour_from		= is_numeric( $admin->get_get('hour_from') )		? $admin->get_get('hour_from') : 0;
	$minute_from	= is_numeric( $admin->get_get('minute_from') )		? $admin->get_get('minute_from') : 0;

	$day_to			= is_numeric( $admin->get_get('day_to') )		? $admin->get_get('day_to') : 0;
	$month_to		= is_numeric( $admin->get_get('month_to') )		? $admin->get_get('month_to') : 0;
	$year_to		= is_numeric( $admin->get_get('year_to') )		? $admin->get_get('year_to') : 0;
	$hour_to		= is_numeric( $admin->get_get('hour_to') )		? $admin->get_get('hour_to') : 0;
	$minute_to		= is_numeric( $admin->get_get('minute_to') )	? $admin->get_get('minute_to') : 0;

	// ============================= 
	// ! Get section from database   
	// ============================= 
	$query_sections		= $database->query('SELECT `module` FROM `' . TABLE_PREFIX . 'sections` WHERE `page_id`= ' . $page_id . ' AND `section_id` = ' . $update_section_id);
	if ( $query_sections->numRows() == 1 )
	{
		if ( $section = $query_sections->fetchRow( MYSQL_ASSOC ) )
		{
			if ( !is_numeric (array_search($section['module'], $module_permissions) ) )
			{
				// $dst = date("I") ? " DST" : "";				// returns "1" if daylight saving time - is not used anywhere!!!!
				$sql		= $block	!= ''	? '`block` = ' . $admin->add_slashes($block) . ', '			: '';
				$sql		= $name		!= ''	? $sql . '`name` = "' . mysql_real_escape_string($name) . '", '	: $sql;

				$date_from	= ($day_from * $month_from * $year_from) > 0	? mktime( $hour_from, $minute_from, 0, $month_from, $day_from, $year_from ) : 0;
				$date_to	= ($day_to * $month_to * $year_to) > 0			? mktime( $hour_to, $minute_to, 0, $month_to, $day_to, $year_to ) : 0;

				if ( $date_from > $date_to )
				{
					$admin->print_error($admin->lang->translate( 'Please check your entries for dates' ), ADMIN_URL.'/pages/modify.php?page_id='.$page_id);
				}

				$sql	.= '`publ_start` = ' . $date_from . ', ';
				$sql	.= '`publ_end` = ' . $date_to;

				$database->query('UPDATE ' . TABLE_PREFIX . 'sections SET ' . $sql . ' WHERE `page_id`= ' . $page_id . ' AND section_id = ' . $update_section_id . ' LIMIT 1');
			}
		}
		else
		{
			$admin->print_error( 'You do not have permissions to modify this page', ADMIN_URL . '/pages/modify.php?page_id=' . $page_id);
		}
	}
	else
	{
		$admin->print_error( 'Section not found', ADMIN_URL . '/pages/modify.php?page_id=' . $page_id );
	}
}

// ============================================ 
// ! Check for error or print success message   
// ============================================ 
if ( $database->is_error() )
{
	$admin->print_error( $database->get_error(), ADMIN_URL . '/pages/modify.php?page_id=' . $page_id );
}
else
{
	$admin->print_success( 'Section properties saved successfully' , ADMIN_URL . '/pages/modify.php?page_id=' . $page_id );
}

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>
