<?php

/**
 * This file is part of Black Cat CMS Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2010-2012, LEPTON Project
 * @link            http://blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 *
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

global $database;
global $admin;

$error = '';

$SQL = 'CREATE TABLE IF NOT EXISTS `'.CAT_TABLE_PREFIX.'search` ('
    . ' `search_id` INT NOT NULL auto_increment,'
    . ' `name` VARCHAR(255) NOT NULL DEFAULT \'\' ,'
    . ' `value` TEXT NOT NULL ,'
    . ' `extra` TEXT NOT NULL ,'
    . ' PRIMARY KEY (`search_id`) '
    . ' )';
if (!$database->query($SQL)) {
    $error .= sprintf('[CREATE TABLE] %s', $database->get_error());
}

// delete existing configuration settings
$SQL = "DELETE FROM `".CAT_TABLE_PREFIX."search` WHERE name='header' OR name='footer'"
    ." OR name='results_header' OR name='results_loop' OR name='results_footer'"
    ." OR name='no_results' OR name='cfg_enable_old_search' OR name='cfg_enable_flush'"
    ." OR name='module_order' OR name='max_excerpt' OR name='time_limit'"
    ." OR name='cfg_search_keywords' OR name='cfg_search_description'"
    ." OR name='cfg_search_non_public_content' OR name='cfg_show_description'"
    ." OR name='template' OR name='cfg_link_non_public_content'"
    ." OR name='cfg_search_images' OR name='cfg_thumbs_width' OR name='cfg_content_image'"
    ." OR name='cfg_search_library' OR name='cfg_search_droplep'"
    ." OR name='cfg_search_use_page_id'";
if (!$database->query($SQL)) {
    $error .= sprintf('[DELETE VALUES] %s', $database->get_error());
}

// set default values for the LEPTON search
$database->query("INSERT INTO `".CAT_TABLE_PREFIX."search` (name, value) VALUES ('module_order', 'wysiwyg')");
$database->query("INSERT INTO `".CAT_TABLE_PREFIX."search` (name, value) VALUES ('max_excerpt', '15')");
$database->query("INSERT INTO `".CAT_TABLE_PREFIX."search` (name, value) VALUES ('time_limit', '0')");
$database->query("INSERT INTO `".CAT_TABLE_PREFIX."search` (name, value) VALUES ('cfg_search_keywords', 'true')");
$database->query("INSERT INTO `".CAT_TABLE_PREFIX."search` (name, value) VALUES ('cfg_search_description', 'true')");
$database->query("INSERT INTO `".CAT_TABLE_PREFIX."search` (name, value) VALUES ('cfg_search_non_public_content', 'false')");
$database->query("INSERT INTO `".CAT_TABLE_PREFIX."search` (name, value) VALUES ('cfg_link_non_public_content', '')");
$database->query("INSERT INTO `".CAT_TABLE_PREFIX."search` (name, value) VALUES ('cfg_show_description', 'true')");
$database->query("INSERT INTO `".CAT_TABLE_PREFIX."search` (name, value) VALUES ('template', '')");
$database->query("INSERT INTO `".CAT_TABLE_PREFIX."search` (name, value) VALUES ('cfg_search_images', 'true')");
$database->query("INSERT INTO `".CAT_TABLE_PREFIX."search` (name, value) VALUES ('cfg_thumbs_width', '100')");
$database->query("INSERT INTO `".CAT_TABLE_PREFIX."search` (name, value) VALUES ('cfg_content_image', 'first')");
$database->query("INSERT INTO `".CAT_TABLE_PREFIX."search` (name, value) VALUES ('cfg_search_library', 'lib_search')");
$database->query("INSERT INTO `".CAT_TABLE_PREFIX."search` (name, value) VALUES ('cfg_search_droplep', 'LEPTON_SearchResults')");
$database->query("INSERT INTO `".CAT_TABLE_PREFIX."search` (name, value) VALUES ('cfg_search_use_page_id', '-1')");

// import dropleps
if (!class_exists('CAT_Helper_Directory')) {
    include_once CAT_PATH.'/framework/LEPTON/Helper/Directory.php';
}
if (!function_exists('dropleps_import')) {
    include_once CAT_PATH.'/modules/dropleps/include.php';
}
$inst_dir   = sanitize_path(dirname(__FILE__).'/install');
$temp_unzip = sanitize_path(CAT_PATH.'/temp/unzip/' );
$dirh       = new CAT_Helper_Directory();
$files      = $dirh->getFiles($inst_dir);

if (is_array($files) && count($files)) {
    foreach($files as $file) {
        // ignore the result here
        dropleps_import( $file, $temp_unzip );
    }
}

if (!empty($error)) $admin->print_error($error);
