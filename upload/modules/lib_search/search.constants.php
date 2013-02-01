<?php

/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author        WebsiteBaker Project        
 * @author        LEPTON Project
 * @author        Ralf Hertsch <rh@lepton-cms.org>
 * @copyright     2004 - 2010 WebsiteBaker Project
 * @copyright     since 2011 LEPTON Project
 * @link          http://blackcat-cms.org
 * @license       http://www.gnu.org/licenses/gpl.html
 * @version       $Id$
 */

// include class.secure.php to protect this file and the whole CMS!
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
// end include class.secure.php

// Constants for REQUESTs
define('REQUEST_SEARCH_PATH', 'search_path');
define('REQUEST_SEARCH_LANG', 'search_lang');
define('REQUEST_SEARCH_TYPE', 'match');
define('REQUEST_SEARCH_STRING', 'string');

// Constants for the search type
define('SEARCH_TYPE_ALL', 'all');
define('SEARCH_TYPE_ANY', 'any');
define('SEARCH_TYPE_EXACT', 'exact');
define('SEARCH_TYPE_IMAGE', 'image');

// constants for the content image
define('CONTENT_IMAGE_NONE', 'none');
define('CONTENT_IMAGE_FIRST', 'first');
define('CONTENT_IMAGE_LAST', 'last');
define('CONTENT_IMAGE_RANDOM', 'random');

// Constants for access to the search settings
define('CFG_SEARCH_MODULE_ORDER', 'module_order');
define('CFG_SEARCH_MAX_EXCERPTS', 'max_excerpt');
define('CFG_SEARCH_SHOW_DESCRIPTIONS', 'cfg_show_description');
define('CFG_SEARCH_DESCRIPTIONS', 'cfg_search_description');
define('CFG_SEARCH_KEYWORDS', 'cfg_search_keywords');
define('CFG_SEARCH_NON_PUBLIC_CONTENT', 'cfg_search_non_public_content');
define('CFG_SEARCH_LINK_NON_PUBLIC_CONTENT', 'cfg_link_non_public_content');
define('CFG_SEARCH_TIME_LIMIT', 'time_limit');
define('CFG_SEARCH_IMAGES', 'cfg_search_images');
define('CFG_THUMBS_WIDTH', 'cfg_thumbs_width');
define('CFG_CONTENT_IMAGE', 'cfg_content_image');
define('CFG_SEARCH_LIBRARY', 'cfg_search_library');
define('CFG_SEARCH_DROPLEP', 'cfg_search_droplep');
define('CFG_SEARCH_USE_PAGE_ID', 'cfg_search_use_page_id');

// $_SESSION constants
define('SESSION_SEARCH_RESULT_ITEMS', 'search_result_items');
define('SESSION_SEARCH_NON_PUBLIC_CONTENT', 'search_non_public_content');
define('SESSION_SEARCH_LINK_NON_PUBLIC_CONTENT', 'link_non_public_content');