<?php

/**
 *  @module         TinyMCE-jQ
 *  @version        see info.php of this module
 *  @authors        erpe, Dietrich Roland Pehlke (Aldus)
 *  @copyright      2010-2011 erpe, Dietrich Roland Pehlke (Aldus)
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 *
 *  Please Notice: TINYMCE is distibuted under the <a href="http://tinymce.moxiecode.com/license.php">(LGPL) License</a> 
 *                 Ajax Filemanager is distributed under the <a href="http://www.gnu.org/licenses/gpl.html)">GPL </a> and <a href="http://www.mozilla.org/MPL/MPL-1.1.html">MPL</a> open source licenses 
 *
 */

// Include the config file
require_once('../../../../../config.php');

if ( (!isset($_SESSION['TINY_MCE_INIT'])) && (!isset($_SERVER['HTTP_REFERER'])) ) die();

// Create new admin object
require_once(WB_PATH.'/framework/class.admin.php');
$admin = new admin('Pages', 'pages_modify', false, false);

// load language file by actuelly wb language
$lang = strtolower(LANGUAGE).'.php';
$plugin_url = WB_URL.'/modules/tiny_mce_jq/tiny_mce/plugins/pagelink';
$plugin_path = WB_PATH.'/modules/tiny_mce_jq/tiny_mce/plugins/pagelink';

require_once(
	file_exists($plugin_path.'/langs/'.$lang)
	? $plugin_path.'/langs/'.$lang
	: $plugin_path.'/langs/en.php'
);

// Setup the template
$template = new Template(WB_PATH.'/modules/tiny_mce_jq/tiny_mce/plugins/pagelink');
$template->set_file('page', 'pagelink.htt');
$template->set_block('page', 'main_block', 'main');

// Function to generate page list
function gen_page_list($parent) {
	global $template, $database, $admin;
	$get_pages = $database->query("SELECT * FROM ".TABLE_PREFIX."pages WHERE parent = '$parent'");
	while($page = $get_pages->fetchRow( MYSQL_ASSOC )) {
		// method page_is_visible was introduced with WB 2.7
		if(method_exists($admin, 'page_is_visible') && !$admin->page_is_visible($page))
			continue;
		$title = stripslashes($page['menu_title']);
		// Add leading -'s so we can tell what level a page is at
		$leading_dashes = '';
		for($i = 0; $i < $page['level']; $i++) {
			$leading_dashes .= '- ';
		}
		$template->set_var('TITLE', $leading_dashes.' '.$title);
		$template->set_var('LINK', '[wblink'.$page['page_id'].']');


		/**
			Note:
			WB charset defined in the template: pagelink.html will be overwritten
			Routine kept for now, maybe it is possible to define custom plugin charsets in a future FCK releases (doc)
		*/
		// work out the specified WB charset 
		if(defined('DEFAULT_CHARSET')) { 
			$template->set_var('CHARSET', DEFAULT_CHARSET);
		} else {
			$template->set_var('CHARSET', 'utf-8');
		}
		$template->parse('page_list', 'page_list_block', true);
		gen_page_list($page['page_id']);
	}
}

// Get pages and put them into the pages list
$template->set_block('main_block', 'page_list_block', 'page_list');
$database = new database();
$get_pages = $database->query("SELECT * FROM ".TABLE_PREFIX."pages WHERE parent = '0'");
if($get_pages->numRows() > 0) {
	// Loop through pages
	$first = true;
	while($page = $get_pages->fetchRow( MYSQL_ASSOC )) {
		// method page_is_visible was introduced with WB 2.7
		if(method_exists($admin, 'page_is_visible') && !$admin->page_is_visible($page))
			continue;
		$title = stripslashes($page['menu_title']);
		$template->set_var('TITLE', $title);
		$template->set_var('LINK', '[wblink'.$page['page_id'].']');
		/**
		 *	Make sure that even one is pre-selected
		 *
		 */
		if ( true === $first ) {
			$template->set_var('SELECTED', ' selected="selected" ');
			$first = false;
		} else {
			$template->set_var('SELECTED', '');
		}
		$template->parse('page_list', 'page_list_block', true);
		gen_page_list($page['page_id']);
	}
} else {
	$template->set_var('TITLE', 'None found');
	$template->set_var('LINK', 'None found');
	$template->parse('page_list', 'page_list_block', false);
}
	$template->set_var(array(
            'pagelinkDlgTitle' => $pagelinkDlgTitle,
            'pagelinklblInsert' => $pagelinklblInsert,
            'pagelinklblCancel' => $pagelinklblCancel,
            'pagelinklblPageSelection' => $pagelinklblPageSelection,
         ) );

// Parse the template object
$template->parse('main', 'main_block', false);
$template->pparse('output', 'page');

?>