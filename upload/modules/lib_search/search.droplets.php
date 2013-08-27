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


/**
 * Register a Droplet with $droplet_name, place at the page with the $page_id
 * and hosted at $module_directory for the LEPTON search.
 * 
 * The LEPTON search will look for a search.php in $module_directory
 * 
 * @param string $droplet_name
 * @param integer $page_id
 * @param string $module_directory
 * @return boolean
 */
function register_droplet_for_search($droplet_name, $page_id, $module_directory) {
    global $database;
    
    $SQL = sprintf("SELECT * FROM %ssearch WHERE name='droplet' AND value='%s'", CAT_TABLE_PREFIX, $droplet_name);
    if (false === ($query = $database->query($SQL))) {
        trigger_error('[ %s ] %s', __FUNCTION__, $database->get_error());
    }
    while (false !== ($droplet = $query->fetchRow(MYSQL_ASSOC))) {
        $value = unserialize($droplet['extra']);
        if (isset($value['page_id']) && ($value['page_id'] == $page_id)) {
            // the Droplet is already registered for this page_id
            return true;
        }
    }
    // Droplet is not registered
    $module_directory = str_replace(array('/','\\'), '', $module_directory);
    if (!file_exists(CAT_PATH.'/modules/'.$module_directory.'/search.php')) return false;
    $SQL = sprintf("INSERT INTO %ssearch (name, value, extra) VALUES ('droplet', '%s', '%s')",
        CAT_TABLE_PREFIX,
        $droplet_name,
        serialize(array('page_id' => $page_id, 'module_directory' => $module_directory)));
    if (!$database->query($SQL)) {
        trigger_error('[ %s ] %s', __FUNCTION__, $database->get_error());
    }
    return true;
} // register_droplet_for_search()

/**
 * Remove the Droplet $droplet_name for the page with the $page_id from the
 * LEPTON search.
 *  
 * @param string $droplet_name
 * @param integer $page_id
 */
function unregister_droplet_for_search($droplet_name, $page_id) {
    global $database;
    $SQL = sprintf("SELECT * FROM %ssearch WHERE name='droplet' AND value='%s'", CAT_TABLE_PREFIX, $droplet_name);
    if (false === ($query = $database->query($SQL))) {
        trigger_error('[ %s ] %s', __FUNCTION__, $database->get_error());
    }
    while (false !== ($droplet = $query->fetchRow(MYSQL_ASSOC))) {
        $value = unserialize($droplet['extra']);
        if (isset($value['page_id']) && ($value['page_id'] == $page_id)) {
            // the Droplet is registered for this page_id
            $SQL = sprintf("DELETE FROM %ssearch WHERE search_id='%s'", CAT_TABLE_PREFIX, $droplet['search_id']);
            if (!$database->query($SQL)) {
                trigger_error('[ %s ] %s', __FUNCTION__, $database->get_error());
            }
            return true;
        }
    }
    return true;
} // unregister_droplet_for_search()

/**
 * Check if a Droplet is registered for the LEPTON search or not
 * 
 * @param string $droplet_name
 * @return boolean true on success
 */
function is_droplet_registered_for_search($droplet_name) {
   global $database;
   $SQL = sprintf("SELECT * FROM %ssearch WHERE name='droplet' AND value='%s'", CAT_TABLE_PREFIX, $droplet_name);
   if (false === ($query = $database->query($SQL))) {
       trigger_error('[ %s ] %s', __FUNCTION__, $database->get_error());
   }
   if ($query->numRows() > 0) return true;
   return false; 
} // is_droplet_registered_for_search()

/**
 * Return an array with all PAGE_IDs the Droplet is registered for the LEPTON search
 * 
 * @param string $droplet_name
 * @return array with PAGE_IDs
 */
function get_droplet_page_ids_for_search($droplet_name) {
    global $database;
    $SQL = sprintf("SELECT * FROM %ssearch WHERE name='droplet' AND value='%s'", CAT_TABLE_PREFIX, $droplet_name);
    if (false === ($query = $database->query($SQL))) {
        trigger_error('[ %s ] %s', __FUNCTION__, $database->get_error());
    }
    $page_ids = array();
    while (false !== ($droplet = $query->fetchRow(MYSQL_ASSOC))) {
        $value = unserialize($droplet['extra']);
        if (isset($value['page_id'])) $page_ids[] = $value['page_id'];
    }
    return $page_ids;  
} // get_droplet_page_ids_for_search()