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
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         lib_search
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


// Required page details
$page_id = 0;
$page_description = '';
$page_keywords = '';

$h = CAT_Helper_Droplet::getInstance();

$h->register_droplet_css('SearchBox',$page_id,'/modules/lib_search/templates/default/','search.box.css');
$h->register_droplet_js('SearchBox',$page_id,'/modules/lib_search/templates/default/','search.box.js');
CAT_Helper_I18n::getInstance()->addFile(LANGUAGE.'.php', CAT_PATH.'/modules/lib_search/languages/');

define('PAGE_ID', 0);
define('ROOT_PARENT', 0);
define('PARENT', 0);
define('LEVEL', 0);
define('PAGE_TITLE', $h->lang()->translate('Search'));
define('MENU_TITLE', $h->lang()->translate('Search'));
define('MODULE', '');
define('VISIBILITY', 'public');
define('PAGE_CONTENT', CAT_PATH.'/modules/'.SEARCH_LIBRARY.'/index.php');

// Find out what the search template is
$query_template = $h->db()->query(sprintf(
    "SELECT `value`  FROM `%ssearch` WHERE `name`='template' LIMIT 1",
    CAT_TABLE_PREFIX
));
$fetch_template = $query_template->fetchRow(MYSQL_ASSOC);
$template       = $fetch_template['value'];
if ($template != '')
	define('TEMPLATE', $template);
unset($template);

// Get the referrer page ID if it exists
if (isset($_REQUEST['referrer']) && is_numeric($_REQUEST['referrer']) && intval($_REQUEST['referrer']) > 0)
	define('REFERRER_ID', intval($_REQUEST['referrer']));
else
	define('REFERRER_ID', 0);

// Include index (wrapper) file
require (CAT_PATH . '/index.php');
