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

// ==================================================== 
// ! Make sure people are allowed to access this page   
// ==================================================== 
if(MANAGE_SECTIONS != 'enabled') {
	header('Location: '.ADMIN_URL.'/pages/index.php');
	exit(0);
}

// =============== 
// ! Get page id   
// =============== 
if(!isset($_GET['page_id']) OR !is_numeric($_GET['page_id'])) {
	header("Location: index.php");
	exit(0);
} else {
	$page_id = intval($_GET['page_id']);
	

	// ================================ 
	// ! Does this page realy exists?   
	// ================================ 
	$temp_result = $database->query("SELECT `page_id` from `".TABLE_PREFIX."pages` where `page_id`='".$page_id."'");
	if (!$temp_result) {
		die( header("Location: index.php") );
	} else {
		if ( $temp_result->numRows() <> 1 ) {
			die( header("Location: index.php") );
		}
	}
}

$debug = false; // to show position and section_id
if(!defined('DEBUG')) { define('DEBUG',$debug);}

// =========================== 
// ! Create new admin object   
// =========================== 
require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages_modify');

// ======================================================= 
// ! Check if we are supposed to add or delete a section   
// ======================================================= 
if(isset($_GET['section_id']) AND is_numeric($_GET['section_id'])) {

	// =========================================== 
	// ! Get more information about this section   
	// =========================================== 
	$section_id = intval($_GET['section_id']);
	$query_section = $database->query('SELECT `module` FROM `'.TABLE_PREFIX.'sections` WHERE `section_id` ='.$section_id);

	if($query_section->numRows() == 0) {
		$admin->print_error('Section not found');
	}
	$section = $query_section->fetchRow( MYSQL_ASSOC );

	// ================================================ 
	// ! Include the modules delete file if it exists   
	// ================================================ 
	if(file_exists(WB_PATH.'/modules/'.$section['module'].'/delete.php')) {
		require(WB_PATH.'/modules/'.$section['module'].'/delete.php');
	}

	$query_section = $database->query('DELETE FROM `'.TABLE_PREFIX.'sections` WHERE `section_id` ='.$section_id.' LIMIT 1');

	if($database->is_error()) {
		$admin->print_error($database->get_error());
	} else {
		require(WB_PATH.'/framework/class.order.php');
		$order = new order(TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
		$order->clean($page_id);
		$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/sections.php?page_id='.$page_id);
		$admin->print_footer();
		exit();
	}
} elseif(isset($_POST['module']) && $_POST['module'] != '') {

	// ==================== 
	// ! Get section info   
	// ==================== 
	$module = preg_replace("/\W/", "", $admin->add_slashes($_POST['module']));  // fix secunia 2010-91-4
	

	// ================================================================================ 
	// ! Is the module-name valide? Or in other words: does the module(-name) exists?   
	// ================================================================================ 
	$temp_result = $database->query("SELECT `name` from `".TABLE_PREFIX."addons` where `directory`='".$module."'");
	if (!$temp_result) {
		$admin->print_error($database->get_error());
	} else {
		if ($temp_result->numRows() <> 1) {
			$admin->print_error($MESSAGE['GENERIC_MODULE_VERSION_ERROR']);
		}
	}
	unset($temp_result);

	// ================================================================ 
	// ! Got the current user the rights to "use" this module at all?   
	// ================================================================ 
	if (true === in_array($module, $_SESSION['MODULE_PERMISSIONS'] ) ) {
		$admin->print_error($MESSAGE['GENERIC_NOT_UPGRADED']);
	}
	
	// ============================== 
	// ! Include the ordering class   
	// ============================== 
	require(WB_PATH.'/framework/class.order.php');

	// ================= 
	// ! Get new order   
	// ================= 
	$order = new order(TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
	$position = $order->get_new($page_id);

	// ========================= 
	// ! Insert module into DB   
	// ========================= 
	$database->query('INSERT INTO `'.TABLE_PREFIX.'sections` SET `page_id` = '.$page_id.', `module` = "'.$module.'", `position` = '.$position.', `block`=1');

	// ====================== 
	// ! Get the section id   
	// ====================== 
	$section_id = $database->get_one("SELECT LAST_INSERT_ID()");	

	// ====================================================== 
	// ! Include the selected modules add file if it exists   
	// ====================================================== 
	if(file_exists(WB_PATH.'/modules/'.$module.'/add.php')) {
		require(WB_PATH.'/modules/'.$module.'/add.php');
	}
}

// ================== 
// ! Get permsissions
// ================== 
$results = $database->query('SELECT `admin_groups`,`admin_users` FROM `'.TABLE_PREFIX.'pages` WHERE `page_id` = '.$page_id);

$results_array = $results->fetchRow(MYSQL_ASSOC);
$old_admin_groups = explode(',', $results_array['admin_groups']);
$old_admin_users = explode(',', $results_array['admin_users']);
$in_old_group = FALSE;
foreach($admin->get_groups_id() as $cur_gid) {
	if (in_array($cur_gid, $old_admin_groups)) {
		$in_old_group = TRUE;
	}
}
if((!$in_old_group) && !is_numeric(array_search($admin->get_user_id(), $old_admin_users))) {
	$admin->print_error($MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
}

// ==================== 
// ! Get page details   
// ==================== 
$results = $database->query('SELECT * FROM `'.TABLE_PREFIX.'pages` WHERE `page_id` = '.$page_id);

if($database->is_error()) {
	$admin->print_error($database->get_error());
}
if($results->numRows() == 0) {
	$admin->print_error($MESSAGE['PAGES_NOT_FOUND']);
}
$results_array = $results->fetchRow();

// ========================== 
// ! Set module permissions   
// ========================== 
$module_permissions = $_SESSION['MODULE_PERMISSIONS'];

// =================== 
// ! Unset block var   
// =================== 
unset($block);

// ============================================= 
// ! Include template info file (if it exists)   
// ============================================= 
if($results_array['template'] != '') {
	$template_location = WB_PATH.'/templates/'.$results_array['template'].'/info.php';
} else {
	$template_location = WB_PATH.'/templates/'.DEFAULT_TEMPLATE.'/info.php';
}
if(file_exists($template_location)) {
	require($template_location);
}

// ======================================== 
// ! Load css files with jquery
// ======================================== 

// =============================== 
// ! // include jscalendar-setup   
// =============================== 
$jscal_use_time = true; // whether to use a clock, too
require_once(WB_PATH."/include/jscalendar/wb-setup.php");

// ==================== 
// ! Add URLs to Dwoo   
// ==================== 
$data_dwoo = array();
$data_dwoo['WB_URL'] = WB_URL;
$data_dwoo['WB_PATH'] = WB_PATH;
$data_dwoo['ADMIN_URL'] = ADMIN_URL;
$data_dwoo['THEME_URL'] = THEME_URL;
$data_dwoo['SETTINGS_LINK'] = ADMIN_URL.'/pages/settings.php?page_id='.$results_array['page_id'];
$data_dwoo['MODIFY_LINK'] = ADMIN_URL.'/pages/modify.php?page_id='.$results_array['page_id'];

// =================================== 
// ! set first defaults and messages   
// =================================== 
$data_dwoo['PAGE_ID'] = $results_array['page_id'];
$data_dwoo['PAGE_TITLE'] = $results_array['page_title'];
$data_dwoo['MENU_TITLE'] = $results_array['menu_title'];
$data_dwoo['SEC_ANCHOR'] = SEC_ANCHOR;
$data_dwoo['SECTION_BLOCKS'] = SECTION_BLOCKS;
$data_dwoo['SECTION_NAME'] = ($_SESSION['USER_ID'] != 1 ? true : false);

// ========================================================= 
// ! Get display name of person who last modified the page   
// ========================================================= 
$user=$admin->get_user_details($results_array['modified_by']);

// ================================================================== 
// ! Convert the unix ts for modified_when to human a readable form   
// ================================================================== 
if($results_array['modified_when'] != 0) {
	$data_dwoo['MODIFIED_WHEN'] = date(TIME_FORMAT.', '.DATE_FORMAT, $results_array['modified_when']);
} else {
	$data_dwoo['MODIFIED_WHEN'] = 'Unknown';
}
$data_dwoo['MODIFIED_BY'] = $user['display_name'];
$data_dwoo['MODIFIED_BY_USERNAME'] = $user['username'];

$query_sections = $database->query('SELECT `section_id`,`module`,`position`,`block`,`publ_start`,`publ_end`,`name` FROM `'.TABLE_PREFIX.'sections` WHERE `page_id` = '.$page_id.' ORDER BY `position` ASC');

if($query_sections->numRows() > 0) {
	$num_sections = $query_sections->numRows();
	$counter = 0;
	while($section = $query_sections->fetchRow( MYSQL_ASSOC )) {
		if(!is_numeric(array_search($section['module'], $module_permissions))) {
			if(!$database->get_one($sql) || !file_exists(WB_PATH.'/modules/'.$section['module'])) $data_dwoo['MODULES'][$counter]['MODULE_MODIFY'] = false;
			else $data_dwoo['MODULES'][$counter]['MODULE_MODIFY'] = true;
			$data_dwoo['MODULES'][$counter]['MODULE_NAME'] = $section['module'];
			$data_dwoo['MODULES'][$counter]['SECTION_ID'] = $section['section_id'];
			$data_dwoo['MODULES'][$counter]['POSITION'] = $section['position'];
			$data_dwoo['MODULES'][$counter]['BLOCK_OPTIONS'] = $section['block'];
			$data_dwoo['MODULES'][$counter]['SECTION_NAME'] = $section['name'];
			// ============================ 
			// ! set calendar values   
			// ============================ 
			$data_dwoo['MODULES'][$counter]['VALUE_PUBL_START'] = ($section['publ_start'] == 0) ? false : date($jscal_format, $section['publ_start']);
			$data_dwoo['MODULES'][$counter]['VALUE_PUBL_END'] = ($section['publ_end'] == 0) ? false : date($jscal_format, $section['publ_end']);
			$counter++;
		} else {
			continue; // m.f.i.
		}
	}
	$data_dwoo['modules_counter'] = $counter;
}

// ================================================================================================================================================= 
// ! now add the calendars -- remember to to set the range to [1970, 2037] if the date is used as timestamp!
// ! the loop is simply a copy from above.   
// ================================================================================================================================================= 
$query_sections = $database->query('SELECT `section_id`,`module` FROM `'.TABLE_PREFIX.'sections` WHERE page_id = '.$page_id.' ORDER BY `position` ASC');

if($query_sections->numRows() > 0) {
	$counter=0;
	while(($section = $query_sections->fetchRow(MYSQL_ASSOC)) !== false) {

		// ============================= 
		// ! Get the modules real name   
		// ============================= 
		$module_name = $database->get_one('SELECT `name` FROM `'.TABLE_PREFIX.'addons` WHERE `directory` = "'.$section['module'].'"', MYSQL_ASSOC );
		if(!is_numeric(array_search($section['module'], $module_permissions))) {
			$data_dwoo['CAL'][$counter]['SECTION_ID'] = $section['section_id'];
			$data_dwoo['CAL'][$counter]['jscal_ifformat'] = $jscal_ifformat;
			$data_dwoo['CAL'][$counter]['jscal_firstday'] = $jscal_firstday;
			$data_dwoo['CAL'][$counter]['jscal_today'] = $jscal_today;
			$data_dwoo['CAL'][$counter]['start_date'] = 'start_date'.$section['section_id'];
			$data_dwoo['CAL'][$counter]['end_date'] = 'end_date'.$section['section_id'];
			$data_dwoo['CAL'][$counter]['trigger_start'] = 'trigger_start'.$section['section_id'];
			$data_dwoo['CAL'][$counter]['trigger_end'] = 'trigger_stop'.$section['section_id'];
			$data_dwoo['CAL'][$counter]['showsTime'] = ((isset($jscal_use_time)) && ($jscal_use_time==TRUE)) ? true : false;
			$data_dwoo['CAL'][$counter]['timeFormat'] = '24';

			$counter++;
		}
	}
	unset($temp_calendar_show_time);
} else {
	
}

// ===================================================== 
// ! Work-out if we should show the "Add Section" form   
// ===================================================== 
$query_sections = $database->query('SELECT `section_id` FROM `'.TABLE_PREFIX.'sections` WHERE `page_id` = '.$page_id.' AND `module` = "menu_link"');
if($query_sections->numRows() == 0) {
	// ================ 
	// ! Modules list   
	// ================ 
	$result = $database->query('SELECT `name`,`directory`,`type` FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "module" AND `function` = "page" AND `directory` != "menu_link" ORDER BY `name`');
	if( true === (0 < $result->numRows())) {
		$counter=0;
		while( false !== ($module = $result->fetchRow(MYSQL_ASSOC))) {

			// =============================================== 
			// ! Check if user is allowed to use this module   
			// =============================================== 
			if(!is_numeric(array_search($module['directory'], $module_permissions))) {
				$data_dwoo['modules'][$counter]['VALUE'] = $module['directory'];
				$data_dwoo['modules'][$counter]['NAME'] = $module['name'];
				$data_dwoo['modules'][$counter]['SELECTED'] = ($module['directory'] == 'wysiwyg') ? true : false;
				$counter++;
			}
		}
	}
}

// ========================= 
// ! Check if $menu is set   
// ========================= 
if(!isset($block[1]) OR $block[1] == '') {
	// ========================== 
	// ! Make our own menu list   
	// ========================== 
	$block[1] = $TEXT['MAIN'];
}

// ========================================= 
// ! Block list
// ========================================= 
$counter=0;
foreach($block as $number => $name) {
	$data_dwoo['BLOCK_OPTIONS'][$counter]['NAME'] = htmlentities(strip_tags($name));
	$data_dwoo['BLOCK_OPTIONS'][$counter]['VALUE'] = $number;
	$counter++;
}

// ======================== 
// ! Insert language text   
// ======================== 
$data_dwoo['HEADING'] = $HEADING;
$data_dwoo['TEXT'] = $TEXT;
$data_dwoo['MESSAGE'] = $MESSAGE;
$data_dwoo['MENU'] = $MENU;

// =========================================================================== 
// ! Create the controller, it is reusable and can render multiple templates   
// =========================================================================== 
global $parser;
if (!is_object($parser)) $parser = new Dwoo();

// ================================== 
// ! Load the template file for the header   
// ================================== 
$tpl = new Dwoo_Template_File(THEME_PATH.'/templates/admins_pages_sections.lte');

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output($tpl, $data_dwoo);

// ================================================== 
// ! include the required file for Javascript admin   
// ================================================== 
if(file_exists(WB_PATH.'/modules/jsadmin/jsadmin_backend_include.php')) {
	include_once(WB_PATH.'/modules/jsadmin/jsadmin_backend_include.php');
}

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>