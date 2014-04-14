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
$page_id          = -1;
$page_description = '';
$page_keywords    = '';

// load search library
require_once CAT_PATH.'/modules/'.SEARCH_LIBRARY.'/library.php';
$s       = new CATSearch();
$page_id = $s->getSearchPageID();

// load droplets extensions
$h = CAT_Helper_Droplet::getInstance();
$h->register_droplet_css('SearchBox',$page_id,'/modules/'.SEARCH_LIBRARY.'/templates/default/','search.box.css');
$h->register_droplet_js('SearchBox',$page_id,'/modules/'.SEARCH_LIBRARY.'/templates/default/','search.box.js');

if(isset($_GET['string']))
    CAT_Helper_Page::addCSS(CAT_URL.'/modules/'.SEARCH_LIBRARY.'/templates/default/frontend.css');

// add language file
CAT_Helper_I18n::getInstance()->addFile(LANGUAGE.'.php', CAT_PATH.'/modules/'.SEARCH_LIBRARY.'/languages/');

// add template search path
global $parser;
$parser->setPath(CAT_PATH.'/modules/'.SEARCH_LIBRARY.'/templates/custom');
$parser->setFallbackPath(CAT_PATH.'/modules/'.SEARCH_LIBRARY.'/templates/default');

define('PAGE_CONTENT', CAT_PATH.'/modules/'.SEARCH_LIBRARY.'/index.php');

// Get the referrer page ID if it exists
if (isset($_REQUEST['referrer']) && is_numeric($_REQUEST['referrer']) && intval($_REQUEST['referrer']) > 0)
	define('REFERRER_ID', intval($_REQUEST['referrer']));
else
	define('REFERRER_ID', 0);

// Include index (wrapper) file
require (CAT_PATH . '/index.php');