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
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 * @license			http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */
 
if (defined('CAT_PATH')) {
	include(CAT_PATH.'/framework/class.secure.php');
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

$val     = CAT_Helper_Validate::getInstance();

// =============== 
// ! Get page id   
// =============== 
$page_id = $val->get('_REQUEST','page_id','numeric');
if ( !$page_id )
{
	header("Location: index.php");
	exit(0);
}

require_once(CAT_PATH.'/framework/class.admin.php');

$admin = new admin('Pages', 'pages_modify');
$page   = CAT_Pages::getInstance($page_id);
$addons = CAT_Helper_Addons::getInstance();

// ============= 
// ! Get perms   
// ============= 
if ( !$page->getPagePermission($page_id,'admin') )
{
	$admin->print_error( 'You do not have permissions to modify this page' );
}

$wysiwyg   = $val->get('_GET','wysiwyg','scalar');
$sectionId = isset($wysiwyg) ? htmlspecialchars($wysiwyg) : NULL;

// ==================== 
// ! Get page details   
// ==================== 
$results_array							= $page->getPageDetails($page_id);

// ========================================================= 
// ! Get display name of person who last modified the page   
// ========================================================= 
$user									= CAT_Users::getInstance()->get_user_details( $results_array['modified_by'] );

// =========================================================================== 
// ! Create the controller, it is reusable and can render multiple templates 	
// =========================================================================== 
global $parser;
$tpl_data = array();

// ============================ 
// ! Include page info script   
// ============================ 
$tpl_data['PAGE_ID']					= $results_array['page_id'];
$tpl_data['PAGE_TITLE']				    = $results_array['page_title'];
$tpl_data['MENU_TITLE']				    = $results_array['menu_title'];
$tpl_data['PAGE_LINK']					= $admin->page_link($results_array['link']);

$tpl_data['MODIFIED_BY']				= $user['display_name'];
$tpl_data['MODIFIED_BY_USERNAME']		= $user['username'];
$tpl_data['MODIFIED_WHEN']		        = ($results_array['modified_when'] != 0)
                                        ? $modified_ts = CAT_Helper_DateTime::getDateTime($results_array['modified_when'])
                                        : false;

$tpl_data['SECTION_BLOCKS']			    = SECTION_BLOCKS;
$tpl_data['SEC_ANCHOR']				    = SEC_ANCHOR;
$tpl_data['DATE_FORMAT']				= DATE_FORMAT;

$tpl_data['CUR_TAB']                    = 'modify';
$tpl_data['PAGE_HEADER']                = $admin->lang->translate('Modify page');

// ========================================================= 
// ! Work-out if we should show the "manage sections" link   
// ========================================================= 
$query_sections = $database->query('SELECT `section_id` FROM `'.CAT_TABLE_PREFIX.'sections` WHERE `page_id` = '.(int)$page_id.' AND `module` = "menu_link"');

$tpl_data['MANAGE_SECTIONS']		   = ( $query_sections->numRows() > 0 || MANAGE_SECTIONS != 'enabled' ) ? false : true;

// =========================================================================== 
// ! get template used for the displayed page (for displaying block details)   
// =========================================================================== 
$get_template		= $database->query("SELECT `template` from `" . CAT_TABLE_PREFIX . "pages` WHERE `page_id` = '$page_id' ");
$template_row		= $get_template->fetchRow( MYSQL_ASSOC );
$current_template	= ( $template_row['template'] != '' ) ? $template_row['template'] : DEFAULT_TEMPLATE;

// ============================== 
// ! Get sections for this page   
// ============================== 
$module_permissions							= $_SESSION['MODULE_PERMISSIONS'];

$tpl_data['modules']				        = $addons->get_addons( 1, 'module', 'page', $module_permissions );

// Remove menu_link from list
foreach ( $tpl_data['modules'] as $index => $module )
{
	if ( $module['VALUE'] == 'menu_link' )
	{
		unset($tpl_data['modules'][$index]);
	}
}

$query_sections = $database->query('SELECT * FROM `'.CAT_TABLE_PREFIX.'sections` WHERE `page_id` = '.intval($page_id).' ORDER BY position ASC');

if ( $query_sections->numRows() > 0 )
{
	$tpl_data['blocks_counter']	= 0;
	while ( $section = $query_sections->fetchRow( MYSQL_ASSOC ) )
	{
		$module		= $section['module'];
		// ==================== 
		// ! Have permission?   
		// ==================== 
		if ( !is_numeric( array_search($module, $module_permissions) ) )
		{
			// =================================================== 
			// ! Include the modules editing script if it exists   
			// =================================================== 
			if ( file_exists(CAT_PATH.'/modules/'.$module.'/modify.php') )
			{
				// =========================================== 
				// ! output block name if blocks are enabled   
				// =========================================== 
				if ( SECTION_BLOCKS )
				{
					$section_id		= $section['section_id'];
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['template_blocks']		= $page->get_template_blocks( $current_template, $section['block'] );
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['current_block_id']	= $page->current_block['id'];
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['current_block_name']	= $page->current_block['name'];
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['section_id']			= $section['section_id'];
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['module']				= $section['module'];
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['name']				= $section['name'];
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['date_day_from']		= $section['publ_start'] > 0 ? date('d', $section['publ_start'] ) : '';
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['date_month_from']		= $section['publ_start'] > 0 ? date('m', $section['publ_start'] ) : '';
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['date_year_from']		= $section['publ_start'] > 0 ? date('Y', $section['publ_start'] ) : '';
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['date_hour_from']		= $section['publ_start'] > 0 ? date('H', $section['publ_start'] ) : '';
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['date_minute_from']	= $section['publ_start'] > 0 ? date('i', $section['publ_start'] ) : '';
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['date_day_to']			= $section['publ_start'] > 0 ? date('d', $section['publ_end'] ) : '';
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['date_month_to']		= $section['publ_start'] > 0 ? date('m', $section['publ_end'] ) : '';
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['date_year_to']		= $section['publ_start'] > 0 ? date('Y', $section['publ_end'] ) : '';
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['date_hour_to']		= $section['publ_start'] > 0 ? date('H', $section['publ_end'] ) : '';
					$tpl_data['blocks'][$tpl_data['blocks_counter']]['date_minute_to']		= $section['publ_start'] > 0 ? date('i', $section['publ_end'] ) : '';
					// ====================================================== 
					// ! Include the module and add it to the output buffer   
					// ====================================================== 
					ob_start();
						require(CAT_PATH.'/modules/'.$module.'/modify.php');
						$tpl_data['blocks'][$tpl_data['blocks_counter']]['content']			= ob_get_contents();
					//ob_end_clean();
                    ob_clean(); // allow multiple buffering for csrf-magic

					$tpl_data['blocks_counter']++;
				}
			}
		}
	}
}

// ==================== 
// ! Parse the site   
// ==================== 
$parser->output('backend_pages_modify.tpl', $tpl_data);

// ====================== 
// ! Print admin footer   
// ====================== 
$admin->print_footer();

?>