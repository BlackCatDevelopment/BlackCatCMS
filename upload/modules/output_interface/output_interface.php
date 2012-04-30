<?php
/**
 * outputInterface
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
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




if (!function_exists('output_interface')) {
	/**
	 * Calls registered Addons and enables them to filter the output 
	 * 
	 * @param STR $output
	 * @return mixed
	 */
	function output_interface($output) {
		global $database;
		$SQL = sprintf("SELECT * FROM %smod_output_interface", TABLE_PREFIX);
		if (false !== ($result = $database->query($SQL))) {
			while(false !== ($data = $result->fetchRow(MYSQL_ASSOC))) {
				if (file_exists(WB_PATH.'/modules/'.$data['module_directory'].'/output_interface.php')) {
					include(WB_PATH.'/modules/'.$data['module_directory'].'/output_interface.php');
					$user_func = $data['module_directory'].'_output_filter';
					if (function_exists($user_func)) {
						$output = call_user_func($user_func, $output);
					}
				}
			}
		}
		else {
			trigger_error(sprintf("[%s] %s", __FUNCTION__, $database->get_error()));
			return false;
		}
		return $output;
	} // outputInterface
}

if (!function_exists('register_output_filter')) {
	/**
	 * Register an Addon for the output filter
	 * 
	 * @param STR $module_directory
	 * @param STR $module_name
	 * @return BOOL
	 */
	function register_output_filter($module_directory, $module_name) {
		global $database;
		$SQL = sprintf("SELECT * FROM %smod_output_interface WHERE module_directory='%s'", TABLE_PREFIX, $module_directory);
		if (false !== ($data = $database->get_one($SQL, MYSQL_ASSOC))) {
			if (empty($data)) {
				$SQL = sprintf("INSERT INTO %smod_output_interface SET module_directory='%s', module_name='%s'", TABLE_PREFIX, $module_directory, $module_name);
				if (!$database->query($SQL)) {
					trigger_error(sprintf("[%s] %s", __FUNCTION__, $database->get_error()));
					return false;
				}
			}
		}
		else {
			trigger_error(sprintf("[%s] %s", __FUNCTION__, $database->get_error()));
			return false;
		}
		return true;
	} // register_output_filter()
	
}

if (!function_exists('unregister_output_filter')) {
	/**
	 * Unregister an Addon from the output filter
	 * 
	 * @param STR $module_directory
	 * @return BOOL
	 */
	function unregister_output_filter($module_directory) {
		global $database;
		$SQL = sprintf("DELETE FROM %smod_output_interface WHERE module_directory='%s'", TABLE_PREFIX, $module_directory);
		if (!$database->query($SQL)) {
			trigger_error(sprintf('[%s] %s', __FUNCTION__, $database->get_error()));
			return false;
		}
		return true;
	} // unregister_output_filter()
}

?>