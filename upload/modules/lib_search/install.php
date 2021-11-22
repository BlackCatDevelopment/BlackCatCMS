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
 *   @copyright       2016, Black Cat Development
 *   @link            https://blackcat-cms.org
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
	while (($level < 10) && (!file_exists($root.'framework/class.secure.php'))) {
		$root .= "../";
		$level += 1;
	}
	if (file_exists($root.'framework/class.secure.php')) {
		include($root.'framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}

$backend = CAT_Backend::getInstance('Addons','module_install',false,false);
$errors  = array();

$SQL = 'CREATE TABLE IF NOT EXISTS `:prefix:search` ('
    . ' `search_id` INT NOT NULL auto_increment,'
    . ' `name` VARCHAR(255) NOT NULL DEFAULT \'\' ,'
    . ' `value` TEXT NOT NULL ,'
    . ' `extra` TEXT NULL ,'
    . ' PRIMARY KEY (`search_id`) '
    . ' )';
if (!$backend->db()->query($SQL)) {
    $errors[] = sprintf('[CREATE TABLE] %s', $backend->db()->getError());
}

// delete existing configuration settings
$SQL = "DELETE FROM `:prefix:search` WHERE name='header' OR name='footer'"
    ." OR name='results_header' OR name='results_loop' OR name='results_footer'"
    ." OR name='no_results' OR name='cfg_enable_old_search' OR name='cfg_enable_flush'"
    ." OR name='module_order' OR name='max_excerpt' OR name='time_limit'"
    ." OR name='cfg_search_keywords' OR name='cfg_search_description'"
    ." OR name='cfg_search_non_public_content' OR name='cfg_show_description'"
    ." OR name='template' OR name='cfg_link_non_public_content'"
    ." OR name='cfg_search_images' OR name='cfg_thumbs_width' OR name='cfg_content_image'"
    ." OR name='cfg_search_library' OR name='cfg_search_droplet'"
    ." OR name='cfg_search_use_page_id'";
if (!$database->query($SQL)) {
    $errors[] = sprintf('[DELETE VALUES] %s', $backend->db()->getError());
}

// set default values for the CAT search_id
$backend->db()->query("INSERT INTO `:prefix:search` (name, value) VALUES ('module_order', 'wysiwyg')");
$backend->db()->query("INSERT INTO `:prefix:search` (name, value) VALUES ('max_excerpt', '15')");
$backend->db()->query("INSERT INTO `:prefix:search` (name, value) VALUES ('time_limit', '0')");
$backend->db()->query("INSERT INTO `:prefix:search` (name, value) VALUES ('cfg_search_keywords', 'true')");
$backend->db()->query("INSERT INTO `:prefix:search` (name, value) VALUES ('cfg_search_description', 'true')");
$backend->db()->query("INSERT INTO `:prefix:search` (name, value) VALUES ('cfg_search_non_public_content', 'false')");
$backend->db()->query("INSERT INTO `:prefix:search` (name, value) VALUES ('cfg_link_non_public_content', '')");
$backend->db()->query("INSERT INTO `:prefix:search` (name, value) VALUES ('cfg_show_description', 'true')");
$backend->db()->query("INSERT INTO `:prefix:search` (name, value) VALUES ('template', '')");
$backend->db()->query("INSERT INTO `:prefix:search` (name, value) VALUES ('cfg_search_images', 'true')");
$backend->db()->query("INSERT INTO `:prefix:search` (name, value) VALUES ('cfg_thumbs_width', '100')");
$backend->db()->query("INSERT INTO `:prefix:search` (name, value) VALUES ('cfg_content_image', 'first')");
$backend->db()->query("INSERT INTO `:prefix:search` (name, value) VALUES ('cfg_search_library', 'lib_search')");
$backend->db()->query("INSERT INTO `:prefix:search` (name, value) VALUES ('cfg_search_droplet', 'CAT_SearchResults')");
$backend->db()->query("INSERT INTO `:prefix:search` (name, value) VALUES ('cfg_search_use_page_id', '-1')");

// import droplets
$inst_dir   = CAT_Helper_Directory::sanitizePath(dirname(__FILE__).'/install');
$temp_unzip = CAT_Helper_Directory::sanitizePath(CAT_PATH.'/temp/unzip/' );
$dirh       = CAT_Helper_Directory::getInstance();
$files      = $dirh->getFiles($inst_dir);

if (is_array($files) && count($files)) {
    foreach($files as $file) {
        // ignore the result here
        CAT_Helper_Droplet::installDroplet( $file, $temp_unzip );
    }
}

if (count($errors)) $backend->print_error(implode('<br />',$errors));
