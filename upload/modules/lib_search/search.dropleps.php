<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
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
 * @link          http://www.lepton-cms.org
 * @license       http://www.gnu.org/licenses/gpl.html
 * @version       $Id: search.constants.php 1526 2011-12-27 05:12:34Z phpmanufaktur $
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('LEPTON_PATH')) {	
	include(LEPTON_PATH.'/framework/class.secure.php'); 
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

/**
 * Register a DropLEP with $droplep_name, place at the page with the $page_id
 * and hosted at $module_directory for the LEPTON search.
 * 
 * The LEPTON search will look for a search.php in $module_directory
 * 
 * @param string $droplep_name
 * @param integer $page_id
 * @param string $module_directory
 * @return boolean
 */
function register_droplep_for_search($droplep_name, $page_id, $module_directory) {
    global $database;
    
    $SQL = sprintf("SELECT * FROM %ssearch WHERE name='droplep' AND value='%s'", TABLE_PREFIX, $droplep_name);
    if (false === ($query = $database->query($SQL))) {
        trigger_error('[ %s ] %s', __FUNCTION__, $database->get_error());
    }
    while (false !== ($droplep = $query->fetchRow(MYSQL_ASSOC))) {
        $value = unserialize($droplep['extra']);
        if (isset($value['page_id']) && ($value['page_id'] == $page_id)) {
            // the DropLEP is already registered for this page_id
            return true;
        }
    }
    // DropLEP is not registered
    $module_directory = str_replace(array('/','\\'), '', $module_directory);
    if (!file_exists(LEPTON_PATH.'/modules/'.$module_directory.'/search.php')) return false;
    $SQL = sprintf("INSERT INTO %ssearch (name, value, extra) VALUES ('droplep', '%s', '%s')",
        TABLE_PREFIX,
        $droplep_name,
        serialize(array('page_id' => $page_id, 'module_directory' => $module_directory)));
    if (!$database->query($SQL)) {
        trigger_error('[ %s ] %s', __FUNCTION__, $database->get_error());
    }
    return true;
} // register_droplep_for_search()

/**
 * Remove the DropLEP $droplep_name for the page with the $page_id from the
 * LEPTON search.
 *  
 * @param string $droplep_name
 * @param integer $page_id
 */
function unregister_droplep_for_search($droplep_name, $page_id) {
    global $database;
    $SQL = sprintf("SELECT * FROM %ssearch WHERE name='droplep' AND value='%s'", TABLE_PREFIX, $droplep_name);
    if (false === ($query = $database->query($SQL))) {
        trigger_error('[ %s ] %s', __FUNCTION__, $database->get_error());
    }
    while (false !== ($droplep = $query->fetchRow(MYSQL_ASSOC))) {
        $value = unserialize($droplep['extra']);
        if (isset($value['page_id']) && ($value['page_id'] == $page_id)) {
            // the DropLEP is registered for this page_id
            $SQL = sprintf("DELETE FROM %ssearch WHERE search_id='%s'", TABLE_PREFIX, $droplep['search_id']);
            if (!$database->query($SQL)) {
                trigger_error('[ %s ] %s', __FUNCTION__, $database->get_error());
            }
            return true;
        }
    }
    return true;
} // unregister_droplep_for_search()

/**
 * Check if a DropLEP is registered for the LEPTON search or not
 * 
 * @param string $droplep_name
 * @return boolean true on success
 */
function is_droplep_registered_for_search($droplep_name) { 
   global $database;
   $SQL = sprintf("SELECT * FROM %ssearch WHERE name='droplep' AND value='%s'", TABLE_PREFIX, $droplep_name);
   if (false === ($query = $database->query($SQL))) {
       trigger_error('[ %s ] %s', __FUNCTION__, $database->get_error());
   }
   if ($query->numRows() > 0) return true;
   return false; 
} // is_droplet_registered_for_search()

/**
 * Return an array with all PAGE_IDs the DropLEP is registered for the LEPTON search
 * 
 * @param string $droplep_name
 * @return array with PAGE_IDs
 */
function get_droplep_page_ids_for_search($droplep_name) {
    global $database;
    $SQL = sprintf("SELECT * FROM %ssearch WHERE name='droplep' AND value='%s'", TABLE_PREFIX, $droplep_name);
    if (false === ($query = $database->query($SQL))) {
        trigger_error('[ %s ] %s', __FUNCTION__, $database->get_error());
    }
    $page_ids = array();
    while (false !== ($droplep = $query->fetchRow(MYSQL_ASSOC))) {
        $value = unserialize($droplep['extra']);
        if (isset($value['page_id'])) $page_ids[] = $value['page_id'];
    }
    return $page_ids;  
} // get_droplep_page_ids_for_search()