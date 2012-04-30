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



// Make sure people are allowed to access this page
if(MANAGE_SECTIONS != 'enabled')
{
	header('Location: '.ADMIN_URL.'/pages/index.php');
	exit(0);
}

// Get page id
if(!isset($_GET['page_id']) OR !is_numeric($_GET['page_id']))
{
	header("Location: index.php");
	exit(0);
} else {
	$page_id = intval($_GET['page_id']);
	
	/**
	 *	Does this page realy exists?
	 *
	 */
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
If(!defined('DEBUG')) { define('DEBUG',$debug);}
// Create new admin object
require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages_modify');

// Check if we are supposed to add or delete a section
if(isset($_GET['section_id']) AND is_numeric($_GET['section_id']))
{
	// Get more information about this section
	$section_id = intval($_GET['section_id']);
    $sql  = 'SELECT `module` FROM `'.TABLE_PREFIX.'sections` ';
    $sql .= 'WHERE `section_id` ='.$section_id;
    $query_section = $database->query($sql);

	if($query_section->numRows() == 0)
    {
		$admin->print_error('Section not found');
	}
	$section = $query_section->fetchRow( MYSQL_ASSOC );
	// Include the modules delete file if it exists
	if(file_exists(WB_PATH.'/modules/'.$section['module'].'/delete.php'))
    {
		require(WB_PATH.'/modules/'.$section['module'].'/delete.php');
	}
    $sql  = 'DELETE FROM `'.TABLE_PREFIX.'sections` ';
    $sql .= 'WHERE `section_id` ='.$section_id.' LIMIT 1';
    $query_section = $database->query($sql);

	if($database->is_error())
    {
		$admin->print_error($database->get_error());
	} else {
		require(WB_PATH.'/framework/class.order.php');
		$order = new order(TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
		$order->clean($page_id);
		$admin->print_success($TEXT['SUCCESS'], ADMIN_URL.'/pages/sections.php?page_id='.$page_id);
		$admin->print_footer();
		exit();
	}
} elseif(isset($_POST['module']) && $_POST['module'] != '')
{
	// Get section info
	$module = preg_replace("/\W/", "", $admin->add_slashes($_POST['module']));  // fix secunia 2010-91-4
	
	/**
	 *	Is the module-name valide? Or in other words: does the module(-name) exists?
	 *
	 */
	$temp_result = $database->query("SELECT `name` from `".TABLE_PREFIX."addons` where `directory`='".$module."'");
	if (!$temp_result) {
		$admin->print_error($database->get_error());
	} else {
		if ($temp_result->numRows() <> 1) {
			$admin->print_error($MESSAGE['GENERIC_MODULE_VERSION_ERROR']);
		}
	}
	unset($temp_result);
	
	/**
	 *	Got the current user the rights to "use" this module at all?
	 *
	 */
	if (true === in_array($module, $_SESSION['MODULE_PERMISSIONS'] ) ) {
		$admin->print_error($MESSAGE['GENERIC_NOT_UPGRADED']);
	}
	
	// Include the ordering class
	require(WB_PATH.'/framework/class.order.php');
	// Get new order
	$order = new order(TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
	$position = $order->get_new($page_id);	
	// Insert module into DB
    $sql  = 'INSERT INTO `'.TABLE_PREFIX.'sections` SET ';
    $sql .= '`page_id` = '.$page_id.', ';
    $sql .= '`module` = "'.$module.'", ';
    $sql .= '`position` = '.$position.', ';
    $sql .= '`block`=1';
    $database->query($sql);
	// Get the section id
	$section_id = $database->get_one("SELECT LAST_INSERT_ID()");	
	// Include the selected modules add file if it exists
	if(file_exists(WB_PATH.'/modules/'.$module.'/add.php'))
    {
		require(WB_PATH.'/modules/'.$module.'/add.php');
	}
}

// Get perms
$sql  = 'SELECT `admin_groups`,`admin_users` FROM `'.TABLE_PREFIX.'pages` ';
$sql .= 'WHERE `page_id` = '.$page_id;
$results = $database->query($sql);

$results_array = $results->fetchRow( MYSQL_ASSOC );
$old_admin_groups = explode(',', $results_array['admin_groups']);
$old_admin_users = explode(',', $results_array['admin_users']);
$in_old_group = FALSE;
foreach($admin->get_groups_id() as $cur_gid)
{
	if (in_array($cur_gid, $old_admin_groups))
    {
		$in_old_group = TRUE;
	}
}
if((!$in_old_group) && !is_numeric(array_search($admin->get_user_id(), $old_admin_users)))
{
	$admin->print_error($MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
}

// Get page details
// $database = new database();
$sql  = 'SELECT * FROM `'.TABLE_PREFIX.'pages` ';
$sql .= 'WHERE `page_id` = '.$page_id;
$results = $database->query($sql);

if($database->is_error())
{
	$admin->print_error($database->get_error());
}
if($results->numRows() == 0)
{
	$admin->print_error($MESSAGE['PAGES_NOT_FOUND']);
}
$results_array = $results->fetchRow();

// Set module permissions
$module_permissions = $_SESSION['MODULE_PERMISSIONS'];

// Unset block var
unset($block);
// Include template info file (if it exists)
if($results_array['template'] != '')
{
	$template_location = WB_PATH.'/templates/'.$results_array['template'].'/info.php';
} else {
	$template_location = WB_PATH.'/templates/'.DEFAULT_TEMPLATE.'/info.php';
}
if(file_exists($template_location))
{
	require($template_location);
}
// Check if $menu is set
if(!isset($block[1]) OR $block[1] == '')
{
	// Make our own menu list
	$block[1] = $TEXT['MAIN'];
}

/*-- load css files with jquery --*/
// include jscalendar-setup
$jscal_use_time = true; // whether to use a clock, too
require_once(WB_PATH."/include/jscalendar/wb-setup.php");

// Setup template object
$template = new Template(THEME_PATH.'/templates');
$template->set_file('page', 'pages_sections.htt');
$template->set_block('page', 'main_block', 'main');
$template->set_block('main_block', 'module_block', 'module_list');
$template->set_block('main_block', 'section_block', 'section_list');
$template->set_block('section_block', 'block_block', 'block_list');
$template->set_block('main_block', 'calendar_block', 'calendar_list');

// set first defaults and messages
$template->set_var(array(
	'PAGE_ID' => $results_array['page_id'],
	'TEXT_PAGE' => $TEXT['PAGE'],
	'PAGE_TITLE' => ($results_array['page_title']),
	'MENU_TITLE' => ($results_array['menu_title']),
	'TEXT_CURRENT_PAGE' => $TEXT['CURRENT_PAGE'],
	'HEADING_MANAGE_SECTIONS' => $HEADING['MANAGE_SECTIONS'],
	'HEADING_MODIFY_PAGE' => $HEADING['MODIFY_PAGE'],
	'TEXT_CHANGE_SETTINGS' => $TEXT['CHANGE_SETTINGS'],
	'TEXT_ADD_SECTION' => $TEXT['ADD_SECTION'],
	'TEXT_ID' => 'ID',
	'TEXT_TYPE' => $TEXT['TYPE'],
	'TEXT_BLOCK' => $TEXT['BLOCK'],
	'TEXT_PUBL_START_DATE' => $TEXT{'PUBL_START_DATE'},
	'TEXT_PUBL_END_DATE' => $TEXT['PUBL_END_DATE'],
	'TEXT_ACTIONS' => $TEXT['ACTIONS'],
	'ADMIN_URL' => ADMIN_URL,
	'WB_URL' => WB_URL,
	'WB_PATH' => WB_PATH,
	'THEME_URL' => THEME_URL
	) 
);

// Insert variables
$template->set_var(array(
	'VAR_PAGE_ID' => $results_array['page_id'],
	'VAR_PAGE_TITLE' => $results_array['page_title'],
	'SETTINGS_LINK' => ADMIN_URL.'/pages/settings.php?page_id='.$results_array['page_id'],
	'MODIFY_LINK' => ADMIN_URL.'/pages/modify.php?page_id='.$results_array['page_id']
	) 
);

$sql  = 'SELECT `section_id`,`module`,`position`,`block`,`publ_start`,`publ_end`,`name` ';
$sql .= 'FROM `'.TABLE_PREFIX.'sections` ';
$sql .= 'WHERE `page_id` = '.$page_id.' ';
$sql .= 'ORDER BY `position` ASC';
$query_sections = $database->query($sql);

if($query_sections->numRows() > 0)
{
	$num_sections = $query_sections->numRows();
	while($section = $query_sections->fetchRow( MYSQL_ASSOC ))
    {
		if(!is_numeric(array_search($section['module'], $module_permissions)))
        {
			// Get the modules real name
            $sql = 'SELECT `name` FROM `'.TABLE_PREFIX.'addons` ';
            $sql .= 'WHERE `directory` = "'.$section['module'].'"';
            if(!$database->get_one($sql) || !file_exists(WB_PATH.'/modules/'.$section['module']))
			{
				$edit_page = '<span class="module_disabled">'.$section['module'].'</span>';
			} 
			else
			{
				$edit_page = '';
			}
			$edit_page_0 = '<a id="sid'.$section['section_id'].'" href="'.ADMIN_URL.'/pages/modify.php?page_id='.$page_id;
			$edit_page_1 = $section['section_id'].'">'.$section['module'].'</a>';
			if(SECTION_BLOCKS)
            {
				if($edit_page == '')
				{
					$edit_page = $edit_page_0.'#'.SEC_ANCHOR.$edit_page_1;
				}
				$input_attribute = 'input_normal';
				$template->set_var(array(
					'STYLE_DISPLAY_SECTION_BLOCK' => ' style="visibility:visible;"',
					'NAME_SIZE' => 300,
					'INPUT_ATTRIBUTE' => $input_attribute,
					'VAR_SECTION_ID' => $section['section_id'],
					'VAR_POSITION' => $section['position'],
					'LINK_MODIFY_URL_VAR_MODUL_NAME' => $edit_page,
					'SELECT' => '',
					'SET_NONE_DISPLAY_OPTION' => ''
					)
				);
				// Add block options to the section_list
				$template->clear_var('block_list');
				foreach($block as $number => $name)
                {
					$template->set_var('NAME', htmlentities(strip_tags($name)));
					$template->set_var('VALUE', $number);
					$template->set_var('SIZE', 1);
					if($section['block'] == $number)
                    {
						$template->set_var('SELECTED', ' selected="selected"');
					} else {
						$template->set_var('SELECTED', '');
					}
					$template->parse('block_list', 'block_block', true);
				}
			} else {
				if($edit_page == '')
				{
					$edit_page = $edit_page_0.'#'.SEC_ANCHOR.$edit_page_1;
				}
				$input_attribute = 'input_normal';
				$template->set_var(array(
					'STYLE_DISPLAY_SECTION_BLOCK' => ' style="visibility:hidden;"',
					'NAME_SIZE' => 300,
					'INPUT_ATTRIBUTE' => $input_attribute,
					'VAR_SECTION_ID' => $section['section_id'],
					'VAR_POSITION' => $section['position'],
					'LINK_MODIFY_URL_VAR_MODUL_NAME' => $edit_page,
					'NAME' => htmlentities(strip_tags($block[1])),
					'VALUE' => 1,
					'SET_NONE_DISPLAY_OPTION' => ''
					)
				);
			}
			
			$template->set_var("SECTION_NAME", $section['name']);
			
			// Insert icon and images
			$template->set_var(array(
				'CLOCK_16_PNG' => 'clock_16.png',
				'CLOCK_DEL_16_PNG' => 'clock_del_16.png',
				'DELETE_16_PNG' => 'delete_16.png'
				) 
			);
			
			/**
			 *	set calendar start value
			 */
			$template->set_var('VALUE_PUBL_START', ( ($section['publ_start'] == 0) ? '' : date($jscal_format, $section['publ_start']) ) );
			
			/**
			 *	set calendar end value
			 */
			$template->set_var('VALUE_PUBL_END', ( ($section['publ_end'] == 0) ? '' :  date($jscal_format, $section['publ_end']) ) );
			
			/**
			 *	Insert icons for moving the section up or down
			 */
			$template->set_var(
				'VAR_MOVE_UP_URL',
				( ( $section['position'] > 1 )
				? '<a href="'.ADMIN_URL.'/pages/move_up.php?page_id='.$page_id.'&amp;section_id='.$section['section_id'].'"><img src="'.THEME_URL.'/images/up_16.png" alt="{TEXT_MOVE_UP}" /></a>'
				: '' )
			);
			
			$template->set_var(
				'VAR_MOVE_DOWN_URL',
				( ($section['position'] < $num_sections )
				? '<a href="'.ADMIN_URL.'/pages/move_down.php?page_id='.$page_id.'&amp;section_id='.$section['section_id'].'"><img src="'.THEME_URL.'/images/down_16.png" alt="{TEXT_MOVE_DOWN}" /></a>'
				: '' )
			);
			
		} else {
		  continue; // m.f.i.
		}
		
		// m.f.i. - obsolete
		$template->set_var(array(
			'DISPLAY_DEBUG' => '',
			'TEXT_SID' => 'SID',
			'DEBUG_COLSPAN_SIZE' => 9
			) 
		);
		
		$template->parse('section_list', 'section_block', true);
	}
}

// now add the calendars -- remember to to set the range to [1970, 2037] if the date is used as timestamp!
// the loop is simply a copy from above.
$sql  = 'SELECT `section_id`,`module` FROM `'.TABLE_PREFIX.'sections` ';
$sql .= 'WHERE page_id = '.$page_id.' ';
$sql .= 'ORDER BY `position` ASC';
$query_sections = $database->query($sql);

$num_sections = $query_sections->numRows();
if( $num_sections > 0 )
{
	$temp_calendar_show_time = ( ( isset($jscal_use_time) ) && ( $jscal_use_time==TRUE ) ) ? "true" : "false";
	
	while( false !== ($section = $query_sections->fetchRow( MYSQL_ASSOC ) ) )
    {
		// Get the modules real name
        $sql  = 'SELECT `name` FROM `'.TABLE_PREFIX.'addons` ';
        $sql .= 'WHERE `directory` = "'.$section['module'].'"';
        $module_name = $database->get_one($sql, MYSQL_ASSOC );

		if(!is_numeric(array_search($section['module'], $module_permissions)))
        {
			$template->set_var(array(
				'VAR_SECTION_ID'	=> $section['section_id'],
				'jscal_ifformat'	=> $jscal_ifformat,
				'jscal_firstday'	=> $jscal_firstday,
				'jscal_today'		=> $jscal_today,
				'start_date'		=> 'start_date'.$section['section_id'],
				'end_date'			=> 'end_date'.$section['section_id'],
				'trigger_start'		=> 'trigger_start'.$section['section_id'],
				'trigger_end'		=> 'trigger_stop'.$section['section_id'],
				'showsTime'			=> $temp_calendar_show_time,
				'timeFormat'		=> "24"
				) 
			);
		}
		
		$template->parse('calendar_list', 'calendar_block', true);
	}
	
	unset($temp_calendar_show_time);
	
	$template->parse("calendar_link", "calendar_block_link", true);

} else {
	$template->parse('calendar_list', '' );
}

// Work-out if we should show the "Add Section" form
$sql  = 'SELECT `section_id` FROM `'.TABLE_PREFIX.'sections` ';
$sql .= 'WHERE `page_id` = '.$page_id.' AND `module` = "menu_link"';
$query_sections = $database->query($sql);
if($query_sections->numRows() == 0)
{
	// Modules list
    $sql  = 'SELECT `name`,`directory`,`type` FROM `'.TABLE_PREFIX.'addons` ';
    $sql .= 'WHERE `type` = "module" AND `function` = "page" AND `directory` != "menu_link" ';
    $sql .= 'ORDER BY `name`';
    $result = $database->query($sql);

	if( true === (0 < $result->numRows() ) )
    {
		while( false !== ($module = $result->fetchRow( MYSQL_ASSOC ) ) )
        {
			/**
			 *	Check if user is allowed to use this module
			 */
			if(!is_numeric(array_search($module['directory'], $module_permissions)))
            {
				$template->set_var('VALUE', $module['directory']);
				$template->set_var('NAME', $module['name']);
				$template->set_var('SELECTED', ($module['directory'] == 'wysiwyg') ? ' selected="selected"' : '' );

				$template->parse('module_list', 'module_block', true);
			}
		}
	}
}

/**
 *	Insert language text and messages
 */
$template->set_var(array(
	'TEXT_MANAGE_SECTIONS'	=> $HEADING['MANAGE_SECTIONS'],
	'TEXT_ARE_YOU_SURE'		=> $TEXT['ARE_YOU_SURE'],
	'TEXT_TYPE'				=> $TEXT['TYPE'],
	'TEXT_ADD'				=> $TEXT['ADD'],
	'TEXT_SAVE'				=> $TEXT['SAVE'],
	'TEXTLINK_MODIFY_PAGE'	=> $HEADING['MODIFY_PAGE'],
	'TEXT_CALENDAR'			=> $TEXT['CALENDAR'],
	'TEXT_DELETE_DATE'		=> $TEXT['DELETE_DATE'],
	'TEXT_ADD_SECTION'		=> $TEXT['ADD_SECTION'],
	'TEXT_MOVE_UP'			=> $TEXT['MOVE_UP'],
	'TEXT_MOVE_DOWN'		=> $TEXT['MOVE_DOWN']
	)
);
$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');

// include the required file for Javascript admin
if(file_exists(WB_PATH.'/modules/jsadmin/jsadmin_backend_include.php'))
{
	include(WB_PATH.'/modules/jsadmin/jsadmin_backend_include.php');
}

// Print admin footer
$admin->print_footer();

?>