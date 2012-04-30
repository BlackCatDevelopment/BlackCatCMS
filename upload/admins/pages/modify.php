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



// Get page id
if(!isset($_GET['page_id']) || !is_numeric($_GET['page_id'])) {
	header("Location: index.php");
	exit(0);
} else {
	$page_id = $_GET['page_id'];
}

require_once(WB_PATH.'/framework/class.admin.php');

$admin = new admin('Pages', 'pages_modify');

// Get perms
if(!$admin->get_page_permission($page_id,'admin')) {
	$admin->print_error($MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
}

$sectionId = isset($_GET['wysiwyg']) ? htmlspecialchars($admin->get_get('wysiwyg')) : NULL;

// Get page details
$results_array=$admin->get_page_details($page_id);

// Get display name of person who last modified the page
$user=$admin->get_user_details($results_array['modified_by']);

// Convert the unix ts for modified_when to human a readable form

$modified_ts = ($results_array['modified_when'] != 0)
        ? $modified_ts = date(TIME_FORMAT.', '.DATE_FORMAT, $results_array['modified_when'])
        : 'Unknown';

// Include page info script
$template = new Template(THEME_PATH.'/templates');
$template->set_file('page', 'pages_modify.htt');
$template->set_block('page', 'main_block', 'main');

$template->set_var(array(
			'PAGE_ID' => $results_array['page_id'],
			'PAGE_TITLE' => ($results_array['page_title']),
			'MENU_TITLE' => ($results_array['menu_title']),
			'ADMIN_URL' => ADMIN_URL,
			'WB_URL' => WB_URL,
			'WB_PATH' => WB_PATH,
			'THEME_URL' => THEME_URL
			));

$template->set_var(array(
			'MODIFIED_BY' => $user['display_name'],
			'MODIFIED_BY_USERNAME' => $user['username'],
			'MODIFIED_WHEN' => $modified_ts,
			'LAST_MODIFIED' => $MESSAGE['PAGES_LAST_MODIFIED'],
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

// Work-out if we should show the "manage sections" link
$sql  = 'SELECT `section_id` FROM `'.TABLE_PREFIX.'sections` WHERE `page_id` = '.(int)$page_id.' ';
$sql .= 'AND `module` = "menu_link"';
$query_sections = $database->query($sql);

$template->set_block('main_block', 'show_section_block', 'show_section');
if($query_sections->numRows() > 0)
{
	$template->set_block('show_section', '');
	$template->set_var('DISPLAY_MANAGE_SECTIONS', 'display:none;');

} elseif(MANAGE_SECTIONS == 'enabled')
{

	$template->set_var('TEXT_MANAGE_SECTIONS', $HEADING['MANAGE_SECTIONS']);
    $template->parse('show_section', 'show_section_block', true);

} else {
	$template->set_block('show_section', '');
	$template->set_var('DISPLAY_MANAGE_SECTIONS', 'display:none;');

}

// Insert language TEXT
$template->set_var(array(
				'TEXT_CURRENT_PAGE' => $TEXT['CURRENT_PAGE'],
				'TEXT_CHANGE_SETTINGS' => $TEXT['CHANGE_SETTINGS'],
				'HEADING_MODIFY_PAGE' => $HEADING['MODIFY_PAGE']
				));

// Parse and print header template
$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');

// get template used for the displayed page (for displaying block details)
if (SECTION_BLOCKS)
{
	$sql = "SELECT `template` from `" . TABLE_PREFIX . "pages` WHERE `page_id` = '$page_id' ";
	$result = $database->query($sql);
	if ($result && $result->numRows() == 1) {
		$row = $result->fetchRow();
		$page_template = ($row['template'] == '') ? DEFAULT_TEMPLATE : $row['template'];
		// include template info file if exists
		if (file_exists(WB_PATH . '/templates/' . $page_template . '/info.php'))
		{
			include_once(WB_PATH . '/templates/' . $page_template . '/info.php');
		}
	}
}

// Get sections for this page
$module_permissions = $_SESSION['MODULE_PERMISSIONS'];

$sql = 'SELECT `section_id`, `module`, `block`, `name` FROM `'.TABLE_PREFIX.'sections`';
$sql .= ' WHERE `page_id` = '.intval($page_id);
$sql .= ' ORDER BY position ASC';

$query_sections = $database->query($sql);
if($query_sections->numRows() > 0)
{
	while($section = $query_sections->fetchRow( MYSQL_ASSOC ))
    {
		$section_id = $section['section_id'];
		$module = $section['module'];
		//Have permission?
		if(!is_numeric(array_search($module, $module_permissions)))
        {
			// Include the modules editing script if it exists
			if(file_exists(WB_PATH.'/modules/'.$module.'/modify.php'))
            {
				// output block name if blocks are enabled
				if (SECTION_BLOCKS) {
					if (isset($block[$section['block']]) && trim(strip_tags(($block[$section['block']]))) != '')
                    {
						$block_name = htmlentities(strip_tags($block[$section['block']]));
					} else {
						if ($section['block'] == 1)
                        {
							$block_name = $TEXT['MAIN'];
						} else {
							$block_name = '#' . (int) $section['block'];
						}
					}
					$html  = '<div id="'.SEC_ANCHOR.$section['section_id'].'"><b>' . $TEXT['BLOCK'] . ': </b>' . $block_name;
					$html .= '<b>  Modul: </b>' . $section['module']." ";
					$html .= '<b>  ID: </b>' . $section_id." ";
					$html .= '<b>  NAME: </b>' . $section['name']."</div>\n";
					
					echo $html;
				} else {
					echo "\n<div id=\"".SEC_ANCHOR.$section_id."\" >&nbsp;</div>\n";
				}
				require(WB_PATH.'/modules/'.$module.'/modify.php');
			}
		}
	}
}

// Print admin footer
$admin->print_footer();

?>